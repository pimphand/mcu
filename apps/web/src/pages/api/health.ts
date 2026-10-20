import type { APIRoute } from 'astro';
import { queryAuth } from '../../lib/db';
import { env, isAuthConfigured, isPostgrestConfigured } from '../../lib/env';
import { json } from '../../lib/http';

export const prerender = false;

type DependencyStatus = 'ok' | 'unreachable' | 'not_configured';

/**
 * Cek koneksi database autentikasi, bukan hanya konfigurasinya. Tanpa ini,
 * database yang tidak bisa dihubungi (mis. host Supabase hanya punya record
 * AAAA sementara mesin tidak punya IPv6) baru terlihat saat login gagal.
 */
async function checkAuth(): Promise<DependencyStatus> {
    if (!isAuthConfigured) {
        return 'not_configured';
    }

    try {
        await Promise.race([
            queryAuth('select 1 as ok'),
            // Pool pg menunggu 5 detik; health check tidak boleh selama itu.
            new Promise((_resolve, reject) =>
                setTimeout(() => reject(new Error('timeout')), 2_500),
            ),
        ]);

        return 'ok';
    } catch {
        return 'unreachable';
    }
}

async function checkPostgrest(): Promise<DependencyStatus> {
    if (!isPostgrestConfigured || !env.POSTGREST_URL) {
        return 'not_configured';
    }

    try {
        const response = await fetch(env.POSTGREST_URL, {
            method: 'GET',
            signal: AbortSignal.timeout(2_000),
            redirect: 'manual',
        });

        return response.ok ? 'ok' : 'unreachable';
    } catch {
        return 'unreachable';
    }
}

export const GET: APIRoute = async () => {
    const [postgrest, auth] = await Promise.all([checkPostgrest(), checkAuth()]);
    const healthy = postgrest !== 'unreachable' && auth !== 'unreachable';

    return json(
        {
            ok: healthy,
            service: 'mcu-web',
            version: '0.1.0',
            checks: {
                app: 'ok',
                postgrest,
                auth,
            },
            timestamp: new Date().toISOString(),
        },
        { status: healthy ? 200 : 503 },
    );
};
