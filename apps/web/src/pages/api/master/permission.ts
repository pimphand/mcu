import type { APIRoute } from 'astro';
import { readSessionToken, verifySession } from '@/lib/auth/session';
import { problem } from '@/lib/http';
import { MasterWriteError, savePermissions, type PermissionEntry } from '@/lib/master-write';
import { menuAllows, type MenuRow } from '@/lib/nav';
import { postgrestRows, PostgrestError } from '@/lib/server-postgrest';

export const prerender = false;

function redirectTo(requestUrl: URL, roleId: number, params: Record<string, string>): Response {
    const target = new URL(`/app/master/role/${roleId}`, requestUrl.origin);

    for (const [key, value] of Object.entries(params)) {
        target.searchParams.set(key, value);
    }

    return Response.redirect(target, 303);
}

export const POST: APIRoute = async ({ request, cookies, url }) => {
    const token = readSessionToken(cookies);

    if (!token) return problem('unauthorized', 'Sesi tidak ditemukan.');

    const session = await verifySession(token);

    if (!session) return problem('unauthorized', 'Sesi tidak valid atau sudah kedaluwarsa.');

    let form: FormData;

    try {
        form = await request.formData();
    } catch {
        return problem('bad_request', 'Body form tidak dapat dibaca.');
    }

    const roleId = Number(form.get('role_id'));

    if (!Number.isInteger(roleId) || roleId <= 0) {
        return problem('bad_request', 'Role tidak valid.');
    }

    try {
        const menus = await postgrestRows<MenuRow>(token, 'v_my_menus', { select: '*' });

        if (!menuAllows(menus, '/role', 'edit')) {
            return problem('forbidden', 'Anda tidak berwenang mengubah izin role.');
        }

        const entries: PermissionEntry[] = [];

        for (const key of form.keys()) {
            const match = /^menu:(\d+)$/.exec(key);

            if (!match?.[1]) continue;

            const menuId = Number(match[1]);

            if (!Number.isInteger(menuId) || menuId <= 0) continue;

            entries.push({
                menu_id: menuId,
                is_view: form.get(`p:${menuId}:view`) === '1',
                is_add: form.get(`p:${menuId}:add`) === '1',
                is_edit: form.get(`p:${menuId}:edit`) === '1',
                is_delete: form.get(`p:${menuId}:delete`) === '1',
            });
        }

        await savePermissions(token, session, roleId, entries);

        return redirectTo(url, roleId, { ok: 'Izin role disimpan.' });
    } catch (error) {
        console.error('Simpan izin role gagal.', error);

        const message =
            error instanceof MasterWriteError || error instanceof PostgrestError
                ? error.message
                : 'Izin role gagal disimpan. Silakan coba lagi.';

        return redirectTo(url, roleId, { error: message });
    }
};
