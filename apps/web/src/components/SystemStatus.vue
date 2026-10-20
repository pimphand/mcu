<script setup lang="ts">
import { onMounted, ref } from 'vue';

type CheckStatus = 'ok' | 'configured' | 'not_configured' | 'unreachable' | 'error';

type Health = {
    ok: boolean;
    service: string;
    version: string;
    checks: {
        app: CheckStatus;
        postgrest: CheckStatus;
        auth: CheckStatus;
    };
    timestamp: string;
};

const health = ref<Health | null>(null);
const loading = ref(true);
const error = ref('');

const labels: Record<CheckStatus, string> = {
    ok: 'Aktif',
    configured: 'Siap',
    not_configured: 'Belum diatur',
    unreachable: 'Tidak terhubung',
    error: 'Error',
};

const tones: Record<CheckStatus, string> = {
    ok: 'bg-success',
    configured: 'bg-success',
    not_configured: 'bg-warning',
    unreachable: 'bg-danger',
    error: 'bg-danger',
};

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/api/health', {
            headers: { accept: 'application/json' },
        });
        const payload = (await response.json()) as Health;

        health.value = payload;
    } catch {
        error.value = 'Status sistem tidak dapat dibaca.';
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <section class="vx-card p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold text-ink">Status sistem</h2>
                <p class="mt-0.5 text-xs text-muted">Pemeriksaan konfigurasi base saat ini.</p>
            </div>
            <button
                type="button"
                class="rounded-[0.357rem] border border-line px-2.5 py-1.5 text-xs font-medium text-body transition hover:border-brand hover:text-brand disabled:opacity-50"
                :disabled="loading"
                @click="load"
            >
                {{ loading ? 'Memuat…' : 'Muat ulang' }}
            </button>
        </div>

        <p v-if="error" class="mt-4 text-sm text-danger" role="alert">{{ error }}</p>

        <dl v-else class="mt-4 space-y-3">
            <div class="flex items-center justify-between gap-4">
                <dt class="text-sm text-ink">Aplikasi</dt>
                <dd class="flex items-center gap-2 text-xs text-muted">
                    <span
                        class="size-2 rounded-full"
                        :class="health ? tones[health.checks.app] : 'bg-line'"
                    ></span>
                    {{ health ? labels[health.checks.app] : 'Memeriksa…' }}
                </dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="text-sm text-ink">PostgREST</dt>
                <dd class="flex items-center gap-2 text-xs text-muted">
                    <span
                        class="size-2 rounded-full"
                        :class="health ? tones[health.checks.postgrest] : 'bg-line'"
                    ></span>
                    {{ health ? labels[health.checks.postgrest] : 'Memeriksa…' }}
                </dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="text-sm text-ink">Autentikasi</dt>
                <dd class="flex items-center gap-2 text-xs text-muted">
                    <span
                        class="size-2 rounded-full"
                        :class="health ? tones[health.checks.auth] : 'bg-line'"
                    ></span>
                    {{ health ? labels[health.checks.auth] : 'Memeriksa…' }}
                </dd>
            </div>
        </dl>

        <p v-if="health" class="mt-4 border-t border-line pt-3 text-[11px] text-muted">
            Versi {{ health.version }} ·
            {{ new Date(health.timestamp).toLocaleTimeString('id-ID') }}
        </p>
    </section>
</template>
