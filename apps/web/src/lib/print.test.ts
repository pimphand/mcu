import { describe, expect, it } from 'vitest';
import {
    buildResumeSummary,
    defaultPrintRange,
    isPrintType,
    printPageConfig,
    printQrValue,
    printTypeConfig,
    registerDateFilter,
    resultConfigFor,
    signatureFor,
} from './print';

describe('print helpers', () => {
    it('defaults to the first day of the current Jakarta month until today', () => {
        expect(defaultPrintRange(new Date('2026-10-18T03:00:00Z'))).toEqual({
            start: '2026-10-01',
            end: '2026-10-18',
        });

        // 20:00 UTC tanggal 31 = 03:00 WIB tanggal 1 bulan berikutnya.
        expect(defaultPrintRange(new Date('2026-10-31T20:00:00Z'))).toEqual({
            start: '2026-11-01',
            end: '2026-11-01',
        });
    });

    it('accepts only the nine legacy print types', () => {
        expect(isPrintType('laboratorium')).toBe(true);
        expect(isPrintType('identitas')).toBe(true);
        expect(isPrintType('users')).toBe(false);
        expect(isPrintType('')).toBe(false);
    });

    it('maps print types to readiness flags and result tables', () => {
        expect(printTypeConfig('resume').doneKey).toBe('validated');
        expect(printTypeConfig('laboratorium').doneKey).toBe('lab_done');
        expect(printTypeConfig('pemeriksaan-fisik').resource).toBe('pemeriksaan_fisiks');
        expect(printTypeConfig('identitas').doneKey).toBeNull();
        expect(resultConfigFor('laboratorium')?.resource).toBe('laboratoria');
        expect(resultConfigFor('identitas')).toBeNull();
    });

    it('uses the legacy paper size for every print type', () => {
        expect(printPageConfig('identitas')).toEqual({
            pageSize: '150mm 100mm',
            pageMargin: '1.2cm',
            contentWidth: '12.6cm',
        });
        expect(printPageConfig('pemeriksaan-fisik').pageSize).toBe('21cm 33cm');
        expect(printPageConfig('resume').pageSize).toBe('21cm 33cm');

        // View legacy audiometri/EKG/spirometri/rectal tidak punya `@page`,
        // jadi DomPDF memakai default A4 dengan margin 1,2cm.
        for (const type of ['audiometri', 'ekg', 'spirometri', 'rectal'] as const) {
            expect(printPageConfig(type)).toEqual({
                pageSize: '210mm 297mm',
                pageMargin: '1.2cm',
                contentWidth: '18.6cm',
            });
        }
    });

    it('builds resume rows in legacy order, with section rows and dashes', () => {
        const rows = buildResumeSummary(
            new Map([
                ['radiologis', { kesan: 'Cor dan Pulmo normal' }],
                ['laboratoria', { kesimpulan: '' }],
            ]),
        );

        expect(rows[0]).toEqual({ label: 'LABORATORIUM (TERLAMPIR)', value: '', section: true });
        expect(rows[1]).toEqual({ label: 'Hasil Laboratorium', value: '-' });
        expect(rows[2]).toEqual({
            label: 'HASIL PHOTO THORAX (TERLAMPIR)',
            value: '',
            section: true,
        });
        expect(rows[3]).toEqual({ label: 'Hasil dan Kesan', value: 'Cor dan Pulmo normal' });
        expect(rows.map((row) => row.label)).toEqual([
            'LABORATORIUM (TERLAMPIR)',
            'Hasil Laboratorium',
            'HASIL PHOTO THORAX (TERLAMPIR)',
            'Hasil dan Kesan',
            'AUDIOMETRI (TERLAMPIR)',
            'Pendengaran Telinga Kanan',
            'Pendengaran Telinga Kiri',
            'EKG (TERLAMPIR)',
            'Kesimpulan EKG',
            'SPIROMETRI (TERLAMPIR)',
            'Kesimpulan Spiro',
        ]);
    });

    it('takes the signature name and ttd path from employees', () => {
        expect(signatureFor({ id: 4, nama: 'dr. Nicha', ttd: 'ttd/dr_nicha.png' })).toEqual({
            name: 'dr. Nicha',
            ttd: 'ttd/dr_nicha.png',
        });
        expect(signatureFor(null, 'dr. Cadangan')).toEqual({ name: 'dr. Cadangan', ttd: null });
    });

    it('builds the header QR value from contract, MCU id, and print type', () => {
        expect(printQrValue('MCU00001', 4, 'radiologi')).toBe('4-MCU00001-radiologi');
        expect(printQrValue('MCU00001', null, 'identitas')).toBe('MCU00001-identitas');
        expect(printQrValue('MCU00001', 4)).toBe('4-MCU00001');
        expect(printQrValue('MCU00001')).toBe('MCU00001');
    });

    it('builds a Jakarta-aware register date filter', () => {
        expect(registerDateFilter('2026-10-01', '2026-10-31')).toBe(
            '(register_date.gte.2026-10-01T00:00:00+07:00,register_date.lte.2026-10-31T23:59:59+07:00)',
        );
    });
});
