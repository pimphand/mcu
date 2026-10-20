<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { ApiError, fetchRows } from '@/lib/client-api';
import { formatDate } from '@/lib/format';
import { uploadListErrorMessage, type UploadReport } from '@/lib/upload-reports';

type UploadRow = Record<string, unknown>;
type DivisiOption = { id: number; name: string };

const props = defineProps<{ report: UploadReport }>();

const PAGE_SIZE = 25;
const DATE_COLUMNS = new Set(['created_at', 'date_mcu', 'mcu_date', 'register_date', 'lab_date']);
/** Nomor baris legacy (`No.`), ikut nomor halaman agar tidak mengulang 1–25. */
const TOTAL_COLUMNS = computed(() => props.report.columns.length + 1);

const rows = ref<UploadRow[]>([]);
const total = ref(0);
const page = ref(0);
const loading = ref(true);
const error = ref('');
const divisis = ref<DivisiOption[]>([]);
const searchInput = ref('');
const search = ref('');
const divisi = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const sort = ref(props.report.defaultSort);

const pageCount = computed(() => Math.max(1, Math.ceil(total.value / PAGE_SIZE)));
const from = computed(() => (total.value === 0 ? 0 : page.value * PAGE_SIZE + 1));
const to = computed(() => Math.min(total.value, (page.value + 1) * PAGE_SIZE));

function filterParams(): Record<string, string> {
    const params: Record<string, string> = { select: '*', order: sort.value };
    // Karakter yang dipakai sintaks PostgREST dibuang dari kata kunci agar
    // pencarian tidak bisa mengubah struktur query.
    const term = search.value
        .trim()
        .replace(/[(),*]/g, ' ')
        .trim();

    if (term) {
        params.or = `(${props.report.searchColumns
            .map((column) => `${column}.ilike.*${term}*`)
            .join(',')})`;
    }

    if (divisi.value) {
        params.divisi_id = `eq.${divisi.value}`;
    }

    const column = props.report.dateColumn;

    if (dateFrom.value && dateTo.value) {
        params.and = `(${column}.gte.${dateFrom.value},${column}.lte.${dateTo.value})`;
    } else if (dateFrom.value) {
        params[column] = `gte.${dateFrom.value}`;
    } else if (dateTo.value) {
        params[column] = `lte.${dateTo.value}`;
    }

    return params;
}

async function load(isRetry = false): Promise<void> {
    loading.value = true;
    error.value = '';

    const start = page.value * PAGE_SIZE;

    try {
        const result = await fetchRows<UploadRow>(props.report.view, filterParams(), {
            range: [start, start + PAGE_SIZE - 1],
        });

        rows.value = result.rows;
        total.value = result.total;
    } catch (cause) {
        if (cause instanceof ApiError && cause.status === 401) {
            window.location.assign(`/login?next=/app/upload/${props.report.type}`);
            return;
        }

        // Data menyusut setelah import/filter sehingga halaman aktif melewati
        // batas; ulangi sekali dari halaman pertama.
        if (cause instanceof ApiError && cause.status === 416 && !isRetry && page.value > 0) {
            page.value = 0;
            await load(true);
            return;
        }

        error.value =
            cause instanceof ApiError
                ? uploadListErrorMessage(props.report.view, cause.status, cause.message)
                : cause instanceof Error
                  ? cause.message
                  : 'Hasil tidak dapat dimuat.';
        rows.value = [];
        total.value = 0;
    } finally {
        loading.value = false;
    }
}

async function loadDivisis(): Promise<void> {
    try {
        const result = await fetchRows<DivisiOption>('divisis', {
            select: 'id,name',
            order: 'name.asc',
        });
        divisis.value = result.rows;
    } catch {
        divisis.value = [];
    }
}

function applyFilters(): void {
    search.value = searchInput.value;
    page.value = 0;
    void load();
}

function resetFilters(): void {
    searchInput.value = '';
    search.value = '';
    divisi.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    sort.value = props.report.defaultSort;
    page.value = 0;
    void load();
}

function goToPage(nextPage: number): void {
    if (nextPage < 0 || nextPage >= pageCount.value) return;

    page.value = nextPage;
    void load();
}

function formatCell(row: UploadRow, column: string): string {
    const value = row[column];

    if (value === null || value === undefined || value === '') return '—';
    if (typeof value === 'boolean') return value ? 'Ya' : 'Tidak';
    if (DATE_COLUMNS.has(column)) return formatDate(String(value));

    return String(value);
}

defineExpose({ refresh: () => load() });

