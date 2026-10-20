import bcrypt from 'bcryptjs';
import type { SessionClaims } from './auth/session';
import { queryServiceRows } from './db';
import { masterConfig, type MasterKind } from './master-data';
import type { MasterValues } from './master-validation';
import { postgrestSend, postgrestRows } from './server-postgrest';
import { removeUploadedSignature, saveSignature, SignatureError } from './signature';

/**
 * Operasi tulis master data.
 *
 * Tabel master (kecuali `users`) ditulis lewat PostgREST memakai JWT pengguna,
 * jadi RLS tetap menjadi lapisan terakhir. `users` di-revoke dari role
 * `authenticated`, sehingga CRUD user memakai koneksi service dengan
 * pemeriksaan izin eksplisit di sini.
 */

export class MasterWriteError extends Error {
    constructor(
        message: string,
        readonly status: 400 | 403 | 404 | 409 = 400,
    ) {
        super(message);
        this.name = 'MasterWriteError';
    }
}

export type WriteResult = {
    /** Kode baru (client/kontrak/department/divisi/karyawan), bila ada. */
    code?: string | null;
};

function asString(value: MasterValues[string] | undefined): string {
    return typeof value === 'string' ? value : '';
}

function asNumber(value: MasterValues[string] | undefined): number | null {
    return typeof value === 'number' && Number.isFinite(value) ? value : null;
}

function asBoolean(value: MasterValues[string] | undefined): boolean {
    return value === true;
}

function writePayload(
    kind: MasterKind,
    values: MasterValues,
    session: SessionClaims,
    mode: 'create' | 'update',
) {
    const config = masterConfig(kind);
    const payload: Record<string, unknown> = { ...values };

    for (const field of config.fields) {
        if (field.type === 'file') {
            delete payload[field.key];
        }
    }

    // Hanya tabel master yang punya kolom audit.
    if (['client', 'contract', 'department', 'divisi', 'employee'].includes(kind)) {
        payload.updated_by = session.user_id;

        if (mode === 'create') {
            payload.created_by = session.user_id;
        }
    }

    return payload;
}

/** Sidebar hanya dua level: induk menu wajib menu utama, bukan anak/self. */
async function assertMenuParent(
    token: string,
    values: MasterValues,
    id: number | null,
): Promise<void> {
    const parentId = values.parent_id;

    if (typeof parentId !== 'number') return;

    if (id !== null && parentId === id) {
        throw new MasterWriteError('Menu tidak bisa menjadi induknya sendiri.');
    }

    const parents = await postgrestRows<{ id: number; parent_id: number | null }>(
        token,
        'menus',
        { select: 'id,parent_id', id: `eq.${parentId}`, limit: 1 },
        'public',
    );

    if (!parents[0] || parents[0].parent_id !== null) {
        throw new MasterWriteError('Induk menu harus menu utama (tanpa induk).');
    }
}

export async function createMaster(
    kind: MasterKind,
    token: string,
    session: SessionClaims,
    values: MasterValues,
    file: FormDataEntryValue | null = null,
): Promise<WriteResult> {
    const config = masterConfig(kind);

    if (kind === 'menu') {
        await assertMenuParent(token, values, null);
    }

    const payload = writePayload(kind, values, session, 'create');
    let uploaded: string | null = null;

    if (config.signature) {
        uploaded = await saveSignature(file);
        if (uploaded) payload.ttd = uploaded;
    }

    try {
        const rows = await postgrestSend<{ code?: string | null }>(
            token,
            config.resource,
            'POST',
            payload,
            {},
            'return=representation',
            'public',
        );

        return { code: rows[0]?.code ?? null };
    } catch (error) {
        if (uploaded) await removeUploadedSignature(uploaded);
        throw error;
    }
}

export async function updateMaster(
    kind: MasterKind,
    token: string,
    session: SessionClaims,
    id: number,
    values: MasterValues,
    file: FormDataEntryValue | null = null,
): Promise<void> {
    const config = masterConfig(kind);

    if (kind === 'menu') {
        await assertMenuParent(token, values, id);
    }

    const payload = writePayload(kind, values, session, 'update');
    let uploaded: string | null = null;
    let previous: string | null = null;

    if (config.signature) {
        if (kind === 'employee') {
            const existing = await postgrestRows<{ ttd: string | null }>(
                token,
                'employees',
                { select: 'ttd', id: `eq.${id}`, limit: 1 },
                'public',
            );
            previous = existing[0]?.ttd ?? null;
        }

        uploaded = await saveSignature(file);
        if (uploaded) payload.ttd = uploaded;
    }

    try {
        await postgrestSend(
            token,
            config.resource,
            'PATCH',
            payload,
            { id: `eq.${id}` },
            'return=representation',
            'public',
        );
    } catch (error) {
        if (uploaded) await removeUploadedSignature(uploaded);
        throw error;
    }

    if (uploaded && previous) {
        await removeUploadedSignature(previous);
    }
}

