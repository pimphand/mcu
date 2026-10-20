import { examResults, type ExamResultConfig } from './exams';

/**
 * Jenis cetak yang tersedia di Print Hasil MCU, mengikuti hub legacy
 * `/participant-print-mcu` (tombol stiker sampai rectal).
 */
export type PrintType =
    | 'identitas'
    | 'resume'
    | 'pemeriksaan-fisik'
    | 'laboratorium'
    | 'radiologi'
    | 'audiometri'
    | 'ekg'
    | 'spirometri'
    | 'rectal';

export type PrintParticipant = {
    participant_id: number;
    code: string;
    nik: string;
    name: string;
    gender: string;
    birthday: string;
    status: string | null;
    packet_name: string | null;
    no_form: number | null;
    register_number: string | null;
    register_date: string | null;
    client_id: number;
    client_name: string | null;
    contract_id: number | null;
    contract_name: string | null;
    divisi_id: number | null;
    divisi_name: string | null;
    department_id: number | null;
    department_name: string | null;
    validated: boolean;
    physical_done: boolean;
    lab_done: boolean;
    radiologi_done: boolean;
    spirometri_done: boolean;
    audiometri_done: boolean;
    rectal_done: boolean;
    ekg_done: boolean;
};

export type DoctorRow = {
    id: number;
    no_mcu: string | null;
    name: string | null;
    result_mcu: string | null;
    doctor: string | null;
    notes: string | null;
    date_mcu: string | null;
};

/**
 * Kontrak seragam untuk komponen isi laporan (`components/print/reports`).
 * Semua komponen mendeklarasikan props ini supaya `ReportSheet` bisa memilih
 * komponen lewat peta jenis cetak tanpa atribut liar di DOM.
 */
export type ResultRow = Record<string, unknown> | null;

/** Hasil lain yang dilampirkan pada cetak pemeriksaan fisik. */
export type RelatedResults = Partial<Record<string, ResultRow>>;

export type ReportProps = {
    participant: PrintParticipant;
    row: ResultRow;
    summary: ResumeSummaryRow[];
    doctor: DoctorRow | null;
    /** Tanda vital; cetak pemeriksaan fisik legacy menampilkannya paling atas. */
    vitals?: ResultRow;
    /** laboratoria/radiologis/audiometris/ekgs/spirometris/rectals. */
    related?: RelatedResults;
};

export type SignatureInfo = {
    name: string | null;
    ttd: string | null;
};

export type EmployeeRow = {
    id: number;
    nama: string | null;
    ttd: string | null;
};

/** Tanda tangan cetak: nama & path `ttd` dari employees, fallback nama hasil. */
export function signatureFor(
    employee: EmployeeRow | null | undefined,
    fallbackName: string | null = null,
): SignatureInfo {
    return { name: employee?.nama ?? fallbackName, ttd: employee?.ttd ?? null };
}

type DoneKey =
    | 'validated'
    | 'physical_done'
    | 'lab_done'
    | 'radiologi_done'
    | 'audiometri_done'
    | 'ekg_done'
    | 'spirometri_done'
    | 'rectal_done';

export type PrintTypeConfig = {
    type: PrintType;
    label: string;
    /** Nama tabel hasil; `null` untuk identitas/resume. */
    resource: string | null;
    /** Kolom status di `v_mcu_progress`, `null` untuk identitas. */
    doneKey: DoneKey | null;
};

export const printTypes: PrintTypeConfig[] = [
    { type: 'identitas', label: 'Stiker', resource: null, doneKey: null },
    { type: 'resume', label: 'Resume', resource: null, doneKey: 'validated' },
    {
        type: 'pemeriksaan-fisik',
        label: 'Pem. Fisik',
        resource: 'pemeriksaan_fisiks',
        doneKey: 'physical_done',
    },
    { type: 'laboratorium', label: 'Lab', resource: 'laboratoria', doneKey: 'lab_done' },
    { type: 'radiologi', label: 'Rad', resource: 'radiologis', doneKey: 'radiologi_done' },
    {
        type: 'audiometri',
        label: 'Audiometri',
        resource: 'audiometris',
        doneKey: 'audiometri_done',
    },
    { type: 'ekg', label: 'EKG', resource: 'ekgs', doneKey: 'ekg_done' },
    {
        type: 'spirometri',
        label: 'Spiro',
        resource: 'spirometris',
        doneKey: 'spirometri_done',
    },
    { type: 'rectal', label: 'Rectal', resource: 'rectals', doneKey: 'rectal_done' },
];

export const printTitles: Record<PrintType, string> = {
    identitas: 'STIKER IDENTITAS',
    resume: 'KESIMPULAN DAN SARAN',
    'pemeriksaan-fisik': 'PEMERIKSAAN FISIK',
    laboratorium: 'LABORATORIUM',
    radiologi: 'RADIOLOGI',
    audiometri: 'AUDIOMETRI',
    ekg: 'ELEKTROKARDIOGRAFI (EKG)',
    spirometri: 'SPIROMETRI',
    rectal: 'RECTAL SWAB',
};

/**
 * Tabel hasil yang dibaca untuk resume beserta kolom yang ditampilkan.
 * Satu query per tabel, bukan per baris resume.
 */
export const resumeSources: { resource: string; fields: string[] }[] = [
    { resource: 'laboratoria', fields: ['kesimpulan'] },
    { resource: 'pemeriksaan_fisiks', fields: ['kesimpulan'] },
    { resource: 'radiologis', fields: ['kesan'] },
    {
        resource: 'audiometris',
        fields: ['pendengaran_telinga_kanan', 'pendengaran_telinga_kiri'],
    },
    { resource: 'ekgs', fields: ['kesimpulan'] },
    { resource: 'spirometris', fields: ['hasil'] },
    { resource: 'rectals', fields: ['kesimpulan'] },
];

