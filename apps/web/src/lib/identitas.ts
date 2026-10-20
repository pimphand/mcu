/**
 * Print Identitas mengikuti halaman legacy:
 *   - `resources/views/pages/participant/indetitas.blade.php` (filter)
 *   - `resources/views/pages/report/multiple/identitas.blade.php` (cetak massal)
 *
 * Ukuran kertas legacy 150 × 100 mm; isi stiker memakai `plan_name` (bukan
 * `packet_name`), tanpa baris Status, dan tanggal register `d-m-Y`.
 */

export type IdentitasQuery = {
    start: number;
    end: number;
    /** Kosong = legacy tidak memfilter tanggal. */
    tglAwal: string | null;
    tglAkhir: string | null;
    /** Dipakai cetak dari Print MCU; halaman Print Identitas legacy tanpa divisi. */
    divisiId: number | null;
};

export type IdentitasRelation = { name: string | null } | null;

export type IdentitasRow = {
    id: number;
    code: string;
    name: string;
    gender: string;
    birthday: string;
    nik: string;
    status: string | null;
    plan_name: string | null;
    packet_name: string | null;
    no_form: number | null;
    register_date: string | null;
    client_id: number;
    contract_id: number | null;
    divisis?: IdentitasRelation;
    departments?: IdentitasRelation;
};

export type IdentitasParticipant = {
    participant_id: number;
    code: string;
    name: string;
    gender: string;
    birthday: string;
    nik: string;
    plan_name: string | null;
    no_form: number | null;
    register_date: string | null;
    client_id: number;
    contract_id: number | null;
    divisi_name: string | null;
    department_name: string | null;
};

const ISO_DATE = /^\d{4}-\d{2}-\d{2}$/;

function positiveInteger(value: string | null, fallback: number): number {
    const parsed = Number(value ?? '');

    return Number.isInteger(parsed) && parsed > 0 ? parsed : fallback;
}

function isoDate(value: string | null): string | null {
    return value && ISO_DATE.test(value) ? value : null;
}

export function parseIdentitasQuery(params: URLSearchParams): IdentitasQuery {
    const start = positiveInteger(params.get('start'), 1);
    const end = Math.max(start, positiveInteger(params.get('end'), 50));

    const divisi = Number(params.get('divisi_id') ?? '');

    return {
        start,
        end,
        tglAwal: isoDate(params.get('tgl_awal')),
        tglAkhir: isoDate(params.get('tgl_akhir')),
        divisiId: Number.isInteger(divisi) && divisi > 0 ? divisi : null,
    };
}

/** Nilai untuk parameter `and` PostgREST; tanggal register memakai WIB. */
export function identitasFilter(query: IdentitasQuery): string {
    const parts = [`no_form.gte.${query.start}`, `no_form.lte.${query.end}`];

    if (query.tglAwal) {
        parts.push(`register_date.gte.${query.tglAwal}T00:00:00+07:00`);
    }

    if (query.tglAkhir) {
        parts.push(`register_date.lte.${query.tglAkhir}T23:59:59+07:00`);
    }

    if (query.divisiId) {
        parts.push(`divisi_id.eq.${query.divisiId}`);
    }

    return `(${parts.join(',')})`;
}

export function identitasHref(query: IdentitasQuery): string {
    const params = new URLSearchParams({
        start: String(query.start),
        end: String(query.end),
    });

    if (query.tglAwal) params.set('tgl_awal', query.tglAwal);
    if (query.tglAkhir) params.set('tgl_akhir', query.tglAkhir);
    if (query.divisiId) params.set('divisi_id', String(query.divisiId));

    return `/app/print/identitas/print?${params.toString()}`;
}

export function mapIdentitas(row: IdentitasRow): IdentitasParticipant {
    return {
        participant_id: row.id,
        code: row.code,
        name: row.name,
        gender: row.gender,
        birthday: row.birthday,
        nik: row.nik,
        plan_name: row.plan_name,
        no_form: row.no_form,
        register_date: row.register_date,
        client_id: row.client_id,
        contract_id: row.contract_id,
        divisi_name: row.divisis?.name ?? null,
        department_name: row.departments?.name ?? null,
    };
}

export const IDENTITAS_SELECT =
    'id,code,name,gender,birthday,nik,status,plan_name,packet_name,no_form,register_date,client_id,contract_id,divisis(name),departments(name)';
