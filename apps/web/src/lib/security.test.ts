import { describe, expect, it } from 'vitest';
import { isSameOrigin, isUnsafeMethod } from './security';

/**
 * Regresi login produksi: server hasil build membentuk `context.url` dari alamat
 * internal (`http://localhost:4401`), sedangkan browser membuka
 * `http://127.0.0.1:4401`. Perbandingan hanya dengan `context.url.origin`
 * menolak POST login yang sah dengan 403 "Origin tidak valid".
 */

function post(headers: Record<string, string>, url = 'http://internal.local/api/auth/login') {
    return new Request(url, { method: 'POST', headers });
}

describe('isSameOrigin', () => {
    it('menerima origin yang sama dengan URL request', () => {
        expect(
            isSameOrigin(
                post({ origin: 'http://localhost:4321' }),
                new URL('http://localhost:4321/api/auth/login'),
            ),
        ).toBe(true);
    });

    it('menerima origin yang sama dengan Host yang dipakai browser', () => {
        expect(
            isSameOrigin(
                post(
                    { origin: 'http://127.0.0.1:4401', host: '127.0.0.1:4401' },
                    'http://127.0.0.1:4401/api/auth/login',
                ),
                new URL('http://localhost:4401/api/auth/login'),
            ),
        ).toBe(true);
    });

    it('menerima origin https di belakang proxy yang mengakhiri TLS', () => {
        expect(
            isSameOrigin(
                post({
                    origin: 'https://mcu.example.com',
                    'x-forwarded-host': 'mcu.example.com',
                    'x-forwarded-proto': 'https',
                }),
                new URL('http://10.0.0.5:4401/api/auth/login'),
            ),
        ).toBe(true);
    });

    it('memakai PUBLIC_APP_URL sebagai cadangan bila Host tidak diteruskan proxy', () => {
        expect(
            isSameOrigin(
                post({ origin: 'https://app.example.com' }),
                new URL('http://localhost:4401/api/auth/login'),
                'https://app.example.com/',
            ),
        ).toBe(true);
    });

    it('menolak origin dari situs lain', () => {
        expect(
            isSameOrigin(
                post({ origin: 'https://evil.example', host: '127.0.0.1:4401' }),
                new URL('http://localhost:4401/api/auth/login'),
                'http://127.0.0.1:4401',
            ),
        ).toBe(false);
    });

    it('memakai Sec-Fetch-Site bila Origin tidak dikirim', () => {
        expect(
            isSameOrigin(
                post({ 'sec-fetch-site': 'same-origin' }),
                new URL('http://localhost:4401/api/auth/login'),
            ),
        ).toBe(true);
        expect(
            isSameOrigin(
                post({ 'sec-fetch-site': 'cross-site' }),
                new URL('http://localhost:4401/api/auth/login'),
            ),
        ).toBe(false);
        expect(isSameOrigin(post({}), new URL('http://localhost:4401/api/auth/login'))).toBe(false);
    });
});

describe('isUnsafeMethod', () => {
    it('menandai metode yang mengubah data (tanpa peduli huruf besar/kecil)', () => {
        for (const method of ['POST', 'put', 'Patch', 'DELETE']) {
            expect(isUnsafeMethod(method)).toBe(true);
        }

        for (const method of ['GET', 'HEAD', 'options']) {
            expect(isUnsafeMethod(method)).toBe(false);
        }
    });
});
