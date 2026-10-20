import ExcelJS from 'exceljs';
import { describe, expect, it } from 'vitest';
import { isLegacyXlsBuffer, isXlsxBuffer, workbookSignature } from './file-signature';

/** Workbook XLSX asli, dipakai untuk membuktikan penilaian berbasis isi. */
async function xlsxBuffer(): Promise<ArrayBuffer> {
    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet('Data');

    sheet.addRow(['NO_MCU']);
    sheet.addRow(['MCU00001']);

    const buffer = await workbook.xlsx.writeBuffer();

    return new Uint8Array(buffer).buffer;
}

const OLE2_HEADER = new Uint8Array([0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1, 0x00, 0x00]);

describe('file signature', () => {
    it('mengenali workbook dari isinya walau nama file tidak berakhiran .xlsx', async () => {
        const buffer = await xlsxBuffer();

        expect(workbookSignature(buffer)).toBe('xlsx');
        expect(isXlsxBuffer(buffer)).toBe(true);
        expect(isLegacyXlsBuffer(buffer)).toBe(false);
    });

    it('mengenali .xls (OLE2) untuk memberi pesan yang tepat', () => {
        expect(workbookSignature(OLE2_HEADER.buffer)).toBe('xls');
        expect(isLegacyXlsBuffer(OLE2_HEADER.buffer)).toBe(true);
        expect(isXlsxBuffer(OLE2_HEADER.buffer)).toBe(false);
    });

    it('menolak berkas yang bukan workbook', () => {
        const text = new TextEncoder().encode('bukan excel').buffer;

        expect(workbookSignature(text)).toBe('other');
        expect(isXlsxBuffer(text)).toBe(false);
        expect(isLegacyXlsBuffer(text)).toBe(false);
    });

    it('aman untuk buffer pendek', () => {
        expect(workbookSignature(new ArrayBuffer(0))).toBe('other');
        expect(isLegacyXlsBuffer(new Uint8Array([0xd0, 0xcf]).buffer)).toBe(false);
    });
});
