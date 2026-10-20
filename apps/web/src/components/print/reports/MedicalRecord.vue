<script setup lang="ts">
import { ageDetail } from '@/lib/format';
import { legacyShortDate, printGender } from '@/lib/print-format';
import type { ReportProps } from '@/lib/print';

defineProps<ReportProps>();

/** Urutan & label sama dengan `pages/report/resume.blade.php` (halaman 1). */
const labels = (participant: ReportProps['participant']) => [
    { label: 'Tgl Register', value: legacyShortDate(participant.register_date) },
    { label: 'MCU ID', value: participant.code },
    {
        label: 'Nama',
        value: `${participant.name} [${printGender(participant.gender)}]`,
    },
    { label: 'Tgl Lahir', value: legacyShortDate(participant.birthday) },
    { label: 'Umur', value: ageDetail(participant.birthday) },
    { label: 'Perusahaan', value: participant.client_name ?? '-' },
    { label: 'NIK', value: participant.nik },
    { label: 'Divisi', value: participant.divisi_name ?? '-' },
    { label: 'Departement', value: participant.department_name ?? '-' },
    { label: 'Paket MCU', value: participant.packet_name ?? '-' },
];
</script>

<template>
    <div style="padding: 2px">
        <div style="text-align: center; margin-bottom: 16px">
            <div
                style="
                    display: inline-block;
                    background: #5f8a8b;
                    color: #fff;
                    padding: 10px;
                    border-radius: 10px;
                    font-size: 18.7px;
                    font-weight: 700;
                "
            >
                MEDICAL RECORD
            </div>
        </div>

        <!-- Tabel 4 kolom seperti legacy: dua sel kosong di kiri supaya data
             peserta berada di separuh kanan lembar. -->
        <table class="print-border" style="font-size: 12px">
            <thead>
                <tr>
                    <th colspan="4" style="background: #e0e0e0; text-align: center">
                        Data Peserta MCU
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in labels(participant)" :key="item.label">
                    <!-- Lebar sel kosong ~7% seperti lembar legacy, supaya data
                         peserta duduk di separuh kanan tabel. -->
                    <td style="width: 7%; border-right-color: #fff; border-bottom-color: #fff"></td>
                    <td style="width: 7%; border-bottom-color: #fff"></td>
                    <td style="border-right-color: #fff; border-bottom-color: #fff">
                        {{ item.label }}
                    </td>
                    <td style="border-bottom-color: #fff">: {{ item.value }}</td>
                </tr>
            </tbody>
        </table>

        <table style="margin-top: 10px">
            <tbody>
                <tr>
                    <td colspan="4" style="text-align: center; font-weight: 700; font-size: 20px">
                        {{ participant.client_name ?? '' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
