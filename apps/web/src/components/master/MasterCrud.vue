<script setup lang="ts">
import { computed, ref } from 'vue';
import { ApiError, fetchMasterList, submitMasterForm } from '@/lib/client-api';
import type { MasterConfig, MasterOptions, MasterRow } from '@/lib/master-data';
import { masterCellText, masterPageCount, masterQuery, masterRowRange } from '@/lib/master-format';
import MasterModal from './MasterModal.vue';

/**
 * Tabel master data sebagai island SPA: cari, urutkan, pindah halaman,
 * tambah/ubah/hapus semuanya lewat fetch. Baris awal datang dari render
 * server, jadi tabel sudah terisi tanpa layar loading.
 */

const props = defineProps<{
    config: MasterConfig;
    rows: MasterRow[];
    total: number;
    page: number;
    perPage: number;
    search: string;
    sort: string;
    canAdd: boolean;
    canEdit: boolean;
    canDelete: boolean;
    options: MasterOptions;
}>();

const rowList = ref<MasterRow[]>([...props.rows]);
const totalRows = ref(props.total);
const currentPage = ref(props.page);
const searchTerm = ref(props.search);
const searchInput = ref(props.search);
const sortValue = ref(props.sort || props.config.defaultSort);
const notice = ref('');
const error = ref('');
const busy = ref(false);
const modalRow = ref<MasterRow | null>(null);
const modalOpen = ref(false);

const pageCount = computed(() => masterPageCount(totalRows.value, props.perPage));
const range = computed(() => masterRowRange(totalRows.value, currentPage.value, props.perPage));

/** Filter aktif ditulis ke URL agar halaman tetap bisa di-bookmark/di-refresh. */
function syncUrl(): void {
    const query = masterQuery({
        search: searchTerm.value,
        sort: sortValue.value,
        page: currentPage.value,
    }).toString();

    window.history.replaceState(
        null,
        '',
        query ? `${props.config.path}?${query}` : props.config.path,
    );
}

async function refresh(targetPage = currentPage.value): Promise<void> {
    busy.value = true;
    error.value = '';

    try {
        const result = await fetchMasterList<MasterRow>(props.config.kind, {
            q: searchTerm.value,
            sort: sortValue.value,
            page: targetPage,
            perPage: props.perPage,
        });

        // Halaman terakhir bisa jadi kosong setelah data dihapus; mundur satu
        // halaman alih-alih menampilkan tabel kosong.
        if (result.rows.length === 0 && result.total > 0 && targetPage > 0) {
            busy.value = false;
            await refresh(targetPage - 1);
            return;
        }

        rowList.value = result.rows;
        totalRows.value = result.total;
        currentPage.value = targetPage;
        syncUrl();
    } catch (cause) {
        if (cause instanceof ApiError && cause.status === 401) {
            window.location.assign(`/login?next=${encodeURIComponent(props.config.path)}`);
            return;
        }

        error.value = cause instanceof Error ? cause.message : 'Data master gagal dimuat.';
    } finally {
        busy.value = false;
    }
}

function applySearch(): void {
    searchTerm.value = searchInput.value.trim();
    void refresh(0);
}

function resetSearch(): void {
    searchInput.value = '';
    searchTerm.value = '';
    sortValue.value = props.config.defaultSort;
    void refresh(0);
}

function changeSort(event: Event): void {
    sortValue.value = (event.target as HTMLSelectElement).value;
    void refresh(0);
}

function goToPage(target: number): void {
    if (target < 0 || target >= pageCount.value || target === currentPage.value) return;

    void refresh(target);
}

function openCreate(): void {
    modalRow.value = null;
    modalOpen.value = true;
}

function openEdit(row: MasterRow): void {
    modalRow.value = row;
    modalOpen.value = true;
}

function closeModal(): void {
    modalOpen.value = false;
    modalRow.value = null;
}

function onSaved(message: string): void {
    const created = modalRow.value === null;

    closeModal();
    notice.value = message;
    void refresh(created ? 0 : currentPage.value);
}

async function mutate(
    action: string,
    id: number,
    extra: Record<string, string> = {},
): Promise<void> {
    busy.value = true;
    error.value = '';

    const data = new FormData();

    data.set('_action', action);
    data.set('id', String(id));
    for (const [key, value] of Object.entries(extra)) data.set(key, value);

    try {
        const result = await submitMasterForm(props.config.kind, data);

        notice.value = result.message;
        await refresh();
    } catch (cause) {
        if (cause instanceof ApiError && cause.status === 401) {
            window.location.assign(`/login?next=${encodeURIComponent(props.config.path)}`);
            return;
        }

        error.value = cause instanceof Error ? cause.message : 'Aksi gagal dijalankan.';
    } finally {
        busy.value = false;
    }
}

function remove(row: MasterRow): void {
    if (!window.confirm('Hapus data ini?')) return;

    void mutate('delete', row.id);
}

