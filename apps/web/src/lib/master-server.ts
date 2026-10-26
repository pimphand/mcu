import type { SessionClaims } from './auth/session';
import { queryServiceRows } from './db';
import type {
    MasterField,
    MasterKind,
    MasterOption,
    MasterOptions,
    MasterRow,
} from './master-data';
import { masterConfig } from './master-data';
import { postgrestPage, postgrestRow, postgrestRows } from './server-postgrest';

// Tipe baris/opsi tinggal di `master-data` supaya halaman dan island Vue ikut
// memakainya; di-ekspor ulang di sini agar pemanggil lama tetap jalan.
export type { MasterOption, MasterOptions, MasterRow };

const RELATION_SELECT: Partial<Record<MasterKind, string>> = {
    contract: '*,clients(name)',
    department: '*,clients(name)',
    divisi: '*,clients(name)',
    employee: '*,clients(name)',
};

function cleanSearch(value: string): string {
    return value
        .trim()
        .replace(/[(),*]/g, ' ')
        .trim();
}

function searchFilter(columns: string[], term: string): Record<string, string> {
    if (!term) return {};

    return {
        or: `(${columns.map((column) => `${column}.ilike.*${term}*`).join(',')})`,
    };
}

function safeSort(kind: MasterKind, value: string | undefined): string {
    const config = masterConfig(kind);
    const allowed = config.sortOptions.map((option) => option.value);

    return value && allowed.includes(value) ? value : config.defaultSort;
}

/**
 * Daftar client legacy menampilkan kontrak aktif di dalam baris client. View
 * ringkas itu dipertahankan sebagai satu kolom teks: satu query untuk semua
 * baris di halaman ini (`in.(...)`), bukan satu query per client.
 */
async function withContractNames(token: string, rows: MasterRow[]): Promise<MasterRow[]> {
    const ids = rows.map((row) => Number(row.id)).filter((id) => Number.isInteger(id));

    if (ids.length === 0) return rows;

    const contracts = await postgrestRows<{
        client_id: number | null;
        code: string | null;
        name: string | null;
    }>(
        token,
        'contracts',
        {
            select: 'client_id,code,name',
            client_id: `in.(${ids.join(',')})`,
            deleted_at: 'is.null',
            order: 'code.asc',
            limit: 1000,
        },
        'public',
    );

    const byClient = new Map<number, string[]>();

    for (const contract of contracts) {
        const key = Number(contract.client_id);
        const label = [contract.code, contract.name].filter(Boolean).join(' / ');

        if (!Number.isInteger(key) || !label) continue;

        byClient.set(key, [...(byClient.get(key) ?? []), label]);
    }

    return rows.map((row) => ({ ...row, contract_names: byClient.get(Number(row.id)) ?? [] }));
}

function withClientName(kind: MasterKind, row: MasterRow): MasterRow {
    if (kind === 'contract' || kind === 'department' || kind === 'divisi' || kind === 'employee') {
        const relation = row.clients as { name?: string } | null;
        return { ...row, client_name: relation?.name ?? null };
    }

    return row;
}

export async function listMaster(
    kind: MasterKind,
    token: string,
    session: SessionClaims,
    params: { search: string; sort: string; page: number; perPage: number },
): Promise<{ rows: MasterRow[]; total: number }> {
    if (kind === 'user') {
        return listUsers(session, params);
    }

    const config = masterConfig(kind);
    const term = cleanSearch(params.search);
    const query: Record<string, string | number> = {
        select: RELATION_SELECT[kind] ?? '*',
        order: safeSort(kind, params.sort),
        ...searchFilter(config.searchColumns, term),
    };

    if (config.softDelete) {
        query.deleted_at = 'is.null';
    }

    const { rows, total } = await postgrestPage<MasterRow>(
        token,
        config.resource,
        query,
        [params.page * params.perPage, params.page * params.perPage + params.perPage - 1],
        'public',
    );

    let mapped = rows.map((row) => withClientName(kind, row));

    if (kind === 'client') {
        mapped = await withContractNames(token, mapped);
    }

    if (kind === 'menu') {
        const parents = await postgrestRows<{ id: number; name: string }>(
            token,
            'menus',
            { select: 'id,name', limit: 500 },
            'public',
        );
        const names = new Map(parents.map((parent) => [parent.id, parent.name]));
        mapped = mapped.map((row) => ({
            ...row,
            parent_name: row.parent_id ? (names.get(Number(row.parent_id)) ?? null) : null,
        }));
    }

    return { rows: mapped, total };
}

type UserListRow = {
    id: number;
    name: string;
    username: string;
    email: string | null;
    role_id: number | null;
    client_id: number | null;
    is_active: boolean;
    created_at: string;
    role_name: string | null;
    role_level: number | null;
    client_name: string | null;
    total: number;
    [key: string]: unknown;
};

