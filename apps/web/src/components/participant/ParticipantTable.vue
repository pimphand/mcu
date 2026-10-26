<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import ParticipantFilterForm from './ParticipantFilterForm.vue';
import ParticipantListTable from './ParticipantListTable.vue';
import { ApiError, fetchRows } from '@/lib/client-api';
import {
    PARTICIPANT_LIST_SELECT,
    activeParticipantFilters,
    emptyParticipantFilters,
    participantFilterQuery,
    participantPostgrestQuery,
    type ParticipantFilters,
} from '@/lib/participant-filters';
import type { ParticipantListRow } from '@/lib/participant-format';

const PAGE_SIZE = 25;

/**
 * Filter dan halaman awal diurai di server (`index.astro`) dari
 * `Astro.url.searchParams`, lalu diteruskan ke island. Dengan begitu tautan
 * yang dibagikan memulihkan filter **dan** isi panel filter, tanpa menunggu
 * `window` ada di browser.
 */
const props = defineProps<{
    initialFilters: ParticipantFilters;
    initialPage: number;
}>();

const rows = ref<ParticipantListRow[]>([]);
const total = ref(0);
const page = ref(props.initialPage);
const applied = ref<ParticipantFilters>(props.initialFilters);
const loading = ref(true);
const error = ref('');

const activeCount = computed(() => activeParticipantFilters(applied.value));
const pageCount = computed(() => Math.max(1, Math.ceil(total.value / PAGE_SIZE)));
const from = computed(() => (total.value === 0 ? 0 : page.value * PAGE_SIZE + 1));
const to = computed(() => Math.min(total.value, (page.value + 1) * PAGE_SIZE));

/** Simpan filter + halaman di URL supaya refresh/bookmark tetap sama. */
function syncUrl(): void {
    const query = participantFilterQuery(applied.value, page.value).toString();

    window.history.replaceState(null, '', query ? `?${query}` : window.location.pathname);
}

async function load(isRetry = false): Promise<void> {
    loading.value = true;
    error.value = '';

    const start = page.value * PAGE_SIZE;

    try {
        const result = await fetchRows<ParticipantListRow>(
            'v_participant_list',
            {
                select: PARTICIPANT_LIST_SELECT,
                order: 'code.asc',
                ...participantPostgrestQuery(applied.value),
            },
            { range: [start, start + PAGE_SIZE - 1] },
        );

        rows.value = result.rows;
        total.value = result.total;
        syncUrl();
    } catch (cause) {
        if (cause instanceof ApiError && cause.status === 401) {
            window.location.assign('/login?next=/app/participant');
            return;
        }

        // Data bisa menyusut sehingga halaman aktif melewati batas; ulangi dari awal.
        if (cause instanceof ApiError && cause.status === 416 && !isRetry && page.value > 0) {
            page.value = 0;
            await load(true);
            return;
        }

        error.value = cause instanceof Error ? cause.message : 'Data peserta tidak dapat dimuat.';
        rows.value = [];
        total.value = 0;
    } finally {
        loading.value = false;
    }
}

function applyFilters(filters: ParticipantFilters): void {
    applied.value = filters;
    page.value = 0;
    void load();
}

function resetFilters(): void {
    applied.value = emptyParticipantFilters();
    page.value = 0;
    void load();
}

function goToPage(nextPage: number): void {
    if (nextPage < 0 || nextPage >= pageCount.value) return;

    page.value = nextPage;
    void load();
}

onMounted(() => {
    void load();
});
</script>

<template>
    <div class="space-y-4">
        <ParticipantFilterForm
            :initial="applied"
            :active-count="activeCount"
            @apply="applyFilters"
            @reset="resetFilters"
        />

        <p
            v-if="error"
            class="rounded-[0.357rem] border border-[#ffe4c9] bg-warning-soft px-3 py-2 text-sm text-[#b76e00]"
            role="alert"
        >
            {{ error }}
        </p>

        <ParticipantListTable
            :rows="rows"
            :loading="loading"
            :active-count="activeCount"
            :total="total"
            :page="page"
            :page-count="pageCount"
            :from="from"
            :to="to"
            @reset="resetFilters"
            @page="goToPage"
        />
    </div>
</template>
