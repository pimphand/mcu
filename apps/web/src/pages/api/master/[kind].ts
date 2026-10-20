import type { APIRoute } from 'astro';
import { readSessionToken, verifySession } from '@/lib/auth/session';
import { json, problem, type ProblemCode } from '@/lib/http';
import { masterConfig, masterKind } from '@/lib/master-data';
import { validateMasterForm } from '@/lib/master-validation';
import {
    createMaster,
    createUser,
    deleteMaster,
    deleteUser,
    MasterWriteError,
    toggleMenu,
    updateMaster,
    updateUser,
} from '@/lib/master-write';
import { listMaster } from '@/lib/master-server';
import { menuAllows, type MenuAction, type MenuRow } from '@/lib/nav';
import { postgrestRows, PostgrestError } from '@/lib/server-postgrest';
import { SignatureError } from '@/lib/signature';

export const prerender = false;

const ACTION_PERMISSION: Record<string, MenuAction> = {
    create: 'add',
    update: 'edit',
    delete: 'delete',
    toggle: 'edit',
};

const CODE_BY_STATUS = {
    400: 'bad_request',
    403: 'forbidden',
    404: 'not_found',
    409: 'conflict',
} satisfies Record<number, ProblemCode>;

const MAX_PER_PAGE = 100;

function redirectTo(requestUrl: URL, path: string, params: Record<string, string>): Response {
    const target = new URL(path, requestUrl.origin);

    for (const [key, value] of Object.entries(params)) {
        target.searchParams.set(key, value);
    }

    return Response.redirect(target, 303);
}

function errorMessage(error: unknown): string {
    if (error instanceof MasterWriteError || error instanceof SignatureError) {
        return error.message;
    }

    if (error instanceof PostgrestError) {
        return error.message;
    }

    return 'Data gagal disimpan. Silakan coba lagi.';
}

/**
 * Klien SPA mengirim `Accept: application/json` agar jawabannya JSON, bukan
 * redirect 303. Permintaan tanpa header itu (mis. kiriman form biasa) tetap
 * diarahkan ke halaman master seperti sebelumnya.
 */
function wantsJson(request: Request): boolean {
    return request.headers.get('accept')?.includes('application/json') ?? false;
}

/** Pertahankan form terbuka saat validasi/penyimpanan gagal. */
function errorRedirect(url: URL, path: string, action: string, id: number, message: string) {
    const params: Record<string, string> = { error: message };

    if (action === 'create') params.new = '1';
    if (action === 'update' && Number.isInteger(id) && id > 0) params.edit = String(id);

    return redirectTo(url, path, params);
}

type MasterResponder = {
    ok(message: string, code?: string | null): Response;
    fail(action: string, id: number, code: ProblemCode, message: string): Response;
};

/**
 * Satu responder per request: mode JSON untuk island SPA, mode redirect untuk
 * kiriman form biasa. State-nya lokal supaya request paralel tidak tercampur.
 */
function responderFor(request: Request, url: URL, path: string): MasterResponder {
    const asJson = wantsJson(request);

    return {
        ok(message, code = null) {
            if (asJson) return json({ ok: true, message, code });

            return redirectTo(url, path, { ok: message });
        },
        fail(action, id, code, message) {
            if (asJson) return problem(code, message);

            return errorRedirect(url, path, action, id, message);
        },
    };
}

function failureCode(error: unknown): ProblemCode {
    if (error instanceof MasterWriteError) return CODE_BY_STATUS[error.status];
    if (error instanceof SignatureError) return 'bad_request';
    if (error instanceof PostgrestError && error.status === 409) return 'conflict';

    return 'internal';
}

/** Permission menu dibaca dari `v_my_menus`; RLS tetap lapisan terakhir. */
async function menuRows(token: string): Promise<MenuRow[] | null> {
    try {
        return await postgrestRows<MenuRow>(token, 'v_my_menus', { select: '*' });
    } catch (error) {
        console.error('Permission menu gagal dimuat.', error);

        return null;
    }
}

/**
 * Daftar data untuk tabel SPA. Dipakai tabel master saat memuat ulang setelah
 * simpan/pindah halaman, tanpa reload halaman penuh.
 */
