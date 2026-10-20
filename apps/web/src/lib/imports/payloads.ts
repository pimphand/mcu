/**
 * Helper murni untuk import batch. Dipisahkan dari `importers.ts` supaya bisa
 * diuji tanpa PostgREST maupun konfigurasi environment.
 */

/** Satu baris upsert beserta kunci konfliknya (`participant_id` atau `client_id,no_mcu`). */
export type UpsertEntry = {
    conflictKey: string;
    payload: Record<string, unknown>;
};

/**
 * Buang baris dengan kunci konflik yang sama dalam satu permintaan batch:
 * PostgREST/PostgreSQL menolak upsert yang menyentuh baris yang sama dua kali
 * ("ON CONFLICT DO UPDATE command cannot affect row a second time"), sedangkan
 * upsert berurutan sebelumnya membiarkan baris terakhir menang. Perilaku itu
 * dipertahankan di sini.
 */
export function dedupeByConflictKey(entries: UpsertEntry[]): Record<string, unknown>[] {
    const byKey = new Map<string, Record<string, unknown>>();

    for (const entry of entries) {
        byKey.set(entry.conflictKey, entry.payload);
    }

    return [...byKey.values()];
}

/**
 * Kelompokkan id peserta per nilai kolom (mis. kesimpulan hasil MCU) supaya
 * pembaruan pemeriksaan fisik bisa dikirim sebagai satu PATCH per nilai,
 * bukan satu PATCH per peserta.
 */
export function groupIdsByValue(entries: { value: string; id: number }[]): Map<string, number[]> {
    const grouped = new Map<string, number[]>();

    for (const entry of entries) {
        const ids = grouped.get(entry.value);

        if (ids) {
            ids.push(entry.id);
        } else {
            grouped.set(entry.value, [entry.id]);
        }
    }

    return grouped;
}
