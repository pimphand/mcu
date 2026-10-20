import ExcelJS from 'exceljs';
import { TEMPLATE_EXAMPLES, TEMPLATE_FIELDS } from './fields';
import type { ImportType } from './types';

/**
 * Template dibuat dari daftar kolom yang sama dengan pembaca import, sehingga
 * judul template dan judul yang diterima import tidak bisa berbeda. Baris
 * contoh memakai kode `MCU-XXXXX` supaya file yang diunggah tanpa diubah
 * dilaporkan sebagai baris dilewati, bukan menimpa data peserta.
 */
export async function buildImportTemplate(type: ImportType): Promise<Buffer> {
    const fields = TEMPLATE_FIELDS[type];
    const example = TEMPLATE_EXAMPLES[type];
    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet('Template');

    sheet.addRow(fields.map((field) => field.heading));
    sheet.addRow(fields.map((field) => example[field.column] ?? ''));

    const header = sheet.getRow(1);
    header.font = { bold: true };
    header.alignment = { vertical: 'middle' };
    header.height = 20;

    sheet.views = [{ state: 'frozen', ySplit: 1 }];
    sheet.columns = fields.map((field) => ({
        width: Math.max(14, field.heading.length + 2),
    }));

    // Tanggal pada baris contoh adalah teks, bukan serial Excel, supaya file
    // template tidak berubah format saat dibuka di Excel versi lain.
    sheet.getRow(2).eachCell((cell) => {
        cell.numFmt = '@';
    });

    const buffer = await workbook.xlsx.writeBuffer();

    return Buffer.from(buffer);
}
