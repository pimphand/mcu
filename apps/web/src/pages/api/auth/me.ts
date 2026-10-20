import type { APIRoute } from 'astro';
import { clearSessionCookie, readSessionToken, verifySession } from '../../../lib/auth/session';
import { queryAuth } from '../../../lib/db';
import { problem } from '../../../lib/http';

export const prerender = false;

type UserState = {
    is_active: boolean;
    token_version: number;
};

export const GET: APIRoute = async ({ cookies }) => {
    const token = readSessionToken(cookies);

    if (!token) {
        return problem('unauthorized', 'Sesi tidak ditemukan.');
    }

    const session = await verifySession(token);

    if (!session) {
        clearSessionCookie(cookies);
        return problem('unauthorized', 'Sesi tidak valid atau sudah kedaluwarsa.');
    }

    try {
        const state = await queryAuth<UserState>(
            'select is_active, token_version from public.users where id = $1 and deleted_at is null',
            [session.user_id],
        );

        if (!state || !state.is_active || state.token_version !== session.token_version) {
            clearSessionCookie(cookies);
            return problem('unauthorized', 'Sesi sudah dicabut. Silakan login kembali.');
        }

        return Response.json({
            user: {
                id: session.user_id,
                app_role: session.app_role,
                level: session.level,
                client_id: session.client_id,
                contract_id: session.contract_id,
            },
        });
    } catch (error) {
        console.error('Gagal memeriksa status sesi.', error);
        return problem('bad_gateway', 'Layanan autentikasi sedang tidak tersedia.');
    }
};
