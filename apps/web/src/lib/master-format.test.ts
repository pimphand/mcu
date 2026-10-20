import { describe, expect, it } from 'vitest';
import type { MasterColumn } from './master-data';
import { masterCellText, masterPageCount, masterQuery, masterRowRange } from './master-format';

const codeColumn: MasterColumn = { key: 'code', label: 'Kode' };
const activeColumn: MasterColumn = { key: 'is_active', label: 'Aktif', format: 'boolean' };
const createdColumn: MasterColumn = { key: 'created_at', label: 'Dibuat', format: 'datetime' };

describe('masterCellText', () => {
    it('menampilkan tanda pisah untuk sel kosong', () => {
        expect(masterCellText({ id: 1, code: null }, codeColumn)).toBe('—');
        expect(masterCellText({ id: 1, code: '' }, codeColumn)).toBe('—');
        expect(masterCellText({ id: 1 }, activeColumn)).toBe('—');
    });

    it('menerjemahkan kolom boolean dan mengubah non-boolean jadi teks', () => {
        expect(masterCellText({ id: 1, is_active: true }, activeColumn)).toBe('Aktif');
        expect(masterCellText({ id: 1, is_active: false }, activeColumn)).toBe('Nonaktif');
        expect(masterCellText({ id: 7, code: 12 }, codeColumn)).toBe('12');
    });

    it('memformat kolom tanggal pada zona Asia/Jakarta', () => {
        expect(
            masterCellText({ id: 1, created_at: '2026-01-02T03:40:00Z' }, createdColumn),
        ).toContain('2026');
        expect(masterCellText({ id: 1, created_at: null }, createdColumn)).toBe('—');
        expect(masterCellText({ id: 1, created_at: 'bukan-tanggal' }, createdColumn)).toBe('—');
    });
});

describe('masterPageCount', () => {
    it('membulatkan ke atas dan minimal satu halaman', () => {
        expect(masterPageCount(0, 25)).toBe(1);
        expect(masterPageCount(25, 25)).toBe(1);
        expect(masterPageCount(26, 25)).toBe(2);
    });

    it('aman saat perPage bukan angka wajar', () => {
        expect(masterPageCount(10, 0)).toBe(10);
    });
});

describe('masterRowRange', () => {
    it('menghitung rentang baris yang tampil', () => {
        expect(masterRowRange(0, 0, 25)).toEqual({ from: 0, to: 0 });
        expect(masterRowRange(120, 0, 25)).toEqual({ from: 1, to: 25 });
        expect(masterRowRange(120, 1, 25)).toEqual({ from: 26, to: 50 });
        expect(masterRowRange(30, 1, 25)).toEqual({ from: 26, to: 30 });
    });
});

describe('masterQuery', () => {
    it('tidak menulis filter default ke URL', () => {
        expect(masterQuery({ search: '', sort: '', page: 0 }).toString()).toBe('');
    });

    it('menulis filter aktif dengan nomor halaman satu-basis', () => {
        expect(masterQuery({ search: 'acs', sort: 'name.asc', page: 2 }).toString()).toBe(
            'q=acs&sort=name.asc&page=3',
        );
    });
});
