<script setup lang="ts">
import { ref } from 'vue';

const loading = ref(false);

async function logout(): Promise<void> {
    loading.value = true;

    try {
        await fetch('/api/auth/logout', { method: 'POST' });
    } finally {
        window.location.assign('/login');
    }
}
</script>

<template>
    <button
        type="button"
        class="vx-btn-outline w-full text-[0.8125rem] disabled:cursor-wait"
        :disabled="loading"
        @click="logout"
    >
        <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" />
        </svg>
        <span>{{ loading ? 'Keluar…' : 'Keluar' }}</span>
    </button>
</template>
