<script setup lang="ts">
import { ageDetail } from '@/lib/format';
import { legacyDateTime, printGender } from '@/lib/print-format';
import type { ReportProps } from '@/lib/print';

const props = defineProps<ReportProps & { qr?: string | null }>();

/** Urutan & label sama dengan `pages/report/identitas.blade.php`. */
const fields = [
    { label: 'MCU ID', value: () => props.participant.code },
    { label: 'NIK', value: () => props.participant.nik },
    {
        label: 'Nama',
        value: () => `${props.participant.name} [${printGender(props.participant.gender)}]`,
    },
    { label: 'Tgl Lahir', value: () => props.participant.birthday },
    { label: 'Usia', value: () => ageDetail(props.participant.birthday) },
    { label: 'Status', value: () => props.participant.status ?? '-' },
    // Legacy mencetak nilai apa adanya; department/paket kosong berarti sel kosong
    // (bukan tanda hubung).
    { label: 'Department', value: () => props.participant.department_name ?? '' },
    { label: 'Paket MCU', value: () => props.participant.packet_name ?? '' },
    { label: 'Tgl Register', value: () => legacyDateTime(props.participant.register_date) },
];
</script>

<template>
    <table>
        <tbody>
            <tr>
                <td style="vertical-align: top">
                    <table style="font-size: 11px">
                        <tbody>
                            <tr v-for="field in fields" :key="field.label">
                                <td style="white-space: nowrap">{{ field.label }}</td>
                                <td style="white-space: nowrap">:</td>
                                <td style="width: 100%">{{ field.value() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width: 150px; vertical-align: top">
                    <img
                        v-if="qr"
                        :src="qr"
                        width="150"
                        height="150"
                        alt="QR peserta"
                        style="display: block; margin-left: auto"
                    />
                </td>
            </tr>
        </tbody>
    </table>
</template>
