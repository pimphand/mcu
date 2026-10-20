import { describe, expect, it } from 'vitest';
import { validateMasterForm } from './master-validation';
import { signatureBasename } from './signature';

function form(entries: Record<string, string>): FormData {
    const data = new FormData();

    for (const [key, value] of Object.entries(entries)) {
        data.set(key, value);
    }

    return data;
}

describe('validasi master data', () => {
    it('menerima client tanpa kode karena kode dibuat database', () => {
        const result = validateMasterForm(
            'client',
            form({ name: ' PT Contoh ', address: 'Jakarta', code: 'C99999' }),
            'create',
        );

        expect(result).toEqual({
            ok: true,
            values: { name: 'PT Contoh', address: 'Jakarta' },
        });
    });

    it('menolak client tanpa nama', () => {
        const result = validateMasterForm('client', form({ name: '  ' }), 'create');

        expect(result).toEqual({ ok: false, message: 'Nama wajib diisi.' });
    });

    it('mengubah client_id pilihan menjadi angka dan mewajibkannya pada kontrak', () => {
        const valid = validateMasterForm(
            'contract',
            form({ client_id: '12', name: 'Kontrak Tahunan' }),
            'create',
        );

        expect(valid).toEqual({
            ok: true,
            values: { client_id: 12, name: 'Kontrak Tahunan' },
        });

        const missing = validateMasterForm('contract', form({ name: 'Kontrak' }), 'create');

        expect(missing.ok).toBe(false);
    });

    it('menyimpan tanggal kosong sebagai null', () => {
        const result = validateMasterForm(
            'employee',
            form({ client_id: '1', nik: '320101', nama: 'dr. Ayu', tanggal_lahir: '' }),
            'create',
        );

        expect(result.ok).toBe(true);
        expect(result.ok && result.values.tanggal_lahir).toBeNull();
    });

    it('mewajibkan password saat membuat user dan mengabaikannya saat edit kosong', () => {
        const created = validateMasterForm(
            'user',
            form({
                role_id: '2',
                client_id: '3',
                name: 'Admin Client',
                username: 'admin3',
                email: 'admin3@example.com',
                password: 'rahasia123',
                is_active: 'true',
            }),
            'create',
        );

        expect(created).toEqual({
            ok: true,
            values: {
                role_id: 2,
                client_id: 3,
                name: 'Admin Client',
                username: 'admin3',
                email: 'admin3@example.com',
                password: 'rahasia123',
                is_active: true,
            },
        });

        const updated = validateMasterForm(
            'user',
            form({
                role_id: '2',
                client_id: '3',
                name: 'Admin Client',
                username: 'admin3',
                email: 'admin3@example.com',
                password: '',
                is_active: 'true',
            }),
            'update',
        );

        expect(updated.ok).toBe(true);
        expect(updated.ok && updated.values).not.toHaveProperty('password');
    });

    it('menolak password yang terlalu pendek dan email tidak valid', () => {
        const shortPassword = validateMasterForm(
            'user',
            form({
                role_id: '2',
                name: 'Admin',
                username: 'admin',
                email: 'admin@example.com',
                password: 'pendek',
            }),
            'create',
        );
        const badEmail = validateMasterForm(
            'user',
            form({
                role_id: '2',
                name: 'Admin',
                username: 'admin',
                email: 'bukan-email',
                password: 'rahasia123',
            }),
            'create',
        );

        expect(shortPassword.ok).toBe(false);
        expect(badEmail.ok).toBe(false);
    });

    it('mengosongkan induk menu menjadi null dan mewajibkan urutan', () => {
        const result = validateMasterForm(
            'menu',
            form({
                parent_id: '',
                sort_order: '12',
                name: 'Master Baru',
                icon: 'circle',
                url: '/baru',
                is_active: 'true',
            }),
            'create',
        );

        expect(result).toEqual({
            ok: true,
            values: {
                parent_id: null,
                sort_order: 12,
                name: 'Master Baru',
                icon: 'circle',
                url: '/baru',
                is_active: true,
            },
        });

        const missingSort = validateMasterForm(
            'menu',
            form({ name: 'Tanpa Urutan', is_active: 'true' }),
            'create',
        );

        expect(missingSort.ok).toBe(false);
    });
});

describe('nama berkas tanda tangan', () => {
    it('mengambil basename dari path legacy', () => {
        expect(signatureBasename('ttd/dr_dini.png')).toBe('dr_dini.png');
        expect(signatureBasename('dr_dini.png')).toBe('dr_dini.png');
    });

    it('menolak nama yang tidak aman', () => {
        expect(signatureBasename('..')).toBeNull();
        expect(signatureBasename('.hidden.png')).toBeNull();
        expect(signatureBasename('nama dengan spasi.png')).toBeNull();
    });
});
