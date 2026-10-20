const STATUS_BY_CODE = {
    bad_request: 400,
    unauthorized: 401,
    forbidden: 403,
    not_found: 404,
    method_not_allowed: 405,
    conflict: 409,
    payload_too_large: 413,
    too_many_requests: 429,
    not_configured: 503,
    bad_gateway: 502,
    internal: 500,
} as const;

export type ProblemCode = keyof typeof STATUS_BY_CODE;

export function json(data: unknown, init: ResponseInit = {}): Response {
    const headers = new Headers(init.headers);
    headers.set('content-type', 'application/json; charset=utf-8');
    headers.set('cache-control', 'no-store');

    return new Response(JSON.stringify(data), {
        ...init,
        headers,
    });
}

export function problem(
    code: ProblemCode,
    message: string,
    details?: Record<string, unknown>,
): Response {
    return json(
        {
            error: code,
            message,
            ...(details ? { details } : {}),
        },
        { status: STATUS_BY_CODE[code] },
    );
}

export function methodNotAllowed(allowed: string[]): Response {
    return json(
        { error: 'method_not_allowed', message: 'Method tidak diizinkan.' },
        {
            status: 405,
            headers: { allow: allowed.join(', ') },
        },
    );
}
