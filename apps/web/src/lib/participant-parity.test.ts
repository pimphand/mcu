import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { describe, expect, it } from 'vitest';
import {
    PARTICIPANT_LIST_COLUMNS,
    emptyParticipantFilters,
    parseParticipantFilters,
    participantFilterQuery,
    participantPostgrestQuery,
} from './participant-filters';

/**
 * Paritas fase 5 dengan halaman Blade legacy.
 *
 * Repo masih menyimpan aplikasi Laravel lama di root sebagai referensi
 * read-only. Test ini membaca sumbernya langsung supaya penambahan field filter
 * di legacy (`participant/filter.blade.php`) atau perubahan kolom view
 * `api.v_participant_list` di `db/` menggagalkan test sampai modul filter Astro
 * ikut disesuaikan.
 */

const ROOT = new URL('../../../../', import.meta.url);

function readRepoFile(...segments: string[]): string {
    return readFileSync(fileURLToPath(new URL(segments.join('/'), ROOT)), 'utf8');
}

const SAMPLE: Record<string, string> = {
    nik: '3273010101010001',
    name: 'Budi',
    gender: 'L',
    birthday: '1990-01-31',
};

/** Nama field input filter legacy (dari `filter.blade.php`). */
function legacyFilterFields(): string[] {
    const blade = readRepoFile('resources/views/pages/participant/filter.blade.php');
    const names = new Set<string>();

    for (const match of blade.matchAll(/name="([a-zA-Z_]+)"/g)) {
        names.add(match[1]!);
    }

    return [...names].sort();
}

describe('paritas filter peserta', () => {
    it('punya field filter yang sama dengan modal filter legacy', () => {
        const fields = legacyFilterFields();

        expect(fields).toEqual(
            [
                'birthday',
                'gender',
                'lab_special',
                'name',
                'nik',
                'packet_a',
                'packet_b',
                'packet_c',
                'packet_d',
                'packet_e',
                'packet_f',
                'plan_a',
                'plan_e',
                'plan_r',
                'plan_s',
                'plan_u',
            ].sort(),
        );
    });

    it('setiap field legacy menghasilkan query filter, bukan diabaikan', () => {
        const params = new URLSearchParams();

        for (const field of legacyFilterFields()) {
            params.set(field, SAMPLE[field] ?? '1');
        }

        const query = participantFilterQuery(parseParticipantFilters(params));
        const postgrest = participantPostgrestQuery(parseParticipantFilters(params));

        for (const field of legacyFilterFields()) {
            expect(query.has(field), `${field} hilang dari query URL`).toBe(true);
            expect(postgrest[field], `${field} tidak dikirim ke PostgREST`).toBeDefined();
        }
    });

    it('tidak pernah mengirim scope client/kontrak dari browser', () => {
        const params = new URLSearchParams('client_id=9&contract_id=9&client_id=eq.9');
        const filters = parseParticipantFilters(params);
        const postgrest = participantPostgrestQuery(filters);

        expect(postgrest).toEqual({});
        expect(participantFilterQuery(filters).toString()).toBe('');
        expect(emptyParticipantFilters()).not.toHaveProperty('clientId');
    });

    it('kolom daftar tersedia di view api.v_participant_list', () => {
        const migration = readRepoFile('db/migrations/0003_views.sql');
        const start = migration.indexOf('view api.v_participant_list');
        const end = migration.indexOf('create or replace view api.v_mcu_progress');
        const view = migration.slice(start, end);

        for (const column of PARTICIPANT_LIST_COLUMNS) {
            expect(new RegExp(`\\b${column}\\b`).test(view), `${column} tidak ada di view`).toBe(
                true,
            );
        }
    });
});
