import { formatDate, formatDateTime } from './format';
import type { MasterColumn, MasterOption, MasterRow } from './master-data';

/**
 * Aturan tampilan tabel master yang dipakai bersama halaman (SSR dan island
 * Vue), supaya sel yang dirender ulang di browser tidak berbeda dari render
 * server.
 */

/** Nilai sel tabel; sel kosong tampil sebagai "—". */
export function masterCellText(row: MasterRow, column: MasterColumn): string {
    const value = row[column.key];

    if (value === null || value === undefined || value === '') return '—';
    if (Array.isArray(value)) return value.length ? value.map(String).join(', ') : '—';
    if (column.format === 'boolean') return value ? 'Aktif' : 'Nonaktif';
    if (column.format === 'date') return formatDate(String(value));
    if (column.format === 'datetime') return formatDateTime(String(value));

    return String(value);
}

/**
 * Saring opsi dropdown master dari kata kunci pengguna (select2 legacy).
 * Pencocokan pada label, tidak peka huruf besar/kecil, dan spasi di ujung
 * diabaikan supaya mengetik " pt " tetap menemukan "PT Contoh".
 */
export function filterMasterOptions(options: MasterOption[], term: string): MasterOption[] {
    const query = term.trim().toLowerCase();

    return query ? options.filter((option) => option.label.toLowerCase().includes(query)) : options;
}

/** Jumlah halaman; minimal 1 agar penomoran tetap tampil saat tabel kosong. */
export function masterPageCount(total: number, perPage: number): number {
    return Math.max(1, Math.ceil(total / Math.max(1, perPage)));
}

/** Rentang baris yang sedang tampil, mis. `{ from: 26, to: 50 }`. */
export function masterRowRange(
    total: number,
    page: number,
    perPage: number,
): { from: number; to: number } {
    if (total <= 0) return { from: 0, to: 0 };

    return {
        from: page * perPage + 1,
        to: Math.min(total, (page + 1) * perPage),
    };
}

export type MasterListState = {
    search: string;
    sort: string;
    /** Indeks halaman (0 = halaman pertama). */
    page: number;
};

/**
 * Query string filter tabel: halaman pertama dan filter kosong tidak ditulis
 * supaya URL bersih dan bisa dibagikan (dipakai `history.replaceState`).
 */
export function masterQuery(state: MasterListState): URLSearchParams {
    const params = new URLSearchParams();

    if (state.search) params.set('q', state.search);
    if (state.sort) params.set('sort', state.sort);
    if (state.page > 0) params.set('page', String(state.page + 1));

    return params;
}