async function listUsers(
    session: SessionClaims,
    params: { search: string; sort: string; page: number; perPage: number },
): Promise<{ rows: MasterRow[]; total: number }> {
    const sortValue = safeSort('user', params.sort);
    const [sortColumn, direction] = sortValue.split('.');
    const allowedColumns = new Set(['created_at', 'name', 'username']);
    const column = allowedColumns.has(sortColumn ?? '') ? sortColumn : 'created_at';
    const dir = direction === 'asc' ? 'ASC' : 'DESC';
    const term = cleanSearch(params.search);
    const values: unknown[] = [];
    const where: string[] = ['u.deleted_at is null'];

    if (session.app_role === 'client_admin') {
        values.push(session.client_id);
        where.push(`u.client_id = $${values.length}`);
    } else if (session.app_role === 'employee' || session.app_role === 'participant') {
        values.push(session.user_id);
        where.push(`u.id = $${values.length}`);
    }

    if (term) {
        values.push(`%${term}%`);
        const index = values.length;
        where.push(
            `(u.name ilike $${index} or u.username ilike $${index} or coalesce(u.email, '') ilike $${index})`,
        );
    }

    const limitIndex = values.push(params.perPage);
    const offsetIndex = values.push(params.page * params.perPage);

    const rows = await queryServiceRows<UserListRow>(
        `select u.id, u.name, u.username, u.email, u.role_id, u.client_id, u.is_active, u.created_at,
                r.name as role_name, r.level as role_level, c.name as client_name,
                count(*) over()::int as total
           from public.users u
           left join public.roles r on r.id = u.role_id
           left join public.clients c on c.id = u.client_id
          where ${where.join(' and ')}
          order by u.${column} ${dir}, u.id ${dir}
          limit $${limitIndex} offset $${offsetIndex}`,
        values,
    );

    return { rows, total: rows[0]?.total ?? 0 };
}

export async function findMasterRow(
    kind: MasterKind,
    token: string,
    id: number,
): Promise<MasterRow | null> {
    if (kind === 'user') {
        const rows = await queryServiceRows<UserListRow>(
            `select u.id, u.name, u.username, u.email, u.role_id, u.client_id, u.is_active, u.created_at,
                    r.name as role_name, r.level as role_level, c.name as client_name
               from public.users u
               left join public.roles r on r.id = u.role_id
               left join public.clients c on c.id = u.client_id
              where u.id = $1 and u.deleted_at is null
              limit 1`,
            [id],
        );

        return rows[0] ?? null;
    }

    const config = masterConfig(kind);

    return postgrestRow<MasterRow>(
        token,
        config.resource,
        { select: '*', id: `eq.${id}` },
        'public',
    );
}

export async function loadMasterOptions(
    token: string,
    fields: MasterField[],
    session?: SessionClaims,
): Promise<MasterOptions> {
    const sources = new Set(fields.map((field) => field.optionSource).filter(Boolean));
    const options: MasterOptions = { clients: [], roles: [], rootMenus: [] };

    const tasks: Array<Promise<void>> = [];

    if (sources.has('clients')) {
        tasks.push(
            postgrestRows<{ id: number; code: string; name: string }>(
                token,
                'clients',
                { select: 'id,code,name', order: 'name.asc', limit: 1000 },
                'public',
            ).then((rows) => {
                options.clients = rows.map((row) => ({
                    value: String(row.id),
                    label: `${row.code} — ${row.name}`,
                }));
            }),
        );
    }

    if (sources.has('roles')) {
        tasks.push(
            postgrestRows<{ id: number; level: number; name: string }>(
                token,
                'roles',
                { select: 'id,level,name', level: 'lte.2', order: 'level.asc', limit: 100 },
                'public',
            ).then((rows) => {
                options.roles = rows
                    .filter((row) => session?.app_role === 'admin' || row.level === 2)
                    .map((row) => ({
                        value: String(row.id),
                        label: `${row.level} — ${row.name}`,
                    }));
            }),
        );
    }

    if (sources.has('rootMenus')) {
        tasks.push(
            postgrestRows<{ id: number; name: string; parent_id: number | null }>(
                token,
                'menus',
                { select: 'id,name,parent_id', order: 'sort_order.asc', limit: 500 },
                'public',
            ).then((rows) => {
                options.rootMenus = rows
                    .filter((row) => row.parent_id === null)
                    .map((row) => ({ value: String(row.id), label: row.name }));
            }),
        );
    }

    await Promise.all(tasks);

    return options;
}

export async function countClientUsers(clientId: number): Promise<number> {
    const rows = await queryServiceRows<{ total: number }>(
        'select count(*)::int as total from public.users where client_id = $1 and deleted_at is null',
        [clientId],
    );

    return rows[0]?.total ?? 0;
}
