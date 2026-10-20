/**
 * Pemetaan antara formulir hasil pemeriksaan (input HTML) dan payload
 * PostgREST. Satu baris hasil per peserta (`participant_id` unik), jadi
 * penyimpanan memakai upsert seperti `updateOrCreate` di
 * `App\Services\ParticipantService`.
 */

import type { ExamResultConfig } from './exams';

export type ResultFormValues = Record<string, string>;

export type ResultPayloadOptions = {
    participantId: number;
    employeeId?: number | null;
    selesai: boolean;
    clientId?: number | null;
    contractId?: number | null;
};

function pad(value: number): string {
    return String(value).padStart(2, '0');
}

/** Nilai `datetime-local` memakai waktu lokal browser, bukan ISO ber-zona. */
function datetimeInputValue(value: string): string {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value.slice(0, 16);
    }

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(
        date.getHours(),
    )}:${pad(date.getMinutes())}`;
}

function fieldInputValue(value: unknown, type?: string): string {
    if (value === null || value === undefined) return '';
    if (typeof value === 'boolean') return value ? 'true' : 'false';
    if (type === 'datetime') return datetimeInputValue(String(value));
    if (type === 'date') return String(value).slice(0, 10);

    return String(value);
}

/** Nilai awal formulir dari baris hasil yang sudah ada (atau kosong). */
export function resultFormValues(
    config: ExamResultConfig,
    row: Record<string, unknown> | null,
): ResultFormValues {
    const values: ResultFormValues = {};

    for (const field of config.fields) {
        values[field.key] = fieldInputValue(row?.[field.key], field.type);
    }

    return values;
}

/**
 * Payload upsert: teks kosong dikirim sebagai `null` agar kolom kembali kosong,
 * angka/boolean dikonversi sesuai tipe kolom, dan `datetime-local` dikirim
 * sebagai ISO supaya zona waktu browser yang dipakai, bukan zona server.
 */
export function resultPayload(
    config: ExamResultConfig,
    values: ResultFormValues,
    options: ResultPayloadOptions,
): Record<string, unknown> {
    const payload: Record<string, unknown> = {
        participant_id: options.participantId,
        employee_id: options.employeeId ?? null,
        selesai: options.selesai,
    };

    for (const field of config.fields) {
        const raw = (values[field.key] ?? '').trim();

        if (field.type === 'boolean') {
            payload[field.key] = raw === '' ? null : raw === 'true';
            continue;
        }

        if (raw === '') {
            payload[field.key] = null;
            continue;
        }

        if (field.type === 'number') {
            const numeric = Number(raw);
            payload[field.key] = Number.isFinite(numeric) ? numeric : null;
            continue;
        }

        if (field.type === 'datetime') {
            const date = new Date(raw);
            payload[field.key] = Number.isNaN(date.getTime()) ? null : date.toISOString();
            continue;
        }

        payload[field.key] = field.type === 'date' ? raw.slice(0, 10) : raw;
    }

    // Kebijakan RLS laboratoria memakai client_id + contract_id milik peserta.
    if (config.resource === 'laboratoria') {
        payload.client_id = options.clientId ?? null;
        payload.contract_id = options.contractId ?? null;
    }

    return payload;
}
