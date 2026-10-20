import { describe, expect, it } from 'vitest';
import { applySessionScope, parseResourcePath, profileForResource } from './postgrest';

function scoped(
    resource: string,
    search: string,
    claims: { app_role: string; client_id: number | null; contract_id: number | null },
): URLSearchParams {
    const url = new URL(`http://postgrest.local/${resource}${search}`);

    applySessionScope([resource], url, claims);

    return url.searchParams;
}

describe('PostgREST proxy helpers', () => {
    it('only allows the whitelisted resources and RPC', () => {
        expect(parseResourcePath('v_participant_list')).toEqual(['v_participant_list']);
        expect(parseResourcePath('rpc/register_participant')).toEqual([
            'rpc',
            'register_participant',
        ]);
        expect(parseResourcePath('users')).toBeNull();
        expect(parseResourcePath('rpc/drop_everything')).toBeNull();
        expect(parseResourcePath('participants/1')).toBeNull();
    });

    it('serves views from api and tables from public', () => {
        expect(profileForResource(['v_participant_list'])).toBe('api');
        expect(profileForResource(['participants'])).toBe('public');
    });

    it('overrides browser-supplied client/contract filters with the session claims', () => {
        const params = scoped(
            'v_participant_list',
            '?client_id=eq.999&contract_id=eq.999&name=ilike.*a*',
            { app_role: 'admin', client_id: 1, contract_id: 5 },
        );

        expect(params.get('client_id')).toBe('eq.1');
        expect(params.get('contract_id')).toBe('eq.5');
        expect(params.get('name')).toBe('ilike.*a*');
    });

    it('returns no operational rows when no contract is selected', () => {
        const params = scoped('v_mcu_progress', '?participant_id=eq.12', {
            app_role: 'client_admin',
            client_id: 1,
            contract_id: null,
        });

        expect(params.get('client_id')).toBe('eq.1');
        expect(params.get('contract_id')).toBe('is.null');
        expect(params.get('participant_id')).toBe('eq.12');
    });

    it('leaves participant access to RLS and skips unscoped resources', () => {
        const participant = scoped('v_participant_list', '', {
            app_role: 'participant',
            client_id: 1,
            contract_id: null,
        });

        expect(participant.get('client_id')).toBe('eq.1');
        expect(participant.get('contract_id')).toBeNull();

        const employees = scoped('employees', '', {
            app_role: 'admin',
            client_id: 1,
            contract_id: 5,
        });

        expect(employees.toString()).toBe('');
    });
});
