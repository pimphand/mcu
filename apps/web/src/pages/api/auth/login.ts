import type { APIRoute } from 'astro';
import { compare } from 'bcryptjs';
import { z } from 'zod';
import { appRoleFromLevel, setSessionCookie, signSession } from '../../../lib/auth/session';
import { queryAuth } from '../../../lib/db';
import { isAuthConfigured } from '../../../lib/env';
import { json, problem } from '../../../lib/http';
import { rateLimit } from '../../../lib/rate-limit';

export const prerender = false;

const LoginSchema = z.object({
    username: z.string().trim().min(1).max(100),
    password: z.string().min(1).max(200),
});

type UserRow = {
    id: number;
    name: string;
    username: string;
    email: string | null;
    password_hash: string;
    role_id: number;
    client_id: number | null;
    is_active: boolean;
    token_version: number;
    level: number;
};

// Used only to keep the bcrypt comparison cost similar when the username is unknown.
const DUMMY_BCRYPT_HASH = '$2b$10$PZO1o5gLCG1w/31USGXRHevpN5jEH.g.AgZtAd03M3zCzyaYfsM72';

export const POST: APIRoute = async ({ request, cookies, clientAddress }) => {
    const contentLength = Number(request.headers.get('content-length') ?? 0);

    if (contentLength > 8_192) {
        return problem('payload_too_large', 'Payload terlalu besar.');
    }

    let body: unknown;

    try {
        body = await request.json();
    } catch {
        return problem('bad_request', 'Body harus berupa JSON yang valid.');
    }

    const parsed = LoginSchema.safeParse(body);

    if (!parsed.success) {
        return problem('bad_request', 'Username dan password wajib diisi.');
    }

    const { username, password } = parsed.data;
    const ip = clientAddress ?? 'unknown';
    const ipLimit = rateLimit(`login:ip:${ip}`, 20, 15 * 60_000);

    if (!ipLimit.ok) {
        return json(
            {
                error: 'too_many_requests',
                message: 'Terlalu banyak percobaan login. Coba lagi nanti.',
            },
            {
                status: 429,
                headers: { 'retry-after': String(ipLimit.retryAfterSeconds) },
            },
        );
    }

    const userLimit = rateLimit(`login:user:${ip}:${username.toLowerCase()}`, 8, 15 * 60_000);

    if (!userLimit.ok) {
        return json(
            {
                error: 'too_many_requests',
                message: 'Terlalu banyak percobaan login. Coba lagi nanti.',
            },
            {
                status: 429,
                headers: { 'retry-after': String(userLimit.retryAfterSeconds) },
            },
        );
    }

    if (!isAuthConfigured) {
        return problem(
            'not_configured',
            'Autentikasi belum dikonfigurasi. Isi AUTH_DATABASE_URL dan JWT_SECRET.',
        );
    }

    try {
        const user = await queryAuth<UserRow>(
            `select u.id,
              u.name,
              u.username,
              u.email,
              u.password_hash,
              u.role_id,
              u.client_id,
              u.is_active,
              u.token_version,
              r.level
         from public.users u
         join public.roles r on r.id = u.role_id
        where (lower(u.username) = lower($1) or lower(u.email) = lower($1))
          and u.deleted_at is null
        limit 1`,
            [username],
        );

        const valid = await compare(password, user?.password_hash ?? DUMMY_BCRYPT_HASH);

        if (!user || !user.is_active || !valid) {
            return problem('unauthorized', 'Username atau password salah.');
        }

        const appRole = appRoleFromLevel(user.level);
        const token = await signSession({
            app_role: appRole,
            user_id: user.id,
            role_id: user.role_id,
            level: user.level,
            client_id: user.client_id,
            contract_id: null,
            token_version: user.token_version,
        });

        setSessionCookie(cookies, token);

        return json({
            user: {
                id: user.id,
                name: user.name,
                username: user.username,
                app_role: appRole,
                level: user.level,
                client_id: user.client_id,
            },
        });
    } catch (error) {
        console.error('Login gagal karena error server.', error);
        return problem('bad_gateway', 'Layanan autentikasi sedang tidak tersedia.');
    }
};
