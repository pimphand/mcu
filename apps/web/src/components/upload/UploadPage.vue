<script setup lang="ts">
import { ref } from 'vue';
import type { ImportType } from '@/lib/imports/types';
import { uploadReports } from '@/lib/upload-reports';
import UploadPanel from './UploadPanel.vue';
import UploadResultsTable from './UploadResultsTable.vue';

const props = defineProps<{
    type: ImportType;
    canUpload: boolean;
}>();

const report = uploadReports[props.type];
const table = ref<{ refresh: () => Promise<void> } | null>(null);

function onImported(): void {
    void table.value?.refresh();
}
</script>

<template>
    <div class="space-y-5">
        <UploadPanel :type="type" :can-upload="canUpload" @imported="onImported" />
        <div>
            <h2 class="mb-2 text-[1rem] font-semibold text-ink">Hasil pada kontrak aktif</h2>
            <UploadResultsTable ref="table" :report="report" />
        </div>
    </div>
</template>