type RelationCounts = {
    users: number;
    contracts: number;
    departments: number;
    divisis: number;
    employees: number;
    participants: number;
};

async function clientRelations(clientId: number): Promise<RelationCounts | null> {
    const rows = await queryServiceRows<RelationCounts>(
        `select
            (select count(*)::int from public.users where client_id = $1) as users,
            (select count(*)::int from public.contracts where client_id = $1) as contracts,
            (select count(*)::int from public.departments where client_id = $1) as departments,
            (select count(*)::int from public.divisis where client_id = $1) as divisis,
            (select count(*)::int from public.employees where client_id = $1) as employees,
            (select count(*)::int from public.participants where client_id = $1) as participants`,
        [clientId],
    );

    return rows[0] ?? null;
}

export async function deleteMaster(
    kind: MasterKind,
    token: string,
    session: SessionClaims,
    id: number,
): Promise<void> {
    const config = masterConfig(kind);

    if (kind === 'contract') {
        await postgrestSend(
            token,
            'contracts',
            'PATCH',
            { deleted_at: new Date().toISOString(), updated_by: session.user_id },
            { id: `eq.${id}` },
            'return=representation',
            'public',
        );
        return;
    }

    if (kind === 'menu') {
        const children = await postgrestRows<{ id: number }>(
            token,
            'menus',
            { select: 'id', parent_id: `eq.${id}`, limit: 1 },
            'public',
        );

        if (children.length > 0) {
            throw new MasterWriteError(
                'Menu masih memiliki sub menu. Pindahkan atau hapus sub menu lebih dulu.',
                409,
            );
        }
    }

    if (kind === 'client') {
        const relations = await clientRelations(id);

        if (relations && Object.values(relations).some((count) => count > 0)) {
            throw new MasterWriteError(
                'Client masih dipakai user, kontrak, department, divisi, karyawan, atau peserta.',
                409,
            );
        }
    }

    if (kind === 'role') {
        const rows = await queryServiceRows<{ total: number }>(
            `select count(*)::int as total
               from public.users
              where role_id = $1 and deleted_at is null`,
            [id],
        );

        if ((rows[0]?.total ?? 0) > 0) {
            throw new MasterWriteError(
                'Role masih dipakai user. Pindahkan user ke role lain.',
                409,
            );
        }
    }

    await postgrestSend(
        token,
        config.resource,
        'DELETE',
        undefined,
        { id: `eq.${id}` },
        'return=minimal',
        'public',
    );
}

export async function toggleMenu(token: string, id: number, isActive: boolean): Promise<void> {
    await postgrestSend(
        token,
        'menus',
        'PATCH',
        { is_active: isActive },
        { id: `eq.${id}` },
        'return=representation',
        'public',
    );
}

type RoleRow = { id: number; level: number };

type UserTarget = {
    id: number;
    role_id: number | null;
    role_level: number | null;
    client_id: number | null;
    is_active: boolean;
};

async function roleById(roleId: number): Promise<RoleRow | null> {
    const rows = await queryServiceRows<RoleRow>(
        'select id, level from public.roles where id = $1 and is_active limit 1',
        [roleId],
    );

    return rows[0] ?? null;
}

async function userTarget(id: number): Promise<UserTarget | null> {
    const rows = await queryServiceRows<UserTarget>(
        `select u.id, u.role_id, u.client_id, u.is_active, r.level as role_level
           from public.users u
           left join public.roles r on r.id = u.role_id
          where u.id = $1 and u.deleted_at is null
          limit 1`,
        [id],
    );

    return rows[0] ?? null;
}

function assertUserScope(session: SessionClaims, target: UserTarget): void {
    if (session.app_role === 'admin' || session.level === 1) return;

    if (session.app_role !== 'client_admin' || target.client_id !== session.client_id) {
        throw new MasterWriteError('Anda tidak berwenang mengubah user ini.', 403);
    }

    if (target.role_level === 1) {
        throw new MasterWriteError('Admin client tidak dapat mengubah akun administrator.', 403);
    }
}

/** Role yang boleh dibuat dari halaman User: Administrator dan Admin Client. */
async function assignableRole(
    session: SessionClaims,
    roleId: number,
    requestedClientId: number | null,
): Promise<{ role: RoleRow; clientId: number | null }> {
    const role = await roleById(roleId);

    if (!role) {
        throw new MasterWriteError('Role tidak ditemukan atau tidak aktif.');
    }

    if (![1, 2].includes(role.level)) {
        throw new MasterWriteError('Halaman ini hanya untuk role Administrator atau Admin Client.');
    }

    if (session.app_role === 'admin' || session.level === 1) {
        if (role.level === 2 && requestedClientId === null) {
            throw new MasterWriteError('Admin Client wajib ditautkan ke satu client.');
        }

        return { role, clientId: role.level === 1 ? null : requestedClientId };
    }

    if (session.app_role !== 'client_admin') {
        throw new MasterWriteError('Role ini tidak dapat mengelola user.', 403);
    }

    if (role.level !== 2 || session.client_id === null) {
        throw new MasterWriteError(
            'Admin client hanya dapat membuat Admin Client di client-nya.',
            403,
        );
    }

    return { role, clientId: session.client_id };
}

