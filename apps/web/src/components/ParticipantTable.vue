<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { formatDate } from '@/lib/format';

type ParticipantRow = {
    id: number;
    code: string;
    name: string;
    nik: string;
    gender: string;
    divisi_name: string | null;
    contract_name: string | null;
    register_number: string | null;
    register_date: string | null;
};

const PAGE_SIZE = 25;

const rows = ref<ParticipantRow[]>([]);
const total = ref(0);
const page = ref(0);
const searchInput = ref('');
const search = ref('');
const loading = ref(true);
const error = ref('');

const pageCount = computed(() => Math.max(1, Math.ceil(total.value / PAGE_SIZE)));
const from = computed(() => (total.value === 0 ? 0 : page.value * PAGE_SIZE + 1));
const to = computed(() => Math.min(total.value, (page.value + 1) * PAGE_SIZE));

function parseTotal(contentRange: string | null, fallback: number): number {
    const match = contentRange?.match(/^(\d+)-(\d+)\/(\d+|\*)$/);

    if (!match || match[3] === '*') {
        return fallback;
    }

    return Number(match[3]);
}

async function load(isRetry = false): Promise<void> {
    loading.value = true;
    error.value = '';

    // Jangan kirim `limit` bersama header Range: PostgREST menjawab 416 untuk
    // halaman setelah halaman pertama.
    const params = new URLSearchParams({
        select: 'id,code,name,nik,gender,divisi_name,contract_name,register_number,register_date',
        order: 'code.asc',
    });

    if (search.value.trim()) {
        params.set('name', `ilike.*${search.value.trim()}*`);
    }

    const start = page.value * PAGE_SIZE;

    try {
        const response = await fetch(`/api/db/v_participant_list?${params.toString()}`, {
            headers: {
                accept: 'application/json',
                range: `${start}-${start + PAGE_SIZE - 1}`,
                prefer: 'count=exact',
            },
        });

        if (response.status === 401) {
            window.location.assign('/login?next=/app/participant');
            return;
        }

        // Data bisa menyusut sehingga halaman aktif melewati batas; ulangi dari awal.
        if (response.status === 416 && !isRetry && page.value > 0) {
            page.value = 0;
            await load(true);
            return;
        }

        if (!response.ok) {
            const payload = (await response.json().catch(() => null)) as {
                message?: string;
            } | null;
            throw new Error(
                response.status === 503
                    ? 'PostgREST belum dikonfigurasi. Jalankan stack database terlebih dahulu.'
                    : (payload?.message ?? 'Data peserta tidak dapat dimuat.'),
            );
        }

        const payload = (await response.json()) as ParticipantRow[];
        rows.value = payload;
        total.value = parseTotal(response.headers.get('content-range'), payload.length);
    } catch (cause) {
        error.value = cause instanceof Error ? cause.message : 'Data peserta tidak dapat dimuat.';
        rows.value = [];
        total.value = 0;
    } finally {
        loading.value = false;
    }
}

function applySearch(): void {
    search.value = searchInput.value;
    page.value = 0;
    void load();
}

function resetSearch(): void {
    searchInput.value = '';
    search.value = '';
    page.value = 0;
    void load();
}

function goToPage(nextPage: number): void {
    if (nextPage < 0 || nextPage >= pageCount.value) return;

    page.value = nextPage;
    void load();
}

onMounted(load);
</script>

<template>
    <div class="space-y-4">
        <form class="flex flex-col gap-2 sm:flex-row" @submit.prevent="applySearch">
            <input
                v-model="searchInput"
                type="search"
                placeholder="Cari nama peserta"
                aria-label="Cari nama peserta"
                class="vx-input"
            />
            <div class="flex gap-2">
                <button type="submit" class="vx-btn-primary">Cari</button>
                <button type="button" class="vx-btn-outline" @click="resetSearch">Reset</button>
            </div>
        </form>

        <p
            v-if="error"
            class="rounded-[0.357rem] border border-[#ffe4c9] bg-warning-soft px-3 py-2 text-sm text-[#b76e00]"
            role="alert"
        >
            {{ error }}
        </p>

        <div class="vx-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead
                        class="border-b border-line bg-[#f3f2f7] text-[0.7rem] uppercase tracking-wide text-muted"
                    >
                        <tr>
                            <th class="px-4 py-3 font-semibold">MCU ID</th>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">NIK</th>
                            <th class="px-4 py-3 font-semibold">JK</th>
                            <th class="px-4 py-3 font-semibold">Divisi</th>
                            <th class="px-4 py-3 font-semibold">Kontrak</th>
                            <th class="px-4 py-3 font-semibold">Register</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <tr v-if="loading">
                            <td colspan="7" class="px-4 py-10 text-center text-muted">
                                Memuat peserta…
                            </td>
                        </tr>
                        <tr v-else-if="rows.length === 0">
                            <td colspan="7" class="px-4 py-10 text-center">
                                <p class="font-medium text-ink">
                                    Belum ada peserta pada konteks ini.
                                </p>
                                <p class="mt-1 text-xs text-muted">
                                    Import peserta atau pilih kontrak lain setelah modul peserta
                                    aktif.
                                </p>
                            </td>
                        </tr>
                        <template v-else>
                            <tr v-for="row in rows" :key="row.id" class="hover:bg-[#f8f8f8]">
                                <td
                                    class="whitespace-nowrap px-4 py-3 font-mono text-xs text-brand"
                                >
                                    <a :href="`/app/participant/${row.id}`" class="hover:underline">
                                        {{ row.code }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 font-medium text-ink">
                                    <a :href="`/app/participant/${row.id}`" class="hover:underline">
                                        {{ row.name }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-muted">
                                    {{ row.nik }}
                                </td>
                                <td class="px-4 py-3 text-muted">{{ row.gender }}</td>
                                <td class="px-4 py-3 text-muted">{{ row.divisi_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-muted">{{ row.contract_name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs"
                                        :class="
                                            row.register_date
                                                ? 'bg-success-soft text-success'
                                                : 'bg-warning-soft text-[#b76e00]'
                                        "
                                    >
                                        {{
                                            row.register_date
                                                ? formatDate(row.register_date)
                                                : 'Belum'
                                        }}
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-2 border-t border-line px-4 py-3 text-xs text-muted sm:flex-row sm:items-center sm:justify-between"
            >
                <p>Menampilkan {{ from }}–{{ to }} dari {{ total }} peserta</p>
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
    </div>
</template>