onMounted(() => {
    void load();
    void loadDivisis();
});
</script>

<template>
    <section class="space-y-4">
        <form
            class="vx-card grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-6"
            @submit.prevent="applyFilters"
        >
            <div class="lg:col-span-2">
                <label for="upload-search" class="vx-label">Cari nama / kode</label>
                <input
                    id="upload-search"
                    v-model="searchInput"
                    type="search"
                    class="vx-input"
                    placeholder="Nama atau MCU ID"
                />
            </div>
            <div>
                <label for="upload-divisi" class="vx-label">Divisi</label>
                <select id="upload-divisi" v-model="divisi" class="vx-input">
                    <option value="">Semua divisi</option>
                    <option v-for="item in divisis" :key="item.id" :value="String(item.id)">
                        {{ item.name }}
                    </option>
                </select>
            </div>
            <div>
                <label for="upload-from" class="vx-label">Tanggal awal</label>
                <input id="upload-from" v-model="dateFrom" type="date" class="vx-input" />
            </div>
            <div>
                <label for="upload-to" class="vx-label">Tanggal akhir</label>
                <input id="upload-to" v-model="dateTo" type="date" class="vx-input" />
            </div>
            <div>
                <label for="upload-sort" class="vx-label">Urutkan</label>
                <select id="upload-sort" v-model="sort" class="vx-input" @change="applyFilters">
                    <option
                        v-for="option in report.sortOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>
            <div class="flex gap-2 sm:col-span-2 lg:col-span-6">
                <button type="submit" class="vx-btn-primary">Terapkan</button>
                <button type="button" class="vx-btn-outline" @click="resetFilters">Reset</button>
            </div>
        </form>

        <p
            v-if="error"
            class="rounded-[0.357rem] border border-[#f8d4d4] bg-danger-soft px-3 py-2 text-sm text-danger"
            role="alert"
        >
            {{ error }}
        </p>

        <div class="vx-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead
                        class="border-b border-line bg-[#f3f2f7] text-[0.7rem] uppercase tracking-wide text-muted"
                    >
                        <tr>
                            <th class="whitespace-nowrap px-3 py-3 text-right font-semibold">
                                No.
                            </th>
                            <th
                                v-for="column in report.columns"
                                :key="column.key"
                                class="whitespace-nowrap px-3 py-3 font-semibold"
                            >
                                {{ column.label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <tr v-if="loading">
                            <td :colspan="TOTAL_COLUMNS" class="px-4 py-10 text-center text-muted">
                                Memuat hasil…
                            </td>
                        </tr>
                        <tr v-else-if="rows.length === 0">
                            <td :colspan="TOTAL_COLUMNS" class="px-4 py-10 text-center">
                                <p class="font-medium text-ink">Belum ada hasil pada filter ini.</p>
                                <p class="mt-1 text-xs text-muted">
                                    Unggah file Excel atau ubah filter tanggal/divisi.
                                </p>
                            </td>
                        </tr>
                        <template v-else>
                            <tr
                                v-for="(row, index) in rows"
                                :key="String(row.id)"
                                class="hover:bg-[#f8f8f8]"
                            >
                                <td class="px-3 py-2.5 text-right align-top text-muted">
                                    {{ page * PAGE_SIZE + index + 1 }}
                                </td>
                                <td
                                    v-for="column in report.columns"
                                    :key="column.key"
                                    class="px-3 py-2.5 align-top"
                                    :class="
                                        column.key === 'code'
                                            ? 'whitespace-nowrap font-mono text-xs text-brand'
                                            : 'text-body'
                                    "
                                >
                                    {{ formatCell(row, column.key) }}
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-2 border-t border-line px-4 py-3 text-xs text-muted sm:flex-row sm:items-center sm:justify-between"
            >
                <p>Menampilkan {{ from }}–{{ to }} dari {{ total }} hasil</p>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-[0.357rem] border border-line px-3 py-1.5 font-medium text-body transition hover:border-brand hover:text-brand disabled:opacity-40"
                        :disabled="page === 0 || loading"
                        @click="goToPage(page - 1)"
                    >
                        Sebelumnya
                    </button>
                    <span>Halaman {{ page + 1 }} / {{ pageCount }}</span>
                    <button
                        type="button"
                        class="rounded-[0.357rem] border border-line px-3 py-1.5 font-medium text-body transition hover:border-brand hover:text-brand disabled:opacity-40"
                        :disabled="page + 1 >= pageCount || loading"
                        @click="goToPage(page + 1)"
                    >
                        Berikutnya
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
