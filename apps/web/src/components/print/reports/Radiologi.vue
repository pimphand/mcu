<script setup lang="ts">
import { displayResult } from '@/lib/print-format';
import type { ReportProps } from '@/lib/print';

defineProps<ReportProps>();

/** Urutan & label sama dengan `pages/report/radiologi.blade.php`. */
const fields = [
    { key: 'cor', label: 'COR' },
    { key: 'diafragma_sinus', label: 'Diafragma Sinus' },
    { key: 'pulmo', label: 'Pulmo' },
    { key: 'kesan', label: 'Kesan' },
];
</script>

<template>
    <!-- `margin-top` 20px: DomPDF tidak meruntuhkan margin judul dengan margin
         blok identitas, sedangkan browser melakukannya. -->
    <div class="print-title" style="font-size: 18px; margin: 20px 0 10px">RADIOLOGI</div>
    <table class="print-border" style="font-size: 14px">
        <thead>
            <tr>
                <!-- `.border` legacy `width: 50%` dengan hasil rata tengah. -->
                <th style="width: 50%">PEMERIKSAAN</th>
                <th style="width: 50%">HASIL</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="field in fields" :key="field.key">
                <td style="width: 50%">{{ field.label }}</td>
                <td style="text-align: center">
                    {{ displayResult(row?.[field.key]) }}
                </td>
            </tr>
        </tbody>
    </table>
</template>
