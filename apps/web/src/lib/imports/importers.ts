import { inFilter, postgrestSend } from '../server-postgrest';
import { chunkArray } from './chunk';
import { GENERAL_FIELDS, RADIOLOGI_FIELDS, VALIDATE_FIELDS, fieldValue, readCode } from './fields';
import { EmployeeResolver } from './employees';
import { LAB_FIELDS } from './lab-fields';
import { findParticipants, type ParticipantMatch } from './participants';
import { dedupeByConflictKey, groupIdsByValue, type UpsertEntry } from './payloads';
import type { FieldSpec } from './types';
import type { ImportContext, ImportOutcome, ImportParticipant, ImportType } from './types';
import { cellTextOrNull, parseDateValue, parseTimestampValue } from './values';
import type { ImportRow } from './workbook';

/**
 * Baris per permintaan batch. Query ke Supabase pooler memakan ±0,6 detik per
 * permintaan, jadi ukuran ini menentukan berapa lama import berjalan: 300 baris
 * radiologi awalnya ±6 menit (satu permintaan per baris) dan menjadi beberapa
 * detik setelah dibatch.
 */
const UPSERT_CHUNK_SIZE = 200;

/** Batas id per PATCH massal `participant_id=in.(...)`. */
const PATCH_CHUNK_SIZE = 200;

function emptyOutcome(): ImportOutcome {
    return { processed: 0, skipped: 0, skippedCodes: [], createdEmployees: [] };
}

function scopePayload(participant: ImportParticipant): Record<string, unknown> {
    return {
        client_id: participant.client_id,
        contract_id: participant.contract_id,
    };
}

function auditPayload(context: ImportContext): Record<string, unknown> {
    return { created_by: context.userId, updated_by: context.userId };
}

/**
 * Upsert massal: satu permintaan untuk banyak baris (`resolution=merge-duplicates`
 * dengan `on_conflict` yang sama seperti versi satu baris).
 */
async function bulkUpsert(
    context: ImportContext,
    resource: string,
    conflict: string,
    entries: UpsertEntry[],
): Promise<void> {
    const payloads = dedupeByConflictKey(entries);

    for (const batch of chunkArray(payloads, UPSERT_CHUNK_SIZE)) {
        await postgrestSend(
            context.token,
            resource,
            'POST',
            batch,
            { on_conflict: conflict },
            'resolution=merge-duplicates,return=minimal',
        );
    }
}

/**
 * Kumpulkan kode peserta dari seluruh baris, lalu ambil pesertanya sekaligus.
 * Hasil pemetaan dipakai semua importer supaya tidak ada lagi satu query
 * peserta per baris.
 */
async function lookupRows(
    context: ImportContext,
    rows: ImportRow[],
    codeSpec: FieldSpec,
    match: ParticipantMatch,
): Promise<Map<string, ImportParticipant>> {
    return findParticipants(
        context,
        rows.map((row) => readCode(row, codeSpec)),
        match,
    );
}

/** Import hasil laboratorium (upsert satu baris per peserta). */
export async function importLaboratorium(
    context: ImportContext,
    rows: ImportRow[],
): Promise<ImportOutcome> {
    const outcome = emptyOutcome();
    const resolver = new EmployeeResolver(context);
    const fields = LAB_FIELDS;
    const codeSpec = fields[0]!;
    const examinerSpec = fields[fields.length - 2]!;
    const participants = await lookupRows(context, rows, codeSpec, 'code-or-form');
    const entries: UpsertEntry[] = [];

    for (const row of rows) {
        const code = readCode(row, codeSpec);

        if (!code) continue;

        const participant = participants.get(code);

        if (!participant) {
            outcome.skipped += 1;
            outcome.skippedCodes.push(code);
            continue;
        }

        const payload: Record<string, unknown> = {
            ...scopePayload(participant),
            ...auditPayload(context),
            participant_id: participant.id,
            form_no: participant.no_form,
            selesai: true,
            employee_id: await resolver.idFor(cellTextOrNull(fieldValue(row, examinerSpec))),
        };

        for (const spec of fields) {
            if (spec.column === 'code') continue;

            payload[spec.column] =
                spec.column === 'lab_date'
                    ? parseTimestampValue(fieldValue(row, spec))
                    : cellTextOrNull(fieldValue(row, spec));
        }

        entries.push({ conflictKey: String(participant.id), payload });
        outcome.processed += 1;
    }

    await bulkUpsert(context, 'laboratoria', 'participant_id', entries);
    outcome.createdEmployees = resolver.createdEmployees;

    return outcome;
}

