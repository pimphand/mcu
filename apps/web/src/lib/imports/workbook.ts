import ExcelJS from 'exceljs';
import { cellText, normalizeHeader } from './values';

/** Batas file dan baris supaya satu unggahan tidak memblokir server. */
export const MAX_IMPORT_BYTES = 10 * 1024 * 1024;
export const MAX_IMPORT_ROWS = 2_000;

export class ImportFileError extends Error {
    constructor(message: string) {
        super(message);
        this.name = 'ImportFileError';
    }
}

export type ImportRow = {
    rowNumber: number;
    /** Nilai sel mentah, dikunci dengan judul kolom yang sudah dinormalisasi. */
    values: Record<string, unknown>;
};

// Cek magic bytes dipakai server sebelum ExcelJS membuka file, dan dipakai
// browser sebelum unggah. Sumbernya satu agar aturannya tidak berbeda.
export { isLegacyXlsBuffer, isXlsxBuffer, workbookSignature } from './file-signature';

/**
 * Urutan sheet yang dibaca: **sheet aktif** lebih dulu — Maatwebsite/legacy
 * membaca sheet aktif, bukan selalu sheet pertama — lalu sheet terlihat
 * lainnya. Sheet tersembunyi dilewati supaya sheet bantu (mis. contoh atau
 * kunci kolom) tidak ikut terbaca sebagai data.
 */
function sheetsInReadOrder(workbook: ExcelJS.Workbook): ExcelJS.Worksheet[] {
    const visible = workbook.worksheets.filter((sheet) => sheet.state === 'visible');
    // `views` hanya terisi bila file menyimpan workbook view; banyak file
    // (termasuk hasil library) tidak menyimpannya, jadi selalu ada fallback.
    const active = workbook.worksheets[workbook.views?.[0]?.activeTab ?? 0];

    if (!active || active.state !== 'visible') {
        return visible;
    }

    return [active, ...visible.filter((sheet) => sheet !== active)];
}

/** Satu sheet menjadi baris berisi nilai mentah; sheet kosong menghasilkan `[]`. */
function readSheetRows(sheet: ExcelJS.Worksheet): ImportRow[] {
    const headerRow = sheet.getRow(1);
    const headers: Array<string | null> = [];

    for (let column = 1; column <= headerRow.cellCount; column += 1) {
        headers[column] = normalizeHeader(headerRow.getCell(column).value) || null;
    }

    const rows: ImportRow[] = [];

    for (let rowNumber = 2; rowNumber <= sheet.rowCount; rowNumber += 1) {
        const row = sheet.getRow(rowNumber);
        const values: Record<string, unknown> = {};
        let hasValue = false;

        for (let column = 1; column < headers.length; column += 1) {
            const key = headers[column];

            if (!key) continue;

            const value = row.getCell(column).value;

            if (cellText(value) !== '') {
                hasValue = true;
            }

            // Judul ganda: kolom pertama yang dipakai, seperti Maatwebsite.
            if (!(key in values)) {
                values[key] = value;
            }
        }

        if (hasValue) {
            rows.push({ rowNumber, values });
        }

        if (rows.length > MAX_IMPORT_ROWS) {
            throw new ImportFileError(
                `Jumlah baris melebihi batas ${MAX_IMPORT_ROWS.toLocaleString('id-ID')} baris.`,
            );
        }
    }

    return rows;
}

/**
 * Baca baris data dari workbook. Judul kolom memakai aturan yang sama dengan
 * `WithHeadingRow` legacy (`NO MCU` -> `no_mcu`), sehingga nama kolom file lama
 * tetap dikenali. Sheet pertama yang berisi data dipakai; file yang menyimpan
 * data di sheet kedua (sheet pertama hanya judul/contoh) tetap bisa diunggah.
 */
export async function readWorkbookRows(buffer: ArrayBuffer): Promise<ImportRow[]> {
    const workbook = new ExcelJS.Workbook();

    try {
        // Tipe Buffer bawaan Node 22 dan tipe Buffer yang dipakai ExcelJS
        // berbeda generik; isinya tetap byte XLSX yang sama.
        type ExcelInput = Parameters<typeof workbook.xlsx.load>[0];
        await workbook.xlsx.load(Buffer.from(buffer) as unknown as ExcelInput);
    } catch {
        throw new ImportFileError('File tidak dapat dibaca sebagai workbook Excel (.xlsx/.xlsm).');
    }

    if (workbook.worksheets.length === 0) {
        throw new ImportFileError('Workbook tidak memiliki sheet data.');
    }

    for (const sheet of sheetsInReadOrder(workbook)) {
        const rows = readSheetRows(sheet);

        if (rows.length > 0) {
            return rows;
        }
    }

    throw new ImportFileError(
        'Tidak ada baris data yang bisa dibaca dari file. Pastikan baris pertama sheet berisi judul kolom (mis. NO_MCU) dan baris berikutnya berisi data.',
    );
}
