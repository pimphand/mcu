<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import ExamResultForm from '@/components/ExamResultForm.vue';
import { callRpc, fetchRows } from '@/lib/client-api';
import { ageLabel, formatDate, formatDateTime, genderLabel } from '@/lib/format';
import { canInputResult, examResults, progressSteps, type ExamResultConfig } from '@/lib/exams';

type ParticipantRow = {
    id: number;
    code: string;
    nik: string;
    name: string;
    gender: string;
    birthday: string;
    phone: string | null;
    status: string | null;
    packet_name: string | null;
    plan_name: string | null;
    plan_u: boolean;
    plan_a: boolean;
    plan_e: boolean;
    plan_s: boolean;
    plan_r: boolean;
    lab_special: boolean;
    no_form: number | null;
    register_number: string | null;
    register_date: string | null;
    divisi_name: string | null;
    department_name: string | null;
    client_id: number | null;
    client_name: string | null;
    contract_id: number | null;
    contract_name: string | null;
};

type ProgressRow = {
    participant_id: number;
    code: string;
    completion_pct: number;
    steps_done: number;
    steps_total: number;
    fisik_done: boolean;
    lab_done: boolean;
    radiologi_done: boolean;
    spirometri_done: boolean;
    audiometri_done: boolean;
    rectal_done: boolean;
    ekg_done: boolean;
};

type RegisterResult = {
    participant_id: number;
    register_number: string;
    register_date: string;
};

const props = defineProps<{
    participantId: number;
    /** Boleh mengisi hasil yang belum ada (permission `is_add`). */
    canWrite: boolean;
    /** Boleh mengubah hasil yang sudah tersimpan (permission `is_edit`). */
    canEdit: boolean;
    /** Boleh menjalankan registrasi peserta (menu Register). */
    canRegister: boolean;
}>();

const participant = ref<ParticipantRow | null>(null);
const progress = ref<ProgressRow | null>(null);
const loading = ref(true);
const error = ref('');

const activeTab = ref<string>('identitas');
const results = reactive<Record<string, Record<string, unknown> | null>>({});
const resultErrors = reactive<Record<string, string>>({});
const resultLoading = ref(false);
/** Tab yang sedang menampilkan formulir input hasil. */
const editing = ref<string | null>(null);

const registering = ref(false);
const actionMessage = ref('');
const actionTone = ref<'success' | 'danger'>('success');

const planLabels: { key: keyof ParticipantRow; label: string }[] = [
    { key: 'plan_u', label: 'U' },
    { key: 'plan_a', label: 'A' },
    { key: 'plan_e', label: 'E' },
    { key: 'plan_s', label: 'S' },
    { key: 'plan_r', label: 'R' },
];

const completion = computed(() => progress.value?.completion_pct ?? 0);

function valueAt(row: Record<string, unknown> | null, key: string): unknown {
    return row ? row[key] : undefined;
}

function isEmpty(value: unknown): boolean {
    return value === null || value === undefined || value === '';
}

function displayValue(value: unknown, type?: string): string {
    if (isEmpty(value)) return '—';

    if (typeof value === 'boolean') {
        return value ? 'Ya' : 'Tidak';
    }

    if (type === 'date') return formatDate(String(value));
    if (type === 'datetime') return formatDateTime(String(value));

    return String(value);
}

/** Peserta tanpa baris hasil dianggap belum punya data (dan boleh diisi). */
function hasAnyField(row: Record<string, unknown> | null, config: ExamResultConfig): boolean {
    return config.fields.some((field) => !isEmpty(valueAt(row, field.key)));
}

async function loadParticipant(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        const [identity, progressRows] = await Promise.all([
            fetchRows<ParticipantRow>('v_participant_list', {
                select: 'id,code,nik,name,gender,birthday,phone,status,packet_name,plan_name,plan_u,plan_a,plan_e,plan_s,plan_r,lab_special,no_form,register_number,register_date,divisi_name,department_name,client_id,client_name,contract_id,contract_name',
                id: `eq.${props.participantId}`,
                limit: 1,
            }),
            fetchRows<ProgressRow>('v_mcu_progress', {
                select: '*',
                participant_id: `eq.${props.participantId}`,
                limit: 1,
            }),
        ]);

        participant.value = identity.rows[0] ?? null;
        progress.value = progressRows.rows[0] ?? null;

        if (!participant.value) {
            error.value = 'Peserta tidak ditemukan atau berada di luar kontrak yang Anda akses.';
        }
    } catch (cause) {
        error.value = cause instanceof Error ? cause.message : 'Data peserta tidak dapat dimuat.';
    } finally {
        loading.value = false;
    }
}

