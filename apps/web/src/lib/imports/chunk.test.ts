import { describe, expect, it } from 'vitest';
import { chunkArray } from './chunk';

describe('chunkArray', () => {
    it('membagi daftar tepat pada batas ukuran potongan', () => {
        expect(chunkArray([1, 2, 3, 4, 5], 2)).toEqual([[1, 2], [3, 4], [5]]);
    });

    it('mengembalikan daftar kosong untuk masukan kosong', () => {
        expect(chunkArray([], 10)).toEqual([]);
    });

    it('menolak ukuran potongan tidak valid', () => {
        expect(() => chunkArray([1], 0)).toThrow(/minimal 1/);
    });
});
