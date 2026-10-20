import type { APIRoute } from 'astro';
import { clearSessionCookie } from '../../../lib/auth/session';
import { methodNotAllowed } from '../../../lib/http';

export const prerender = false;

export const POST: APIRoute = async ({ cookies }) => {
    clearSessionCookie(cookies);

    return new Response(null, {
        status: 204,
        headers: { 'cache-control': 'no-store' },
    });
};

export const ALL: APIRoute = async () => methodNotAllowed(['POST']);
