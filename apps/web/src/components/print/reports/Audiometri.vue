<script setup lang="ts">
import HasilTable from './HasilTable.vue';
import { examResults } from '@/lib/exams';
import { printTitles, type ReportProps } from '@/lib/print';

defineProps<ReportProps>();

const fields = examResults.find((item) => item.resource === 'audiometris')?.fields ?? [];
</script>

<template>
    <HasilTable
        :title="printTitles.audiometri"
        :fields="fields"
        :row="row"
        empty-label="Tidak ada hasil audiometri untuk peserta ini."
    />
</template>
