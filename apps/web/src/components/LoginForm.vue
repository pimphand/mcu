<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps<{
    next?: string;
}>();

const username = ref('');
const password = ref('');
const loading = ref(false);
const error = ref('');

const nextPath = computed(() => {
    const value = props.next ?? '/app';
    return value.startsWith('/') && !value.startsWith('//') ? value : '/app';
});

async function submit(): Promise<void> {
    error.value = '';

    if (!username.value.trim() || !password.value) {
        error.value = 'Username dan password wajib diisi.';
        return;
    }

    loading.value = true;

    try {
        const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers: { 'content-type': 'application/json' },
            body: JSON.stringify({
                username: username.value.trim(),
                password: password.value,
            }),
        });

        const payload = (await response.json().catch(() => null)) as { message?: string } | null;

        if (!response.ok) {
            error.value =
                payload?.message ??
                (response.status === 503
                    ? 'Autentikasi belum dikonfigurasi di environment ini.'
                    : 'Login gagal. Periksa username dan password.');
            return;
        }

        window.location.assign(nextPath.value);
    } catch {
        error.value = 'Tidak dapat menghubungi server. Periksa koneksi lalu coba lagi.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <form class="space-y-4" novalidate @submit.prevent="submit">
        <div>
            <label for="username" class="vx-label">Username atau email</label>
            <input
                id="username"
                v-model="username"
                type="text"
                name="username"
                autocomplete="username"
                required
                maxlength="100"
                placeholder="username atau email"
                class="vx-input"
            />
        </div>

        <div>
            <label for="password" class="vx-label">Password</label>
            <input
                id="password"
                v-model="password"
                type="password"
                name="password"
                autocomplete="current-password"
                required
                maxlength="200"
                placeholder="••••••••"
                class="vx-input"
            />
        </div>

        <p
            v-if="error"
            class="rounded-[0.357rem] border border-[#f8d4d4] bg-danger-soft px-3 py-2 text-sm text-danger"
            role="alert"
            aria-live="polite"
        >
            {{ error }}
        </p>

        <button type="submit" class="vx-btn-primary w-full" :disabled="loading">
            <span
                v-if="loading"
                class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
                aria-hidden="true"
            ></span>
            {{ loading ? 'Memeriksa…' : 'Masuk' }}
        </button>

        <p class="text-center text-[0.7rem] leading-5 text-muted">
            Gunakan akun yang dibuat oleh administrator. Password awal peserta mengikuti tanggal
            lahir.
        </p>
    </form>
</template>
