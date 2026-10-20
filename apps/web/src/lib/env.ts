import { z } from 'zod';

const EnvSchema = z.object({
    NODE_ENV: z.enum(['development', 'test', 'production']).default('development'),
    PUBLIC_APP_URL: z.url().default('http://localhost:4321'),
    POSTGREST_URL: z.url().optional(),
    DATABASE_URL: z.string().min(1).optional(),
    AUTH_DATABASE_URL: z.string().min(1).optional(),
    JWT_SECRET: z.string().min(32).optional(),
    /** Direktori berkas tanda tangan karyawan (`.../public/ttd`). */
    SIGNATURE_DIR: z.string().min(1).optional(),
    JWT_TTL_SECONDS: z.coerce.number().int().positive().max(86_400).default(28_800),
});

/**
 * Astro/Vite memuat `.env` ke `import.meta.env`, sedangkan server hasil build
 * (`npm start`) memakai `process.env` dari `--env-file`. Keduanya harus dibaca
 * supaya `astro dev`/`preview` dan server produksi punya konfigurasi yang sama;
 * `process.env` diutamakan agar override saat runtime tetap menang.
 */
const viteEnv = import.meta.env as unknown as Record<string, string | undefined>;

function pick(
    name:
        | 'PUBLIC_APP_URL'
        | 'POSTGREST_URL'
        | 'DATABASE_URL'
        | 'AUTH_DATABASE_URL'
        | 'JWT_SECRET'
        | 'JWT_TTL_SECONDS'
        | 'SIGNATURE_DIR',
): string | undefined {
    return process.env[name] ?? viteEnv[name];
}

const parsed = EnvSchema.safeParse({
    ...process.env,
    NODE_ENV: process.env.NODE_ENV ?? (import.meta.env.PROD ? 'production' : 'development'),
    PUBLIC_APP_URL: pick('PUBLIC_APP_URL'),
    POSTGREST_URL: pick('POSTGREST_URL'),
    DATABASE_URL: pick('DATABASE_URL'),
    AUTH_DATABASE_URL: pick('AUTH_DATABASE_URL'),
    JWT_SECRET: pick('JWT_SECRET'),
    JWT_TTL_SECONDS: pick('JWT_TTL_SECONDS'),
    SIGNATURE_DIR: pick('SIGNATURE_DIR'),
});

if (!parsed.success) {
    const detail = parsed.error.issues.map((issue) => `${issue.path.join('.')}: ${issue.message}`);
    throw new Error(`Konfigurasi environment tidak valid:\n${detail.join('\n')}`);
}

export const env = parsed.data;

export const sessionCookieName = 'mcu_session';

export const isAuthConfigured = Boolean(
    env.JWT_SECRET && (env.AUTH_DATABASE_URL || env.DATABASE_URL),
);

export const isPostgrestConfigured = Boolean(env.POSTGREST_URL);

export function isProduction(): boolean {
    return env.NODE_ENV === 'production';
}

export function cookieIsSecure(): boolean {
    return env.PUBLIC_APP_URL.startsWith('https://');
}
