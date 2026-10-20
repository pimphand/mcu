<script setup lang="ts">
import type { ReportProps } from '@/lib/print';

defineProps<ReportProps>();
</script>

<template>
    <table class="print-border" style="font-size: 12px">
        <thead>
            <tr>
                <th colspan="2" style="background: #e0e0e0; text-align: center">PEMERIKSAAN</th>
                <th style="background: #e0e0e0; text-align: center">HASIL</th>
            </tr>
        </thead>
        <tbody>
            <tr v-if="summary.length === 0">
                <td colspan="3" style="text-align: center">Belum ada hasil pemeriksaan.</td>
            </tr>
            <!-- Baris bagian (mis. `LABORATORIUM (TERLAMPIR)`) tanpa nilai,
                 sama seperti tabel resume legacy. -->
            <template v-for="item in summary" :key="item.label">
                <tr v-if="item.section">
                    <td colspan="2">{{ item.label }}</td>
                    <td></td>
                </tr>
                <tr v-else>
                    <td colspan="2">{{ item.label }}</td>
                    <td>{{ item.value }}</td>
                </tr>
            </template>
            <tr>
                <td colspan="2">HASIL MCU</td>
                <td>{{ doctor?.result_mcu ?? '-' }}</td>
            </tr>
            <tr>
                <td colspan="2">Saran</td>
                <td>{{ doctor?.notes ?? '-' }}</td>
            </tr>
            <tr>
                <td colspan="2">Dokter pemeriksa</td>
                <td>{{ doctor?.doctor ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div style="border: 1px solid #000; width: 100%; margin-top: 10px">
        <div
            style="
                background: #e0e0e0;
                border-bottom: 1px solid #000;
                padding: 5px;
                font-weight: 700;
                font-size: 15px;
            "
        >
            FIT STATUS
        </div>
        <div
            style="
                background: #a8d0d0;
                text-align: center;
                padding: 10px;
                font-size: 20px;
                font-weight: 700;
            "
        >
            {{ doctor?.result_mcu || 'BELUM DIVALIDASI' }}
        </div>
    </div>
</template>
