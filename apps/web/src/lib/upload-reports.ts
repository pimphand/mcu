import type { ImportType } from './imports/types';

export type UploadColumn = {
    key: string;
    label: string;
    align?: 'left' | 'right';
};

export type UploadReport = {
    type: ImportType;
    /** URL menu legacy, dipakai untuk cek permission `is_view`/`is_add`. */
    legacyUrl: string;
    title: string;
    description: string;
    /** View PostgREST (`api.*`) yang berisi hasil + identitas peserta. */
    view: string;
    /** Kolom tanggal untuk filter rentang. */
    dateColumn: string;
    searchColumns: string[];
    defaultSort: string;
    sortOptions: Array<{ value: string; label: string }>;
    columns: UploadColumn[];
};

const COMMON_SORTS = [
    { value: 'code.asc', label: 'MCU ID (A–Z)' },
    { value: 'participant_name.asc', label: 'Nama (A–Z)' },
];

/** Sortir tanggal upload; legacy memakai `created_at` sebagai urutan bawaan. */
const UPLOAD_DATE_SORTS = [
    { value: 'created_at.desc.nullslast', label: 'Tanggal upload terbaru' },
    { value: 'created_at.asc.nullslast', label: 'Tanggal upload terlama' },
];

export const uploadReports: Record<ImportType, UploadReport> = {
    validate_doctor: {
        type: 'validate_doctor',
        legacyUrl: '/participant-upload-validasi-dokter',
        title: 'Upload Validasi Dokter',
        description:
            'Unggah hasil validasi dokter. Kesimpulan hasil MCU juga disalin ke pemeriksaan fisik peserta yang sudah memiliki datanya.',
        view: 'v_validate_doctor_listing',
        dateColumn: 'date_mcu',
        searchColumns: ['participant_name', 'code'],
        // Legacy (`UploadFileController@getValidateDoctorData`) memakai
        // `-created_at` lalu `id` desc: hasil unggahan terbaru di atas.
        defaultSort: 'created_at.desc.nullslast',
        sortOptions: [
            ...UPLOAD_DATE_SORTS,
            { value: 'date_mcu.desc.nullslast', label: 'Tanggal MCU terbaru' },
            { value: 'date_mcu.asc.nullslast', label: 'Tanggal MCU terlama' },
            { value: 'gender.asc', label: 'Gender (A–Z)' },
            { value: 'result_mcu.asc', label: 'Hasil MCU (A–Z)' },
            { value: 'doctor.asc', label: 'Dokter pemeriksa (A–Z)' },
            ...COMMON_SORTS,
        ],
        // Urutan kolom `pages/upload/validate-dokter.blade.php` dipertahankan
        // (No., Tanggal Upload, Tanggal MCU, MCU ID, Nama, Gender, Hasil MCU,
        // Catatan, Dokter Pemeriksa) agar pengguna tidak perlu pelatihan ulang;
        // Divisi ditambahkan di akhir karena halaman ini punya filternya.
        columns: [
            { key: 'created_at', label: 'Tanggal Upload' },
            { key: 'date_mcu', label: 'Tanggal MCU' },
            { key: 'code', label: 'MCU ID' },
            { key: 'participant_name', label: 'Nama' },
            { key: 'gender', label: 'Gender' },
            { key: 'result_mcu', label: 'Hasil MCU' },
            { key: 'notes', label: 'Catatan' },
            { key: 'doctor', label: 'Dokter Pemeriksa' },
            { key: 'divisi_name', label: 'Divisi' },
        ],
    },
    laboratorium: {
        type: 'laboratorium',
        legacyUrl: '/participant-upload-laboratorium',
        title: 'Upload Hasil Laboratorium',
        description:
            'Unggah hasil laboratorium dari file Excel. Pemeriksa dipetakan ke master karyawan; nama baru otomatis dibuat.',
        view: 'v_lab_listing',
        dateColumn: 'register_date',
        searchColumns: ['participant_name', 'code'],
        defaultSort: 'register_date.desc.nullslast',
        sortOptions: [
            { value: 'register_date.desc.nullslast', label: 'Tanggal register terbaru' },
            { value: 'register_date.asc.nullslast', label: 'Tanggal register terlama' },
            { value: 'lab_date.desc.nullslast', label: 'Tanggal lab terbaru' },
            ...COMMON_SORTS,
        ],
        columns: [
            { key: 'code', label: 'MCU ID' },
            { key: 'participant_name', label: 'Nama' },
            { key: 'gender', label: 'JK' },
            { key: 'divisi_name', label: 'Divisi' },
            { key: 'lab_date', label: 'Tgl Lab' },
            { key: 'hemoglobin', label: 'Hb' },
            { key: 'lekosit', label: 'Leukosit' },
            { key: 'sgpt', label: 'SGPT' },
            { key: 'sgot', label: 'SGOT' },
            { key: 'hbsag', label: 'HBsAg' },
            { key: 'kesimpulan', label: 'Kesimpulan' },
            { key: 'pemeriksa_lab', label: 'Pemeriksa' },
        ],
    },
    radiologi: {
        type: 'radiologi',
        legacyUrl: '/participant-upload-radiologi',
        title: 'Upload Hasil Radiologi',
        description:
            'Unggah hasil radiologi dari file Excel. Kolom NO_MCU harus berisi kode peserta (contoh MCU00001).',
        view: 'v_radiologi_listing',
        dateColumn: 'date_mcu',
        searchColumns: ['participant_name', 'code'],
        defaultSort: 'date_mcu.desc.nullslast',
        sortOptions: [
            { value: 'date_mcu.desc.nullslast', label: 'Tanggal MCU terbaru' },
            { value: 'date_mcu.asc.nullslast', label: 'Tanggal MCU terlama' },
            ...COMMON_SORTS,
        ],
        columns: [
            { key: 'code', label: 'MCU ID' },
            { key: 'participant_name', label: 'Nama' },
            { key: 'gender', label: 'JK' },
            { key: 'divisi_name', label: 'Divisi' },
            { key: 'date_mcu', label: 'Tgl MCU' },
            { key: 'cor', label: 'Cor' },
            { key: 'pulmo', label: 'Pulmo' },
            { key: 'kesan', label: 'Kesan' },
            { key: 'diperiksa', label: 'Pemeriksa' },
        ],
    },
    general_result: {
        type: 'general_result',
        legacyUrl: '/participant-upload-umum',
        title: 'Upload Hasil Umum',
        description:
            'Unggah hasil umum / diagnosa akhir dari file Excel. Tanggal MCU kosong memakai tanggal register peserta.',
        view: 'v_general_result_listing',
        dateColumn: 'mcu_date',
        searchColumns: ['participant_name', 'code'],
        defaultSort: 'mcu_date.desc.nullslast',
        sortOptions: [
            { value: 'mcu_date.desc.nullslast', label: 'Tanggal MCU terbaru' },
            { value: 'mcu_date.asc.nullslast', label: 'Tanggal MCU terlama' },
            ...COMMON_SORTS,
        ],
        columns: [
            { key: 'code', label: 'MCU ID' },
            { key: 'participant_name', label: 'Nama' },
            { key: 'gender', label: 'JK' },
            { key: 'divisi_name', label: 'Divisi' },
            { key: 'mcu_date', label: 'Tgl MCU' },
            { key: 'result', label: 'Hasil Umum' },
        ],
    },
};

/**
 * Pesan saat daftar hasil gagal dimuat. View daftar hasil dibuat oleh migrasi
 * `0013_upload_views.sql`; bila belum dijalankan, PostgREST menjawab 404
 * "could not find the table" yang tidak memberi petunjuk apa pun.
 */
export function uploadListErrorMessage(view: string, status: number, message: string): string {
    if (status === 404) {
        return `Daftar hasil belum tersedia: view ${view} belum ada di database. Jalankan migrasi 0013 (db/scripts/migrate.sh) lebih dulu.`;
    }

    return message;
}

export const templateFileNames: Record<ImportType, string> = {
    validate_doctor: 'template-upload-validasi-dokter.xlsx',
    laboratorium: 'template-upload-laboratorium.xlsx',
    radiologi: 'template-upload-radiologi.xlsx',
    general_result: 'template-upload-hasil-umum.xlsx',
};