async function loadResult(config: ExamResultConfig): Promise<void> {
    if (results[config.key] || resultErrors[config.key] || resultLoading.value) return;

    const filterValue =
        config.filterBy === 'no_mcu' ? participant.value?.code : props.participantId;

    if (!filterValue) return;

    resultLoading.value = true;

    try {
        const { rows } = await fetchRows<Record<string, unknown>>(config.resource, {
            select: '*',
            [config.filterBy]: `eq.${filterValue}`,
            limit: 1,
        });

        results[config.key] = rows[0] ?? null;
    } catch (cause) {
        resultErrors[config.key] =
            cause instanceof Error ? cause.message : 'Hasil pemeriksaan tidak dapat dimuat.';
    } finally {
        resultLoading.value = false;
    }
}

/** `results[key] === null` berarti peserta belum punya baris hasil. */
function resultLoaded(key: string): boolean {
    return key in results;
}

/** Tombol mengisi data baru butuh `add`, sedangkan merevisi butuh `edit`. */
function canModify(config: ExamResultConfig): boolean {
    return hasAnyField(results[config.key] ?? null, config) ? props.canEdit : props.canWrite;
}

function selectTab(key: string): void {
    activeTab.value = key;
    editing.value = null;

    const config = examResults.find((item) => item.key === key);

    if (config) {
        void loadResult(config);
    }
}

function currentResult(config: ExamResultConfig): Record<string, unknown> | null {
    return results[config.key] ?? null;
}

async function refreshProgress(): Promise<void> {
    try {
        const { rows } = await fetchRows<ProgressRow>('v_mcu_progress', {
            select: '*',
            participant_id: `eq.${props.participantId}`,
            limit: 1,
        });

        progress.value = rows[0] ?? null;
    } catch (cause) {
        actionTone.value = 'danger';
        actionMessage.value =
            cause instanceof Error ? cause.message : 'Progres pemeriksaan gagal dimuat ulang.';
    }
}

function handleSaved(config: ExamResultConfig, row: Record<string, unknown>): void {
    results[config.key] = row;
    editing.value = null;
    actionTone.value = 'success';
    actionMessage.value = `Hasil ${config.label.toLowerCase()} tersimpan.`;
    void refreshProgress();
}

async function register(): Promise<void> {
    if (!props.canWrite || registering.value) return;

    registering.value = true;
    actionMessage.value = '';

    try {
        const payload = await callRpc<RegisterResult[]>('register_participant', {
            p_participant_id: props.participantId,
        });
        const registered = payload[0];

        if (registered) {
            if (participant.value) {
                participant.value.register_number = registered.register_number;
                participant.value.register_date = registered.register_date;
            }

            actionTone.value = 'success';
            actionMessage.value = `Peserta terdaftar dengan nomor ${registered.register_number}.`;
        }
    } catch (cause) {
        actionTone.value = 'danger';
        actionMessage.value =
            cause instanceof Error ? cause.message : 'Registrasi peserta gagal disimpan.';
    } finally {
        registering.value = false;
    }
}

watch(
    () => props.participantId,
    () => {
        activeTab.value = 'identitas';
        void loadParticipant();
    },
);

onMounted(loadParticipant);
</script>

