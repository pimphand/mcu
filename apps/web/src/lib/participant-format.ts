/**
 * Tipe baris dan aturan tampilan daftar peserta. Dipakai bersama oleh
 * `ParticipantTable.vue` (pemuat data) dan `ParticipantListTable.vue` (tabel)
 * supaya kolom plan yang tampil selalu sama dengan yang difilter.
 */

/** Baris `api.v_participant_list` yang dipakai daftar peserta. */
export type ParticipantListRow = {
    id: number;
    code: string;
    nik: string | null;
    name: string;
    gender: string | null;
    birthday: string | null;
    divisi_name: string | null;
    department_name: string | null;
    contract_name: string | null;
    packet_name: string | null;
    plan_u: boolean;
    plan_a: boolean;
    plan_e: boolean;
    plan_s: boolean;
    plan_r: boolean;
    lab_special: boolean;
    register_number: string | null;
    register_date: string | null;
};

/** Kolom plan legacy U/A/E/S/R beserta labelnya. */
export const PARTICIPANT_PLAN_COLUMNS = [
    ['plan_u', 'U'],
    ['plan_a', 'A'],
    ['plan_e', 'E'],
    ['plan_s', 'S'],
    ['plan_r', 'R'],
] as const;

/** Huruf plan yang aktif pada satu baris, mis. `['U', 'S']`. */
export function participantPlanBadges(row: ParticipantListRow): string[] {
    return PARTICIPANT_PLAN_COLUMNS.filter(([column]) => row[column]).map(([, label]) => label);
}
