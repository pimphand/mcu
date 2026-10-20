/**
 * Konfigurasi tampilan hasil pemeriksaan peserta. Urutan, label, dan nama
 * kolom mengikuti tabel legacy (`docs/FEATURE_INVENTORY.md` §5) supaya
 * pemetaan saat form pemeriksaan dibuat tetap satu sumber.
 */

export type ResultFieldType = 'boolean' | 'date' | 'datetime' | 'number';

export type ResultField = {
    key: string;
    label: string;
    type?: ResultFieldType;
};

export type ExamResultConfig = {
    /** Kunci internal (dipakai tab dan progres). */
    key: string;
    label: string;
    /** Resource PostgREST yang menyimpan satu baris hasil per peserta. */
    resource: string;
    /** Kolom filter; `validate_doctors` memakai kode MCU, bukan id peserta. */
    filterBy: 'participant_id' | 'no_mcu';
    /** Kolom status selesai; `null` bila tabel tidak punya flag. */
    doneColumn: string | null;
    fields: ResultField[];
};

const boolean = (key: string, label: string): ResultField => ({ key, label, type: 'boolean' });

export const examResults: ExamResultConfig[] = [
    {
        key: 'tanda_vitals',
        label: 'Tanda Vital',
        resource: 'tanda_vitals',
        filterBy: 'participant_id',
        doneColumn: 'selesai',
        fields: [
            { key: 'keluhan_utama_text', label: 'Keluhan utama' },
            { key: 'riwayat_penyakit_sekarang_text', label: 'Riwayat penyakit sekarang' },
            { key: 'riwayat_penyakit_terdahulu_text', label: 'Riwayat penyakit terdahulu' },
            { key: 'alergi_text', label: 'Alergi' },
            { key: 'riwayat_trauma_text', label: 'Riwayat trauma' },
            boolean('merokok', 'Merokok'),
            boolean('konsumsi_alkohol', 'Konsumsi alkohol'),
            { key: 'tinggi_badan', label: 'Tinggi badan', type: 'number' },
            { key: 'berat_badan', label: 'Berat badan', type: 'number' },
            { key: 'imt', label: 'IMT' },
            { key: 'tekanan_darah', label: 'Tekanan darah' },
            { key: 'frekuensi_nadi', label: 'Frekuensi nadi', type: 'number' },
            { key: 'frekuensi_nafas', label: 'Frekuensi nafas', type: 'number' },
            { key: 'suhu', label: 'Suhu', type: 'number' },
            boolean('vaksin_hepatitis', 'Vaksin hepatitis'),
            boolean('vaksin_tetanus', 'Vaksin tetanus'),
            boolean('ibu_hamil', 'Ibu hamil'),
        ],
    },
    {
        key: 'pemeriksaan_fisiks',
        label: 'Pemeriksaan Fisik',
        resource: 'pemeriksaan_fisiks',
        filterBy: 'participant_id',
        doneColumn: 'selesai',
        fields: [
            { key: 'keadaan_umum', label: 'Keadaan umum' },
            { key: 'kepala_text', label: 'Kepala' },
            { key: 'hidung', label: 'Hidung' },
            { key: 'mata', label: 'Mata' },
            { key: 'telinga', label: 'Telinga' },
            { key: 'kelainan_telinga', label: 'Kelainan telinga' },
            { key: 'pupil', label: 'Pupil' },
            { key: 'visus', label: 'Visus' },
            { key: 'buta_warna', label: 'Buta warna' },
            { key: 'gigi', label: 'Gigi' },
            { key: 'kode_gigi', label: 'Kode gigi' },
            { key: 'bibir', label: 'Bibir' },
            { key: 'lidah', label: 'Lidah' },
            { key: 'tenggorokan_text', label: 'Tenggorokan' },
            { key: 'faring', label: 'Faring' },
            { key: 'leher_kgb', label: 'Leher KGB' },
            { key: 'leher_jvp', label: 'Leher JVP' },
            { key: 'jantung_inspeksi', label: 'Jantung — inspeksi' },
            { key: 'jantung_auskultasi', label: 'Jantung — auskultasi' },
            { key: 'jantung_palpasi', label: 'Jantung — palpasi' },
            { key: 'jantung_perkusi', label: 'Jantung — perkusi' },
            { key: 'paru_inspeksi', label: 'Paru — inspeksi' },
            { key: 'paru_auskultasi_vasikuler', label: 'Paru — auskultasi vasikuler' },
            { key: 'paru_auskultasi_ronkhi', label: 'Paru — ronkhi' },
            { key: 'paru_auskultasi_wheezing', label: 'Paru — wheezing' },
            { key: 'paru_palpasi', label: 'Paru — palpasi' },
            { key: 'paru_perkusi', label: 'Paru — perkusi' },
            { key: 'abdomen_inspeksi', label: 'Abdomen — inspeksi' },
            { key: 'abdomen_auskultasi', label: 'Abdomen — auskultasi' },
            { key: 'abdomen_palpasi', label: 'Abdomen — palpasi' },
            { key: 'abdomen_perkusi', label: 'Abdomen — perkusi' },
            { key: 'reflex_fisiologis_atas', label: 'Refleks fisiologis atas' },
            { key: 'reflex_fisiologis_bawah', label: 'Refleks fisiologis bawah' },
            { key: 'reflex_phatologis_atas', label: 'Refleks patologis atas' },
            { key: 'reflex_phatologis_bawah', label: 'Refleks patologis bawah' },
            { key: 'ekg_text', label: 'EKG' },
            { key: 'neurologis_text', label: 'Neurologis' },
            { key: 'kesimpulan', label: 'Kesimpulan' },
            { key: 'saran', label: 'Saran' },
        ],
    },
    {
        key: 'laboratoria',
        label: 'Laboratorium',
        resource: 'laboratoria',
        filterBy: 'participant_id',
        doneColumn: 'selesai',
        fields: [
            { key: 'hemoglobin', label: 'Hemoglobin' },
            { key: 'hematokrit', label: 'Hematokrit' },
            { key: 'lekosit', label: 'Lekosit' },
            { key: 'trombosit', label: 'Trombosit' },
            { key: 'eritrosit', label: 'Eritrosit' },
            { key: 'basofil', label: 'Basofil' },
            { key: 'eosinofil', label: 'Eosinofil' },
            { key: 'batang', label: 'Batang' },
            { key: 'segmen', label: 'Segmen' },
            { key: 'limfosit', label: 'Limfosit' },
            { key: 'monosit', label: 'Monosit' },
            { key: 'sgot', label: 'SGOT' },
            { key: 'sgpt', label: 'SGPT' },
            { key: 'ureum', label: 'Ureum' },
            { key: 'creatinin', label: 'Creatinin' },
            { key: 'glukosa_puasa', label: 'Glukosa puasa' },
            { key: 'glukosa_sewaktu', label: 'Glukosa sewaktu' },
            { key: 'cholesterol_total', label: 'Cholesterol total' },
            { key: 'trigliserida', label: 'Trigliserida' },
            { key: 'hdl_cholesterol', label: 'HDL cholesterol' },
            { key: 'ldl_cholestero', label: 'LDL cholesterol' },
            { key: 'asam_urat', label: 'Asam urat' },
            { key: 'hbsag', label: 'HBsAg' },
            { key: 'reduksi', label: 'Reduksi' },
            { key: 'berat_jenis', label: 'Berat jenis' },
            { key: 'ph_reaksi', label: 'pH reaksi' },
            { key: 'warna', label: 'Warna' },
            { key: 'kekeruhan', label: 'Kekeruhan' },
            { key: 'urobilinogen', label: 'Urobilinogen' },
            { key: 'bilirubin', label: 'Bilirubin' },
            { key: 'eritrosit_urine', label: 'Eritrosit (urine)' },
            { key: 'keton', label: 'Keton' },
            { key: 'protein', label: 'Protein' },
            { key: 'sedimen_epitel', label: 'Sedimen epitel' },
            { key: 'sedimen_eritrosit', label: 'Sedimen eritrosit' },
            { key: 'sedimen_leukosit', label: 'Sedimen leukosit' },
            { key: 'sedimen_bakteri', label: 'Sedimen bakteri' },
            { key: 'sedimen_kristal', label: 'Sedimen kristal' },
            { key: 'kesimpulan', label: 'Kesimpulan' },
            { key: 'pemeriksa_lab', label: 'Pemeriksa lab' },
            { key: 'lab_date', label: 'Tanggal lab', type: 'datetime' },
        ],
    },
    {
        key: 'radiologis',
        label: 'Radiologi',
        resource: 'radiologis',
        filterBy: 'participant_id',
        doneColumn: 'selesai',
        fields: [
            { key: 'cor', label: 'Cor' },
            { key: 'diafragma_sinus', label: 'Diafragma / sinus' },
            { key: 'pulmo', label: 'Pulmo' },
            { key: 'kesan', label: 'Kesan' },
            { key: 'diperiksa', label: 'Diperiksa' },
            { key: 'date_mcu', label: 'Tanggal MCU', type: 'date' },
        ],
    },
    {
        key: 'audiometris',
        label: 'Audiometri',
        resource: 'audiometris',
        filterBy: 'participant_id',
        doneColumn: 'selesai',
        fields: [
            { key: 'audiometri_telinga_kanan', label: 'Audiometri telinga kanan' },
            { key: 'audiometri_telinga_kiri', label: 'Audiometri telinga kiri' },
            { key: 'pendengaran_telinga_kanan', label: 'Pendengaran telinga kanan' },
            { key: 'pendengaran_telinga_kiri', label: 'Pendengaran telinga kiri' },
            { key: 'kesimpulan', label: 'Kesimpulan' },
            { key: 'saran', label: 'Saran' },
        ],
    },
    {
        key: 'spirometris',
        label: 'Spirometri',
        resource: 'spirometris',
        filterBy: 'participant_id',
        doneColumn: 'selesai',
        fields: [
            { key: 'hasil', label: 'Hasil' },
            { key: 'retriksi', label: 'Retriksi' },
            { key: 'obstruksif', label: 'Obstruktif' },
            boolean('normal', 'Normal'),
            boolean('restrictive', 'Restrictive'),
            boolean('obstructive', 'Obstructive'),
            boolean('mixed', 'Mixed'),
            boolean('lainnya', 'Lainnya'),
        ],
    },
    {
        key: 'rectals',
        label: 'Rectal',
        resource: 'rectals',
        filterBy: 'participant_id',
        doneColumn: 'selesai',
        fields: [
            { key: 'salmonella_thypi', label: 'Salmonella thypi' },
            { key: 'shigella_sp', label: 'Shigella sp.' },
            { key: 'e_coli_pathogen', label: 'E. coli patogen' },
            { key: 'kesimpulan', label: 'Kesimpulan' },
        ],
    },
    {
        key: 'ekgs',
        label: 'EKG',
        resource: 'ekgs',
        filterBy: 'participant_id',
        doneColumn: 'selesai',
        fields: [
            boolean('takikardi', 'Takikardi'),
            boolean('bradikardi', 'Bradikardi'),
            boolean('aritmia', 'Aritmia'),
            boolean('aresst', 'Arrest'),
            { key: 'penemuan_lain', label: 'Penemuan lain' },
            boolean('keadaan_jantung_normal', 'Keadaan jantung normal'),
            { key: 'kesimpulan', label: 'Kesimpulan' },
        ],
    },
    {
        key: 'general_results',
        label: 'Hasil Umum',
        resource: 'general_results',
        filterBy: 'participant_id',
        doneColumn: null,
        fields: [
            { key: 'name', label: 'Nama' },
            { key: 'mcu_id', label: 'MCU ID' },
            { key: 'result', label: 'Hasil' },
            { key: 'mcu_date', label: 'Tanggal MCU', type: 'datetime' },
        ],
    },
    {
        key: 'validate_doctors',
        label: 'Validasi Dokter',
        resource: 'validate_doctors',
        filterBy: 'no_mcu',
        doneColumn: null,
        fields: [
            { key: 'name', label: 'Nama' },
            { key: 'result_mcu', label: 'Hasil MCU' },
            { key: 'doctor', label: 'Dokter' },
            { key: 'notes', label: 'Catatan' },
            { key: 'date_mcu', label: 'Tanggal MCU', type: 'datetime' },
        ],
    },
];

/**
 * Hanya tabel hasil per peserta yang punya alur input manual seperti modal
 * legacy (`ParticipantController@update*`). Hasil Umum dan Validasi Dokter
 * berasal dari upload/import, dan `validate_doctors` tidak punya
 * `participant_id`, jadi keduanya hanya bisa dibaca.
 */
export function canInputResult(config: ExamResultConfig): boolean {
    return config.filterBy === 'participant_id' && config.doneColumn !== null;
}

/** Progres pemeriksaan pada `api.v_mcu_progress` (7 langkah legacy). */
export const progressSteps = [
    { key: 'fisik_done', label: 'Tanda vital' },
    { key: 'lab_done', label: 'Laboratorium' },
    { key: 'radiologi_done', label: 'Radiologi' },
    { key: 'spirometri_done', label: 'Spirometri' },
    { key: 'audiometri_done', label: 'Audiometri' },
    { key: 'rectal_done', label: 'Rectal' },
    { key: 'ekg_done', label: 'EKG' },
] as const;
