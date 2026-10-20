import type { APIRoute } from 'astro';
import { readSessionToken, verifySession } from '../../../lib/auth/session';
import { env } from '../../../lib/env';
import { json, problem } from '../../../lib/http';
import {
    applySessionScope,
    buildPostgrestUrl,
    parseResourcePath,
    profileForResource,
} from '../../../lib/postgrest';

export const prerender = false;

const ALLOWED_METHODS = new Set(['GET', 'HEAD', 'POST', 'PATCH', 'DELETE']);
const MAX_BODY_BYTES = 2_000_000;
const FORWARDED_REQUEST_HEADERS = ['range', 'prefer', 'content-type'];
const FORWARDED_RESPONSE_HEADERS = [
    'content-type',
    'content-range',
    'content-location',
    'preference-applied',
    'vary',
];

export const ALL: APIRoute = async ({ request, params, cookies, url }) => {
    if (!ALLOWED_METHODS.has(request.method)) {
        return json(
            { error: 'method_not_allowed', message: 'Method tidak diizinkan.' },
            { status: 405, headers: { allow: [...ALLOWED_METHODS].join(', ') } },
        );
    }

    const token = readSessionToken(cookies);

    if (!token) {
        return problem('unauthorized', 'Sesi tidak ditemukan.');
    }

    const session = await verifySession(token);

    if (!session) {
        return problem('unauthorized', 'Sesi tidak valid atau sudah kedaluwarsa.');
    }

    const parts = parseResourcePath(params.path);

    if (!parts) {
        return problem('not_found', 'Resource tidak ditemukan.');
    }

    if (!env.POSTGREST_URL) {
        return problem('not_configured', 'PostgREST belum dikonfigurasi.');
    }

    const headers = new Headers({
        authorization: `Bearer ${token}`,
        accept: 'application/json',
    });

    for (const name of FORWARDED_REQUEST_HEADERS) {
        const value = request.headers.get(name);

        if (value) {
            headers.set(name, value);
        }
    }

    // View ada di schema `api`, tabel dan RPC di `public`. Profile ditentukan
    // server (bukan dari browser) agar schema yang diekspos tetap terkendali.
    const profile = profileForResource(parts);
    headers.set('accept-profile', profile);

    if (request.method !== 'GET' && request.method !== 'HEAD') {
        headers.set('content-profile', profile);
    }

    let body: ArrayBuffer | undefined;

    if (request.method !== 'GET' && request.method !== 'HEAD') {
        body = await request.arrayBuffer();

        if (body.byteLength > MAX_BODY_BYTES) {
            return problem('payload_too_large', 'Payload melebihi batas 2 MB.');
        }
    }

    try {
        const upstreamUrl = buildPostgrestUrl(parts, url.search);
        applySessionScope(parts, upstreamUrl, session);

        const upstream = await fetch(upstreamUrl, {
            method: request.method,
            headers,
            body,
            redirect: 'manual',
            signal: AbortSignal.timeout(15_000),
        });

        const responseHeaders = new Headers({ 'cache-control': 'no-store' });

        for (const name of FORWARDED_RESPONSE_HEADERS) {
            const value = upstream.headers.get(name);

            if (value) {
                responseHeaders.set(name, value);
            }
        }

        return new Response(upstream.body, {
            status: upstream.status,
            headers: responseHeaders,
        });
    } catch (error) {
        console.error('Proxy PostgREST gagal.', error);
        return problem('bad_gateway', 'Layanan data sedang tidak tersedia.');
    }
};
