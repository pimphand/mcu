<script setup lang="ts">
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { ApiError, submitMasterForm } from '@/lib/client-api';
import type {
    MasterConfig,
    MasterField,
    MasterOption,
    MasterOptions,
    MasterRow,
} from '@/lib/master-data';

/**
 * Form tambah/ubah master data sebagai modal di atas tabel. Simpan dikirim
 * lewat fetch (JSON), jadi halaman tidak perlu dimuat ulang dan tabel di
 * belakangnya tidak hilang.
 */

const props = defineProps<{
    config: MasterConfig;
    /** Baris yang diubah; `null` berarti form tambah data. */
    row: MasterRow | null;
    options: MasterOptions;
}>();

const emit = defineEmits<{ close: []; saved: [message: string] }>();

const editing = Boolean(props.row);
const formRef = ref<HTMLFormElement | null>(null);
const saving = ref(false);
const error = ref('');

/** Nilai field teks/select/angka (semua dikirim sebagai string). */
const values = reactive<Record<string, string>>({});
/** Nilai field checkbox. */
const flags = reactive<Record<string, boolean>>({});

for (const field of props.config.fields) {
    if (field.type === 'boolean') {
        flags[field.key] = props.row ? Boolean(props.row[field.key]) : field.key === 'is_active';
        continue;
    }

    const value = props.row?.[field.key];

    values[field.key] = value === null || value === undefined ? '' : String(value);
}

function optionsFor(field: MasterField): MasterOption[] {
    if (field.options) return field.options;

    return field.optionSource ? props.options[field.optionSource] : [];
}

function requiredField(field: MasterField): boolean {
    return Boolean(field.required || (field.requiredOnCreate && !editing));
}

function signatureHref(value: unknown): string {
    const file = String(value ?? '')
        .split('/')
        .filter(Boolean)
        .pop();

    return `/app/print/signature/${file ?? ''}`;
}

function close(): void {
    if (saving.value) return;

    emit('close');
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') close();
}

async function submit(): Promise<void> {
    if (saving.value) return;

    saving.value = true;
    error.value = '';

    const data = new FormData();

    data.set('_action', editing ? 'update' : 'create');
    if (props.row) data.set('id', String(props.row.id));

    for (const field of props.config.fields) {
        if (field.type === 'file') {
            const file =
                formRef.value?.querySelector<HTMLInputElement>('input[type="file"]')?.files?.[0];

            if (file) data.set(field.key, file);

            continue;
        }

        data.set(
            field.key,
            field.type === 'boolean'
                ? String(Boolean(flags[field.key]))
                : String(values[field.key] ?? ''),
        );
    }

    try {
        const result = await submitMasterForm(props.config.kind, data);

        emit('saved', result.message);
    } catch (cause) {
        if (cause instanceof ApiError && cause.status === 401) {
            window.location.assign(`/login?next=${encodeURIComponent(props.config.path)}`);
            return;
        }

        error.value = cause instanceof Error ? cause.message : 'Data gagal disimpan.';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    window.addEventListener('keydown', onKeydown);
    formRef.value?.querySelector<HTMLElement>('input, select, textarea')?.focus();
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <div
            class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-[#5e5873]/45 p-4 sm:p-6"
            role="presentation"
            @click.self="close"
        >
            <div
                class="vx-card my-auto w-full max-w-3xl overflow-hidden"
                role="dialog"
                aria-modal="true"
                aria-labelledby="master-modal-title"
            >
                <form ref="formRef" class="space-y-4 p-5" @submit.prevent="submit">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 id="master-modal-title" class="text-[1rem] font-semibold text-ink">
                                {{ editing ? 'Ubah data' : 'Tambah data' }}
                            </h2>
                            <p class="mt-1 text-xs text-muted">{{ config.title }}</p>
                        </div>
                        <button
                            type="button"
                            class="text-xs font-medium text-body hover:text-brand"
                            @click="close"
                        >
                            Tutup
                        </button>
                    </div>

                    <p
                        v-if="error"
                        class="rounded-[0.357rem] border border-[#f8d4d4] bg-danger-soft px-3 py-2 text-sm text-danger"
                        role="alert"
                    >
                        {{ error }}
                    </p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            v-for="field in config.fields"
                            :key="field.key"
                            :class="field.half ? '' : 'sm:col-span-2'"
                        >
                            <label :for="`field-${field.key}`" class="vx-label">
                                {{ field.label }}
                                <span v-if="requiredField(field)" class="text-danger"> *</span>
                            </label>

                            <select
                                v-if="field.type === 'select'"
                                :id="`field-${field.key}`"
                                v-model="values[field.key]"
                                class="vx-input"
                                :required="requiredField(field)"
                                :disabled="saving"
                            >
                                <option v-if="!field.required" value="">— Tidak dipilih —</option>
                                <option
                                    v-for="option in optionsFor(field)"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>

                            <label
                                v-else-if="field.type === 'boolean'"
                                class="flex h-[2.625rem] items-center gap-2 rounded-[0.357rem] border border-line px-3 text-sm text-body"
                            >
                                <input
                                    :id="`field-${field.key}`"
                                    v-model="flags[field.key]"
                                    type="checkbox"
                                    class="h-4 w-4 accent-[#7367f0]"
                                    :disabled="saving"
                                />
                                Ya
                            </label>

                            <textarea
                                v-else-if="field.type === 'textarea'"
                                :id="`field-${field.key}`"
                                v-model="values[field.key]"
                                rows="3"
                                :maxlength="field.maxLength"
                                :placeholder="field.placeholder"
                                :required="field.required"
                                class="vx-input"
                                :disabled="saving"
                            ></textarea>

                            <input
                                v-else-if="field.type === 'file'"
                                :id="`field-${field.key}`"
                                type="file"
                                accept="image/png,image/jpeg"
                                class="vx-input"
                                :disabled="saving"
                            />

                            <input
                                v-else
                                :id="`field-${field.key}`"
                                v-model="values[field.key]"
                                :type="field.type ?? 'text'"
                                :maxlength="field.maxLength"
                                :min="field.min"
                                :max="field.max"
                                :step="field.step"
                                :placeholder="field.placeholder"
                                :required="requiredField(field)"
                                :autocomplete="
                                    field.type === 'password' ? 'new-password' : undefined
                                "
                                class="vx-input"
                                :disabled="saving"
                            />

                            <p v-if="field.help" class="mt-1 text-[0.7rem] leading-5 text-muted">
                                {{ field.help }}
                            </p>

                            <div
                                v-if="field.type === 'file' && editing && row?.[field.key]"
                                class="mt-1 space-y-2"
                            >
                                <img
                                    :src="signatureHref(row[field.key])"
                                    alt="Tanda tangan saat ini"
                                    class="h-16 w-auto rounded-[0.357rem] border border-line bg-white object-contain p-1"
                                />
                                <p class="text-[0.7rem] text-muted">
                                    File sekarang:
                                    <a
                                        :href="signatureHref(row[field.key])"
                                        target="_blank"
                                        rel="noopener"
                                        class="text-brand hover:underline"
                                    >
                                        {{ String(row[field.key]) }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 border-t border-line pt-4">
                        <button
                            type="submit"
                            class="vx-btn-primary disabled:cursor-wait"
                            :disabled="saving"
                        >
                            {{ editing ? 'Simpan perubahan' : 'Simpan' }}
                        </button>
                        <button
                            type="button"
                            class="vx-btn-outline"
                            :disabled="saving"
                            @click="close"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
