<script setup lang="ts">
import HasilTable from './HasilTable.vue';
import { examResults } from '@/lib/exams';
import type { ReportProps } from '@/lib/print';

defineProps<ReportProps>();

const fields = examResults.find((item) => item.resource === 'ekgs')?.fields ?? [];
</script>

<template>
    <HasilTable
        title="EKG"
        :fields="fields"
        :row="row"
        empty-label="Tidak ada hasil ekg untuk peserta ini."
    />
</template>