function toggleMenu(row: MasterRow): void {
    void mutate('toggle', row.id, { is_active: row.is_active ? 'false' : 'true' });
}
</script>

<template>
    <div class="space-y-4">
        <div class="vx-card overflow-hidden">
            <div
                class="flex flex-col gap-3 border-b border-line px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <form class="flex flex-1 flex-col gap-2 sm:flex-row" @submit.prevent="applySearch">
                    <input
                        v-model="searchInput"
                        type="search"
                        :placeholder="`Cari ${config.searchColumns.join(' / ')}`"
                        aria-label="Cari data"
                        class="vx-input sm:max-w-sm"
                    />
                    <select
                        :value="sortValue"
                        aria-label="Urutkan"
                        class="vx-input sm:max-w-[13rem]"
                        @change="changeSort"
                    >
                        <option
                            v-for="option in config.sortOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="vx-btn-primary">Terapkan</button>
                        <button type="button" class="vx-btn-outline" @click="resetSearch">
                            Reset
                        </button>
                    </div>
                </form>
                <button
                    v-if="canAdd"
                    type="button"
                    class="vx-btn-primary shrink-0"
                    @click="openCreate"
                >
                    Tambah
                </button>
            </div>

            <p
                v-if="notice"
                class="border-b border-line bg-success-soft px-4 py-2 text-sm text-success"
                role="status"
            >
                {{ notice }}
            </p>

            <p
                v-if="error"
                class="border-b border-line bg-danger-soft px-4 py-2 text-sm text-danger"
                role="alert"
            >
                {{ error }}
            </p>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead
                        class="border-b border-line bg-[#f3f2f7] text-[0.7rem] uppercase tracking-wide text-muted"
                    >
                        <tr>
                            <th
                                v-for="column in config.columns"
                                :key="column.key"
                                class="whitespace-nowrap px-3 py-3 font-semibold"
                            >
                                {{ column.label }}
                            </th>
                            <th class="px-3 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <tr v-if="rowList.length === 0">
                            <td :colspan="config.columns.length + 1" class="px-4 py-10 text-center">
                                <p class="font-medium text-ink">Belum ada data.</p>
                                <p class="mt-1 text-xs text-muted">
                                    Tambahkan data baru atau ubah kata kunci pencarian.
                                </p>
                            </td>
                        </tr>
                        <tr v-for="row in rowList" :key="row.id" class="hover:bg-[#f8f8f8]">
                            <td
                                v-for="column in config.columns"
                                :key="column.key"
                                class="px-3 py-2.5 align-top text-body"
                            >
                                <span
                                    v-if="column.key === 'code'"
                                    class="whitespace-nowrap font-mono text-xs text-brand"
                                >
                                    {{ masterCellText(row, column) }}
                                </span>
                                <template v-else>{{ masterCellText(row, column) }}</template>
                            </td>
                            <td class="whitespace-nowrap px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        v-if="config.kind === 'role' && canEdit"
                                        :href="`/app/master/role/${row.id}`"
                                        class="text-xs font-medium text-brand hover:underline"
                                    >
                                        Izin
                                    </a>
                                    <button
                                        v-if="config.kind === 'menu' && canEdit"
                                        type="button"
                                        class="text-xs font-medium text-body hover:text-brand"
                                        :disabled="busy"
                                        @click="toggleMenu(row)"
                                    >
                                        {{ row.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                    <button
                                        v-if="canEdit"
                                        type="button"
                                        class="text-xs font-medium text-brand hover:underline"
                                        @click="openEdit(row)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        v-if="canDelete"
                                        type="button"
                                        class="text-xs font-medium text-danger hover:underline"
                                        :disabled="busy"
                                        @click="remove(row)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-2 border-t border-line px-4 py-3 text-xs text-muted sm:flex-row sm:items-center sm:justify-between"
            >
                <p>Menampilkan {{ range.from }}–{{ range.to }} dari {{ totalRows }} data</p>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-[0.357rem] border border-line px-3 py-1.5 font-medium text-body transition hover:border-brand hover:text-brand disabled:opacity-40"
                        :disabled="currentPage === 0 || busy"
                        @click="goToPage(currentPage - 1)"
                    >
                        Sebelumnya
                    </button>
                    <span>Halaman {{ currentPage + 1 }} / {{ pageCount }}</span>
                    <button
                        type="button"
                        class="rounded-[0.357rem] border border-line px-3 py-1.5 font-medium text-body transition hover:border-brand hover:text-brand disabled:opacity-40"
                        :disabled="currentPage + 1 >= pageCount || busy"
                        @click="goToPage(currentPage + 1)"
                    >
                        Berikutnya
                    </button>
                </div>
            </div>
        </div>

        <MasterModal
            v-if="modalOpen"
            :config="config"
            :row="modalRow"
            :options="options"
            @close="closeModal"
            @saved="onSaved"
        />
    </div>
</template>
