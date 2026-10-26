const UNSAFE_METHODS = new Set(['POST', 'PUT', 'PATCH', 'DELETE']);

export function isUnsafeMethod(method: string): boolean {
    return UNSAFE_METHODS.has(method.toUpperCase());
}

/** Origin dari URL konfigurasi; `null` bila nilainya bukan URL yang valid. */
function configuredOrigin(value: string | undefined): string | null {
    if (!value) return null;

    try {
        return new URL(value).origin;
    } catch {
        return null;
    }
}

/**
 * Origin menurut `Host` yang dipakai browser, bukan URL internal server.
 *
 * Server hasil build (`@astrojs/node`) membentuk `context.url` dari alamat
 * internal (mis. `http://localhost:4401`), sehingga membuka aplikasi lewat
 * `http://127.0.0.1:4401` — atau di belakang reverse proxy yang mengakhiri TLS —
 * membuat perbandingan dengan `context.url.origin` menolak request sah dengan
 * 403 "Origin tidak valid" (login tidak bisa dilakukan). `Host` diisi browser
 * sesuai alamat tujuan dan tidak bisa dipalsukan halaman lain, jadi memakai
 * header ini tetap mempertahankan proteksi CSRF.
 */
function requestHostOrigin(request: Request, fallbackProtocol: string): string | null {
    const forwardedHost = request.headers.get('x-forwarded-host');
    const host = (forwardedHost ?? request.headers.get('host'))?.split(',')[0]?.trim();

    if (!host) return null;

    const forwardedProto = request.headers.get('x-forwarded-proto')?.split(',')[0]?.trim();
    const protocol =
        forwardedProto === 'https' || forwardedProto === 'http' ? forwardedProto : fallbackProtocol;

    return `${protocol}://${host}`;
}

/**
 * Astro's checkOrigin protects form-encoded requests. API JSON requests need
 * an explicit check that compares Origin (or Sec-Fetch-Site) with the request
 * origin. Missing metadata is rejected instead of assumed safe.
 *
 * `allowedOrigin` adalah `PUBLIC_APP_URL`; dipakai sebagai cadangan ketika
 * `Host`/`X-Forwarded-Host` tidak diteruskan oleh proxy.
 */
export function isSameOrigin(request: Request, requestUrl: URL, allowedOrigin?: string): boolean {
    const origin = request.headers.get('origin');

    if (origin) {
        const candidates = new Set<string>([requestUrl.origin]);
        const hostOrigin = requestHostOrigin(request, requestUrl.protocol.replace(':', ''));

        if (hostOrigin) candidates.add(hostOrigin);

        const configured = configuredOrigin(allowedOrigin);

        if (configured) candidates.add(configured);

        return candidates.has(origin);
    }

    return request.headers.get('sec-fetch-site') === 'same-origin';
}

export function applySecurityHeaders(headers: Headers, options: { https: boolean }): void {
    headers.set('X-Content-Type-Options', 'nosniff');
    headers.set('Referrer-Policy', 'strict-origin-when-cross-origin');
    headers.set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');
    headers.set('X-Frame-Options', 'DENY');
    headers.set('Content-Security-Policy', "frame-ancestors 'none'");

    if (options.https) {
        headers.set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}
