<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { ApiError, callRpc, fetchRows } from '@/lib/client-api';
import { formatDate } from '@/lib/format';

type RegisterRow = {
    id: number;
    code: string;
    nik: string;
    name: string;
    no_form: number | null;
    divisi_name: string | null;
    register_number: string | null;
    register_date: string | null;
};

type RegisterResult = {
    participant_id: number;
    register_number: string;
    register_date: string;
};

const props = defineProps<{
    canWrite: boolean;
}>();

const PAGE_SIZE = 25;

const rows = ref<RegisterRow[]>([]);
const total = ref(0);
const page = ref(0);
const searchInput = ref('');
const search = ref('');
const status = ref<'all' | 'pending' | 'registered'>('registered');
const loading = ref(true);
const error = ref('');

const registerDate = ref(jakartaToday());
const bulkBusy = ref(false);
const rowBusy = ref<number | null>(null);
const message = ref('');
const messageTone = ref<'success' | 'danger'>('success');

const pageCount = computed(() => Math.max(1, Math.ceil(total.value / PAGE_SIZE)));
const from = computed(() => (total.value === 0 ? 0 : page.value * PAGE_SIZE + 1));
const to = computed(() => Math.min(total.value, (page.value + 1) * PAGE_SIZE));

function jakartaToday(): string {
    return new Intl.DateTimeFormat('en-CA', {
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date());
}

function setMessage(tone: 'success' | 'danger', text: string): void {
    messageTone.value = tone;
    message.value = text;
}

async function load(isRetry = false): Promise<void> {
    loading.value = true;
    error.value = '';

    const params: Record<string, string | number> = {
        select: 'id,code,nik,name,no_form,divisi_name,register_number,register_date',
        order: 'code.asc',
    };

    if (status.value === 'pending') params.register_date = 'is.null';
    if (status.value === 'registered') params.register_date = 'not.is.null';

    // Buang karakter sintaks filter PostgREST (`(`, `)`, `,`) agar pencarian
    // tidak bisa menambah kondisi sendiri.
    const term = search.value.trim().replace(/[(),]/g, ' ').replace(/\s+/g, ' ');

    if (term) {
        params.or = `(name.ilike.*${term}*,code.ilike.*${term}*,nik.ilike.*${term}*)`;
    }

    const start = page.value * PAGE_SIZE;

    try {
        const result = await fetchRows<RegisterRow>('v_participant_list', params, {
            range: [start, start + PAGE_SIZE - 1],
        });

        rows.value = result.rows;
        total.value = result.total;
    } catch (cause) {
        if (cause instanceof ApiError && cause.status === 401) {
            window.location.assign('/login?next=/app/participant/register');
            return;
        }

        // Total menyusut setelah register (mis. filter "Belum register"), sehingga
        // halaman yang sedang dibuka bisa melewati batas. Ulangi dari halaman 1.
        if (cause instanceof ApiError && cause.status === 416 && !isRetry && page.value > 0) {
            page.value = 0;
            await load(true);
            return;
        }

        error.value = cause instanceof Error ? cause.message : 'Data register tidak dapat dimuat.';
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
    status.value = 'registered';
    page.value = 0;
    void load();
}

function goToPage(nextPage: number): void {
    if (nextPage < 0 || nextPage >= pageCount.value) return;

    page.value = nextPage;
    void load();
}

async function registerOne(row: RegisterRow): Promise<void> {
    if (!props.canWrite || rowBusy.value !== null) return;

    rowBusy.value = row.id;
    setMessage('success', '');

    try {
        const payload = await callRpc<RegisterResult[]>('register_participant', {
            p_participant_id: row.id,
        });
        const registered = payload[0];

        if (registered) {
            row.register_number = registered.register_number;
            row.register_date = registered.register_date;
            setMessage(
                'success',
                `${row.code} terdaftar dengan nomor ${registered.register_number}.`,
            );

            // Muat ulang agar total & filter (mis. "Belum register") konsisten.
            await load();
        }
    } catch (cause) {
        setMessage(
            'danger',
            cause instanceof Error ? cause.message : 'Registrasi peserta gagal disimpan.',
        );
    } finally {
        rowBusy.value = null;
    }
}

async function registerAll(): Promise<void> {
    if (!props.canWrite || bulkBusy.value) return;

    bulkBusy.value = true;
    setMessage('success', '');

    try {
        const count = await callRpc<number>('register_all', { p_date: registerDate.value });

        setMessage(
            'success',
            `${count} peserta berhasil di register pada ${formatDate(registerDate.value)}.`,
        );

        page.value = 0;
        await load();
    } catch (cause) {
        setMessage(
            'danger',
            cause instanceof Error ? cause.message : 'Registrasi massal gagal dijalankan.',
        );
    } finally {
        bulkBusy.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <form class="flex flex-1 flex-col gap-2 sm:flex-row" @submit.prevent="applySearch">
                <input
                    v-model="searchInput"
                    type="search"
                    placeholder="Cari kode, nama, atau NIK"
                    aria-label="Cari peserta untuk register"
                    class="vx-input"
                />
                <select v-model="status" class="vx-input sm:max-w-[190px]" @change="applySearch">
                    <option value="registered">Sudah register</option>
                    <option value="pending">Belum register</option>
                    <option value="all">Semua peserta</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="vx-btn-primary">Cari</button>
                    <button type="button" class="vx-btn-outline" @click="resetSearch">Reset</button>
                </div>
            </form>

            <a
                href="/app/participant/scan"
                class="inline-flex shrink-0 items-center justify-center rounded-[0.357rem] border border-line px-4 py-2.5 text-sm font-medium text-body transition hover:border-brand hover:text-brand"
            >
                Scan barcode
            </a>
        </div>

        <div
            v-if="canWrite"
            class="vx-card flex flex-col gap-3 p-4 sm:flex-row sm:items-end sm:justify-between"
        >
            <div>
                <h2 class="text-[0.9375rem] font-semibold text-ink">Register massal</h2>
                <p class="mt-0.5 text-[0.75rem] text-muted">
                    Hanya peserta pada client dan kontrak aktif yang belum register. Nomor register
                    memakai no. form legacy.
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <label class="sr-only" for="register-date">Tanggal register</label>
                <input
                    id="register-date"
                    v-model="registerDate"
                    type="date"
                    class="vx-input sm:w-[170px]"
                />
                <button
                    type="button"
                    class="vx-btn-primary"
                    :disabled="bulkBusy || !registerDate"
                    @click="registerAll"
                >
                    {{ bulkBusy ? 'Memproses…' : 'Register semua' }}
                </button>
            </div>
        </div>

        <p
            v-if="message"
            class="rounded-[0.357rem] px-3 py-2 text-sm"
            :class="
                messageTone === 'success'
                    ? 'bg-success-soft text-success'
                    : 'bg-danger-soft text-danger'
            "
            role="status"
        >
            {{ message }}
        </p>

        <p
            v-if="error"
            class="rounded-[0.357rem] border border-[#ffe4c9] bg-warning-soft px-3 py-2 text-sm text-[#b76e00]"
            role="alert"
        >
            {{ error }}
        </p>

        <div class="vx-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead
                        class="border-b border-line bg-[#f3f2f7] text-[0.7rem] uppercase tracking-wide text-muted"
                    >
                        <tr>
                            <th class="px-4 py-3 font-semibold">MCU ID</th>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">NIK</th>
                            <th class="px-4 py-3 font-semibold">Divisi</th>
                            <th class="px-4 py-3 font-semibold">No. form</th>
                            <th class="px-4 py-3 font-semibold">No. register</th>
                            <th class="px-4 py-3 font-semibold">Tgl. register</th>
                            <th class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <tr v-if="loading">
                            <td colspan="8" class="px-4 py-10 text-center text-muted">
                                Memuat data register…
                            </td>
                        </tr>
                        <tr v-else-if="rows.length === 0">
                            <td colspan="8" class="px-4 py-10 text-center">
                                <p class="font-medium text-ink">
                                    Tidak ada peserta pada filter ini.
                                </p>
                                <p class="mt-1 text-xs text-muted">
                                    Coba ubah status menjadi “Semua peserta” atau kosongkan
                                    pencarian.
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
                                <td class="px-4 py-3 text-muted">{{ row.divisi_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-muted">{{ row.no_form ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-muted">
                                    {{ row.register_number ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-muted">
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
                                <td class="px-4 py-3">
                                    <button
                                        v-if="canWrite && !row.register_date"
                                        type="button"
                                        class="rounded-[0.357rem] border border-line px-3 py-1.5 text-xs font-medium text-body transition hover:border-brand hover:text-brand disabled:opacity-40"
                                        :disabled="rowBusy !== null"
                                        @click="registerOne(row)"
                                    >
                                        {{ rowBusy === row.id ? 'Menyimpan…' : 'Register' }}
                                    </button>
                                    <span v-else class="text-xs text-muted">—</span>
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
