import { env } from './env';

type QueryValue = string | number | undefined | null;

function buildUrl(resource: string, params: Record<string, QueryValue>): URL {
    if (!env.POSTGREST_URL) {
        throw new Error('POSTGREST_URL belum dikonfigurasi.');
    }

    const url = new URL(env.POSTGREST_URL);
    const base = url.pathname.replace(/\/+$/, '');
    url.pathname = `${base}/${resource}`;

    for (const [key, value] of Object.entries(params)) {
        if (value !== undefined && value !== null && value !== '') {
            url.searchParams.set(key, String(value));
        }
    }

    return url;
}

export type PostgrestMethod = 'POST' | 'PATCH' | 'DELETE';

const UNREACHABLE_MESSAGE =
    'Layanan data (PostgREST) tidak dapat dihubungi. Pastikan service PostgREST berjalan.';

/**
 * Bungkus `fetch` agar error jaringan (service mati/timeout) tidak muncul di
 * UI sebagai "fetch failed" tanpa petunjuk. Detail asli tetap masuk log server.
 */
async function postgrestFetch(url: URL, init: RequestInit, resource: string): Promise<Response> {
    try {
        return await fetch(url, init);
    } catch (error) {
        console.error(`PostgREST tidak dapat dihubungi (${resource}).`, error);
        throw new Error(UNREACHABLE_MESSAGE, { cause: error });
    }
}

export class PostgrestError extends Error {
    constructor(
        message: string,
        readonly status: number,
    ) {
        super(message);
        this.name = 'PostgrestError';
    }
}

function failureMessage(status: number, payload: unknown): string {
    const record = payload as { message?: unknown } | null;

    return record && typeof record.message === 'string' && record.message.trim()
        ? record.message
        : `PostgREST menolak permintaan (${status}).`;
}

/**
 * Mutasi PostgREST dari server memakai JWT pengguna supaya RLS tetap berlaku.
 * `prefer` dipakai untuk upsert (`resolution=merge-duplicates`) dan membaca
 * baris hasil (`return=representation`).
 */
export async function postgrestSend<T>(
    token: string,
    resource: string,
    method: PostgrestMethod,
    body: unknown,
    params: Record<string, QueryValue> = {},
    prefer = 'return=representation',
    profile: 'api' | 'public' = 'public',
): Promise<T[]> {
    const response = await postgrestFetch(
        buildUrl(resource, params),
        {
            method,
            headers: {
                authorization: `Bearer ${token}`,
                accept: 'application/json',
                'content-type': 'application/json',
                prefer,
                'content-profile': profile,
            },
            body: JSON.stringify(body),
            signal: AbortSignal.timeout(15_000),
        },
        resource,
    );

    if (!response.ok) {
        const payload = await response.json().catch(() => null);
        throw new PostgrestError(failureMessage(response.status, payload), response.status);
    }

    // `Prefer: return=minimal` menjawab 201/204 tanpa body, jadi jangan
    // memanggil response.json() tanpa memeriksa isi respons.
    const text = await response.text();

    if (!text) {
        return [];
    }

    try {
        return JSON.parse(text) as T[];
    } catch {
        throw new PostgrestError('Respons PostgREST tidak dapat dibaca.', 502);
    }
}

export async function postgrestRpc<T>(
    token: string,
    name: string,
    args: Record<string, unknown> = {},
): Promise<T> {
    const response = await postgrestFetch(
        buildUrl(`rpc/${name}`, {}),
        {
            method: 'POST',
            headers: {
                authorization: `Bearer ${token}`,
                accept: 'application/json',
                'content-type': 'application/json',
                'content-profile': 'public',
            },
            body: JSON.stringify(args),
            signal: AbortSignal.timeout(15_000),
        },
        `rpc/${name}`,
    );

    if (!response.ok) {
        const payload = await response.json().catch(() => null);
        throw new PostgrestError(failureMessage(response.status, payload), response.status);
    }

    // Fungsi SQL yang mengembalikan satu nilai skalar dikirim sebagai nilai
    // JSON langsung (mis. `"E-00001"`), bukan array satu baris.
    return (await response.json()) as T;
}

/**
 * Halaman tabel dengan total dari `Content-Range` (satu request). Dipakai
 * halaman master data; JWT pengguna tetap ditempel sehingga RLS berlaku.
 */
export async function postgrestPage<T>(
    token: string,
    resource: string,
    params: Record<string, QueryValue>,
    range: [number, number],
    profile: 'api' | 'public' = 'public',
): Promise<{ rows: T[]; total: number }> {
    const response = await postgrestFetch(
        buildUrl(resource, params),
        {
            headers: {
                authorization: `Bearer ${token}`,
                accept: 'application/json',
                'accept-profile': profile,
                range: `${range[0]}-${range[1]}`,
                prefer: 'count=exact',
            },
            signal: AbortSignal.timeout(15_000),
        },
        resource,
    );

    if (!response.ok) {
        const payload = await response.json().catch(() => null);
        throw new PostgrestError(failureMessage(response.status, payload), response.status);
    }

    const rows = (await response.json()) as T[];
    const match = /^(\d+)-(\d+)\/(\d+|\*)$/.exec(response.headers.get('content-range') ?? '');
    const total = match && match[3] !== '*' ? Number(match[3]) : rows.length;

    return { rows, total };
}

export async function postgrestRows<T>(
    token: string,
    resource: string,
    params: Record<string, QueryValue> = {},
    profile: 'api' | 'public' = 'api',
): Promise<T[]> {
    const response = await postgrestFetch(
        buildUrl(resource, params),
        {
            headers: {
                authorization: `Bearer ${token}`,
                accept: 'application/json',
                'accept-profile': profile,
            },
            signal: AbortSignal.timeout(15_000),
        },
        resource,
    );

    if (!response.ok) {
        const payload = (await response.json().catch(() => null)) as { message?: string } | null;
        throw new Error(payload?.message ?? `Query ${resource} gagal (${response.status}).`);
    }

    return (await response.json()) as T[];
}

export async function postgrestRow<T>(
    token: string,
    resource: string,
    params: Record<string, QueryValue> = {},
    profile: 'api' | 'public' = 'api',
): Promise<T | null> {
    const rows = await postgrestRows<T>(token, resource, { ...params, limit: 1 }, profile);

    return rows[0] ?? null;
}

/** Daftar id untuk filter `in.(...)`; kosong menghasilkan `null`. */
export function inFilter(ids: number[]): string | null {
    return ids.length > 0 ? `in.(${ids.join(',')})` : null;
}

/** Daftar teks (kode peserta) untuk filter `in.(...)`. */
export function inTextFilter(values: string[]): string | null {
    if (values.length === 0) return null;

    return `in.(${values.map((value) => `"${value.replace(/"/g, '\\"')}"`).join(',')})`;
}
