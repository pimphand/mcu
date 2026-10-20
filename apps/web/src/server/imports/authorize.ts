import type { AstroCookies } from 'astro';
import { readSessionToken, verifySession, type SessionClaims } from '../../lib/auth/session';
import { problem } from '../../lib/http';
import { menuAllows, type MenuAction, type MenuRow } from '../../lib/nav';
import { postgrestRows } from '../../lib/server-postgrest';
import { uploadReports } from '../../lib/upload-reports';
import type { ImportType } from '../../lib/imports/types';

export type ImportAuth = {
    session: SessionClaims;
    token: string;
};

/**
 * Endpoint import hanya untuk staf dengan konteks MCU aktif dan izin menu
 * legacy (`is_add` untuk unggah, `is_view` untuk template). RLS tetap menjadi
 * penjaga terakhir untuk baris data.
 */
export async function authorizeImport(
    cookies: AstroCookies,
    type: ImportType,
    action: MenuAction,
): Promise<ImportAuth | Response> {
    const token = readSessionToken(cookies);

    if (!token) {
        return problem('unauthorized', 'Sesi tidak ditemukan.');
    }

    const session = await verifySession(token);

    if (!session) {
        return problem('unauthorized', 'Sesi tidak valid atau sudah kedaluwarsa.');
    }

    if (!['admin', 'client_admin', 'employee'].includes(session.app_role)) {
        return problem('forbidden', 'Role ini tidak dapat mengunggah hasil.');
    }

    if (session.client_id === null || session.contract_id === null) {
        return problem('forbidden', 'Pilih client dan kontrak MCU terlebih dahulu.');
    }

    try {
        const menus = await postgrestRows<MenuRow>(
            token,
            'v_my_menus',
            { select: '*', order: 'sort_order.asc,menu_id.asc' },
            'api',
        );

        if (!menuAllows(menus, uploadReports[type].legacyUrl, action)) {
            return problem('forbidden', 'Anda tidak berwenang mengunggah hasil ini.');
        }
    } catch (error) {
        console.error('Gagal memeriksa permission upload.', error);
        return problem('bad_gateway', 'Layanan data sedang tidak tersedia.');
    }

    return { session, token };
}
