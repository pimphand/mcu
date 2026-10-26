import { describe, expect, it } from 'vitest';
import { participantPlanBadges, type ParticipantListRow } from './participant-format';

function row(plans: Partial<ParticipantListRow>): ParticipantListRow {
    return {
        id: 1,
        code: 'MCU-1',
        nik: null,
        name: 'Budi',
        gender: 'L',
        birthday: null,
        divisi_name: null,
        department_name: null,
        contract_name: null,
        packet_name: null,
        plan_u: false,
        plan_a: false,
        plan_e: false,
        plan_s: false,
        plan_r: false,
        lab_special: false,
        register_number: null,
        register_date: null,
        ...plans,
    };
}

describe('participantPlanBadges', () => {
    it('menampilkan huruf plan aktif sesuai urutan kolom legacy U/A/E/S/R', () => {
        expect(participantPlanBadges(row({ plan_s: true, plan_u: true }))).toEqual(['U', 'S']);
        expect(participantPlanBadges(row({ plan_r: true, plan_a: true }))).toEqual(['A', 'R']);
    });

    it('mengembalikan daftar kosong bila tidak ada plan aktif', () => {
        expect(participantPlanBadges(row({}))).toEqual([]);
        expect(participantPlanBadges(row({ lab_special: true }))).toEqual([]);
    });
});
