import type { IconName } from './icons';

/**
 * Sidebar dibaca dari `api.v_my_menus` (migrasi `0011_my_menus.sql`): menu +
 * permission milik role user yang login. Label, urutan, ikon, dan hak akses
 * diatur lewat tabel `menus`/`permissions` (migrasi `0012_menu_registry.sql`),
 * jadi menambah menu tidak perlu mengubah kode.
 */

/** Satu baris `api.v_my_menus` (menu + permission milik role user). */
export type MenuRow = {
    menu_id: number;
    parent_id: number | null;
    name: string;
    url: string | null;
    icon: string | null;
    sort_order: number;
    is_active: boolean;
    is_view: boolean;
    is_add: boolean;
    is_edit: boolean;
    is_delete: boolean;
};

/** Aksi yang dicek terhadap kolom permission `is_*` di `permissions`. */
export type MenuAction = 'view' | 'add' | 'edit' | 'delete';

const ACTION_COLUMN = {
    view: 'is_view',
    add: 'is_add',
    edit: 'is_edit',
    delete: 'is_delete',
} as const satisfies Record<MenuAction, keyof MenuRow>;

export type NavItem = {
    label: string;
    /** Route Astro bila halaman sudah diport; `null` berarti belum tersedia. */
    href: string | null;
    /** URL legacy, disimpan untuk penelusuran paritas. */
    legacyUrl: string | null;
    icon: IconName;
};

export type NavEntry =
    | { kind: 'link'; item: NavItem }
    | { kind: 'group'; title: string; icon?: IconName; items: NavItem[] };

/**
 * Route legacy → route Astro. Hanya halaman yang sudah ada di `apps/web` yang
 * dipetakan; sisanya tampil sebagai "soon". Tambahkan entri di sini setiap
 * halaman hasil port siap dipakai.
 */
const APP_ROUTES: Record<string, string> = {
    '/': '/app',
    '/mcu-in-out': '/app/mcu-in-out',
    '/participant': '/app/participant',
    '/participant-register': '/app/participant/register',
    '/participant-print-mcu': '/app/print/mcu',
    '/participant-print-identitas': '/app/print/identitas',
    '/participant-upload-validasi-dokter': '/app/upload/validate-dokter',
    '/participant-upload-laboratorium': '/app/upload/laboratorium',
    '/participant-upload-radiologi': '/app/upload/radiologi',
    '/participant-upload-umum': '/app/upload/umum',
    '/department': '/app/master/department',
    '/divisi': '/app/master/divisi',
    '/employee': '/app/master/employee',
    '/client': '/app/master/client',
    '/role': '/app/master/role',
    '/user': '/app/master/user',
    '/menu': '/app/master/menu',
};

/** Nama ikon Feather legacy (`menus.icon`) → ikon sprite Astro. */
const ICON_ALIASES: Record<string, IconName> = {
    home: 'home',
    'log-in': 'login',
    login: 'login',
    logout: 'logout',
    users: 'users',
    user: 'users',
    circle: 'dot',
    dot: 'dot',
    list: 'menu',
    menu: 'menu',
    'folder-plus': 'folder',
    folder: 'folder',
    'file-text': 'file',
    file: 'file',
    'file-up': 'upload',
    'file-heart': 'file',
    'file-signature': 'file',
    upload: 'upload',
    printer: 'printer',
    database: 'database',
    monitor: 'shield',
    shield: 'shield',
    'clipboard-check': 'clipboard',
    clipboard: 'clipboard',
    activity: 'pulse',
    'heart-pulse': 'pulse',
    pulse: 'pulse',
    building: 'building',
};

/** Ikon tak dikenal memakai titik, sama seperti menu legacy ber-ikon `circle`. */
export function iconFromLegacy(icon?: string | null): IconName {
    return (icon && ICON_ALIASES[icon]) || 'dot';
}

/** `null` bila halaman legacy itu belum diport ke Astro. */
export function appHrefFor(legacyUrl?: string | null): string | null {
    if (!legacyUrl) return null;

    return APP_ROUTES[legacyUrl] ?? null;
}

/**
 * Cek tombol aksi terhadap permission menu (URL legacy), mis.
 * `menuAllows(menus, '/participant', 'edit')`. Menu yang tidak terlihat oleh
 * role tersebut otomatis `false` karena tidak ada barisnya di `v_my_menus`.
 *
 * Ini hanya menyembunyikan tombol; server tetap memeriksa RLS dan klaim sesi.
 */
export function menuAllows(rows: MenuRow[], legacyUrl: string, action: MenuAction): boolean {
    const row = rows.find((item) => item.url === legacyUrl);

    return row ? row[ACTION_COLUMN[action]] : false;
}

function toItem(row: MenuRow): NavItem {
    const legacyUrl = row.url && row.url !== '#' ? row.url : null;
    // Hanya menu aktif yang sampai ke sini; menu aktif yang halamannya belum
    // diport tetap tampil sebagai "soon" (href null).
    return {
        label: row.name,
        href: appHrefFor(legacyUrl),
        legacyUrl,
        icon: iconFromLegacy(row.icon),
    };
}

function byOrder(a: MenuRow, b: MenuRow): number {
    return a.sort_order - b.sort_order || a.menu_id - b.menu_id;
}

/**
 * Susun baris `v_my_menus` menjadi sidebar. Root yang punya anak menjadi grup
 * berjudul (MCU, Laporan, ...), sedangkan root tanpa anak (Home, MCU IN/OUT)
 * menjadi tautan tunggal — sama seperti sidebar legacy.
 *
 * `is_active = false` berarti menu disembunyikan sama sekali (bukan "soon").
 * Anak dari induk yang non-aktif ikut disembunyikan karena tidak punya tempat
 * tampil di sidebar.
 */
export function buildNav(rows: MenuRow[]): NavEntry[] {
    const active = rows.filter((row) => row.is_active);
    const activeIds = new Set(active.map((row) => row.menu_id));
    const roots = active.filter((row) => row.parent_id === null).sort(byOrder);
    const childrenByParent = new Map<number, MenuRow[]>();

    for (const row of active) {
        if (row.parent_id === null || !activeIds.has(row.parent_id)) continue;

        const children = childrenByParent.get(row.parent_id) ?? [];
        children.push(row);
        childrenByParent.set(row.parent_id, children);
    }

    const entries: NavEntry[] = [];

    for (const root of roots) {
        const children = (childrenByParent.get(root.menu_id) ?? []).sort(byOrder);

        if (children.length > 0) {
            entries.push({
                kind: 'group',
                title: root.name,
                icon: root.icon ? iconFromLegacy(root.icon) : undefined,
                items: children.map(toItem),
            });
            continue;
        }

        // Root tanpa anak = tautan tunggal (Home, MCU IN/OUT). Root ber-url
        // '#' yang tidak punya menu terlihat dilewati agar tidak ada grup kosong.
        if (root.url && root.url !== '#') {
            entries.push({ kind: 'link', item: toItem(root) });
        }
    }

    return entries;
}

/** Item datar untuk navigasi seluler (hanya yang sudah punya halaman). */
export function flatNav(entries: NavEntry[]): NavItem[] {
    return entries
        .flatMap((entry) => (entry.kind === 'link' ? [entry.item] : entry.items))
        .filter((item) => item.href);
}
