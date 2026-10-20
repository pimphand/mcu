<script setup lang="ts">
import type { ReportProps } from '@/lib/print';

const props = defineProps<ReportProps>();

type Cell =
    | { type: 'section'; label: string }
    /** `align` mengikuti view legacy: sel hasil rata tengah, sel Saran rata kiri. */
    | { type: 'row'; label: string; value: string; align?: 'left' };

function isBlank(value: unknown): boolean {
    return value === null || value === undefined || value === '';
}

function text(value: unknown): string {
    return isBlank(value) ? '-' : String(value);
}

function yaTidak(value: unknown): string {
    return value ? 'YA' : 'TIDAK';
}

function withUnit(value: unknown, unit: string): string {
    return isBlank(value) ? '-' : `${value} ${unit}`;
}

/**
 * Urutan baris mengikuti `pages/report/pemeriksaan-fisik.blade.php` supaya
 * pemenggalan halaman (termasuk halaman 2 yang dimulai dari Auskultasi) sama.
 */
function buildRows(): Cell[] {
    const { row, vitals, related = {} } = props;
    const rows: Cell[] = [];

    if (vitals) {
        rows.push({ type: 'section', label: 'TANDA VITAL' });
        rows.push(
            { type: 'row', label: 'Keluhan Utama', value: text(vitals.keluhan_utama_text) },
            {
                type: 'row',
                label: 'Riwayat Penyakit Sekarang',
                value: text(vitals.riwayat_penyakit_sekarang_text),
            },
            {
                type: 'row',
                label: 'Riwayat Penyakit Terdahulu',
                value: text(vitals.riwayat_penyakit_terdahulu_text),
            },
            { type: 'row', label: 'Alergi', value: text(vitals.alergi_text) },
            { type: 'row', label: 'Merokok', value: yaTidak(vitals.merokok) },
            { type: 'row', label: 'Konsumsi Alkohol', value: yaTidak(vitals.konsumsi_alkohol) },
            { type: 'row', label: 'Riwayat Trauma', value: text(vitals.riwayat_trauma_text) },
            { type: 'row', label: 'Tinggi Badan', value: withUnit(vitals.tinggi_badan, 'cm') },
            { type: 'row', label: 'Berat Badan', value: withUnit(vitals.berat_badan, 'kg') },
            { type: 'row', label: 'IMT', value: text(vitals.imt) },
            { type: 'row', label: 'Nilai IMT', value: text(vitals.imt_nilai) },
            { type: 'row', label: 'Tekanan Darah', value: withUnit(vitals.tekanan_darah, 'mmHg') },
            {
                type: 'row',
                label: 'Frekuensi Nadi',
                value: withUnit(vitals.frekuensi_nadi, 'x/menit'),
            },
            { type: 'row', label: 'Suhu', value: withUnit(vitals.suhu, 'Celcius') },
            {
                type: 'row',
                label: 'Frekuensi Nafas',
                value: withUnit(vitals.frekuensi_nafas, 'x/menit'),
            },
            { type: 'row', label: 'Ibu Hamil', value: yaTidak(vitals.ibu_hamil) },
        );
    }

    rows.push(
        { type: 'section', label: 'KEADAAN UMUM' },
        { type: 'row', label: 'Keadaan Umum', value: text(row?.keadaan_umum) },
        { type: 'section', label: 'KEPALA' },
        { type: 'row', label: 'Kepala', value: text(row?.kepala_text) },
        { type: 'row', label: 'Hidung', value: text(row?.hidung) },
        { type: 'row', label: 'Mata', value: text(row?.mata) },
        { type: 'row', label: 'Pupil', value: text(row?.pupil) },
        { type: 'row', label: 'Visus', value: text(row?.visus) },
    );

    if (row?.berkacamata) {
        rows.push({ type: 'row', label: 'Berkacamata', value: yaTidak(row.berkacamata) });
    }

    rows.push(
        { type: 'row', label: 'Buta Warna', value: text(row?.buta_warna) },
        { type: 'row', label: 'Telinga', value: text(row?.telinga) },
        { type: 'row', label: 'kelainan Telinga', value: text(row?.kelainan_telinga) },
        { type: 'row', label: 'Gigi', value: text(row?.gigi) },
        { type: 'section', label: 'MULUT' },
        { type: 'row', label: 'Bibir', value: text(row?.bibir) },
        { type: 'row', label: 'Lidah', value: text(row?.lidah) },
        { type: 'section', label: 'TENGGOROKAN' },
        { type: 'row', label: 'Tenggorokan', value: text(row?.tenggorokan_text) },
        { type: 'row', label: 'Faring', value: text(row?.faring) },
        { type: 'section', label: 'LEHER' },
        { type: 'row', label: 'Leher KGB', value: text(row?.leher_kgb) },
        { type: 'row', label: 'Leher JVP', value: text(row?.leher_jvp) },
        { type: 'section', label: 'THORAX JANTUNG' },
        { type: 'row', label: 'Inspeksi', value: text(row?.jantung_inspeksi) },
        { type: 'row', label: 'Auskultasi', value: text(row?.jantung_auskultasi) },
        { type: 'row', label: 'Palpasi', value: text(row?.jantung_palpasi) },
        { type: 'row', label: 'Perkusi', value: text(row?.jantung_perkusi) },
        { type: 'section', label: 'THORAX PARU-PARU' },
        { type: 'row', label: 'Inspeksi', value: text(row?.paru_inspeksi) },
        {
            type: 'row',
            label: 'Auskultasi Vasikuler',
            value: text(row?.paru_auskultasi_vasikuler),
        },
        { type: 'row', label: 'Auskultasi Ronkhi', value: text(row?.paru_auskultasi_ronkhi) },
        { type: 'row', label: 'Auskultasi Wheezing', value: text(row?.paru_auskultasi_wheezing) },
        { type: 'row', label: 'Palpasi', value: text(row?.paru_palpasi) },
        { type: 'row', label: 'Perkusi', value: text(row?.paru_perkusi) },
        { type: 'section', label: 'THORAX ABDOMEN' },
        { type: 'row', label: 'Inspeksi', value: text(row?.abdomen_inspeksi) },
        { type: 'row', label: 'Auskultasi', value: text(row?.abdomen_auskultasi) },
        { type: 'row', label: 'Palpasi', value: text(row?.abdomen_palpasi) },
        { type: 'row', label: 'Perkusi', value: text(row?.abdomen_perkusi) },
        { type: 'section', label: 'EXTREMITAS' },
        { type: 'row', label: 'Reflex Fisiologis Atas', value: text(row?.reflex_fisiologis_atas) },
        { type: 'row', label: 'Reflex Phatologis Atas', value: text(row?.reflex_phatologis_atas) },
        {
            type: 'row',
            label: 'Reflex Fisiologis Bawah',
            value: text(row?.reflex_fisiologis_bawah),
        },
        {
            type: 'row',
            label: 'Reflex Phatologis Bawah',
            value: text(row?.reflex_phatologis_bawah),
        },
    );

    if (row?.lab_diperiksa) {
        rows.push(
            { type: 'section', label: 'LABORATORIUM (TERLAMPIR)' },
            {
                type: 'row',
                label: 'Hasil Laboratorium',
                value: text(related.laboratoria?.kesimpulan),
            },
        );
    }

    rows.push(
        { type: 'section', label: 'HASIL PHOTO THORAX (TERLAMPIR)' },
        {
            type: 'row',
            label: 'Hasil dan Kesan',
            value: row?.radiologi_diperiksa ? text(related.radiologis?.kesan) : 'TIDAK DIPERIKSA',
        },
    );

    if (row?.neurologis_tidak_diperiksa) {
        rows.push({
            type: 'row',
            label: 'Neurologis',
            value: row.neurologis_text === 'BDN' ? 'DALAM BATAS NORMAL' : text(row.neurologis_text),
        });
    }

    if (row?.audiometri_diperiksa) {
        rows.push(
            { type: 'section', label: 'HASIL AUDIOMETRI (TERLAMPIR)' },
            { type: 'row', label: 'Pendengaran Telinga Kanan', value: 'DIPERIKSA' },
            { type: 'row', label: 'Pendengaran Telinga Kiri', value: 'DIPERIKSA' },
        );
    }

    if (row?.ekg_diperiksa) {
        rows.push(
            { type: 'section', label: 'EKG (TERLAMPIR)' },
            { type: 'row', label: 'Kesimpulan EKG', value: 'DIPERIKSA' },
        );
    }

    if (row?.spiro_diperiksa) {
        rows.push(
            { type: 'section', label: 'SPIROMETRI (TERLAMPIR)' },
            {
                type: 'row',
                label: 'Kesimpulan Spiro',
                value: related.spirometris?.selesai ? 'DIPERIKSA' : 'TIDAK DIPERIKSA',
            },
        );
    }

    if (row?.rectal_diperiksa) {
        rows.push(
            { type: 'section', label: 'RECTAL SWAB (TERLAMPIR)' },
            {
                type: 'row',
                label: 'Salmonella Thypi',
                value: text(related.rectals?.salmonella_thypi),
            },
            { type: 'row', label: 'Shigella SP', value: text(related.rectals?.shigella_sp) },
            {
                type: 'row',
                label: 'E. Coli Pathogen',
                value: text(related.rectals?.e_coli_pathogen),
            },
        );
    }

    rows.push(
        { type: 'section', label: 'HASIL MCU' },
        { type: 'row', label: 'Kesimpulan', value: text(row?.kesimpulan) },
        // Legacy memakai class `text-start` (tidak terdefinisi) untuk saran.
        { type: 'row', label: 'Saran', value: text(row?.saran), align: 'left' },
    );

    return rows;
}

const rows = buildRows();
</script>

<template>
    <!-- `font-size` 13px & baris judul tanpa huruf tebal sama dengan `.border`
         pada `pages/report/pemeriksaan-fisik.blade.php`. -->
    <table class="print-border" style="font-size: 13px">
        <thead>
            <tr>
                <!-- Tanpa `text-align`, jadi rata tengah seperti `<th class="border">` legacy. -->
                <th style="width: 50%">PEMERIKSAAN</th>
                <th>HASIL</th>
            </tr>
        </thead>
        <tbody>
            <template v-for="(item, index) in rows" :key="index">
                <tr v-if="item.type === 'section'">
                    <td colspan="2">{{ item.label }}</td>
                </tr>
                <tr v-else>
                    <td>{{ item.label }}</td>
                    <td
                        :style="{
                            textAlign: item.align === 'left' ? 'left' : 'center',
                            whiteSpace: 'pre-line',
                        }"
                    >
                        {{ item.value }}
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</template>