export const GET: APIRoute = async ({ params, cookies, url }) => {
    const token = readSessionToken(cookies);

    if (!token) return problem('unauthorized', 'Sesi tidak ditemukan.');

    const session = await verifySession(token);

    if (!session) return problem('unauthorized', 'Sesi tidak valid atau sudah kedaluwarsa.');

    const kind = masterKind(params.kind);

    if (!kind) return problem('not_found', 'Jenis data tidak dikenal.');

    const config = masterConfig(kind);
    const menus = await menuRows(token);

    if (!menus) return problem('bad_gateway', 'Layanan data sedang tidak tersedia.');

    if (!menuAllows(menus, config.legacyUrl, 'view')) {
        return problem('forbidden', 'Anda tidak berwenang melihat data ini.');
    }

    const perPageParam = Number(url.searchParams.get('per_page') ?? '25');
    const pageParam = Number(url.searchParams.get('page') ?? '0');

    try {
        const list = await listMaster(kind, token, session, {
            search: (url.searchParams.get('q') ?? '').trim().slice(0, 100),
            sort: url.searchParams.get('sort') ?? '',
            page: Number.isInteger(pageParam) && pageParam > 0 ? pageParam : 0,
            perPage:
                Number.isInteger(perPageParam) && perPageParam > 0
                    ? Math.min(perPageParam, MAX_PER_PAGE)
                    : 25,
        });

        return json({ rows: list.rows, total: list.total });
    } catch (error) {
        console.error('Daftar master data gagal dimuat.', error);

        return problem('bad_gateway', 'Data master gagal dimuat.');
    }
};

export const POST: APIRoute = async ({ request, params, cookies, url }) => {
    const token = readSessionToken(cookies);

    if (!token) return problem('unauthorized', 'Sesi tidak ditemukan.');

    const session = await verifySession(token);

    if (!session) return problem('unauthorized', 'Sesi tidak valid atau sudah kedaluwarsa.');

    const kind = masterKind(params.kind);

    if (!kind) return problem('not_found', 'Jenis data tidak dikenal.');

    const config = masterConfig(kind);
    const responder = responderFor(request, url, config.path);

    let form: FormData;

    try {
        form = await request.formData();
    } catch {
        return problem('bad_request', 'Body form tidak dapat dibaca.');
    }

    const action = String(form.get('_action') ?? '').trim();
    const permission = ACTION_PERMISSION[action];

    if (!permission) {
        return problem('bad_request', 'Aksi tidak dikenal.');
    }

    // Permission menu diperiksa di server: tombol yang disembunyikan bukan
    // satu-satunya lapisan, karena request bisa dikirim langsung.
    const menus = await menuRows(token);

    if (!menus) return problem('bad_gateway', 'Layanan data sedang tidak tersedia.');

    if (!menuAllows(menus, config.legacyUrl, permission)) {
        return problem('forbidden', 'Anda tidak berwenang melakukan aksi ini.');
    }

    if (
        (kind === 'role' || kind === 'menu' || kind === 'client') &&
        session.app_role !== 'admin' &&
        session.level !== 1
    ) {
        return problem(
            'forbidden',
            kind === 'client'
                ? 'Hanya administrator yang dapat mengubah data client.'
                : 'Hanya administrator yang dapat mengubah role/menu.',
        );
    }

    const id = Number(form.get('id'));

    try {
        if (action === 'toggle') {
            if (!Number.isInteger(id) || id <= 0) {
                return responder.fail(action, id, 'bad_request', 'Data tidak valid.');
            }

            await toggleMenu(token, id, String(form.get('is_active')) === 'true');

            return responder.ok('Status menu diperbarui.');
        }

        if (action === 'delete') {
            if (!Number.isInteger(id) || id <= 0) {
                return responder.fail(action, id, 'bad_request', 'Data tidak valid.');
            }

            if (kind === 'user') {
                await deleteUser(session, id);
            } else {
                await deleteMaster(kind, token, session, id);
            }

            return responder.ok('Data berhasil dihapus.');
        }

        const mode = action === 'create' ? 'create' : 'update';

        if (mode === 'update' && (!Number.isInteger(id) || id <= 0)) {
            return responder.fail(action, id, 'bad_request', 'Data tidak valid.');
        }

        const validation = validateMasterForm(kind, form, mode);

        if (!validation.ok) {
            return responder.fail(action, id, 'bad_request', validation.message);
        }

        if (kind === 'user') {
            if (mode === 'create') {
                await createUser(session, validation.values);
            } else {
                await updateUser(session, id, validation.values);
            }

            return responder.ok(
                mode === 'create' ? 'User berhasil disimpan.' : 'Perubahan user disimpan.',
            );
        }

        if (mode === 'create') {
            const result = await createMaster(
                kind,
                token,
                session,
                validation.values,
                form.get('ttd'),
            );

            return responder.ok(
                result.code
                    ? `Data berhasil disimpan (${result.code}).`
                    : 'Data berhasil disimpan.',
                result.code ?? null,
            );
        }

        await updateMaster(kind, token, session, id, validation.values, form.get('ttd'));

        return responder.ok('Perubahan berhasil disimpan.');
    } catch (error) {
        console.error('Mutasi master data gagal.', error);

        return responder.fail(
            action,
            Number.isInteger(id) ? id : 0,
            failureCode(error),
            errorMessage(error),
        );
    }
};

// Dipakai halaman untuk memastikan resource yang tidak punya halaman tidak
// membocorkan daftar jenis data lewat method lain.
export const ALL: APIRoute = async ({ params }) => {
    return masterKind(params.kind)
        ? problem('method_not_allowed', 'Gunakan GET atau POST untuk data master.')
        : problem('not_found', 'Jenis data tidak dikenal.');
};
