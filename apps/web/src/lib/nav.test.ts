import { describe, expect, it } from 'vitest';
import { appHrefFor, buildNav, flatNav, iconFromLegacy, menuAllows, type MenuRow } from './nav';

function row(partial: Partial<MenuRow> & Pick<MenuRow, 'menu_id' | 'name'>): MenuRow {
    return {
        parent_id: null,
        url: null,
        icon: null,
        sort_order: 0,
        is_active: true,
        is_view: true,
        is_add: true,
        is_edit: true,
        is_delete: true,
        ...partial,
    };
}

const rows: MenuRow[] = [
    row({ menu_id: 1, name: 'Home', url: '/', icon: 'home', sort_order: 1 }),
    row({ menu_id: 2, name: 'MCU IN/OUT', url: '/mcu-in-out', icon: 'log-in', sort_order: 2 }),
    row({ menu_id: 3, name: 'MCU', url: '#', icon: 'folder-plus', sort_order: 3 }),
    row({ menu_id: 7, name: 'Data Peserta', parent_id: 3, url: '/participant', sort_order: 7 }),
    row({
        menu_id: 11,
        name: 'Print Peserta',
        parent_id: 3,
        url: '/participant-print',
        sort_order: 11,
        is_active: false,
    }),
    row({ menu_id: 4, name: 'Laporan', url: '#', icon: 'file-text', sort_order: 4 }),
];

describe('permission tombol dari v_my_menus', () => {
    const menus: MenuRow[] = [
        row({
            menu_id: 7,
            name: 'Data Peserta',
            url: '/participant',
            is_add: true,
            is_edit: false,
        }),
        row({ menu_id: 8, name: 'Register', url: '/participant-register', is_add: false }),
    ];

    it('membaca flag permission per aksi', () => {
        expect(menuAllows(menus, '/participant', 'view')).toBe(true);
        expect(menuAllows(menus, '/participant', 'add')).toBe(true);
        expect(menuAllows(menus, '/participant', 'edit')).toBe(false);
        expect(menuAllows(menus, '/participant', 'delete')).toBe(true);
        expect(menuAllows(menus, '/participant-register', 'add')).toBe(false);
    });

    it('menolak menu yang tidak terlihat oleh role tersebut', () => {
        expect(menuAllows(menus, '/role', 'view')).toBe(false);
    });
});

describe('sidebar dari v_my_menus', () => {
    it('memetakan route legacy yang sudah diport ke route Astro', () => {
        expect(appHrefFor('/')).toBe('/app');
        expect(appHrefFor('/participant')).toBe('/app/participant');
        expect(appHrefFor('/participant-print-mcu')).toBe('/app/print/mcu');
        expect(appHrefFor('/participant-print-identitas')).toBe('/app/print/identitas');
        expect(appHrefFor('/participant-print')).toBeNull();
        expect(appHrefFor('/client')).toBe('/app/master/client');
        expect(appHrefFor('/department')).toBe('/app/master/department');
        expect(appHrefFor('/divisi')).toBe('/app/master/divisi');
        expect(appHrefFor('/employee')).toBe('/app/master/employee');
        expect(appHrefFor('/role')).toBe('/app/master/role');
        expect(appHrefFor('/user')).toBe('/app/master/user');
        expect(appHrefFor('/menu')).toBe('/app/master/menu');
        expect(appHrefFor('/participant-sticker')).toBeNull();
        expect(appHrefFor(null)).toBeNull();
    });

    it('memetakan ikon Feather legacy dan memakai titik sebagai cadangan', () => {
        expect(iconFromLegacy('log-in')).toBe('login');
        expect(iconFromLegacy('folder-plus')).toBe('folder');
        expect(iconFromLegacy('circle')).toBe('dot');
        expect(iconFromLegacy('tidak-dikenal')).toBe('dot');
        expect(iconFromLegacy(null)).toBe('dot');
    });

    it('menyusun root tanpa anak sebagai tautan dan root beranak sebagai grup', () => {
        const entries = buildNav(rows);

        // 'Laporan' tidak punya menu terlihat, jadi tidak dirender.
        expect(entries.map((entry) => entry.kind)).toEqual(['link', 'link', 'group']);
        expect(entries[0]).toEqual({
            kind: 'link',
            item: { label: 'Home', href: '/app', legacyUrl: '/', icon: 'home' },
        });
        expect(entries[1]).toMatchObject({ kind: 'link', item: { href: '/app/mcu-in-out' } });

        const mcu = entries[2];
        expect(mcu).toMatchObject({ kind: 'group', title: 'MCU', icon: 'folder' });
        expect(mcu?.kind === 'group' ? mcu.items : null).toEqual([
            {
                label: 'Data Peserta',
                href: '/app/participant',
                legacyUrl: '/participant',
                icon: 'dot',
            },
        ]);
    });

    it('menyembunyikan menu is_active=false beserta turunannya', () => {
        const entries = buildNav([
            row({ menu_id: 1, name: 'Home', url: '/' }),
            row({ menu_id: 3, name: 'MCU', url: '#' }),
            row({ menu_id: 7, name: 'Data Peserta', parent_id: 3, url: '/participant' }),
            row({
                menu_id: 11,
                name: 'Print Peserta',
                parent_id: 3,
                url: '/participant-print',
                is_active: false,
            }),
            row({ menu_id: 4, name: 'Laporan', url: '#', is_active: false }),
            row({ menu_id: 22, name: 'Rekap', parent_id: 4, url: '/report-recap-register' }),
        ]);

        expect(
            entries.map((entry) => (entry.kind === 'link' ? entry.item.label : entry.title)),
        ).toEqual(['Home', 'MCU']);

        const mcu = entries[1];
        expect(mcu?.kind === 'group' ? mcu.items.map((item) => item.label) : null).toEqual([
            'Data Peserta',
        ]);

        expect(entries.some((entry) => entry.kind === 'group' && entry.title === 'Laporan')).toBe(
            false,
        );
    });

    it('mengurutkan menu sesuai sort_order dan melewati grup kosong', () => {
        const entries = buildNav([
            row({ menu_id: 3, name: 'MCU', url: '#', icon: 'folder-plus', sort_order: 30 }),
            row({ menu_id: 4, name: 'Laporan', url: '#', sort_order: 40 }),
            row({ menu_id: 7, name: 'Data Peserta', parent_id: 3, url: '/participant' }),
            row({ menu_id: 1, name: 'Home', url: '/', sort_order: 10 }),
        ]);

        expect(
            entries.map((entry) => (entry.kind === 'link' ? entry.item.label : entry.title)),
        ).toEqual(['Home', 'MCU']);
    });

    it('menyediakan item datar hanya untuk halaman yang sudah ada', () => {
        expect(flatNav(buildNav(rows))).toEqual([
            { label: 'Home', href: '/app', legacyUrl: '/', icon: 'home' },
            {
                label: 'MCU IN/OUT',
                href: '/app/mcu-in-out',
                legacyUrl: '/mcu-in-out',
                icon: 'login',
            },
            {
                label: 'Data Peserta',
                href: '/app/participant',
                legacyUrl: '/participant',
                icon: 'dot',
            },
        ]);
    });
});
