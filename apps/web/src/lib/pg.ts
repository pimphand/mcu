import { readFileSync } from 'node:fs';
import type { ConnectionOptions } from 'node:tls';

type SslOption = boolean | ConnectionOptions | undefined;

/**
 * pg-connection-string memperlakukan `sslmode=require` sebagai `verify-full`,
 * sehingga koneksi ke PostgreSQL terkelola (mis. Supabase) gagal dengan pesan
 * "self-signed certificate in certificate chain". Karena itu `sslmode` dilepas
 * dari URL dan TLS diatur eksplisit:
 *
 * - `disable`/`allow`              -> tanpa TLS
 * - `prefer`/`require`/`no-verify` -> terenkripsi tanpa verifikasi CA (semantik libpq)
 * - `verify-ca`/`verify-full`      -> verifikasi penuh, `PGSSLROOTCERT` opsional
 */
export function sslOptionsForMode(mode: string | undefined): SslOption {
    if (!mode) return undefined;

    if (mode === 'disable' || mode === 'allow') return false;

    if (mode === 'prefer' || mode === 'require' || mode === 'no-verify') {
        return { rejectUnauthorized: false };
    }

    if (mode === 'verify-ca' || mode === 'verify-full') {
        const caPath = process.env.PGSSLROOTCERT;

        return {
            rejectUnauthorized: true,
            ...(caPath ? { ca: readFileSync(caPath, 'utf8') } : {}),
        };
    }

    throw new Error(`sslmode tidak dikenal: ${mode}`);
}

export function normalizePgConnection(rawUrl: string): {
    connectionString: string;
    ssl: SslOption;
} {
    const url = new URL(rawUrl);
    const mode = url.searchParams.get('sslmode') ?? process.env.PGSSLMODE ?? '';
    url.searchParams.delete('sslmode');

    return { connectionString: url.toString(), ssl: sslOptionsForMode(mode) };
}
