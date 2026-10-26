<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { filterMasterOptions } from '@/lib/master-format';
import type { MasterOption } from '@/lib/master-data';

/**
 * Dropdown dengan pencarian, pengganti select2 yang dipakai halaman Blade
 * lama. Daftar client/role/menu induk bisa panjang, jadi pengguna harus bisa
 * mengetik untuk menyaring tanpa menggulir semua opsi.
 *
 * Nilai tetap dikirim sebagai string lewat `v-model`, sama seperti `<select>`,
 * sehingga `MasterModal` tidak perlu tahu cara komponen ini bekerja.
 */

const props = defineProps<{
    id: string;
    modelValue: string;
    options: MasterOption[];
    placeholder?: string;
    /** Teks opsi kosong; hanya tampil bila field tidak wajib. */
    emptyLabel?: string;
    required?: boolean;
    disabled?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const root = ref<HTMLDivElement | null>(null);
const input = ref<HTMLInputElement | null>(null);
const open = ref(false);
const query = ref('');
/** Indeks opsi yang disorot; -1 berarti belum ada. */
const active = ref(-1);

const selected = computed(
    () => props.options.find((option) => option.value === props.modelValue) ?? null,
);

const filtered = computed(() => filterMasterOptions(props.options, query.value));

/** Saat terbuka input kosong agar pengguna bisa langsung mengetik. */
const display = computed(() => (open.value ? query.value : (selected.value?.label ?? '')));

const placeholder = computed(() => {
    if (open.value) return selected.value?.label ?? props.placeholder ?? 'Ketik untuk mencari…';

    return props.placeholder ?? 'Pilih';
});

// Opsi kosong hanya relevan saat belum mengetik; saat mencari, daftar hanya
// berisi opsi yang cocok dengan kata kunci.
const emptyVisible = computed(() => !props.required && !query.value.trim());

function openList(): void {
    if (props.disabled || open.value) return;

    open.value = true;
    query.value = '';
    active.value = filtered.value.findIndex((option) => option.value === props.modelValue);
    void nextTick(() => input.value?.focus());
}

function closeList(): void {
    open.value = false;
    query.value = '';
    active.value = -1;
}

function choose(value: string): void {
    emit('update:modelValue', value);
    closeList();
    void nextTick(() => input.value?.blur());
}

function onInput(event: Event): void {
    query.value = (event.target as HTMLInputElement).value;
    open.value = true;
    active.value = -1;
}

function move(step: number): void {
    if (!open.value) {
        openList();
        return;
    }

    const total = filtered.value.length;

    if (total === 0) return;

    active.value = (active.value + step + total) % total;
}

function onKeydown(event: KeyboardEvent): void {
    // Saat tertutup input bersifat readonly (menampilkan label terpilih), jadi
    // ketikan pertama membuka daftar sekaligus menjadi kata kunci awal.
    if (!open.value && event.key.length === 1 && !event.metaKey && !event.ctrlKey) {
        event.preventDefault();
        openList();
        query.value = event.key;
        return;
    }

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        move(1);
        return;
    }

    if (event.key === 'ArrowUp') {
        event.preventDefault();
        move(-1);
        return;
    }

    if (event.key === 'Enter') {
        const option = filtered.value[active.value] ?? filtered.value[0];

        if (open.value && option) {
            event.preventDefault();
            choose(option.value);
        }

        return;
    }

    if (event.key === 'Escape' && open.value) {
        // Hanya tutup daftar; Esc kedua baru menutup modal induk.
        event.preventDefault();
        event.stopPropagation();
        closeList();
        return;
    }

    if (event.key === 'Tab') closeList();
}

function onDocumentPointerDown(event: Event): void {
    if (open.value && !root.value?.contains(event.target as Node)) closeList();
}

onMounted(() => {
    document.addEventListener('pointerdown', onDocumentPointerDown);
});

onBeforeUnmount(() => {
    document.removeEventListener('pointerdown', onDocumentPointerDown);
});
</script>

<template>
    <div ref="root" class="relative">
        <input
            :id="id"
            ref="input"
            type="text"
            role="combobox"
            autocomplete="off"
            :value="display"
            :placeholder="placeholder"
            :readonly="!open"
            :disabled="disabled"
            :required="required"
            :aria-expanded="open"
            :aria-controls="`${id}-list`"
            :aria-autocomplete="'list'"
            :aria-activedescendant="active >= 0 ? `${id}-option-${active}` : undefined"
            class="vx-input cursor-pointer read-only:cursor-pointer"
            @click="openList"
            @input="onInput"
            @keydown="onKeydown"
        />
        <span
            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-muted"
            aria-hidden="true"
        >
            ▾
        </span>

        <ul
            v-if="open"
            :id="`${id}-list`"
            role="listbox"
            class="absolute z-10 mt-1 max-h-64 w-full overflow-y-auto rounded-[0.357rem] border border-line bg-white py-1 shadow-vx"
        >
            <li v-if="emptyVisible" role="none">
                <button
                    type="button"
                    role="option"
                    :aria-selected="modelValue === ''"
                    class="block w-full px-3 py-2 text-left text-sm text-muted hover:bg-[#f8f8f8]"
                    @click="choose('')"
                >
                    {{ emptyLabel ?? '— Tidak dipilih —' }}
                </button>
            </li>
            <li
                v-for="(option, index) in filtered"
                :id="`${id}-option-${index}`"
                :key="option.value"
                role="option"
                :aria-selected="option.value === modelValue"
            >
                <button
                    type="button"
                    class="block w-full px-3 py-2 text-left text-sm text-body hover:bg-[#f8f8f8]"
                    :class="
                        index === active
                            ? 'bg-brand-soft text-brand-dark'
                            : option.value === modelValue
                              ? 'font-medium text-brand'
                              : ''
                    "
                    @click="choose(option.value)"
                >
                    {{ option.label }}
                </button>
            </li>
            <li
                v-if="filtered.length === 0"
                class="px-3 py-2 text-sm text-muted"
                role="presentation"
            >
                Tidak ada pilihan yang cocok.
            </li>
        </ul>
    </div>
</template>
