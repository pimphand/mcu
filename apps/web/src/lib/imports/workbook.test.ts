import ExcelJS from 'exceljs';
import { describe, expect, it } from 'vitest';
import { ImportFileError, MAX_IMPORT_ROWS, isXlsxBuffer, readWorkbookRows } from './workbook';

async function workbookBuffer(rows: Array<Array<string | number>>): Promise<ArrayBuffer> {
    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet('Data');

    for (const row of rows) {
        sheet.addRow(row);
    }

    const buffer = await workbook.xlsx.writeBuffer();

    return new Uint8Array(buffer).buffer;
}

type SheetSpec = {
    name: string;
    rows: Array<Array<string | number>>;
    hidden?: boolean;
};

async function multiSheetBuffer(sheets: SheetSpec[], activeTab?: number): Promise<ArrayBuffer> {
    const workbook = new ExcelJS.Workbook();

    if (activeTab !== undefined) {
        // ExcelJS menuntut seluruh field WorkbookView, bukan hanya activeTab.
        workbook.views = [
            {
                x: 0,
                y: 0,
                width: 10000,
                height: 20000,
                firstSheet: 0,
                activeTab,
                visibility: 'visible',
            },
        ];
    }

    for (const spec of sheets) {
        const sheet = workbook.addWorksheet(spec.name);

        for (const row of spec.rows) {
            sheet.addRow(row);
        }

        if (spec.hidden) {
            sheet.state = 'hidden';
        }
    }

    const buffer = await workbook.xlsx.writeBuffer();

    return new Uint8Array(buffer).buffer;
}

const HEADINGS = [
    'TGL_MCU',
    'NO_MCU',
    'NAMA',
    'GENDER',
    'HASIL_MCU',
    'CATATAN',
    'DOKTER_PEMERIKSA',
];
const DATA_ROW = ['2026-10-19', 'MCU00001', 'Budi', 'L', 'FIT', 'catatan', 'dr. Uji'];

describe('readWorkbookRows', () => {
    it('normalizes headings and skips empty rows', async () => {
        const buffer = await workbookBuffer([
            ['NO MCU', 'Nama Peserta', 'Hemoglobin'],
            [17, 'Budi', 13.2],
            ['', '', ''],
            ['MCU00018', 'Siti', ''],
        ]);
        const rows = await readWorkbookRows(buffer);

        expect(rows).toHaveLength(2);
        expect(rows[0]?.values.no_mcu).toBe(17);
        expect(rows[0]?.values.nama_peserta).toBe('Budi');
        expect(rows[1]?.rowNumber).toBe(4);
    });

    it('keeps the first column when a heading appears twice', async () => {
        const buffer = await workbookBuffer([
            ['Code', 'Code'],
            ['MCU00001', 'MCU00002'],
        ]);
        const rows = await readWorkbookRows(buffer);

        expect(rows[0]?.values.code).toBe('MCU00001');
    });

    it('rejects files that are not XLSX workbooks', async () => {
        const buffer = new TextEncoder().encode('bukan excel').buffer;

        expect(isXlsxBuffer(buffer)).toBe(false);
        await expect(readWorkbookRows(buffer)).rejects.toBeInstanceOf(ImportFileError);
    });

    it('rejects a workbook without data rows', async () => {
        const buffer = await workbookBuffer([['NO_MCU', 'NAMA']]);

        await expect(readWorkbookRows(buffer)).rejects.toThrow(/Tidak ada baris data/);
    });

    it('uses a documented row limit', () => {
        expect(MAX_IMPORT_ROWS).toBe(2000);
    });

    it('reads data from the second sheet when the first only has headings', async () => {
        const buffer = await multiSheetBuffer([
            { name: 'Sheet1', rows: [HEADINGS] },
            { name: 'Data', rows: [HEADINGS, DATA_ROW] },
        ]);
        const rows = await readWorkbookRows(buffer);

        expect(rows).toHaveLength(1);
        expect(rows[0]?.values.no_mcu).toBe('MCU00001');
        expect(rows[0]?.values.hasil_mcu).toBe('FIT');
    });

    it('prefers the active sheet over the first sheet', async () => {
        const buffer = await multiSheetBuffer(
            [
                {
                    name: 'Sheet1',
                    rows: [HEADINGS, ['2026-01-01', 'MCU00001', 'Satu', 'L', 'A', '', '']],
                },
                {
                    name: 'Sheet2',
                    rows: [HEADINGS, ['2026-01-02', 'MCU00002', 'Dua', 'P', 'B', '', '']],
                },
            ],
            1,
        );
        const rows = await readWorkbookRows(buffer);

        expect(rows[0]?.values.no_mcu).toBe('MCU00002');
    });

    it('skips hidden sheets', async () => {
        const buffer = await multiSheetBuffer([
            { name: 'Contoh', rows: [HEADINGS], hidden: true },
            { name: 'Data', rows: [HEADINGS, DATA_ROW] },
        ]);
        const rows = await readWorkbookRows(buffer);

        expect(rows).toHaveLength(1);
        expect(rows[0]?.values.no_mcu).toBe('MCU00001');
    });
});
