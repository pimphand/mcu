import { describe, expect, it } from 'vitest';
import { inTextValues, matchParticipants } from './participants';
import type { ImportParticipant } from './types';

function participant(overrides: Partial<ImportParticipant>): ImportParticipant {
    return {
        id: 1,
        code: 'MCU00001',
        name: 'Peserta',
        gender: 'L',
        no_form: 1,
        register_date: null,
        client_id: 1,
        contract_id: 1,
        ...overrides,
    };
}

describe('matchParticipants', () => {
    it('memakai baris terbaru (urutan register_date desc dari server)', () => {
        const rows = [
            participant({ id: 9, code: 'MCU00009', no_form: 9, register_date: '2026-10-19' }),
            participant({ id: 3, code: 'MCU00009', no_form: 3, register_date: '2026-01-01' }),
        ];

        const found = matchParticipants(rows, ['MCU00009']);

        expect(found.get('MCU00009')?.id).toBe(9);
    });

    it('mencocokkan kode angka ke nomor formulir, dan kode teks ke kolom code', () => {
        const rows = [
            participant({ id: 11, code: 'MCU00001', no_form: 17 }),
            participant({ id: 12, code: 'MCU00002', no_form: 18 }),
        ];

        const found = matchParticipants(rows, ['17', 'MCU00002', 'MCU00001']);

        expect(found.get('17')?.id).toBe(11);
        expect(found.get('MCU00002')?.id).toBe(12);
        expect(found.get('MCU00001')?.id).toBe(11);
        // Kode angka tidak dicocokkan ke kolom `code`, begitu pula sebaliknya.
        expect(found.has('MCU00017')).toBe(false);
    });

    it('menormalkan nomor formulir dengan nol di depan', () => {
        const rows = [participant({ id: 21, no_form: 12 })];

        expect(matchParticipants(rows, ['0012']).get('0012')?.id).toBe(21);
    });

    it('mengabaikan kode kosong dan kode tak dikenal', () => {
        const found = matchParticipants([participant({ id: 31 })], ['  ', 'TIDAK-ADA']);

        expect(found.size).toBe(0);
    });

    it('mode `code` hanya memakai kolom code, termasuk kode berupa angka', () => {
        const rows = [
            participant({ id: 41, code: '17', no_form: 900 }),
            participant({ id: 42, code: 'MCU00042', no_form: 42 }),
        ];

        const byCode = matchParticipants(rows, ['17', '42'], 'code');

        expect(byCode.get('17')?.id).toBe(41);
        expect(byCode.has('42')).toBe(false);
    });

    it('melewati nomor formulir di luar rentang aman', () => {
        const rows = [participant({ id: 51, no_form: 51 })];

        expect(matchParticipants(rows, ['99999999999999999999']).size).toBe(0);
    });
});

describe('inTextValues', () => {
    it('mengutip nilai dan melolosikan karakter khusus PostgREST', () => {
        expect(inTextValues(['MCU00001', 'A"B', 'C\\D'])).toBe('in.("MCU00001","A\\"B","C\\\\D")');
    });
});