function duplicateMessage(error: unknown): never {
    const message = error instanceof Error ? error.message : '';

    if (message.includes('users_username_key')) {
        throw new MasterWriteError('Username sudah dipakai.', 409);
    }

    if (message.includes('users_email_key')) {
        throw new MasterWriteError('Email sudah dipakai.', 409);
    }

    throw error;
}

export async function createUser(session: SessionClaims, values: MasterValues): Promise<void> {
    const roleId = asNumber(values.role_id);
    const password = asString(values.password);

    if (roleId === null) throw new MasterWriteError('Role wajib dipilih.');
    if (password.length < 8) throw new MasterWriteError('Password minimal 8 karakter.');

    const { clientId } = await assignableRole(session, roleId, asNumber(values.client_id));
    const passwordHash = await bcrypt.hash(password, 12);

    try {
        await queryServiceRows(
            `insert into public.users
                (name, username, email, password_hash, role_id, client_id, is_active)
             values ($1, $2, $3, $4, $5, $6, $7)
             returning id`,
            [
                asString(values.name),
                asString(values.username),
                asString(values.email),
                passwordHash,
                roleId,
                clientId,
                asBoolean(values.is_active),
            ],
        );
    } catch (error) {
        duplicateMessage(error);
    }
}

export async function updateUser(
    session: SessionClaims,
    id: number,
    values: MasterValues,
): Promise<void> {
    const target = await userTarget(id);

    if (!target) throw new MasterWriteError('User tidak ditemukan.', 404);

    assertUserScope(session, target);

    const roleId = asNumber(values.role_id);

    if (roleId === null) throw new MasterWriteError('Role wajib dipilih.');

    const { clientId } = await assignableRole(session, roleId, asNumber(values.client_id));
    const isActive = asBoolean(values.is_active);

    if (id === session.user_id && (roleId !== session.role_id || !isActive)) {
        throw new MasterWriteError(
            'Anda tidak dapat menonaktifkan atau mengubah role akun sendiri.',
            409,
        );
    }

    const password = asString(values.password);
    const passwordHash = password ? await bcrypt.hash(password, 12) : null;

    try {
        await queryServiceRows(
            `update public.users
                set name = $2,
                    username = $3,
                    email = $4,
                    role_id = $5,
                    client_id = $6,
                    is_active = $7,
                    password_hash = coalesce($8, password_hash),
                    token_version = token_version
                      + case when $8 is null and $7 then 0 else 1 end,
                    updated_at = now()
              where id = $1 and deleted_at is null`,
            [
                id,
                asString(values.name),
                asString(values.username),
                asString(values.email),
                roleId,
                clientId,
                isActive,
                passwordHash,
            ],
        );
    } catch (error) {
        duplicateMessage(error);
    }
}

export async function deleteUser(session: SessionClaims, id: number): Promise<void> {
    const target = await userTarget(id);

    if (!target) throw new MasterWriteError('User tidak ditemukan.', 404);

    assertUserScope(session, target);

    if (id === session.user_id) {
        throw new MasterWriteError('Anda tidak dapat menghapus akun sendiri.', 409);
    }

    await queryServiceRows('delete from public.users where id = $1', [id]);
}

export type PermissionEntry = {
    menu_id: number;
    is_view: boolean;
    is_add: boolean;
    is_edit: boolean;
    is_delete: boolean;
};

/**
 * Simpan matriks izin satu role. RLS `permissions_write` sudah mensyaratkan
 * admin, pemeriksaan sesi di sini menjaga pesan error tetap jelas.
 */
export async function savePermissions(
    token: string,
    session: SessionClaims,
    roleId: number,
    entries: PermissionEntry[],
): Promise<void> {
    if (session.app_role !== 'admin' && session.level !== 1) {
        throw new MasterWriteError('Hanya administrator yang dapat mengubah izin role.', 403);
    }

    const role = await roleById(roleId);

    if (!role) throw new MasterWriteError('Role tidak ditemukan.', 404);

    if (entries.length === 0) throw new MasterWriteError('Tidak ada menu untuk disimpan.');
    if (entries.length > 500)
        throw new MasterWriteError('Terlalu banyak menu dalam satu simpanan.');

    const unique = new Map(entries.map((entry) => [entry.menu_id, entry]));

    await postgrestSend(
        token,
        'permissions',
        'POST',
        [...unique.values()].map((entry) => ({ ...entry, role_id: roleId })),
        { on_conflict: 'role_id,menu_id' },
        'resolution=merge-duplicates,return=representation',
        'public',
    );
}

export { SignatureError };
