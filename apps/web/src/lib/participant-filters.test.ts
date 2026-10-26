import { describe, expect, it } from 'vitest';
import {
    activeParticipantFilters,
    emptyParticipantFilters,
    parseParticipantFilters,
    participantFilterQuery,
    participantPostgrestQuery,
    participantSearchTerm,
    type ParticipantFilters,
} from './participant-filters';

function withPlans(plans: Partial<ParticipantFilters['plans']>): ParticipantFilters {
    return {
        ...emptyParticipantFilters(),
        plans: { ...emptyParticipantFilters().plans, ...plans },
    };
}

describe('parseParticipantFilters', () => {
    it('membaca semua field filter dari query string', () => {
        const parsed = parseParticipantFilters(
            new URLSearchParams(
                'search=ASTRA&nik=3273010101010001&name=Budi&gender=L&birthday=1990-01-31&plan_u=1&plan_s=true&packet_c=1&lab_special=1',
            ),
        );

        expect(parsed).toEqual({
            search: 'ASTRA',
            nik: '3273010101010001',
            name: 'Budi',
            gender: 'L',
            birthday: '1990-01-31',
            plans: { u: true, a: false, e: false, s: true, r: false },
            packets: { a: false, b: false, c: true, d: false, e: false, f: false },
            labSpecial: true,
        });
    });

    it('mengabaikan nilai asing, gender selain L/P, dan tanggal tidak valid', () => {
        const parsed = parseParticipantFilters(
            new URLSearchParams('gender=X&birthday=31-01-1990&plan_u=maybe&page=3'),
        );

        expect(parsed.gender).toBe('');
        expect(parsed.birthday).toBe('');
        expect(parsed.plans.u).toBe(false);
        expect(activeParticipantFilters(parsed)).toBe(0);
    });

    it('memangkas spasi dan membatasi panjang kata kunci', () => {
        const parsed = parseParticipantFilters(new URLSearchParams(`name=${'x'.repeat(150)}`));

        expect(parsed.name).toHaveLength(100);
        expect(parseParticipantFilters(new URLSearchParams('search=%20Budi%20')).search).toBe(
            'Budi',
        );
    });
});

describe('participantFilterQuery', () => {
    it('tidak menulis filter kosong maupun halaman pertama', () => {
        expect(participantFilterQuery(emptyParticipantFilters()).toString()).toBe('');
        expect(participantFilterQuery(emptyParticipantFilters(), 0).has('page')).toBe(false);
    });

    it('bolak-balik lewat URL mempertahankan filter dan halaman', () => {
        const filters: ParticipantFilters = {
            ...withPlans({ a: true, r: true }),
            search: 'Budi',
            nik: '3273',
            name: 'Santoso',
            gender: 'P',
            birthday: '1985-12-01',
            packets: { a: false, b: true, c: false, d: false, e: false, f: false },
            labSpecial: true,
        };
        const query = participantFilterQuery(filters, 2);

        expect(query.get('page')).toBe('3');
        expect(parseParticipantFilters(query)).toEqual(filters);
    });
});

describe('participantPostgrestQuery', () => {
    it('mencari kode atau nama untuk pencarian bebas seperti legacy', () => {
        const params = participantPostgrestQuery({
            ...emptyParticipantFilters(),
            search: 'Budi',
        });

        expect(params).toEqual({ or: '(name.ilike.*Budi*,code.ilike.*Budi*)' });
    });

    it('menerjemahkan setiap field ke operator PostgREST yang tepat', () => {
        const params = participantPostgrestQuery({
            ...withPlans({ u: true, e: true }),
            nik: '3273010101010001',
            name: 'Budi',
            gender: 'L',
            birthday: '1990-01-31',
            packets: { a: true, b: false, c: false, d: false, e: false, f: false },
            labSpecial: true,
        });

        expect(params).toEqual({
            nik: 'eq.3273010101010001',
            name: 'ilike.*Budi*',
            gender: 'eq.L',
            birthday: 'eq.1990-01-31',
            lab_special: 'is.true',
            plan_u: 'is.true',
            plan_e: 'is.true',
            packet_a: 'is.true',
        });
    });

    it('tidak mengirim parameter apa pun saat filter kosong', () => {
        expect(participantPostgrestQuery(emptyParticipantFilters())).toEqual({});
    });
});

describe('participantSearchTerm', () => {
    it('membuang karakter yang memecah logical tree PostgREST', () => {
        expect(participantSearchTerm('Budi, (Santoso) "A"\\B')).toBe('Budi Santoso A B');
    });

    it('merapatkan spasi berlebih', () => {
        expect(participantSearchTerm('  Budi   Santoso  ')).toBe('Budi Santoso');
    });
});

describe('activeParticipantFilters', () => {
    it('menghitung pencarian, identitas, plan, paket, dan lab khusus', () => {
        expect(activeParticipantFilters(emptyParticipantFilters())).toBe(0);
        expect(
            activeParticipantFilters({
                ...withPlans({ u: true }),
                search: 'Budi',
                gender: 'P',
                packets: { a: false, b: false, c: true, d: false, e: false, f: false },
                labSpecial: true,
            }),
        ).toBe(5);
    });
});
