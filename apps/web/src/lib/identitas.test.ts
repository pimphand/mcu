import { describe, expect, it } from 'vitest';
import { identitasFilter, identitasHref, mapIdentitas, parseIdentitasQuery } from './identitas';
import { printPageConfig } from './print';

describe('Print Identitas', () => {
    it('memakai default legacy Dari No 1 dan Sampai No 50', () => {
        expect(parseIdentitasQuery(new URLSearchParams())).toEqual({
            start: 1,
            end: 50,
            tglAwal: null,
            tglAkhir: null,
            divisiId: null,
        });
    });

    it('menjaga end minimal sama dengan start dan mengabaikan tanggal tidak valid', () => {
        const query = parseIdentitasQuery(
            new URLSearchParams({
                start: '10',
                end: '5',
                tgl_awal: '2026-10-01',
                tgl_akhir: '10/10/2026',
                divisi_id: '4',
            }),
        );

        expect(query).toEqual({
            start: 10,
            end: 10,
            tglAwal: '2026-10-01',
            tglAkhir: null,
            divisiId: 4,
        });
    });

    it('menyusun filter PostgREST No. Form dan tanggal register WIB', () => {
        expect(
            identitasFilter({
                start: 1,
                end: 50,
                tglAwal: '2026-10-01',
                tglAkhir: '2026-10-31',
                divisiId: 4,
            }),
        ).toBe(
            '(no_form.gte.1,no_form.lte.50,register_date.gte.2026-10-01T00:00:00+07:00,register_date.lte.2026-10-31T23:59:59+07:00,divisi_id.eq.4)',
        );

        expect(
            identitasFilter({
                start: 2,
                end: 4,
                tglAwal: null,
                tglAkhir: null,
                divisiId: null,
            }),
        ).toBe('(no_form.gte.2,no_form.lte.4)');
    });

    it('mempertahankan filter saat kembali dari halaman cetak', () => {
        expect(
            identitasHref({
                start: 3,
                end: 7,
                tglAwal: '2026-10-01',
                tglAkhir: null,
                divisiId: 4,
            }),
        ).toBe('/app/print/identitas/print?start=3&end=7&tgl_awal=2026-10-01&divisi_id=4');
    });

    it('memetakan data legacy: plan_name, department, dan divisi', () => {
        expect(
            mapIdentitas({
                id: 38,
                code: 'MCU00002',
                name: 'HEDYANI AHMAD MOSA',
                gender: 'P',
                birthday: '1992-03-31',
                nik: '800150324001',
                status: 'Karyawan',
                plan_name: 'Medical Check Up',
                packet_name: 'Paket Lain',
                no_form: 2,
                register_date: '2026-10-12T09:15:00+07:00',
                client_id: 1,
                contract_id: 2,
                divisis: { name: 'ACCOUNTING' },
                departments: { name: 'FINANCE' },
            }),
        ).toEqual({
            participant_id: 38,
            code: 'MCU00002',
            name: 'HEDYANI AHMAD MOSA',
            gender: 'P',
            birthday: '1992-03-31',
            nik: '800150324001',
            plan_name: 'Medical Check Up',
            no_form: 2,
            register_date: '2026-10-12T09:15:00+07:00',
            client_id: 1,
            contract_id: 2,
            divisi_name: 'ACCOUNTING',
            department_name: 'FINANCE',
        });
    });

    it('memakai kertas stiker legacy 150 x 100 mm dengan margin 1,2 cm', () => {
        expect(printPageConfig('identitas')).toEqual({
            pageSize: '150mm 100mm',
            pageMargin: '1.2cm',
            contentWidth: '12.6cm',
        });
    });
});
