import type { FieldSpec } from './types';

const f = (column: string, heading = column.toUpperCase(), aliases: string[] = []): FieldSpec => ({
    column,
    heading,
    aliases,
});

/**
 * Kolom laboratorium mengikuti `LaboratoriumImport` legacy. Beberapa judul
 * file lama tidak sama dengan nama kolom database (`NBATANG`, `PHREAKSI`,
 * `SEDIMENEPITEL`), jadi alias dipertahankan supaya file produksi tetap
 * terbaca. Template yang kami unduh memakai judul di kolom `heading`.
 */
export const LAB_FIELDS: FieldSpec[] = [
    f('code', 'CODE', ['no_mcu', 'kode']),
    // Nama peserta diambil dari tabel participants; tabel laboratoria legacy
    // tidak menyimpan kolom `name`, jadi kolom NAME di file diabaikan.
    f('hemoglobin'),
    f('hematokrit'),
    f('lekosit'),
    f('trombosit'),
    f('eritrosit'),
    f('basofil'),
    f('eosinofil'),
    f('batang', 'NBATANG', ['n_batang']),
    f('segmen', 'NSEGMEN', ['n_segmen']),
    f('limfosit'),
    f('monosit'),
    f('sgot'),
    f('sgpt'),
    f('ureum'),
    f('creatinin'),
    f('glukosa_puasa', 'GLUKOSA_PUASA', ['glukosa']),
    f('glukosa_sewaktu', 'GLUKOSA_SEWAKTU'),
    f('cholesterol_total', 'CHOLESTEROL_TOTAL', ['kolesterol_total', 'kolesterol']),
    f('trigliserida'),
    f('hdl_cholesterol', 'HDL_CHOLESTEROL', ['hdl']),
    f('ldl_cholestero', 'LDL_CHOLESTEROL', ['ldl_cholestero', 'ldl']),
    f('asam_urat', 'ASAM_URAT'),
    f('hbsag'),
    f('reduksi'),
    f('berat_jenis', 'BERATJENIS', ['berat_jenis', 'beratjenis']),
    f('ph_reaksi', 'PHREAKSI', ['ph_reaksi', 'ph']),
    f('warna'),
    f('kekeruhan'),
    f('urobilinogen'),
    f('bilirubin'),
    f('eritrosit_urine', 'ERITROSIT_URINE'),
    f('keton'),
    f('protein'),
    f('sedimen_epitel', 'SEDIMENEPITEL', ['sedimen_epitel']),
    f('sedimen_eritrosit', 'SEDIMENERTROSIT', ['sedimen_eritrosit']),
    f('sedimen_leukosit', 'SEDIMENLEUKOSIT', ['sedimen_leukosit']),
    f('sedimen_bakteri', 'SEDIMENBAKTERI', ['sedimen_bakteri']),
    f('sedimen_kristal', 'SEDIMENKRISTAL', ['sedimen_kristal']),
    f('kesimpulan', 'KESIMPULAN_LAB', ['kesimpulan', 'kesimpulan_lab']),
    f('pemeriksa_lab', 'PEMERIKSA_LAB', ['pemeriksa']),
    f('lab_date', 'LAB_DATE', ['tgl_lab', 'tanggal_lab', 'tanggal']),
];

/** Baris contoh template: kode sengaja tidak ada agar tidak menimpa peserta. */
export const LAB_EXAMPLE: Record<string, string> = {
    code: 'MCU-XXXXX',
    hemoglobin: '13.2',
    pemeriksa_lab: 'dr. Contoh Pemeriksa',
};