/** Import radiologi. Legacy mencocokkan `NO_MCU` ke `participants.code` saja. */
export async function importRadiologi(
    context: ImportContext,
    rows: ImportRow[],
): Promise<ImportOutcome> {
    const outcome = emptyOutcome();
    const resolver = new EmployeeResolver(context);
    const [codeSpec, dateSpec, corSpec, sinusSpec, pulmoSpec, kesanSpec, examinerSpec] =
        RADIOLOGI_FIELDS;
    const participants = await lookupRows(context, rows, codeSpec!, 'code');
    const entries: UpsertEntry[] = [];

    for (const row of rows) {
        const code = readCode(row, codeSpec!);

        if (!code) continue;

        const participant = participants.get(code);

        if (!participant) {
            outcome.skipped += 1;
            outcome.skippedCodes.push(code);
            continue;
        }

        const examiner = cellTextOrNull(fieldValue(row, examinerSpec!));

        entries.push({
            conflictKey: String(participant.id),
            payload: {
                // Tabel radiologis tidak punya client_id; scope client dijaga
                // oleh RLS lewat relasi participant dan filter saat lookup.
                contract_id: participant.contract_id,
                ...auditPayload(context),
                participant_id: participant.id,
                date_mcu: parseDateValue(fieldValue(row, dateSpec!)),
                cor: cellTextOrNull(fieldValue(row, corSpec!)),
                diafragma_sinus: cellTextOrNull(fieldValue(row, sinusSpec!)),
                pulmo: cellTextOrNull(fieldValue(row, pulmoSpec!)),
                kesan: cellTextOrNull(fieldValue(row, kesanSpec!)),
                diperiksa: examiner,
                selesai: true,
                employee_id: await resolver.idFor(examiner),
            },
        });
        outcome.processed += 1;
    }

    await bulkUpsert(context, 'radiologis', 'participant_id', entries);
    outcome.createdEmployees = resolver.createdEmployees;

    return outcome;
}

/** Import validasi dokter + salin kesimpulan ke pemeriksaan fisik. */
export async function importValidateDoctor(
    context: ImportContext,
    rows: ImportRow[],
): Promise<ImportOutcome> {
    const outcome = emptyOutcome();
    const [dateSpec, codeSpec, nameSpec, genderSpec, resultSpec, notesSpec, doctorSpec] =
        VALIDATE_FIELDS;
    const participants = await lookupRows(context, rows, codeSpec!, 'code-or-form');
    const entries: UpsertEntry[] = [];
    const conclusions: { value: string; id: number }[] = [];

    for (const row of rows) {
        const code = readCode(row, codeSpec!);

        if (!code) continue;

        const participant = participants.get(code);

        if (!participant) {
            outcome.skipped += 1;
            outcome.skippedCodes.push(code);
            continue;
        }

        const resultMcu = cellTextOrNull(fieldValue(row, resultSpec!));

        entries.push({
            conflictKey: `${participant.client_id},${participant.code}`,
            payload: {
                client_id: participant.client_id,
                contract_id: participant.contract_id,
                no_mcu: participant.code,
                date_mcu: parseTimestampValue(fieldValue(row, dateSpec!)),
                name: cellTextOrNull(fieldValue(row, nameSpec!)) ?? participant.name,
                gender: cellTextOrNull(fieldValue(row, genderSpec!)) ?? participant.gender,
                result_mcu: resultMcu,
                doctor: cellTextOrNull(fieldValue(row, doctorSpec!)),
                notes: cellTextOrNull(fieldValue(row, notesSpec!)),
            },
        });

        if (resultMcu) {
            // Hanya menimpa pemeriksaan fisik yang sudah ada; peserta tanpa
            // pemeriksaan fisik tidak dibuatkan data palsu. Nilai kesimpulan
            // yang sama dikirim sebagai satu PATCH massal.
            conclusions.push({ value: resultMcu, id: participant.id });
        }

        outcome.processed += 1;
    }

    await bulkUpsert(context, 'validate_doctors', 'client_id,no_mcu', entries);

    for (const [kesimpulan, ids] of groupIdsByValue(conclusions)) {
        for (const batch of chunkArray(ids, PATCH_CHUNK_SIZE)) {
            const filter = inFilter(batch);

            if (!filter) continue;

            await postgrestSend(
                context.token,
                'pemeriksaan_fisiks',
                'PATCH',
                { kesimpulan },
                { participant_id: filter },
                'return=minimal',
            );
        }
    }

    return outcome;
}

/** Import hasil umum (upsert satu baris per peserta). */
export async function importGeneralResult(
    context: ImportContext,
    rows: ImportRow[],
): Promise<ImportOutcome> {
    const outcome = emptyOutcome();
    const [dateSpec, codeSpec, nameSpec, resultSpec] = GENERAL_FIELDS;
    const participants = await lookupRows(context, rows, codeSpec!, 'code-or-form');
    const entries: UpsertEntry[] = [];

    for (const row of rows) {
        const code = readCode(row, codeSpec!);

        if (!code) continue;

        const participant = participants.get(code);

        if (!participant) {
            outcome.skipped += 1;
            outcome.skippedCodes.push(code);
            continue;
        }

        entries.push({
            conflictKey: String(participant.id),
            payload: {
                ...scopePayload(participant),
                participant_id: participant.id,
                name: cellTextOrNull(fieldValue(row, nameSpec!)) ?? participant.name,
                mcu_id: participant.code,
                result: cellTextOrNull(fieldValue(row, resultSpec!)),
                mcu_date:
                    parseTimestampValue(fieldValue(row, dateSpec!)) ?? participant.register_date,
            },
        });
        outcome.processed += 1;
    }

    await bulkUpsert(context, 'general_results', 'participant_id', entries);

    return outcome;
}

export const IMPORTERS: Record<
    ImportType,
    (context: ImportContext, rows: ImportRow[]) => Promise<ImportOutcome>
> = {
    laboratorium: importLaboratorium,
    radiologi: importRadiologi,
    validate_doctor: importValidateDoctor,
    general_result: importGeneralResult,
};
