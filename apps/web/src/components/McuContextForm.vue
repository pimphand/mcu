<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';

type Client = {
    id: number;
    code: string;
    name: string;
};

type Contract = {
    id: number;
    code: string;
    name: string;
    client_id: number;
};

const props = defineProps<{
    currentClientId: number | null;
    currentContractId: number | null;
    appRole: string;
    /** Permission menu MCU IN/OUT; tanpa izin tombol IN/OUT tidak ditampilkan. */
    canWrite: boolean;
}>();

const clients = ref<Client[]>([]);
const contracts = ref<Contract[]>([]);
const clientId = ref<number | null>(null);
const contractId = ref<number | null>(null);
const loading = ref(false);
const loadingClients = ref(true);
const loadingContracts = ref(false);
const error = ref('');

async function handleUnauthorized(response: Response): Promise<void> {
    if (response.status === 401) {
        window.location.assign('/login?next=/app/mcu-in-out');
        throw new Error('Sesi berakhir.');
    }
}

async function request<T>(url: string, init?: RequestInit): Promise<T> {
    const response = await fetch(url, {
        ...init,
        headers: {
            accept: 'application/json',
            ...(init?.body ? { 'content-type': 'application/json' } : {}),
            ...init?.headers,
        },
    });

    await handleUnauthorized(response);

    const payload = (await response.json().catch(() => null)) as (T & { message?: string }) | null;

    if (!response.ok) {
        throw new Error(payload?.message ?? 'Permintaan gagal.');
    }

    return payload as T;
}

async function loadContracts(value: number, preferredContractId: number | null): Promise<void> {
    loadingContracts.value = true;
    error.value = '';

    const params = new URLSearchParams({
        select: 'id,code,name,client_id',
        client_id: `eq.${value}`,
        // Legacy memakai model SoftDeletes: kontrak yang sudah dihapus tidak
        // boleh muncul (dan `mcu/in` menolaknya dengan 404).
        deleted_at: 'is.null',
        order: 'name.asc',
        limit: '200',
    });

    try {
        const rows = await request<Contract[]>(`/api/db/contracts?${params.toString()}`);
        contracts.value = rows;
        contractId.value =
            preferredContractId && rows.some((contract) => contract.id === preferredContractId)
                ? preferredContractId
                : null;
    } catch (cause) {
        contracts.value = [];
        error.value = cause instanceof Error ? cause.message : 'Kontrak tidak dapat dimuat.';
    } finally {
        loadingContracts.value = false;
    }
}

async function loadClients(): Promise<void> {
    loadingClients.value = true;
    error.value = '';

    const params = new URLSearchParams({
        select: 'id,code,name',
        order: 'name.asc',
        limit: '200',
    });

    try {
        const rows = await request<Client[]>(`/api/db/clients?${params.toString()}`);
        clients.value = rows;

        const preferred =
            props.currentClientId && rows.some((client) => client.id === props.currentClientId)
                ? props.currentClientId
                : (rows[0]?.id ?? null);

        clientId.value = preferred;
    } catch (cause) {
        error.value = cause instanceof Error ? cause.message : 'Client tidak dapat dimuat.';
    } finally {
        loadingClients.value = false;
    }
}

async function submit(): Promise<void> {
    if (loading.value) return;

    loading.value = true;
    error.value = '';

    // Form legacy memakai satu tombol: IN saat belum ada konteks, OUT saat
    // konteks aktif. Idem agar alurnya sama dengan aplikasi lama.
    const leaving = props.currentContractId !== null;

    try {
        if (leaving) {
            await request('/api/mcu/out', { method: 'POST', body: JSON.stringify({}) });
            window.location.assign('/app/mcu-in-out?status=out');
            return;
        }

        if (!clientId.value || !contractId.value) {
            error.value = 'Pilih client dan kontrak terlebih dahulu.';
            return;
        }

        await request('/api/mcu/in', {
            method: 'POST',
            body: JSON.stringify({
                client_id: clientId.value,
                contract_id: contractId.value,
            }),
        });

        window.location.assign('/app/mcu-in-out?status=in');
    } catch (cause) {
        error.value =
            cause instanceof Error
                ? cause.message
                : leaving
                  ? 'MCU OUT gagal dijalankan.'
                  : 'MCU IN gagal dijalankan.';
    } finally {
        loading.value = false;
    }
}

onMounted(loadClients);

watch(clientId, async (value) => {
    contracts.value = [];
    contractId.value = null;

    if (value) {
        await loadContracts(
            value,
            value === props.currentClientId ? props.currentContractId : null,
        );
    }
});
</script>

<template>
    <form class="space-y-3" @submit.prevent="submit">
        <div>
            <label for="client" class="vx-label">Pilih Client</label>
            <select
                id="client"
                v-model.number="clientId"
                class="vx-input"
                required
                :disabled="loadingClients || loading"
            >
                <option :value="null">Pilih</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                    {{ client.code }} | {{ client.name }}
                </option>
            </select>
        </div>

        <div>
            <label for="contract" class="vx-label">Pilih Kontrak</label>
            <select
                id="contract"
                v-model.number="contractId"
                class="vx-input disabled:bg-surface disabled:text-muted"
                required
                :disabled="!clientId || loadingContracts || loading"
            >
                <option :value="null">
                    {{ loadingContracts ? 'Memuat kontrak…' : 'Pilih' }}
                </option>
                <option v-for="contract in contracts" :key="contract.id" :value="contract.id">
                    {{ contract.code }} | {{ contract.name }}
                </option>
            </select>
        </div>

        <p
            v-if="error"
            class="rounded-[0.357rem] border border-[#f8d4d4] bg-danger-soft px-3 py-2 text-sm text-danger"
            role="alert"
        >
            {{ error }}
        </p>

        <div
            v-else-if="!loadingClients && clients.length === 0"
            class="rounded-[0.357rem] border border-[#ffe4c9] bg-warning-soft px-3 py-2 text-sm text-[#b76e00]"
            role="status"
        >
            <template v-if="appRole === 'participant'">
                Role Peserta tidak dapat memilih konteks MCU.
            </template>
            <template v-else>
                Belum ada client yang dapat dipilih. Pastikan data client sudah tersedia di
                database.
            </template>
        </div>

        <p
            v-if="!canWrite"
            class="rounded-[0.357rem] border border-[#ffe4c9] bg-warning-soft px-3 py-2 text-sm text-[#b76e00]"
            role="status"
        >
            Role Anda tidak memiliki izin mengubah konteks MCU. Hubungi administrator.
        </p>

        <div v-else class="text-center">
            <button
                v-if="currentContractId"
                type="submit"
                class="inline-flex items-center justify-center rounded-[0.357rem] bg-danger px-5 py-2.5 text-sm font-medium text-white transition hover:bg-[#d94646] disabled:cursor-wait disabled:opacity-60"
                :disabled="loading"
            >
                {{ loading ? 'Memproses…' : 'OUT' }}
            </button>
            <button
                v-else
                type="submit"
                class="vx-btn-primary disabled:cursor-wait"
                :disabled="loading || loadingClients || !contractId"
            >
                {{ loading ? 'Memproses…' : 'IN' }}
            </button>
        </div>
    </form>
</template>
