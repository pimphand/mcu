<script setup lang="ts">
import type { ResultField } from '@/lib/exams';
import { displayResult, isEmptyResult } from '@/lib/print-format';

const props = defineProps<{
    title: string;
    fields: ResultField[];
    row: Record<string, unknown> | null;
    emptyLabel: string;
}>();

function isRowEmpty(): boolean {
    return !props.row || props.fields.every((field) => isEmptyResult(props.row?.[field.key]));
}
</script>

<template>
    <!-- Judul & ukuran huruf tabel mengikuti view auditometri/EKG/spirometri/rectal
         legacy: judul 18px, sel `.border` 14px, kolom PEMERIKSAAN 50% dan HASIL
         rata tengah. -->
    <!-- `margin-top` 20px: DomPDF tidak meruntuhkan margin judul dengan margin
         blok identitas, sedangkan browser melakukannya. -->
    <div class="print-title" style="font-size: 18px; margin: 20px 0 10px">{{ props.title }}</div>
    <table class="print-border" style="font-size: 14px">
        <tbody>
            <tr v-if="isRowEmpty()">
                <td colspan="2" style="text-align: center">{{ props.emptyLabel }}</td>
            </tr>
            <template v-else>
                <tr v-for="field in props.fields" :key="field.key">
                    <td style="width: 50%">{{ field.label }}</td>
                    <td style="text-align: center">
                        {{ displayResult(props.row?.[field.key], field.type) }}
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</template>
