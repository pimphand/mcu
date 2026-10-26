<script setup lang="ts">
import { ref } from 'vue';
import {
    PACKET_KEYS,
    PLAN_KEYS,
    emptyParticipantFilters,
    parseParticipantFilters,
    participantFilterQuery,
    type ParticipantFilters,
} from '@/lib/participant-filters';

/**
 * Form filter daftar peserta. Field dan label mengikuti modal
 * `participant/filter.blade.php` legacy, tetapi switch langsung mengirim filter
 * boolean (legacy membaca nama field yang salah untuk plan U, lab khusus, dan
 * paket sehingga switch itu tidak pernah berpengaruh).
 *
 * Komponen ini hanya menyusun draft; pemanggilan data tetap di `ParticipantTable`
 * supaya satu sumber kebenaran untuk halaman aktif dan URL.
 */

const props = defineProps<{
    initial: ParticipantFilters;
    activeCount: number;
}>();

const emit = defineEmits<{
    apply: [filters: ParticipantFilters];
    reset: [];
}>();

const open = ref(false);
const draft = ref<ParticipantFilters>(cloneFilters(props.initial));

function cloneFilters(source: ParticipantFilters): ParticipantFilters {
    return {
        ...source,
        plans: { ...source.plans },
        packets: { ...source.packets },
    };
}

function submit(): void {
    // Normalisasi lewat query string agar nilai yang dikirim sama dengan yang
    // nanti ditulis ke URL (spasi dipangkas, tanggal/gender divalidasi).
    emit('apply', parseParticipantFilters(participantFilterQuery(draft.value)));
}

function resetFilters(): void {
    draft.value = emptyParticipantFilters();
    emit('reset');
}
</script>

<template>
    <form class="vx-card p-4" @submit.prevent="submit">
        <div class="flex flex-col gap-2 sm:flex-row">
            <input
                v-model="draft.search"
                type="search"
                placeholder="Cari kode atau nama peserta"
                aria-label="Cari kode atau nama peserta"
                class="vx-input"
            />
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="vx-btn-primary">Cari</button>
                <button
                    type="button"
                    class="vx-btn-outline"
                    aria-controls="participant-filter-panel"
                    :aria-expanded="open"
                    @click="open = !open"
                >
                    Filter
                    <span
                        v-if="activeCount"
                        class="ml-1 rounded-full bg-brand px-1.5 py-0.5 text-[0.65rem] font-semibold text-white"
                    >
                        {{ activeCount }}
                    </span>
                </button>
                <button type="button" class="vx-btn-outline" @click="resetFilters">Reset</button>
            </div>
        </div>

        <div
            v-show="open"
            id="participant-filter-panel"
            class="mt-4 grid gap-4 border-t border-line pt-4 sm:grid-cols-2 lg:grid-cols-4"
        >
            <div>
                <label class="vx-label" for="filter-nik">NIK</label>
                <input
                    id="filter-nik"
                    v-model="draft.nik"
                    type="text"
                    inputmode="numeric"
                    autocomplete="off"
                    class="vx-input"
                    placeholder="NIK peserta"
                />
            </div>

            <div>
                <label class="vx-label" for="filter-name">Nama</label>
                <input
                    id="filter-name"
                    v-model="draft.name"
                    type="text"
                    autocomplete="off"
                    class="vx-input"
                    placeholder="Nama peserta"
                />
            </div>

            <div>
                <label class="vx-label" for="filter-gender">Jenis Kelamin</label>
                <select id="filter-gender" v-model="draft.gender" class="vx-input">
                    <option value="">Semua</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>

            <div>
                <label class="vx-label" for="filter-birthday">Tanggal Lahir</label>
                <input id="filter-birthday" v-model="draft.birthday" type="date" class="vx-input" />
            </div>

            <fieldset class="sm:col-span-2 lg:col-span-4">
                <legend class="vx-label">Plan MCU</legend>
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                    <label
                        v-for="plan in PLAN_KEYS"
                        :key="plan"
                        class="flex items-center gap-2 text-sm text-body"
                    >
                        <input
                            v-model="draft.plans[plan]"
                            type="checkbox"
                            class="h-4 w-4 accent-brand"
                        />
                        {{ plan.toUpperCase() }}
                    </label>
                    <label class="flex items-center gap-2 text-sm text-body">
                        <input
                            v-model="draft.labSpecial"
                            type="checkbox"
                            class="h-4 w-4 accent-brand"
                        />
                        Lab Khusus
                    </label>
                </div>
            </fieldset>

            <fieldset class="sm:col-span-2 lg:col-span-4">
                <legend class="vx-label">Paket</legend>
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                    <label
                        v-for="packet in PACKET_KEYS"
                        :key="packet"
                        class="flex items-center gap-2 text-sm text-body"
                    >
                        <input
                            v-model="draft.packets[packet]"
                            type="checkbox"
                            class="h-4 w-4 accent-brand"
                        />
                        Paket {{ packet.toUpperCase() }}
                    </label>
                </div>
            </fieldset>

            <div class="sm:col-span-2 lg:col-span-4">
                <button type="submit" class="vx-btn-primary">Terapkan filter</button>
            </div>
        </div>
    </form>
</template>