<template>
    <div class="space-y-5">
        <p v-if="loading" class="vx-card px-5 py-10 text-center text-sm text-muted">
            Memuat data peserta…
        </p>

        <div
            v-else-if="error"
            class="rounded-[0.5rem] border border-[#fbd6d6] bg-danger-soft p-6"
            role="alert"
        >
            <h2 class="text-[1rem] font-semibold text-danger">Data peserta tidak tersedia</h2>
            <p class="mt-1 text-[0.875rem] text-danger/80">{{ error }}</p>
            <a
                href="/app/participant"
                class="mt-4 inline-flex rounded-[0.357rem] border border-danger/40 px-4 py-2 text-sm font-medium text-danger"
            >
                Kembali ke daftar peserta
            </a>
        </div>

        <template v-else-if="participant">
            <section class="vx-card overflow-hidden">
                <div
                    class="flex flex-col gap-4 border-b border-line bg-[linear-gradient(118deg,#7367f0,#9e95f5)] px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="min-w-0">
                        <p class="font-mono text-[0.75rem] text-white/80">{{ participant.code }}</p>
                        <h2 class="mt-1 truncate text-[1.35rem] font-semibold text-white">
                            {{ participant.name }}
                        </h2>
                        <p class="mt-1 text-[0.8rem] text-white/80">
                            {{ genderLabel(participant.gender) }} ·
                            {{ ageLabel(participant.birthday) }} ·
                            {{ participant.client_name ?? '—' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="rounded-full bg-white/15 px-3 py-1.5 text-[0.75rem] font-medium"
                        >
                            {{ participant.register_number ?? 'Belum register' }}
                        </span>
                        <button
                            v-if="canRegister && !participant.register_date"
                            type="button"
                            class="rounded-[0.357rem] bg-white px-4 py-2 text-[0.8125rem] font-semibold text-brand transition hover:bg-brand-soft disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="registering"
                            @click="register"
                        >
                            {{ registering ? 'Menyimpan…' : 'Register peserta' }}
                        </button>
                    </div>
                </div>

                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="text-[0.7rem] uppercase tracking-wide text-muted">NIK</p>
                        <p class="mt-1 text-[0.875rem] font-medium text-ink">
                            {{ participant.nik }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[0.7rem] uppercase tracking-wide text-muted">
                            Tanggal lahir
                        </p>
                        <p class="mt-1 text-[0.875rem] font-medium text-ink">
                            {{ formatDate(participant.birthday) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[0.7rem] uppercase tracking-wide text-muted">Telepon</p>
                        <p class="mt-1 text-[0.875rem] font-medium text-ink">
                            {{ participant.phone ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[0.7rem] uppercase tracking-wide text-muted">Status</p>
                        <p class="mt-1 text-[0.875rem] font-medium text-ink">
                            {{ participant.status ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[0.7rem] uppercase tracking-wide text-muted">Divisi</p>
                        <p class="mt-1 text-[0.875rem] font-medium text-ink">
                            {{ participant.divisi_name ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[0.7rem] uppercase tracking-wide text-muted">Department</p>
                        <p class="mt-1 text-[0.875rem] font-medium text-ink">
                            {{ participant.department_name ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[0.7rem] uppercase tracking-wide text-muted">Kontrak</p>
                        <p class="mt-1 text-[0.875rem] font-medium text-ink">
                            {{ participant.contract_name ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[0.7rem] uppercase tracking-wide text-muted">No. form</p>
                        <p class="mt-1 text-[0.875rem] font-medium text-ink">
                            {{ participant.no_form ?? '—' }}
                        </p>
                    </div>
                </div>

                <div
                    class="flex flex-wrap items-center gap-2 border-t border-line bg-[#f8f8f8] px-5 py-3 text-[0.75rem] text-body"
                >
                    <span class="font-medium text-ink">Paket</span>
                    <span>{{ participant.packet_name ?? participant.plan_name ?? '—' }}</span>
                    <span
                        v-for="plan in planLabels"
                        :key="plan.label"
                        class="rounded-full px-2 py-0.5"
                        :class="
                            participant[plan.key]
                                ? 'bg-brand-soft text-brand-dark'
                                : 'bg-[#f3f2f7] text-muted'
                        "
                    >
                        {{ plan.label }}
                    </span>
                    <span
                        v-if="participant.lab_special"
                        class="rounded-full bg-warning-soft px-2 py-0.5 text-[#b76e00]"
                    >
                        Lab khusus
                    </span>
                </div>

                <p
                    v-if="actionMessage"
                    class="border-t border-line px-5 py-3 text-[0.8125rem]"
                    :class="actionTone === 'success' ? 'text-success' : 'text-danger'"
                    role="status"
                >
                    {{ actionMessage }}
                </p>
            </section>

            <section class="vx-card p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-[1rem] font-semibold text-ink">Progres pemeriksaan</h3>
                        <p class="mt-0.5 text-[0.75rem] text-muted">
                            {{ progress?.steps_done ?? 0 }} dari {{ progress?.steps_total ?? 7 }}
                            langkah selesai.
                        </p>
                    </div>
                    <span class="text-[0.8125rem] font-semibold text-brand">
                        {{ completion }}%
                    </span>
                </div>

                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-[#f3f2f7]">
                    <div
                        class="h-full rounded-full bg-brand transition-all"
                        :style="{ width: `${completion}%` }"
                        role="progressbar"
                        :aria-valuenow="completion"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    ></div>
                </div>

                <ul class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    <li
                        v-for="step in progressSteps"
                        :key="step.key"
                        class="flex items-center justify-between gap-2 rounded-[0.357rem] border border-line px-3 py-2 text-[0.8125rem]"
                    >
                        <span class="text-body">{{ step.label }}</span>
                        <span
                            class="rounded-full px-2 py-0.5 text-[0.7rem] font-medium"
                            :class="
                                progress?.[step.key]
                                    ? 'bg-success-soft text-success'
                                    : 'bg-[#f3f2f7] text-muted'
                            "
                        >
                            {{ progress?.[step.key] ? 'Selesai' : 'Belum' }}
                        </span>
                    </li>
                </ul>
            </section>

            <section class="vx-card overflow-hidden">
                <div
                    class="flex gap-1 overflow-x-auto border-b border-line bg-white px-3 py-2"
                    role="tablist"
                    aria-label="Hasil pemeriksaan"
                >
                    <button
                        type="button"
                        role="tab"
                        :aria-selected="activeTab === 'identitas'"
                        class="shrink-0 rounded-[0.357rem] px-3 py-2 text-[0.8125rem] font-medium transition"
                        :class="
                            activeTab === 'identitas'
                                ? 'bg-brand text-white'
                                : 'text-body hover:bg-[#f3f2f7]'
                        "
                        @click="selectTab('identitas')"
                    >
                        Identitas
                    </button>
                    <button
                        v-for="config in examResults"
                        :key="config.key"
                        type="button"
                        role="tab"
                        :aria-selected="activeTab === config.key"
                        class="shrink-0 rounded-[0.357rem] px-3 py-2 text-[0.8125rem] font-medium transition"
                        :class="
                            activeTab === config.key
                                ? 'bg-brand text-white'
                                : 'text-body hover:bg-[#f3f2f7]'
                        "
                        @click="selectTab(config.key)"
                    >
                        {{ config.label }}
                    </button>
                </div>

                <div class="p-5">
                    <div v-if="activeTab === 'identitas'" class="space-y-3">
                        <p class="text-[0.875rem] leading-6 text-body">
                            Data identitas dan paket peserta. Detail hasil tiap pemeriksaan ada di
                            tab masing-masing, dan hasil yang belum ada bisa diisi dari tab
                            pemeriksaannya.
                        </p>
                        <dl class="grid gap-3 sm:grid-cols-2">
                            <div class="flex justify-between gap-4 border-b border-line pb-2">
                                <dt class="text-[0.8125rem] text-muted">MCU ID</dt>
                                <dd class="font-mono text-[0.8125rem] text-ink">
                                    {{ participant.code }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 border-b border-line pb-2">
                                <dt class="text-[0.8125rem] text-muted">Nomor register</dt>
                                <dd class="text-[0.8125rem] text-ink">
                                    {{ participant.register_number ?? '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 border-b border-line pb-2">
                                <dt class="text-[0.8125rem] text-muted">Tanggal register</dt>
                                <dd class="text-[0.8125rem] text-ink">
                                    {{ formatDateTime(participant.register_date) }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 border-b border-line pb-2">
                                <dt class="text-[0.8125rem] text-muted">Nama paket</dt>
                                <dd class="text-[0.8125rem] text-ink">
                                    {{ participant.packet_name ?? participant.plan_name ?? '—' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <template v-for="config in examResults" :key="config.key">
                        <div v-if="activeTab === config.key">
                            <p v-if="resultErrors[config.key]" class="text-[0.875rem] text-danger">
                                {{ resultErrors[config.key] }}
                            </p>
                            <p
                                v-else-if="!resultLoaded(config.key)"
                                class="text-[0.875rem] text-muted"
                            >
                                Memuat hasil {{ config.label.toLowerCase() }}…
                            </p>
                            <ExamResultForm
                                v-else-if="
                                    editing === config.key && canWrite && canInputResult(config)
                                "
                                :config="config"
                                :participant-id="participantId"
                                :client-id="participant.client_id"
                                :contract-id="participant.contract_id"
                                :row="results[config.key]"
                                @saved="handleSaved(config, $event)"
                                @cancel="editing = null"
                            />
                            <template v-else>
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                    <p
                                        v-if="!hasAnyField(results[config.key], config)"
                                        class="text-[0.875rem] text-muted"
                                    >
                                        Belum ada hasil {{ config.label.toLowerCase() }} untuk
                                        peserta ini.
                                    </p>
                                    <p v-else class="text-[0.75rem] text-muted">
                                        Hasil {{ config.label.toLowerCase() }} sudah tersimpan.
                                    </p>
                                    <button
                                        v-if="canModify(config) && canInputResult(config)"
                                        type="button"
                                        class="vx-btn-primary"
                                        @click="editing = config.key"
                                    >
                                        {{
                                            hasAnyField(results[config.key], config)
                                                ? 'Ubah hasil'
                                                : `Isi hasil ${config.label.toLowerCase()}`
                                        }}
                                    </button>
                                </div>
                                <dl
                                    v-if="hasAnyField(results[config.key], config)"
                                    class="grid gap-3 sm:grid-cols-2"
                                >
                                    <div
                                        v-for="field in config.fields"
                                        :key="field.key"
                                        class="flex justify-between gap-4 border-b border-line pb-2"
                                    >
                                        <dt class="text-[0.8125rem] text-muted">
                                            {{ field.label }}
                                        </dt>
                                        <dd class="text-right text-[0.8125rem] text-ink">
                                            {{
                                                displayValue(
                                                    valueAt(currentResult(config) ?? {}, field.key),
                                                    field.type,
                                                )
                                            }}
                                        </dd>
                                    </div>
                                </dl>
                            </template>
                        </div>
                    </template>
                </div>
            </section>
        </template>
    </div>
</template>