export type ResumeRow = {
    /** Baris bagian tebal (mis. `LABORATORIUM (TERLAMPIR)`). */
    section?: boolean;
    label: string;
    resource?: string;
    field?: string;
};

export type ResumeSummaryRow = { label: string; value: string; section?: boolean };

/** Urutan baris tabel resume sama dengan `pages/report/resume.blade.php`. */
export const resumeRows: ResumeRow[] = [
    { section: true, label: 'LABORATORIUM (TERLAMPIR)' },
    { label: 'Hasil Laboratorium', resource: 'laboratoria', field: 'kesimpulan' },
    { section: true, label: 'HASIL PHOTO THORAX (TERLAMPIR)' },
    { label: 'Hasil dan Kesan', resource: 'radiologis', field: 'kesan' },
    { section: true, label: 'AUDIOMETRI (TERLAMPIR)' },
    {
        label: 'Pendengaran Telinga Kanan',
        resource: 'audiometris',
        field: 'pendengaran_telinga_kanan',
    },
    {
        label: 'Pendengaran Telinga Kiri',
        resource: 'audiometris',
        field: 'pendengaran_telinga_kiri',
    },
    { section: true, label: 'EKG (TERLAMPIR)' },
    { label: 'Kesimpulan EKG', resource: 'ekgs', field: 'kesimpulan' },
    { section: true, label: 'SPIROMETRI (TERLAMPIR)' },
    { label: 'Kesimpulan Spiro', resource: 'spirometris', field: 'hasil' },
];

/**
 * Menyusun baris resume dari hasil tiap tabel. Nilai kosong dicetak `-` seperti
 * view legacy; baris bagian tidak punya nilai.
 */
export function buildResumeSummary(
    results: Map<string, Record<string, unknown> | null>,
): ResumeSummaryRow[] {
    return resumeRows.map((row) => {
        if (row.section) return { label: row.label, value: '', section: true };

        const value = results.get(row.resource ?? '')?.[row.field ?? ''];

        return {
            label: row.label,
            value: value === null || value === undefined || value === '' ? '-' : String(value),
        };
    });
}

/**
 * Isi QR header cetakan: `{contract_id}-{kode MCU}-{jenis cetak}` supaya peserta
 * dari kontrak berbeda dan tiap lembar hasil tetap unik. Tanpa kontrak, pakai
 * kode peserta; tanpa jenis, pakai format lama.
 */
export function printQrValue(
    code: string,
    contractId?: number | null,
    type?: string | null,
): string {
    const base = contractId ? `${contractId}-${code}` : code;

    return type ? `${base}-${type}` : base;
}

export function isPrintType(value: string): value is PrintType {
    return printTypes.some((item) => item.type === value);
}

export type PrintPageConfig = {
    /** Ukuran kertas `@page`. */
    pageSize: string;
    /** Margin `@page` (atas kanan bawah kiri). */
    pageMargin: string;
    /** Lebar area cetak = lebar kertas - margin kiri/kanan. */
    contentWidth: string;
};

/**
 * Ukuran kertas tiap jenis cetak mengikuti view legacy: stiker 150x100mm dan
 * hasil pemeriksaan 21x33cm. Audiometri/EKG/spirometri/rectal tidak punya
 * deklarasi `@page`, sehingga DomPDF memakai default A4 dengan margin 1,2cm.
 */
export function printPageConfig(type: PrintType): PrintPageConfig {
    if (type === 'identitas') {
        return { pageSize: '150mm 100mm', pageMargin: '1.2cm', contentWidth: '12.6cm' };
    }

    if (type === 'audiometri' || type === 'ekg' || type === 'spirometri' || type === 'rectal') {
        return { pageSize: '210mm 297mm', pageMargin: '1.2cm', contentWidth: '18.6cm' };
    }

    return { pageSize: '21cm 33cm', pageMargin: '1cm 1cm 1cm 2.5cm', contentWidth: '17.5cm' };
}

export function printTypeConfig(type: PrintType): PrintTypeConfig {
    const config = printTypes.find((item) => item.type === type);

    if (!config) {
        throw new Error(`Jenis cetak tidak dikenal: ${type}`);
    }

    return config;
}

/** Konfigurasi field tabel hasil (label & urutan sama dengan detail peserta). */
export function resultConfigFor(type: PrintType): ExamResultConfig | null {
    const resource = printTypeConfig(type).resource;

    if (!resource) return null;

    return examResults.find((config) => config.resource === resource) ?? null;
}

function jakartaDate(now: Date): string {
    return new Intl.DateTimeFormat('en-CA', {
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(now);
}

/**
 * Rentang default halaman Print MCU: tanggal 1 bulan berjalan sampai hari ini
 * (zona Asia/Jakarta), sama seperti `ParticipantController@printMCU`.
 */
export function defaultPrintRange(now: Date = new Date()): { start: string; end: string } {
    const today = jakartaDate(now);

    return { start: `${today.slice(0, 8)}01`, end: today };
}

export function isIsoDate(value: string | null | undefined): value is string {
    return Boolean(value && /^\d{4}-\d{2}-\d{2}$/.test(value));
}

/**
 * Filter rentang register untuk PostgREST memakai offset Asia/Jakarta supaya
 * peserta yang register dini hari tidak jatuh ke tanggal sebelumnya.
 */
export function registerDateFilter(start: string, end: string): string {
    return `(register_date.gte.${start}T00:00:00+07:00,register_date.lte.${end}T23:59:59+07:00)`;
}
