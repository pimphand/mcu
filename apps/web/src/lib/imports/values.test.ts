import { describe, expect, it } from 'vitest';
import {
    cellText,
    cellTextOrNull,
    normalizeHeader,
    parseDateValue,
    parseTimestampValue,
} from './values';

describe('parseDateValue', () => {
    it('reads Excel serial numbers and Date cells', () => {
        expect(parseDateValue(45572)).toBe('2024-10-07');
        expect(parseDateValue(new Date(Date.UTC(2024, 9, 7)))).toBe('2024-10-07');
    });

    it('reads the text formats used by legacy templates', () => {
        expect(parseDateValue('07-Oct-24')).toBe('2024-10-07');
        expect(parseDateValue('7 Oktober 2024')).toBe('2024-10-07');
        expect(parseDateValue('07/10/2024')).toBe('2024-10-07');
        expect(parseDateValue('2024-10-07')).toBe('2024-10-07');
        expect(parseDateValue('2024/10/07')).toBe('2024-10-07');
        expect(parseDateValue('20241007')).toBe('2024-10-07');
    });

    it('uses m/d order when the second number can only be a day', () => {
        expect(parseDateValue('10/07/2024')).toBe('2024-07-10');
    });

    it('rejects impossible dates and empty cells', () => {
        expect(parseDateValue('31/02/2024')).toBeNull();
        expect(parseDateValue('')).toBeNull();
        expect(parseDateValue(null)).toBeNull();
        expect(parseDateValue('bukan tanggal')).toBeNull();
    });

    it('turns a date into an Asia/Jakarta timestamp', () => {
        expect(parseTimestampValue('07-Oct-24')).toBe('2024-10-07T00:00:00+07:00');
    });
});

describe('cellText', () => {
    it('reads formulas, rich text, booleans, and numbers', () => {
        expect(cellText({ formula: 'A1', result: 'ok' })).toBe('ok');
        expect(cellText({ richText: [{ text: 'Hemoglobin' }, { text: ' 13' }] })).toBe(
            'Hemoglobin 13',
        );
        expect(cellText(true)).toBe('TRUE');
        expect(cellText(0)).toBe('0');
        expect(cellText('  nama  ')).toBe('nama');
        expect(cellTextOrNull('   ')).toBeNull();
    });
});

describe('normalizeHeader', () => {
    it('matches the legacy WithHeadingRow normalization', () => {
        expect(normalizeHeader('NO_MCU')).toBe('no_mcu');
        expect(normalizeHeader('Berat Jenis')).toBe('berat_jenis');
        expect(normalizeHeader('SGPT (U/L)')).toBe('sgpt_u_l');
        expect(normalizeHeader('')).toBe('');
    });
});
