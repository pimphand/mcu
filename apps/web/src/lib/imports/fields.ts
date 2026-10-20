import type { ImportRow } from './workbook';
import { normalizeHeader } from './values';
import { LAB_EXAMPLE, LAB_FIELDS } from './lab-fields';
import type { FieldSpec, ImportType } from './types';

export const RADIOLOGI_FIELDS: FieldSpec[] = [
    { column: 'code', heading: 'NO_MCU', aliases: ['code', 'kode'] },
    { column: 'date_mcu', heading: 'TGL_MCU', aliases: ['date_mcu', 'tanggal_mcu'] },
    { column: 'cor', heading: 'COR' },
    { column: 'diafragma_sinus', heading: 'DIAFRAGMA_SINUS', aliases: ['diafragma_sinus'] },
    { column: 'pulmo', heading: 'PULMO' },
    { column: 'kesan', heading: 'KESAN' },
    { column: 'diperiksa', heading: 'PEMERIKSA', aliases: ['diperiksa'] },
];

export const VALIDATE_FIELDS: FieldSpec[] = [
    { column: 'date_mcu', heading: 'TGL_MCU', aliases: ['date_mcu', 'tanggal_mcu'] },
    { column: 'code', heading: 'NO_MCU', aliases: ['code', 'kode'] },
    { column: 'name', heading: 'NAMA', aliases: ['name'] },
    { column: 'gender', heading: 'GENDER', aliases: ['jenis_kelamin', 'jk'] },
    { column: 'result_mcu', heading: 'HASIL_MCU', aliases: ['hasil', 'kesimpulan'] },
    { column: 'notes', heading: 'CATATAN', aliases: ['notes'] },
    { column: 'doctor', heading: 'DOKTER_PEMERIKSA', aliases: ['dokter', 'pemeriksa'] },
];

export const GENERAL_FIELDS: FieldSpec[] = [
    { column: 'date_mcu', heading: 'TGL_MCU', aliases: ['date_mcu', 'tanggal_mcu'] },
    { column: 'code', heading: 'NO_MCU', aliases: ['code', 'kode', 'mcu_id'] },
    { column: 'name', heading: 'NAMA', aliases: ['name'] },
    {
        column: 'result',
        heading: 'HASIL_UMUM',
        aliases: ['diagnosa_akhir', 'hasil', 'kesan'],
    },
];

export const TEMPLATE_FIELDS: Record<ImportType, FieldSpec[]> = {
    laboratorium: LAB_FIELDS,
    radiologi: RADIOLOGI_FIELDS,
    validate_doctor: VALIDATE_FIELDS,
    general_result: GENERAL_FIELDS,
};

export const TEMPLATE_EXAMPLES: Record<ImportType, Record<string, string>> = {
    laboratorium: LAB_EXAMPLE,
    radiologi: {
        code: 'MCU-XXXXX',
        date_mcu: '07-Oct-24',
        cor: 'Contoh cor',
        pulmo: 'Contoh pulmo',
        kesan: 'Contoh kesan',
        diperiksa: 'dr. Contoh Pemeriksa',
    },
    validate_doctor: {
        date_mcu: '07-Oct-24',
        code: 'MCU-XXXXX',
        name: 'CONTOH NAMA PESERTA',
        gender: 'L',
        result_mcu: 'Contoh kesimpulan hasil MCU',
        notes: 'Contoh catatan',
        doctor: 'dr. Contoh Pemeriksa',
    },
    general_result: {
        date_mcu: '07-Oct-24',
        code: 'MCU-XXXXX',
        name: 'CONTOH NAMA PESERTA',
        result: 'Contoh hasil umum / diagnosa akhir',
    },
};

/** Sel mentah untuk satu kolom, mencoba judul template lalu alias legacy. */
export function fieldValue(row: ImportRow, spec: FieldSpec): unknown {
    const keys = [normalizeHeader(spec.heading), ...(spec.aliases ?? []).map(normalizeHeader)];

    for (const key of keys) {
        if (key in row.values) {
            return row.values[key];
        }
    }

    return null;
}

export function hasField(row: ImportRow, spec: FieldSpec): boolean {
    return fieldValue(row, spec) !== null;
}

/**
 * Nilai kode peserta: `NO_MCU` bisa berisi kode MCU (`MCU00001`) atau nomor
 * formulir (`17`). Pencocokan nomor formulir hanya untuk digit murni.
 */
export function readCode(row: ImportRow, spec: FieldSpec): string {
    const value = fieldValue(row, spec);

    return typeof value === 'number'
        ? String(value)
        : typeof value === 'string'
          ? value.trim()
          : '';
}

export function isFormNumber(code: string): boolean {
    return /^\d+$/.test(code);
}
