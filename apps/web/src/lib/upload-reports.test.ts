import { describe, expect, it } from 'vitest';
import type { ImportType } from './imports/types';
import { uploadListErrorMessage, uploadReports } from './upload-reports';

/**
 * Paritas konfigurasi tabel upload dengan halaman Blade legacy.
 *
 * Nilai `columns`/`sortOptions` dikirim langsung ke PostgREST, jadi nama yang
 * salah ketik tidak memunculkan error melainkan sel kosong ("—"). Daftar kolom
 * view di bawah disalin dari `db/migrations/0013_upload_views.sql`.
 */

const VIEW_COLUMNS: Record<ImportType, string[]> = {
    validate_doctor: [
        'id',
        'code',
        'no_mcu',
        'participant_name',
        'name',
        'gender',
        'no_form',
        'register_date',
        'divisi_id',
        'divisi_name',
        'client_id',
        'contract_id',
        'date_mcu',
        'result_mcu',
        'doctor',
        'notes',
        'created_at',
        'updated_at',
    ],
    laboratorium: [
        'id',
        'participant_id',
        'code',
        'participant_name',
        'gender',
        'no_form',
        'register_date',
        'divisi_id',
        'divisi_name',
        'client_id',
        'contract_id',
        'employee_id',
        'form_no',
        'hemoglobin',
        'hematokrit',
        'lekosit',
        'trombosit',
        'eritrosit',
        'basofil',
        'eosinofil',
        'batang',
        'segmen',
        'limfosit',
        'monosit',
        'sgot',
        'sgpt',
        'ureum',
        'creatinin',
        'kesimpulan',
        'selesai',
        'glukosa_puasa',
        'cholesterol_total',
        'asam_urat',
        'glukosa_sewaktu',
        'trigliserida',
        'hdl_cholesterol',
        'ldl_cholestero',
        'reduksi',
        'berat_jenis',
        'ph_reaksi',
        'warna',
        'kekeruhan',
        'urobilinogen',
        'bilirubin',
        'eritrosit_urine',
        'keton',
        'protein',
        'sedimen_epitel',
        'sedimen_eritrosit',
        'sedimen_leukosit',
        'sedimen_bakteri',
        'sedimen_kristal',
        'hbsag',
        'user_lab',
        'lab_date',
        'pemeriksa_lab',
        'created_at',
        'updated_at',
    ],
    radiologi: [
        'id',
        'participant_id',
        'code',
        'participant_name',
        'gender',
        'no_form',
        'register_date',
        'divisi_id',
        'divisi_name',
        'client_id',
        'contract_id',
        'employee_id',
        'date_mcu',
        'cor',
        'diafragma_sinus',
        'pulmo',
        'kesan',
        'diperiksa',
        'selesai',
        'created_at',
        'updated_at',
    ],
    general_result: [
        'id',
        'participant_id',
        'code',
        'participant_name',
        'name',
        'gender',
        'no_form',
        'register_date',
        'divisi_id',
        'divisi_name',
        'client_id',
        'contract_id',
        'mcu_id',
        'result',
        'mcu_date',
        'created_at',
        'updated_at',
    ],
};

const TYPES = Object.keys(uploadReports) as ImportType[];

/** Urutan kolom `pages/upload/validate-dokter.blade.php` (tanpa kolom No.). */
const LEGACY_VALIDATE_COLUMNS: Array<[string, string]> = [
    ['created_at', 'Tanggal Upload'],
    ['date_mcu', 'Tanggal MCU'],
    ['code', 'MCU ID'],
    ['participant_name', 'Nama'],
    ['gender', 'Gender'],
    ['result_mcu', 'Hasil MCU'],
    ['notes', 'Catatan'],
    ['doctor', 'Dokter Pemeriksa'],
];

describe('uploadReports', () => {
    it('memakai view yang sama dengan daftar hasil migrasi 0013', () => {
        expect([...TYPES].sort()).toEqual([
            'general_result',
            'laboratorium',
            'radiologi',
            'validate_doctor',
        ]);

        expect(uploadReports.validate_doctor.view).toBe('v_validate_doctor_listing');
        expect(uploadReports.laboratorium.view).toBe('v_lab_listing');
        expect(uploadReports.radiologi.view).toBe('v_radiologi_listing');
        expect(uploadReports.general_result.view).toBe('v_general_result_listing');
    });

    it('hanya memakai kolom yang benar-benar ada di view', () => {
        for (const type of TYPES) {
            const report = uploadReports[type];
            const columns = new Set(VIEW_COLUMNS[type]);

            for (const column of report.columns) {
                expect(columns, `${type}.columns.${column.key}`).toContain(column.key);
            }

            for (const column of report.searchColumns) {
                expect(columns, `${type}.searchColumns.${column}`).toContain(column);
            }

            expect(columns, `${type}.dateColumn`).toContain(report.dateColumn);
        }
    });

    it('mengurutkan memakai kolom view dan arah yang sah', () => {
        for (const type of TYPES) {
            const report = uploadReports[type];
            const columns = new Set(VIEW_COLUMNS[type]);
            const values = report.sortOptions.map((option) => option.value);

            expect(values, `${type}.defaultSort`).toContain(report.defaultSort);

            for (const value of values) {
                const [column, direction, nulls] = value.split('.');

                expect(columns, `${type} tidak punya kolom ${column}`).toContain(column);
                expect(['asc', 'desc'], `${type} arah sortir ${value}`).toContain(direction);
                expect([undefined, 'nullslast', 'nullsfirst'], `${type} nulls ${value}`).toContain(
                    nulls,
                );
            }
        }
    });

    it('menjaga urutan dan istilah kolom validasi dokter seperti legacy', () => {
        // Delapan kolom legacy harus muncul berurutan; kolom tambahan (Divisi)
        // hanya boleh berada setelahnya.
        expect(
            uploadReports.validate_doctor.columns
                .slice(0, LEGACY_VALIDATE_COLUMNS.length)
                .map((column) => [column.key, column.label]),
        ).toEqual(LEGACY_VALIDATE_COLUMNS);

        expect(uploadReports.validate_doctor.columns.slice(LEGACY_VALIDATE_COLUMNS.length)).toEqual(
            [{ key: 'divisi_name', label: 'Divisi' }],
        );
    });

    it('memakai tanggal upload terbaru sebagai urutan bawaan validasi dokter', () => {
        expect(uploadReports.validate_doctor.defaultSort).toBe('created_at.desc.nullslast');
    });
});

describe('uploadListErrorMessage', () => {
    it('menjelaskan view yang belum ada saat migrasi 0013 belum dijalankan', () => {
        const message = uploadListErrorMessage(
            'v_validate_doctor_listing',
            404,
            "Could not find the table 'api.v_validate_doctor_listing' in the schema cache",
        );

        expect(message).toContain('v_validate_doctor_listing');
        expect(message).toContain('migrasi 0013');
    });

    it('meneruskan pesan asli untuk error lain', () => {
        expect(uploadListErrorMessage('v_lab_listing', 503, 'Layanan data sedang sibuk.')).toBe(
            'Layanan data sedang sibuk.',
        );
    });
});
