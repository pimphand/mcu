type Bucket = {
    count: number;
    resetAt: number;
};

const buckets = new Map<string, Bucket>();

export type RateLimitResult = {
    ok: boolean;
    remaining: number;
    retryAfterSeconds: number;
};

/**
 * In-memory limiter is suitable for a single instance and local development.
 * Production should use Redis or nginx limit_req so limits are shared and
 * survive restarts.
 */
export function rateLimit(key: string, limit: number, windowMs: number): RateLimitResult {
    const now = Date.now();
    const current = buckets.get(key);

    if (!current || current.resetAt <= now) {
        buckets.set(key, { count: 1, resetAt: now + windowMs });
        return { ok: true, remaining: limit - 1, retryAfterSeconds: 0 };
    }

    current.count += 1;
    const remaining = Math.max(0, limit - current.count);
    const ok = current.count <= limit;

    return {
        ok,
        remaining,
        retryAfterSeconds: ok ? 0 : Math.ceil((current.resetAt - now) / 1000),
    };
}

export function resetRateLimit(key?: string): void {
    if (key) {
        buckets.delete(key);
        return;
    }

    buckets.clear();
}
