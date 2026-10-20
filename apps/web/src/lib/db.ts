import pg from 'pg';
import { env } from './env';
import { normalizePgConnection } from './pg';

const { Pool } = pg;

// Kolom id/role_id/client_id bertipe bigint dan driver pg mengembalikan int8
// sebagai string. Klaim JWT lalu menjadi string dan `verifySession` menolaknya
// (semua request jadi 401), jadi int8 dikembalikan sebagai angka — semua id di
// aplikasi ini jauh di bawah Number.MAX_SAFE_INTEGER.
pg.types.setTypeParser(pg.types.builtins.INT8, (value: string) => Number(value));

let pool: pg.Pool | undefined;
let servicePool: pg.Pool | undefined;

function connectionOptions(): { connectionString: string; ssl: pg.PoolConfig['ssl'] } {
    const url = env.AUTH_DATABASE_URL ?? env.DATABASE_URL;

    if (!url) {
        throw new Error('AUTH_DATABASE_URL atau DATABASE_URL belum dikonfigurasi.');
    }

    // `sslmode` di URL diatur eksplisit di sini; lihat lib/pg.ts.
    return normalizePgConnection(url);
}

function getPool(): pg.Pool {
    if (!pool) {
        pool = new Pool({
            ...connectionOptions(),
            max: 5,
            idleTimeoutMillis: 30_000,
            connectionTimeoutMillis: 5_000,
        });
    }

    return pool;
}

/**
 * Koneksi service (role `mcu_service`, bypass RLS) hanya untuk operasi server
 * yang tidak bisa lewat PostgREST — saat ini CRUD `users` (tabel di-revoke)
 * dan pengecekan relasi sebelum hapus client.
 */
function getServicePool(): pg.Pool {
    if (!servicePool) {
        if (!env.DATABASE_URL) {
            throw new Error('DATABASE_URL belum dikonfigurasi untuk operasi service.');
        }

        servicePool = new Pool({
            ...normalizePgConnection(env.DATABASE_URL),
            max: 5,
            idleTimeoutMillis: 30_000,
            connectionTimeoutMillis: 5_000,
        });
    }

    return servicePool;
}

export async function queryServiceRows<T extends pg.QueryResultRow>(
    text: string,
    values: unknown[] = [],
): Promise<T[]> {
    const result = await getServicePool().query<T>(text, values);

    return result.rows;
}

export async function queryAuth<T extends pg.QueryResultRow>(
    text: string,
    values: unknown[] = [],
): Promise<T | null> {
    const result = await getPool().query<T>(text, values);

    return result.rows[0] ?? null;
}

export async function queryRows<T extends pg.QueryResultRow>(
    text: string,
    values: unknown[] = [],
): Promise<T[]> {
    const result = await getPool().query<T>(text, values);

    return result.rows;
}

export async function closeAuthPool(): Promise<void> {
    if (pool) {
        await pool.end();
        pool = undefined;
    }
}
