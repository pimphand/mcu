<script setup lang="ts">
import { displayResult } from '@/lib/print-format';
import type { ReportProps } from '@/lib/print';

const props = defineProps<ReportProps>();

/** Urutan, label, dan nilai normal sama dengan `pages/report/rectal.blade.php`. */
const rows = [
    { key: 'salmonella_thypi', label: 'Salmonella Thypi', normal: 'Negatif' },
    { key: 'shigella_sp', label: 'Shigella SP', normal: 'Negatif' },
    { key: 'e_coli_pathogen', label: 'E. Coli Pathogen', normal: 'Negatif' },
];
</script>

<template>
    <table class="print-border" style="font-size: 14px">
        <thead>
            <tr>
                <th style="width: 33.3%">PEMERIKSAAN</th>
                <th style="width: 33.3%">HASIL</th>
                <th style="width: 33.3%">NILAI NORMAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3">RECTAL SWAB</td>
            </tr>
            <tr v-for="item in rows" :key="item.key">
                <td>{{ item.label }}</td>
                <td style="text-align: center">{{ displayResult(props.row?.[item.key]) }}</td>
                <td style="text-align: center">{{ item.normal }}</td>
            </tr>
        </tbody>
    </table>
</template>
