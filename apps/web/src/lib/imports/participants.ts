import { postgrestRows } from '../server-postgrest';
import { chunkArray } from './chunk';
import { isFormNumber } from './fields';
import type { ImportContext, ImportParticipant } from './types';

const PARTICIPANT_SELECT = 'id,code,name,gender,no_form,register_date,client_id,contract_id';

/**
 * Urutan kandidat: `register_date` terbaru menang bila ada kode/nomor formulir
 * ganda, lalu id terbesar. Urutan ini dipertahankan server (PostgREST) karena
 * pengambilan baris terbaik dilakukan dari daftar yang sudah terurut.
 */
const PARTICIPANT_ORDER = 'register_date.desc.nullslast,id.desc';

/**
 * Cara mencocokkan `NO_MCU` dari file: `code-or-form` menerima kode MCU maupun
 * nomor formulir, sedangkan `code` hanya kode MCU (`RadiologiImport` legacy).
 */
export type ParticipantMatch = 'code-or-form' | 'code';

/**
 * Batas kode per permintaan `in.(...)`. Import legacy memanggil satu query per
 * baris; query terhadap Supabase pooler memakan ±0,6 detik per permintaan,
 * sehingga file 300 baris butuh beberapa menit. Dengan batch, jumlah permintaan
 * turun ke belasan.
 */
export const PARTICIPANT_LOOKUP_CHUNK = 150;

/** Nilai teks untuk filter `in.(...)` PostgREST. */
export function inTextValues(values: string[]): string {
    return `in.(${values.map((value) => `"${value.replace(/\\/g, '\\\\').replace(/"/g, '\\"')}"`).join(',')})`;
}

/**
 * Kunci nomor formulir: nomor formulir bisa ditulis dengan nol di depan
 * (`0012`), jadi kedua sisi dinormalkan ke angka. Nilai di luar rentang aman
 * tidak dipakai agar tidak salah cocok karena pembulatan.
 */
function formKey(value: string): string | null {
    const number = Number(value);

    return Number.isSafeInteger(number) ? String(number) : null;
}

/**
 * Petakan kode dari file ke peserta terbaik. Baris masukan sudah terurut
 * `register_date` terbaru lebih dulu, sehingga kemunculan pertama sebuah kode
 * adalah peserta yang menang — sama dengan query `limit 1` per kode.
 *
 * Pada mode `code-or-form`, kode angka hanya dicocokkan ke `no_form` dan kode
 * lain hanya ke `code` (mengikuti `DoctorValidateImport` legacy). Mode `code`
 * memaksa pencocokan ke kolom `code` saja, seperti `RadiologiImport` legacy.
 */
export function matchParticipants(
    rows: ImportParticipant[],
    codes: string[],
    match: ParticipantMatch = 'code-or-form',
): Map<string, ImportParticipant> {
    const byCode = new Map<string, ImportParticipant>();
    const byForm = new Map<string, ImportParticipant>();

    for (const row of rows) {
        if (!byCode.has(row.code)) {
            byCode.set(row.code, row);
        }

        if (row.no_form !== null) {
            const key = formKey(String(row.no_form));

            if (key !== null && !byForm.has(key)) {
                byForm.set(key, row);
            }
        }
    }

    const found = new Map<string, ImportParticipant>();

    for (const code of codes) {
        const trimmed = code.trim();

        if (trimmed === '') continue;

        let participant: ImportParticipant | undefined;

        if (match === 'code' || !isFormNumber(trimmed)) {
            participant = byCode.get(trimmed);
        } else {
            const key = formKey(trimmed);

            participant = key === null ? undefined : byForm.get(key);
        }

        if (participant) {
            found.set(trimmed, participant);
        }
    }

    return found;
}

/**
 * Cari peserta untuk sekumpulan kode dalam beberapa permintaan, bukan satu
 * permintaan per baris. Scope client + kontrak ditempel eksplisit (bukan hanya
 * RLS) supaya admin pun tidak bisa menimpa peserta kontrak lain, sama seperti
 * import legacy yang memakai sesi MCU IN.
 */
export async function findParticipants(
    context: ImportContext,
    codes: string[],
    match: ParticipantMatch = 'code-or-form',
): Promise<Map<string, ImportParticipant>> {
    const unique = [...new Set(codes.map((code) => code.trim()).filter((code) => code !== ''))];

    if (unique.length === 0) {
        return new Map();
    }

    const rows: ImportParticipant[] = [];
    const byCode = match === 'code' ? unique : unique.filter((code) => !isFormNumber(code));
    const byForm = match === 'code' ? [] : unique.filter(isFormNumber);

    for (const chunk of chunkArray(byCode, PARTICIPANT_LOOKUP_CHUNK)) {
        rows.push(
            ...(await postgrestRows<ImportParticipant>(
                context.token,
                'participants',
                {
                    select: PARTICIPANT_SELECT,
                    client_id: `eq.${context.clientId}`,
                    contract_id: `eq.${context.contractId}`,
                    code: inTextValues(chunk),
                    order: PARTICIPANT_ORDER,
                },
                'public',
            )),
        );
    }

    for (const chunk of chunkArray(byForm, PARTICIPANT_LOOKUP_CHUNK)) {
        rows.push(
            ...(await postgrestRows<ImportParticipant>(
                context.token,
                'participants',
                {
                    select: PARTICIPANT_SELECT,
                    client_id: `eq.${context.clientId}`,
                    contract_id: `eq.${context.contractId}`,
                    no_form: `in.(${chunk.join(',')})`,
                    order: PARTICIPANT_ORDER,
                },
                'public',
            )),
        );
    }

    return matchParticipants(rows, unique, match);
}
