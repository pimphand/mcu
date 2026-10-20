<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { fetchRows } from '@/lib/client-api';

type ScanRow = {
    id: number;
    code: string;
    name: string;
    register_number: string | null;
};

const code = ref('');
const busy = ref(false);
const error = ref('');

async function lookup(rawCode: string): Promise<void> {
    const term = rawCode.trim();

    if (!term) {
        error.value = 'Masukkan kode MCU terlebih dahulu.';
        return;
    }

    busy.value = true;
    error.value = '';

    try {
        const { rows } = await fetchRows<ScanRow>('v_participant_list', {
            select: 'id,code,name,register_number',
            code: `eq.${term}`,
            limit: 2,
        });

        const match = rows[0];

        if (!match) {
            error.value = `Kode ${term} tidak ditemukan pada client dan kontrak aktif.`;
            return;
        }

        window.location.assign(`/app/participant/${match.id}`);
    } catch (cause) {
        error.value = cause instanceof Error ? cause.message : 'Pencarian kode gagal.';
    } finally {
        busy.value = false;
    }
}

function submit(): void {
    void lookup(code.value);
}

onMounted(() => {
    const initial = new URLSearchParams(window.location.search).get('code');

    if (initial) {
        code.value = initial;
        void lookup(initial);
    }
});
</script>

<template>
    <div class="mx-auto w-full max-w-xl">
        <form class="vx-card space-y-4 p-6" @submit.prevent="submit">
            <div>
                <label class="vx-label" for="mcu-code">Kode peserta</label>
                <input
                    id="mcu-code"
                    v-model="code"
                    class="vx-input font-mono"
                    placeholder="MCU00001"
                    autocomplete="off"
                    autofocus
                />
                <p class="mt-1 text-[0.75rem] text-muted">
                    Scan barcode atau ketik kode MCU (bukan NIK). Pencarian dibatasi RLS ke kontrak
                    aktif.
                </p>
            </div>

            <p v-if="error" class="text-[0.8125rem] text-danger" role="alert">{{ error }}</p>

            <button type="submit" class="vx-btn-primary w-full" :disabled="busy">
                {{ busy ? 'Mencari…' : 'Cari peserta' }}
            </button>
        </form>

        <p class="mt-4 text-center text-[0.75rem] text-muted">
            Untuk pemindai barcode, buka
            <code class="rounded bg-white px-1.5 py-0.5">/app/participant/scan?code=KODE</code>
            agar hasil langsung terbuka.
        </p>
    </div>
</template>
