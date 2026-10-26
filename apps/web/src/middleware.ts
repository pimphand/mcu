import { defineMiddleware } from 'astro:middleware';
import { clearSessionCookie, readSessionToken, verifySession } from './lib/auth/session';
import type { MenuRow } from './lib/nav';
import { env } from './lib/env';
import { applySecurityHeaders, isSameOrigin, isUnsafeMethod } from './lib/security';
import { postgrestRows } from './lib/server-postgrest';

const PROTECTED_PREFIX = '/app';

function secure(response: Response, https: boolean): Response {
    // Header pada `Response.redirect()` bersifat immutable, sehingga tidak bisa
    // ditambahi langsung. Salin ke Headers baru dan pertahankan Set-Cookie.
    const headers = new Headers(response.headers);

    for (const cookie of response.headers.getSetCookie()) {
        headers.append('set-cookie', cookie);
    }

    applySecurityHeaders(headers, { https });

    return new Response(response.body, {
        status: response.status,
        statusText: response.statusText,
        headers,
    });
}

export const onRequest = defineMiddleware(async (context, next) => {
    const https = context.url.protocol === 'https:';

    if (
        isUnsafeMethod(context.request.method) &&
        !isSameOrigin(context.request, context.url, env.PUBLIC_APP_URL)
    ) {
        return secure(
            new Response(JSON.stringify({ error: 'forbidden', message: 'Origin tidak valid.' }), {
                status: 403,
                headers: {
                    'content-type': 'application/json; charset=utf-8',
                    'cache-control': 'no-store',
                },
            }),
            https,
        );
    }

    const token = readSessionToken(context.cookies);
    const session = token ? await verifySession(token) : null;

    context.locals.user = session ?? undefined;

    if (token && !session) {
        clearSessionCookie(context.cookies);
    }

    const isProtected =
        context.url.pathname === PROTECTED_PREFIX ||
        context.url.pathname.startsWith(`${PROTECTED_PREFIX}/`);

    if (isProtected && !session) {
        const nextPath = `${context.url.pathname}${context.url.search}`;
        return secure(context.redirect(`/login?next=${encodeURIComponent(nextPath)}`), https);
    }

    // Menu + permission dimuat sekali per request halaman /app supaya sidebar
    // dan tombol aksi memakai sumber yang sama (`api.v_my_menus`).
    if (isProtected && session && token) {
        try {
            context.locals.menus = await postgrestRows<MenuRow>(token, 'v_my_menus', {
                select: '*',
                order: 'sort_order.asc,menu_id.asc',
            });
        } catch (error) {
            console.error('Menu sidebar gagal dimuat.', error);
            context.locals.menusError =
                error instanceof Error ? error.message : 'Menu sidebar gagal dimuat.';
        }
    }

    const response = await next();
    return secure(response, https);
});
