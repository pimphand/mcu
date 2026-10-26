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
            // Koneksi pertama ke PostgreSQL terkelola (TLS + pooler) bisa lewat
            // 2 detik; batas ini harus di atas itu agar status tidak salah
            // "unreachable" saat server baru dinyalakan.
            new Promise((_resolve, reject) =>
                setTimeout(() => reject(new Error('timeout')), 5_000),
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
            // `GET /` PostgREST membangun OpenAPI dan pada stack ini butuh
            // ~0,9 detik; batas 2 detik membuat health check sesekali 503.
            signal: AbortSignal.timeout(5_000),
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
