/**
 * Deteksi jenis workbook dari **isi** file, bukan dari nama/ekstensi.
 *
 * Modul ini sengaja tidak mengimpor ExcelJS supaya bisa dipakai di browser
 * (validasi sebelum unggah) maupun di server (validasi sebelum parse), dengan
 * aturan yang sama persis di keduanya.
 */

/** XLSX/XLSM adalah arsip ZIP (PK\x03\x04). */
export function isXlsxBuffer(buffer: ArrayBuffer): boolean {
    if (buffer.byteLength < 4) {
        return false;
    }

    const bytes = new Uint8Array(buffer, 0, 4);

    return bytes[0] === 0x50 && bytes[1] === 0x4b && bytes[2] === 0x03 && bytes[3] === 0x04;
}

/**
 * Excel 97-2003 (`.xls`) adalah berkas OLE2/BIFF, bukan arsip ZIP. Deteksi ini
 * dipakai hanya untuk memberi pesan yang tepat ("simpan ulang sebagai .xlsx"),
 * bukan untuk memprosesnya.
 */
export function isLegacyXlsBuffer(buffer: ArrayBuffer): boolean {
    if (buffer.byteLength < 8) {
        return false;
    }

    const bytes = new Uint8Array(buffer, 0, 8);
    const ole2 = [0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1];

    return ole2.every((value, index) => bytes[index] === value);
}

export type WorkbookSignature = 'xlsx' | 'xls' | 'other';

/** Ringkasan hasil deteksi untuk pesan validasi di UI maupun API. */
export function workbookSignature(buffer: ArrayBuffer): WorkbookSignature {
    if (isXlsxBuffer(buffer)) {
        return 'xlsx';
    }

    if (isLegacyXlsBuffer(buffer)) {
        return 'xls';
    }

    return 'other';
}
