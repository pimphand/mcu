/**
 * Tabel cetak laboratorium legacy (`pages/report/laboratorium.blade.php`)
 * memakai tiga kolom: PEMERIKSAAN, HASIL, NILAI NORMAL, dengan baris bagian
 * tebal (HEMATOLOGI, IMMUNO SEROLOGI, HEPATITIS, URINE LENGKAP).
 *
 * Nilai rujukan disalin dari view legacy. Baris `optional` hanya dicetak bila
 * peserta punya nilainya, sama seperti `@if` pada view legacy.
 */
export type PrintLabRow = {
    key: string;
    label: string;
    normal: string;
    optional?: boolean;
};

export type PrintLabSection = {
    title: string;
    rows: PrintLabRow[];
};

export const printLabSections: PrintLabSection[] = [
    {
        title: 'HEMATOLOGI',
        rows: [
            { key: 'hemoglobin', label: 'Hemoglobin', normal: 'P: 12 - 16 , L: 13 – 17 g/dl' },
            { key: 'hematokrit', label: 'Hematokrit', normal: 'P: 35–47% , L: 40–52%' },
            { key: 'lekosit', label: 'Lekosit', normal: '5.000 / µl – 10.000 / µ' },
            { key: 'trombosit', label: 'Trombosit', normal: '150.000 – 450.000 / µl' },
            {
                key: 'eritrosit',
                label: 'Eritrosit',
                normal: 'P: 4,0 – 5,5 juta / µl , L: 4,5 – 6,0 juta / µl',
            },
            { key: 'basofil', label: 'Basofil', normal: '0 – 1 %' },
            { key: 'eosinofil', label: 'Eosinofil', normal: '1 – 3 %' },
            { key: 'batang', label: 'Batang', normal: '2 – 6 %' },
            { key: 'segmen', label: 'Segmen', normal: '50 – 70 %' },
            { key: 'limfosit', label: 'Limfosit', normal: '20 – 40 %' },
            { key: 'monosit', label: 'Monosit', normal: '2 – 6%' },
        ],
    },
    { title: 'IMMUNO SEROLOGI', rows: [] },
    {
        title: 'HEPATITIS',
        rows: [
            { key: 'sgpt', label: 'SGPT', normal: '0 - 45 µl', optional: true },
            { key: 'hbsag', label: 'HBsAg Kualitatif', normal: 'Negatif' },
            {
                key: 'creatinin',
                label: 'Creatinin',
                normal: 'P: < 0.6 - 1.2 mg/dL, L: < 0.6 - 1.4 mg/dL',
                optional: true,
            },
            { key: 'ureum', label: 'Ureum', normal: '10 - 40 mg', optional: true },
        ],
    },
    {
        title: 'URINE LENGKAP',
        rows: [
            { key: 'reduksi', label: 'Reduksi', normal: 'Negatif' },
            { key: 'berat_jenis', label: 'Berat Jenis', normal: '' },
            { key: 'ph_reaksi', label: 'PH / Reaksi', normal: '' },
            { key: 'warna', label: 'Warna', normal: '' },
            { key: 'kekeruhan', label: 'Kekeruhan', normal: '' },
            { key: 'urobilinogen', label: 'Urobilinogen', normal: 'Normal' },
            { key: 'bilirubin', label: 'Bilirubin', normal: 'Negatif' },
            { key: 'eritrosit_urine', label: 'Eritrosit', normal: 'Negatif' },
            { key: 'keton', label: 'Keton', normal: 'Negatif' },
            { key: 'protein', label: 'Protein', normal: 'Negatif' },
            { key: 'sedimen_epitel', label: 'Sedimen – Epitel', normal: 'Negatif' },
            { key: 'sedimen_eritrosit', label: 'Sedimen – Eritrosit', normal: '0 – 1 / Lpb' },
            { key: 'sedimen_leukosit', label: 'Sedimen – Leukosit', normal: '0 – 5 / Lpb' },
            { key: 'sedimen_bakteri', label: 'Sedimen - Bakteri', normal: 'Negatif' },
            { key: 'sedimen_kristal', label: 'Sedimen – Kristal', normal: 'Negatif' },
        ],
    },
];

export function labRowVisible(row: PrintLabRow, result: Record<string, unknown> | null): boolean {
    if (!row.optional) return true;

    const value = result?.[row.key];

    return value !== null && value !== undefined && value !== '';
}
