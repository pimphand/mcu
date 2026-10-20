const UNSAFE_METHODS = new Set(['POST', 'PUT', 'PATCH', 'DELETE']);

export function isUnsafeMethod(method: string): boolean {
    return UNSAFE_METHODS.has(method.toUpperCase());
}

/**
 * Astro's checkOrigin protects form-encoded requests. API JSON requests need
 * an explicit check that compares Origin (or Sec-Fetch-Site) with the request
 * origin. Missing metadata is rejected instead of assumed safe.
 */
export function isSameOrigin(request: Request, requestUrl: URL): boolean {
    const origin = request.headers.get('origin');

    if (origin) {
        return origin === requestUrl.origin;
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
