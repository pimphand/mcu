import { describe, expect, it } from 'vitest';
import { dedupeByConflictKey, groupIdsByValue } from './payloads';

describe('dedupeByConflictKey', () => {
    it('menyisakan baris terakhir per kunci konflik (perilaku upsert berurutan)', () => {
        const payloads = dedupeByConflictKey([
            { conflictKey: '700', payload: { participant_id: 700, cor: 'pertama' } },
            { conflictKey: '701', payload: { participant_id: 701, cor: 'lain' } },
            { conflictKey: '700', payload: { participant_id: 700, cor: 'terakhir' } },
        ]);

        expect(payloads).toEqual([
            { participant_id: 700, cor: 'terakhir' },
            { participant_id: 701, cor: 'lain' },
        ]);
    });

    it('meneruskan daftar kosong', () => {
        expect(dedupeByConflictKey([])).toEqual([]);
    });
});

describe('groupIdsByValue', () => {
    it('mengelompokkan id per nilai agar satu PATCH mewakili banyak peserta', () => {
        const grouped = groupIdsByValue([
            { value: 'SEHAT', id: 1 },
            { value: 'SEHAT', id: 2 },
            { value: 'PERLU KONTROL', id: 3 },
            { value: 'SEHAT', id: 4 },
        ]);

        expect([...grouped.entries()]).toEqual([
            ['SEHAT', [1, 2, 4]],
            ['PERLU KONTROL', [3]],
        ]);
    });
});
