/**
 * Helper kecil untuk komponen browser yang memanggil proxy BFF
 * `/api/db/*`. Jangan memakai `POSTGREST_URL` langsung dari browser: token
 * sesi ada di cookie httpOnly dan hanya BFF yang boleh menempelkannya.
 */

export class ApiError extends Error {
    constructor(
        message: string,
        readonly status: number,
    ) {
        super(message);
        this.name = 'ApiError';
    }
}

export type ListResult<T> = {
    rows: T[];
    total: number;
};

/** Hasil mutasi master data (create/update/delete/toggle). */
export type MasterMutationResult = {
    message: string;
    code?: string | null;
};

type QueryValue = string | number | boolean | undefined | null;

const FALLBACK_MESSAGES: Record<number, string> = {
    401: 'Sesi tidak valid. Silakan login ulang.',
    403: 'Anda tidak berwenang melakukan aksi ini.',
    404: 'Data tidak ditemukan.',
    503: 'PostgREST belum dikonfigurasi. Jalankan stack database terlebih dahulu.',
};

function parseTotal(contentRange: string | null, fallback: number): number {
    const match = contentRange?.match(/^(\d+)-(\d+)\/(\d+|\*)$/);

    if (!match || match[3] === '*') {
        return fallback;
    }

    return Number(match[3]);
}

function messageFrom(payload: unknown, status: number): string {
    const record = payload as { message?: unknown; hint?: unknown } | null;

    if (record && typeof record.message === 'string' && record.message.trim()) {
        return record.message;
    }

    return FALLBACK_MESSAGES[status] ?? 'Permintaan data gagal.';
}

async function toError(response: Response): Promise<ApiError> {
    const payload = await response.json().catch(() => null);

    return new ApiError(messageFrom(payload, response.status), response.status);
}

function queryString(params: Record<string, QueryValue>): string {
    const search = new URLSearchParams();

    for (const [key, value] of Object.entries(params)) {
        if (value !== undefined && value !== null && value !== '') {
            search.set(key, String(value));
        }
    }

    return search.toString();
}

/**
 * Daftar baris master data lewat endpoint JSON `/api/master/<kind>`. Dipakai
 * tabel SPA untuk memuat ulang data setelah simpan tanpa reload halaman.
 */
export async function fetchMasterList<T>(
    kind: string,
    params: { q?: string; sort?: string; page?: number; perPage?: number } = {},
): Promise<ListResult<T>> {
    const query = queryString({
        q: params.q,
        sort: params.sort,
        page: params.page,
        per_page: params.perPage,
    });
    const response = await fetch(`/api/master/${kind}?${query}`, {
        headers: { accept: 'application/json' },
    });

    if (!response.ok) {
        throw await toError(response);
    }

    return (await response.json()) as ListResult<T>;
}

/**
 * Kirim form master (create/update/delete/toggle). Server menjawab JSON saat
 * klien meminta `application/json`, jadi pengguna tidak dipindahkan halaman.
 */
export async function submitMasterForm(
    kind: string,
    form: FormData,
): Promise<MasterMutationResult> {
    const response = await fetch(`/api/master/${kind}`, {
        method: 'POST',
        headers: { accept: 'application/json' },
        body: form,
    });

    if (!response.ok) {
        throw await toError(response);
    }

    return (await response.json()) as MasterMutationResult;
}

/**
 * GET satu resource view/tabel lewat proxy. `range` memakai header Range agar
 * pagination server-side dan `Content-Range` bisa dipakai menghitung total.
 */
export async function fetchRows<T>(
    resource: string,
    params: Record<string, QueryValue> = {},
    options: { range?: [number, number]; signal?: AbortSignal } = {},
): Promise<ListResult<T>> {
    const headers = new Headers({ accept: 'application/json' });
    const query: Record<string, QueryValue> = { ...params };

    if (options.range) {
        headers.set('range', `${options.range[0]}-${options.range[1]}`);
        headers.set('prefer', 'count=exact');

        // PostgREST menolak (416) bila `limit` dikirim bersama header Range untuk
        // offset > 0; panjang halaman sudah ditentukan header Range.
        delete query.limit;
    }

    const response = await fetch(`/api/db/${resource}?${queryString(query)}`, {
        headers,
        signal: options.signal,
    });

    if (!response.ok) {
        throw await toError(response);
    }

    const rows = (await response.json()) as T[];

    return { rows, total: parseTotal(response.headers.get('content-range'), rows.length) };
}

/**
 * Simpan satu baris hasil pemeriksaan. `participant_id` unik per tabel, jadi
 * POST + `resolution=merge-duplicates` berlaku sebagai update-or-create
 * (setara `updateOrCreate` legacy) dan mengembalikan baris terbaru.
 */
export async function upsertRow<T>(
    resource: string,
    values: Record<string, unknown>,
    conflictColumn = 'participant_id',
): Promise<T | null> {
    const response = await fetch(`/api/db/${resource}?on_conflict=${conflictColumn}`, {
        method: 'POST',
        headers: {
            accept: 'application/json',
            'content-type': 'application/json',
            prefer: 'return=representation,resolution=merge-duplicates',
        },
        body: JSON.stringify(values),
    });

    if (!response.ok) {
        throw await toError(response);
    }

    const rows = (await response.json()) as T[];

    return rows[0] ?? null;
}

/**
 * Panggil RPC PostgREST. Hanya RPC di `ALLOWED_RPC` (server) yang diteruskan.
 */
export async function callRpc<T>(name: string, args: Record<string, unknown> = {}): Promise<T> {
    const response = await fetch(`/api/db/rpc/${name}`, {
        method: 'POST',
        headers: {
            accept: 'application/json',
            'content-type': 'application/json',
            prefer: 'return=representation',
        },
        body: JSON.stringify(args),
    });

    if (!response.ok) {
        throw await toError(response);
    }

    return (await response.json()) as T;
}
