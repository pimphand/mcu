<script setup lang="ts">
import { printLabSections, labRowVisible } from '@/lib/print-lab';
import { displayResult, isEmptyResult } from '@/lib/print-format';
import type { ReportProps } from '@/lib/print';

const props = defineProps<ReportProps>();

/** Legacy hanya mencetak tabel + kesimpulan; pemeriksa ada di blok tanda tangan. */
const isEmpty = () =>
    !props.row ||
    printLabSections.every((section) =>
        section.rows.every((item) => isEmptyResult(props.row?.[item.key])),
    );
</script>

<template>
    <div class="print-lab">
        <table class="print-border" style="font-size: 12px">
            <thead>
                <tr>
                    <th style="width: 33.3%">PEMERIKSAAN</th>
                    <th style="width: 33.3%">HASIL</th>
                    <th style="width: 33.3%">NILAI NORMAL</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="isEmpty()">
                    <td colspan="3" style="text-align: center">
                        Tidak ada hasil laboratorium untuk peserta ini.
                    </td>
                </tr>
                <template v-else>
                    <template v-for="section in printLabSections" :key="section.title">
                        <tr>
                            <td colspan="3">
                                <b>{{ section.title }}</b>
                            </td>
                        </tr>
                        <tr
                            v-for="item in section.rows.filter((row) =>
                                labRowVisible(row, props.row),
                            )"
                            :key="item.key"
                        >
                            <td>{{ item.label }}</td>
                            <td style="text-align: center">
                                {{ displayResult(props.row?.[item.key]) }}
                            </td>
                            <td style="text-align: center">{{ item.normal }}</td>
                        </tr>
                    </template>
                </template>
            </tbody>
        </table>
    </div>
</template>
