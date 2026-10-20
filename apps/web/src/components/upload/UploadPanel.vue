<script setup lang="ts">
import { computed, ref } from 'vue';
import { errorMessage, importResponseSchema, type ImportResponse } from '@/lib/imports/api';
import { workbookSignature, type WorkbookSignature } from '@/lib/imports/file-signature';
import type { ImportType } from '@/lib/imports/types';

const props = defineProps<{
    type: ImportType;
    canUpload: boolean;
}>();

const emit = defineEmits<{ imported: [] }>();

const MAX_BYTES = 10 * 1024 * 1024;

/**
 * Jenis file dinilai dari isinya, bukan dari nama. File yang diunduh dari email
 * atau di-rename (mis. `laporan.xlsm`, nama dengan spasi/titik ganda) tetap bisa
 * diunggah selama isinya workbook Excel. Aturan yang sama dipakai server.
 */
async function detectWorkbook(file: File): Promise<WorkbookSignature> {
    return workbookSignature(await file.slice(0, 8).arrayBuffer());
}

const fileInput = ref<HTMLInputElement | null>(null);
const selected = ref<File | null>(null);
const uploading = ref(false);
const error = ref('');
const result = ref<ImportResponse | null>(null);

const canSubmit = computed(() => props.canUpload && selected.value !== null && !uploading.value);

function chooseFile(): void {
    fileInput.value?.click();
}

async function onFileChange(event: Event): Promise<void> {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    error.value = '';
    result.value = null;
    selected.value = null;

    if (!file) return;

    if (file.size > MAX_BYTES) {
        error.value = 'Ukuran file melebihi batas 10 MB.';
        input.value = '';
        return;
    }

    let kind: WorkbookSignature;

    try {
        kind = await detectWorkbook(file);
    } catch {
        kind = 'other';
    }

    if (kind === 'xls') {
        error.value =
            'File .xls (Excel 97-2003) belum didukung. Buka di Excel, simpan ulang sebagai .xlsx atau .xlsm, lalu unggah kembali.';
        input.value = '';
        return;
    }

    if (kind !== 'xlsx') {
        error.value = `File "${file.name}" bukan workbook Excel (.xlsx/.xlsm).`;
        input.value = '';
        return;
    }

    selected.value = file;
}

async function submit(): Promise<void> {
    const file = selected.value;

    if (!file || !props.canUpload) return;

    uploading.value = true;
    error.value = '';
    result.value = null;

    try {
        const form = new FormData();
        form.append('file', file);

        const response = await fetch(`/api/import/${props.type}`, {
            method: 'POST',
            body: form,
        });

        if (response.status === 401) {
            window.location.assign(`/login?next=/app/upload/${props.type}`);
            return;
        }

        const payload: unknown = await response.json().catch(() => null);

        if (!response.ok) {
            error.value = errorMessage(payload, 'File tidak dapat diproses.');
            return;
        }

        const parsed = importResponseSchema.safeParse(payload);

        if (!parsed.success) {
            error.value = 'Respons server tidak dikenali. Muat ulang halaman lalu coba lagi.';
            return;
        }

        result.value = parsed.data;
        selected.value = null;

        if (fileInput.value) {
            fileInput.value.value = '';
        }

        emit('imported');
    } catch {
        error.value = 'Koneksi ke server gagal. Coba lagi.';
    } finally {
        uploading.value = false;
    }
}
</script>

<template>
    <section class="vx-card overflow-hidden">
        <div
            class="flex flex-col gap-3 border-b border-line px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-[1rem] font-semibold text-ink">Unggah file Excel</h2>
                <p class="mt-1 text-[0.75rem] leading-5 text-muted">
                    Maksimal 10 MB, format .xlsx atau .xlsm. Baris dengan kode peserta yang tidak
                    ditemukan pada kontrak aktif dilaporkan, bukan dianggap berhasil.
                </p>
            </div>
            <a :href="`/api/templates/${type}`" class="vx-btn-outline shrink-0" download>
                Unduh template
            </a>
        </div>

        <div class="space-y-4 px-5 py-4">
            <p
                v-if="!canUpload"
                class="rounded-[0.357rem] border border-[#ffe4c9] bg-warning-soft px-3 py-2 text-sm text-[#b76e00]"
                role="status"
            >
                Role Anda tidak memiliki izin unggah untuk menu ini.
            </p>

            <input
                ref="fileInput"
                type="file"
                accept=".xlsx,.xlsm,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel.sheet.macroEnabled.12"
                class="sr-only"
                :disabled="!canUpload || uploading"
                @change="onFileChange"
            />

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button
                    type="button"
                    class="vx-btn-outline"
                    :disabled="!canUpload || uploading"
                    @click="chooseFile"
                >
                    Pilih file
                </button>
                <p class="min-w-0 flex-1 truncate text-sm text-body">
                    {{ selected ? selected.name : 'Belum ada file dipilih.' }}
                </p>
                <button type="button" class="vx-btn-primary" :disabled="!canSubmit" @click="submit">
                    {{ uploading ? 'Memproses…' : 'Unggah dan import' }}
                </button>
            </div>

            <p
                v-if="error"
                class="rounded-[0.357rem] border border-[#f8d4d4] bg-danger-soft px-3 py-2 text-sm text-danger"
                role="alert"
            >
                {{ error }}
            </p>

            <div
                v-if="result"
                class="space-y-2 rounded-[0.357rem] border border-[#c9f0da] bg-success-soft px-3 py-2 text-sm text-[#1f7a45]"
                role="status"
            >
                <p>
                    Import selesai: <strong>{{ result.processed }}</strong> baris tersimpan,
                    <strong>{{ result.skipped }}</strong> dilewati dari {{ result.rows }} baris
                    data.
                </p>
                <div v-if="result.skipped_codes.length > 0">
                    <p class="font-medium">
                        Kode yang dilewati (peserta tidak ditemukan pada kontrak aktif):
                    </p>
                    <p class="mt-1 break-words font-mono text-xs">
                        {{ result.skipped_codes.join(', ') }}
                    </p>
                </div>
                <div v-if="result.created_employees.length > 0">
                    <p class="font-medium">Pemeriksa baru dibuat:</p>
                    <p class="mt-1 break-words text-xs">
                        {{ result.created_employees.join(', ') }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
