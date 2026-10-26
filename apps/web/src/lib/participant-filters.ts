/**
 * Filter daftar peserta MCU.
 *
 * Field dan semantik pencarian mengikuti `ParticipantService::paginate` legacy
 * (`resources/views/pages/participant/filter.blade.php`): pencarian bebas
 * mencocokkan kode **atau** nama, NIK cocok persis, nama memakai LIKE, gender
 * `L`/`P`, tanggal lahir persis, serta switch plan U/A/E/S/R, lab khusus, dan
 * paket A–F. Semua nilai di sini dipakai untuk dua hal sekaligus: query
 * PostgREST ke `api.v_participant_list` dan query string URL halaman, supaya
 * filter bisa dibagikan/di-refresh tanpa kehilangan konteks (legacy memakai
 * `withQueryString()`).
 *
 * Scope client/kontrak **tidak** ada di sini: BFF menempelkannya dari klaim
 * sesi, jadi browser tidak bisa melebarkan daftar ke kontrak lain.
 */

export const PLAN_KEYS = ['u', 'a', 'e', 's', 'r'] as const;
export const PACKET_KEYS = ['a', 'b', 'c', 'd', 'e', 'f'] as const;
export const GENDERS = ['L', 'P'] as const;

export type PlanKey = (typeof PLAN_KEYS)[number];
export type PacketKey = (typeof PACKET_KEYS)[number];
export type Gender = (typeof GENDERS)[number];

/**
 * Kolom `v_participant_list` yang dipakai daftar peserta. Diletakkan di sini
 * supaya test paritas bisa mencocokkannya dengan definisi view di `db/`.
 */
export const PARTICIPANT_LIST_COLUMNS = [
    'id',
    'code',
    'nik',
    'name',
    'gender',
    'birthday',
    'divisi_name',
    'department_name',
    'contract_name',
    'packet_name',
    'plan_u',
    'plan_a',
    'plan_e',
    'plan_s',
    'plan_r',
    'lab_special',
    'register_number',
    'register_date',
] as const;

export const PARTICIPANT_LIST_SELECT = PARTICIPANT_LIST_COLUMNS.join(',');

export type ParticipantFilters = {
    /** Pencarian bebas: kode MCU atau nama. */
    search: string;
    nik: string;
    name: string;
    gender: '' | Gender;
    /** `YYYY-MM-DD`, cocok persis seperti legacy. */
    birthday: string;
    plans: Record<PlanKey, boolean>;
    packets: Record<PacketKey, boolean>;
    labSpecial: boolean;
};

const DATE_PATTERN = /^\d{4}-\d{2}-\d{2}$/;
const MAX_TERM = 100;

/** Karakter yang bisa memecah sintaks `or=(...)`/logical tree PostgREST. */
const UNPARSABLE = /[,()"\\]/g;

export function emptyParticipantFilters(): ParticipantFilters {
    return {
        search: '',
        nik: '',
        name: '',
        gender: '',
        birthday: '',
        plans: { u: false, a: false, e: false, s: false, r: false },
        packets: { a: false, b: false, c: false, d: false, e: false, f: false },
        labSpecial: false,
    };
}

function term(params: URLSearchParams, key: string): string {
    return (params.get(key) ?? '').trim().slice(0, MAX_TERM);
}

function flag(params: URLSearchParams, key: string): boolean {
    const value = params.get(key);

    return value === '1' || value === 'true';
}

function gender(params: URLSearchParams): '' | Gender {
    const value = params.get('gender');

    return value === 'L' || value === 'P' ? value : '';
}

/**
 * Baca filter dari query string. Nilai asing/rusak diabaikan (bukan error)
 * supaya URL lama atau hasil edit manual tetap membuka daftar, bukan 500.
 */
export function parseParticipantFilters(params: URLSearchParams): ParticipantFilters {
    const filters = emptyParticipantFilters();

    filters.search = term(params, 'search');
    filters.nik = term(params, 'nik');
    filters.name = term(params, 'name');
    filters.gender = gender(params);

    const birthday = term(params, 'birthday');
    filters.birthday = DATE_PATTERN.test(birthday) ? birthday : '';

    for (const plan of PLAN_KEYS) {
        filters.plans[plan] = flag(params, `plan_${plan}`);
    }

    for (const packet of PACKET_KEYS) {
        filters.packets[packet] = flag(params, `packet_${packet}`);
    }

    filters.labSpecial = flag(params, 'lab_special');

    return filters;
}

/** Jumlah filter aktif; dipakai untuk badge tombol Filter dan empty state. */
export function activeParticipantFilters(filters: ParticipantFilters): number {
    let count = 0;

    if (filters.search) count += 1;
    if (filters.nik) count += 1;
    if (filters.name) count += 1;
    if (filters.gender) count += 1;
    if (filters.birthday) count += 1;
    if (filters.labSpecial) count += 1;
    count += PLAN_KEYS.filter((plan) => filters.plans[plan]).length;
    count += PACKET_KEYS.filter((packet) => filters.packets[packet]).length;

    return count;
}

/**
 * Query string halaman (untuk `history.replaceState`). Filter kosong dan
 * halaman pertama tidak ditulis supaya URL default tetap bersih.
 */
export function participantFilterQuery(filters: ParticipantFilters, page = 0): URLSearchParams {
    const params = new URLSearchParams();

    if (filters.search) params.set('search', filters.search);
    if (filters.nik) params.set('nik', filters.nik);
    if (filters.name) params.set('name', filters.name);
    if (filters.gender) params.set('gender', filters.gender);
    if (filters.birthday) params.set('birthday', filters.birthday);
    if (filters.labSpecial) params.set('lab_special', '1');

    for (const plan of PLAN_KEYS) {
        if (filters.plans[plan]) params.set(`plan_${plan}`, '1');
    }

    for (const packet of PACKET_KEYS) {
        if (filters.packets[packet]) params.set(`packet_${packet}`, '1');
    }

    if (page > 0) params.set('page', String(page + 1));

    return params;
}

/**
 * Buang karakter yang tidak bisa diparse PostgREST di dalam logical tree.
 * Nilai tetap dicari apa adanya (spasi dirapatkan); ini hanya mencegah 400,
 * bukan sanitasi data.
 */
export function participantSearchTerm(value: string): string {
    return value.replace(UNPARSABLE, ' ').replace(/\s+/g, ' ').trim();
}

/**
 * Parameter query PostgREST untuk `v_participant_list`. Mengembalikan record
 * (bukan string) agar pemanggil bisa menambahkan `select`/`order` sendiri.
 */
export function participantPostgrestQuery(filters: ParticipantFilters): Record<string, string> {
    const params: Record<string, string> = {};

    const search = participantSearchTerm(filters.search);

    if (search) {
        params.or = `(name.ilike.*${search}*,code.ilike.*${search}*)`;
    }

    if (filters.nik) {
        params.nik = `eq.${filters.nik}`;
    }

    if (filters.name) {
        params.name = `ilike.*${participantSearchTerm(filters.name)}*`;
    }

    if (filters.gender) {
        params.gender = `eq.${filters.gender}`;
    }

    if (filters.birthday) {
        params.birthday = `eq.${filters.birthday}`;
    }

    if (filters.labSpecial) {
        params.lab_special = 'is.true';
    }

    for (const plan of PLAN_KEYS) {
        if (filters.plans[plan]) params[`plan_${plan}`] = 'is.true';
    }

    for (const packet of PACKET_KEYS) {
        if (filters.packets[packet]) params[`packet_${packet}`] = 'is.true';
    }

    return params;
}
