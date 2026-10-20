import { describe, expect, it } from 'vitest';
import { ageDetail, ageLabel, formatDate, formatDateTime, genderLabel, roleLabel } from './format';

describe('format helpers', () => {
    it('maps known roles and falls back to the raw role', () => {
        expect(roleLabel('admin')).toBe('Administrator');
        expect(roleLabel('client_admin')).toBe('Admin Client');
        expect(roleLabel('custom')).toBe('custom');
        expect(roleLabel(null)).toBe('Pengguna');
    });

    it('formats valid dates in Asia/Jakarta', () => {
        expect(formatDate('2026-10-17T00:00:00+07:00')).toContain('2026');
        expect(formatDateTime('invalid')).toBe('—');
    });

    it('maps gender codes and keeps unknown values readable', () => {
        expect(genderLabel('L')).toBe('Laki-laki');
        expect(genderLabel('P')).toBe('Perempuan');
        expect(genderLabel(null)).toBe('—');
        expect(genderLabel('X')).toBe('X');
    });

    it('counts full years and waits for the birthday to pass', () => {
        const now = new Date('2026-10-18T03:00:00Z');

        expect(ageLabel('2000-10-18', now)).toBe('26 tahun');
        expect(ageLabel('2000-10-19', now)).toBe('25 tahun');
        expect(ageLabel('1981-03-30', now)).toBe('45 tahun');
        expect(ageLabel('', now)).toBe('—');
        expect(ageLabel('2000-01-01', now)).toBe('26 tahun');
    });

    it('breaks the age into years, months, and days like the legacy print', () => {
        const now = new Date('2026-10-18T03:00:00Z');

        expect(ageDetail('1998-01-20', now)).toBe('28 tahun 8 bulan 28 hari');
        expect(ageDetail('2000-10-19', now)).toBe('25 tahun 11 bulan 29 hari');
        expect(ageDetail('2026-03-01', now)).toBe('0 tahun 7 bulan 17 hari');
        expect(ageDetail('', now)).toBe('—');
    });
});
