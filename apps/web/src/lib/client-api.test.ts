import { afterEach, describe, expect, it, vi } from 'vitest';
import { ApiError, fetchRows } from './client-api';

describe('fetchRows', () => {
    afterEach(() => {
        vi.unstubAllGlobals();
    });

    it('sends Range without a limit query param and reads the total', async () => {
        let requestedUrl = '';
        let requestedInit: RequestInit | undefined;

        vi.stubGlobal(
            'fetch',
            vi.fn(async (input: RequestInfo | URL, init?: RequestInit) => {
                requestedUrl = String(input);
                requestedInit = init;

                return new Response(JSON.stringify([{ id: 1 }]), {
                    status: 206,
                    headers: { 'content-range': '25-49/337' },
                });
            }),
        );

        const result = await fetchRows<{ id: number }>(
            'v_participant_list',
            { select: 'id', limit: 25 },
            { range: [25, 49] },
        );

        expect(requestedUrl).not.toContain('limit=');
        expect(requestedUrl).toContain('select=id');
        expect(new Headers(requestedInit?.headers).get('range')).toBe('25-49');
        expect(new Headers(requestedInit?.headers).get('prefer')).toBe('count=exact');
        expect(result.total).toBe(337);
        expect(result.rows).toEqual([{ id: 1 }]);
    });

    it('throws a typed ApiError on 416 so callers can clamp the page', async () => {
        vi.stubGlobal(
            'fetch',
            vi.fn(async () => {
                return new Response(
                    JSON.stringify({ message: 'Requested range not satisfiable' }),
                    {
                        status: 416,
                        headers: { 'content-type': 'application/json' },
                    },
                );
            }),
        );

        await expect(
            fetchRows('v_participant_list', { select: 'id' }, { range: [500, 524] }),
        ).rejects.toMatchObject({ name: 'ApiError', status: 416 });
        await expect(
            fetchRows('v_participant_list', { select: 'id' }, { range: [500, 524] }),
        ).rejects.toBeInstanceOf(ApiError);
    });
});
