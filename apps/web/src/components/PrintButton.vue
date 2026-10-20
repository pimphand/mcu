<script setup lang="ts">
function printPage(): void {
    window.print();
}
</script>

<template>
    <button
        type="button"
        class="rounded-[0.357rem] bg-brand px-4 py-2 text-[0.8125rem] font-medium text-white transition hover:bg-brand-dark"
        @click="printPage"
    >
        Cetak / PDF
    </button>
</template>
