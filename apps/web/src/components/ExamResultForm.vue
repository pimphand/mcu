<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { fetchRows, upsertRow } from '@/lib/client-api';
import type { ExamResultConfig } from '@/lib/exams';
import { resultFormValues, resultPayload, type ResultFormValues } from '@/lib/result-form';

const props = defineProps<{
    config: ExamResultConfig;
    participantId: number;
    clientId?: number | null;
    contractId?: number | null;
    /** Baris hasil yang sudah ada; `null` berarti peserta belum punya hasil. */
    row?: Record<string, unknown> | null;
}>();

const emit = defineEmits<{
    (event: 'saved', row: Record<string, unknown>): void;
    (event: 'cancel'): void;
}>();

const values = reactive<ResultFormValues>(resultFormValues(props.config, props.row ?? null));
const employees = ref<{ id: number; nama: string }[]>([]);
const employeeId = ref(props.row?.employee_id ? String(props.row.employee_id) : '');
const employeeError = ref('');
/** Hasil baru langsung ditandai selesai; pengguna tetap bisa melepas centangnya. */
const selesai = ref(props.row ? Boolean(props.row.selesai) : true);
const saving = ref(false);
const error = ref('');

onMounted(async () => {
    try {
        const { rows } = await fetchRows<{ id: number; nama: string }>('employees', {
            select: 'id,nama',
            order: 'nama.asc',
            limit: 200,
        });

        employees.value = rows;
    } catch (cause) {
        // Daftar petugas hanya pelengkap; hasil tetap bisa disimpan tanpa tanda tangan.
        employeeError.value =
            cause instanceof Error ? cause.message : 'Daftar petugas gagal dimuat.';
    }
});

async function submit(): Promise<void> {
    if (saving.value) return;

    saving.value = true;
    error.value = '';

    try {
        const saved = await upsertRow<Record<string, unknown>>(
            props.config.resource,
            resultPayload(props.config, values, {
                participantId: props.participantId,
                clientId: props.clientId,
                contractId: props.contractId,
                employeeId: employeeId.value ? Number(employeeId.value) : null,
                selesai: selesai.value,
            }),
        );

        if (saved) {
            emit('saved', saved);
        }
    } catch (cause) {
        error.value = cause instanceof Error ? cause.message : 'Hasil pemeriksaan gagal disimpan.';
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <form class="space-y-3" @submit.prevent="submit">
        <div class="grid gap-3 sm:grid-cols-2">
            <div v-for="field in config.fields" :key="field.key">
                <label class="vx-label" :for="`${config.key}-${field.key}`">
                    {{ field.label }}
                </label>
                <select
                    v-if="field.type === 'boolean'"
                    :id="`${config.key}-${field.key}`"
                    v-model="values[field.key]"
                    class="vx-input"
                >
                    <option value="">—</option>
                    <option value="true">Ya</option>
                    <option value="false">Tidak</option>
                </select>
                <input
                    v-else
                    :id="`${config.key}-${field.key}`"
                    v-model="values[field.key]"
                    class="vx-input"
                    :type="
                        field.type === 'number'
                            ? 'number'
                            : field.type === 'date'
                              ? 'date'
                              : field.type === 'datetime'
                                ? 'datetime-local'
                                : 'text'
                    "
                    :step="field.type === 'number' ? 'any' : undefined"
                />
            </div>

            <div>
                <label class="vx-label" :for="`${config.key}-employee`">Petugas pemeriksa</label>
                <select
                    :id="`${config.key}-employee`"
                    v-model="employeeId"
                    class="vx-input"
                    :disabled="Boolean(employeeError)"
                >
                    <option value="">Pilih petugas</option>
                    <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                        {{ employee.nama }}
                    </option>
                </select>
                <p v-if="employeeError" class="mt-1 text-[0.75rem] text-danger">
                    {{ employeeError }}
                </p>
            </div>

            <div class="flex items-end">
                <label
                    class="inline-flex cursor-pointer items-center gap-2 text-[0.8125rem] text-body"
                >
                    <input v-model="selesai" type="checkbox" class="h-4 w-4" />
                    Tandai pemeriksaan selesai
                </label>
            </div>
        </div>

        <p
            v-if="error"
            class="rounded-[0.357rem] bg-danger-soft px-3 py-2 text-[0.8125rem] text-danger"
            role="alert"
        >
            {{ error }}
        </p>

        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" class="vx-btn-primary" :disabled="saving">
                {{ saving ? 'Menyimpan…' : 'Simpan hasil' }}
            </button>
            <button type="button" class="vx-btn-outline" :disabled="saving" @click="emit('cancel')">
                Batal
            </button>
        </div>
    </form>
</template>
