<script setup lang="ts">
import { formatDate } from '@/lib/format';
import { participantPlanBadges, type ParticipantListRow } from '@/lib/participant-format';

/**
 * Tabel daftar peserta (tampilan saja): kolom mengikuti daftar legacy
 * `pages/participant/index.blade.php` — MCU ID, NIK, nama, JK, tgl lahir,
 * divisi, department, kontrak, plan, paket, dan status register.
 */

defineProps<{
    rows: ParticipantListRow[];
    loading: boolean;
    /** Jumlah filter aktif; menentukan pesan empty state. */
    activeCount: number;
    total: number;
    /** Indeks halaman (0 = halaman pertama). */
    page: number;
    pageCount: number;
    from: number;
    to: number;
}>();

defineEmits<{
    reset: [];
    page: [page: number];
}>();
</script>

<template>
    <div class="vx-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1080px] text-left text-sm">
                <thead
                    class="border-b border-line bg-[#f3f2f7] text-[0.7rem] uppercase tracking-wide text-muted"
                >
                    <tr>
                        <th class="px-4 py-3 font-semibold">MCU ID</th>
                        <th class="px-4 py-3 font-semibold">NIK</th>
                        <th class="px-4 py-3 font-semibold">Nama</th>
                        <th class="px-4 py-3 font-semibold">JK</th>
                        <th class="px-4 py-3 font-semibold">Tgl. Lahir</th>
                        <th class="px-4 py-3 font-semibold">Divisi</th>
                        <th class="px-4 py-3 font-semibold">Department</th>
                        <th class="px-4 py-3 font-semibold">Kontrak</th>
                        <th class="px-4 py-3 font-semibold">Plan</th>
                        <th class="px-4 py-3 font-semibold">Paket</th>
                        <th class="px-4 py-3 font-semibold">Register</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    <tr v-if="loading">
                        <td colspan="11" class="px-4 py-10 text-center text-muted">
                            Memuat peserta…
                        </td>
                    </tr>
                    <tr v-else-if="rows.length === 0">
                        <td colspan="11" class="px-4 py-10 text-center">
                            <p class="font-medium text-ink">
                                {{
                                    activeCount
                                        ? 'Tidak ada peserta yang cocok dengan filter.'
                                        : 'Belum ada peserta pada konteks ini.'
                                }}
                            </p>
                            <p class="mt-1 text-xs text-muted">
                                {{
                                    activeCount
                                        ? 'Ubah atau reset filter untuk melihat peserta lain.'
                                        : 'Import peserta atau pilih kontrak lain setelah modul peserta aktif.'
                                }}
                            </p>
                            <button
                                v-if="activeCount"
                                type="button"
                                class="vx-btn-outline mt-3"
                                @click="$emit('reset')"
                            >
                                Reset filter
                            </button>
                        </td>
                    </tr>
                    <template v-else>
                        <tr v-for="row in rows" :key="row.id" class="hover:bg-[#f8f8f8]">
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-brand">
                                <a :href="`/app/participant/${row.id}`" class="hover:underline">
                                    {{ row.code }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-muted">
                                {{ row.nik ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-ink">
                                <a :href="`/app/participant/${row.id}`" class="hover:underline">
                                    {{ row.name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-muted">{{ row.gender ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-muted">
                                {{ formatDate(row.birthday) }}
                            </td>
                            <td class="px-4 py-3 text-muted">{{ row.divisi_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted">{{ row.department_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted">{{ row.contract_name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="badge in participantPlanBadges(row)"
                                        :key="badge"
                                        class="rounded-full bg-brand-soft px-2 py-0.5 text-[0.7rem] font-semibold text-brand"
                                    >
                                        {{ badge }}
                                    </span>
                                    <span
                                        v-if="row.lab_special"
                                        class="rounded-full bg-[#e0f7fa] px-2 py-0.5 text-[0.7rem] font-semibold text-[#00838f]"
                                    >
                                        Lab
                                    </span>
                                    <span
                                        v-if="
                                            participantPlanBadges(row).length === 0 &&
                                            !row.lab_special
                                        "
                                        class="text-muted"
                                    >
                                        —
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted">{{ row.packet_name ?? '—' }}</td>
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
                                        row.register_date ? formatDate(row.register_date) : 'Belum'
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
                    @click="$emit('page', page - 1)"
                >
                    Sebelumnya
                </button>
                <span>Halaman {{ page + 1 }} / {{ pageCount }}</span>
                <button
                    type="button"
                    class="rounded-[0.357rem] border border-line px-3 py-1.5 font-medium text-body transition hover:border-brand hover:text-brand disabled:opacity-40"
                    :disabled="page + 1 >= pageCount || loading"
                    @click="$emit('page', page + 1)"
                >
                    Berikutnya
                </button>
            </div>
        </div>
    </div>
</template>
