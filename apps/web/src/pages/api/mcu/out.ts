import type { APIRoute } from 'astro';
import {
    readSessionToken,
    setSessionCookie,
    signSession,
    verifySession,
} from '../../../lib/auth/session';
import { problem } from '../../../lib/http';

export const prerender = false;

export const POST: APIRoute = async ({ cookies }) => {
    const token = readSessionToken(cookies);

    if (!token) {
        return problem('unauthorized', 'Sesi tidak ditemukan.');
    }

    const session = await verifySession(token);

    if (!session) {
        return problem('unauthorized', 'Sesi tidak valid atau sudah kedaluwarsa.');
    }

    const newToken = await signSession({
        app_role: session.app_role,
        user_id: session.user_id,
        role_id: session.role_id,
        level: session.level,
        client_id: session.client_id,
        contract_id: null,
        token_version: session.token_version,
    });

    setSessionCookie(cookies, newToken);

    return Response.json({ context: { contract_id: null } });
};
