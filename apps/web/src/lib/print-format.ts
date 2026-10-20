import { formatDate, formatDateTime } from './format';
import type { ResultFieldType } from './exams';

export function isEmptyResult(value: unknown): boolean {
    return value === null || value === undefined || value === '';
}

/**
 * Label gender pada cetakan legacy berasal dari data MySQL (`Laki-Laki`),
 * bukan kode `L`/`P` yang dipakai UI.
 */
export function printGender(value?: string | null): string {
    if (value === 'L') return 'Laki-Laki';
    if (value === 'P') return 'Perempuan';

    return value ?? '—';
}

/**
 * View legacy mencetak tanggal/timestamp apa adanya dari database
 * (`2026-10-12`, `2026-10-12 14:24:04`), bukan format lokal.
 */
export function legacyDate(value: string | null | undefined): string {
    if (!value) return '-';

    return value.slice(0, 10);
}

/**
 * Resume legacy mencetak tanggal dengan `format('d-m-Y')`, bukan nilai database
 * apa adanya seperti view hasil lainnya.
 */
export function legacyShortDate(value: string | null | undefined): string {
    if (!value) return '-';

    const [year, month, day] = value.slice(0, 10).split('-');

    return day && month && year ? `${day}-${month}-${year}` : value.slice(0, 10);
}

export function legacyDateTime(value: string | null | undefined): string {
    if (!value) return '-';

    return value.slice(0, 19).replace('T', ' ');
}

/** Nilai sel hasil untuk laporan; `null`/kosong dicetak sebagai `-` seperti legacy. */
export function displayResult(value: unknown, fieldType?: ResultFieldType): string {
    if (isEmptyResult(value)) return '-';
    if (typeof value === 'boolean') return value ? 'Ya' : 'Tidak';
    if (fieldType === 'date') return formatDate(String(value));
    if (fieldType === 'datetime') return formatDateTime(String(value));

    return String(value);
}
