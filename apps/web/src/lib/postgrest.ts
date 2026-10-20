import { env } from './env';

/**
 * Only these resources may be reached through the BFF proxy. Keep this list
 * explicit; never proxy arbitrary paths or expose the users table.
 */
export const ALLOWED_RESOURCES = new Set([
    'roles',
    'menus',
    'permissions',
    'clients',
    'contracts',
    'departments',
    'divisis',
    'employees',
    'participants',
    'tanda_vitals',
    'pemeriksaan_fisiks',
    'laboratoria',
    'radiologis',
    'spirometris',
    'audiometris',
    'rectals',
    'ekgs',
    'validate_doctors',
    'general_results',
    'import_jobs',
    'rpc',
    'v_participant_list',
    'v_mcu_progress',
    'v_dashboard_summary',
    'v_my_menus',
    'v_lab_listing',
    'v_radiologi_listing',
    'v_validate_doctor_listing',
    'v_general_result_listing',
]);

export const ALLOWED_RPC = new Set(['register_participant', 'register_all']);

/**
 * View ringkas untuk UI. Sisanya (tabel dan RPC) berada di schema `public`.
 */
export const API_VIEWS = new Set([
    'v_participant_list',
    'v_mcu_progress',
    'v_dashboard_summary',
    'v_my_menus',
    'v_lab_listing',
    'v_radiologi_listing',
    'v_validate_doctor_listing',
    'v_general_result_listing',
]);

/**
 * PostgREST me-resolve nama tanpa schema hanya di schema pertama dari
 * `db-schemas` (default `api`), sehingga tabel `public` harus diminta lewat
 * header `Accept-Profile`/`Content-Profile`. BFF yang menentukannya supaya
 * browser tidak bisa memaksa schema lain.
 */
export function profileForResource(parts: string[]): 'api' | 'public' {
    return parts[0] && API_VIEWS.has(parts[0]) ? 'api' : 'public';
}

/**
 * View yang wajib mengikuti konteks MCU di klaim sesi. Legacy membatasi daftar
 * peserta dengan `client_id` + `contract_id` dari session (ParticipantService::paginate),
 * jadi scope-nya ditempel di BFF — bukan diambil dari query string browser —
 * supaya admin pun tidak bisa melihat/meregister peserta kontrak lain.
 */
export const SCOPED_RESOURCES = new Set([
    'v_participant_list',
    'v_mcu_progress',
    'v_lab_listing',
    'v_radiologi_listing',
    'v_validate_doctor_listing',
    'v_general_result_listing',
]);

export function applySessionScope(
    parts: string[],
    url: URL,
    claims: { app_role: string; client_id: number | null; contract_id: number | null },
): void {
    if (!parts[0] || !SCOPED_RESOURCES.has(parts[0])) {
        return;
    }

    if (claims.client_id !== null) {
        url.searchParams.set('client_id', `eq.${claims.client_id}`);
    }

    if (claims.contract_id !== null) {
        url.searchParams.set('contract_id', `eq.${claims.contract_id}`);
        return;
    }

    // Tanpa kontrak aktif, role operasional tidak punya data MCU (legacy
    // memblokir di `McuMiddleware`). Peserta tetap memakai RLS `user_id`.
    if (claims.app_role !== 'participant') {
        url.searchParams.set('contract_id', 'is.null');
    }
}

const SEGMENT_PATTERN = /^[a-z_][a-z0-9_]*$/i;

export function parseResourcePath(path: string | undefined): string[] | null {
    const parts = (path ?? '').split('/').filter(Boolean);

    if (parts.length === 0 || parts.length > 2) {
        return null;
    }

    if (!parts.every((part) => SEGMENT_PATTERN.test(part))) {
        return null;
    }

    const [resource, action] = parts;

    if (!resource || !ALLOWED_RESOURCES.has(resource)) {
        return null;
    }

    if (resource === 'rpc') {
        return action && ALLOWED_RPC.has(action) ? parts : null;
    }

    return parts.length === 1 ? parts : null;
}

export function buildPostgrestUrl(parts: string[], search: string): URL {
    if (!env.POSTGREST_URL) {
        throw new Error('POSTGREST_URL belum dikonfigurasi.');
    }

    const url = new URL(env.POSTGREST_URL);
    const basePath = url.pathname.replace(/\/+$/, '');
    url.pathname = `${basePath}/${parts.join('/')}`;
    url.search = search;

    return url;
}
