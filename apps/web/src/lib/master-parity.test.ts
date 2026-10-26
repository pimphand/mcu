import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { describe, expect, it } from 'vitest';
import { masterConfig, type MasterKind } from './master-data';

/**
 * Paritas fase 4 dengan halaman Blade legacy.
 *
 * Repo ini masih menyimpan aplikasi Laravel lama di root (`resources/views`,
 * `app/Http/Controllers`) sebagai referensi read-only. Test ini membaca
 * sumbernya langsung, jadi bila field atau aturan wajib di legacy berubah,
 * test akan gagal sampai konfigurasi master di Astro ikut disesuaikan.
 *
 * Yang dibandingkan hanya kontrak input: nama field form tambah dan aturan
 * `required` di controller. Tampilan tabel versi baru memang lebih ringkas
 * (detail lengkap ada di modal ubah), jadi kolom tabel hanya diuji untuk
 * kolom identitas yang dipakai legacy untuk mengenali baris.
 */

const ROOT = new URL('../../../../', import.meta.url);

function readRepoFile(...segments: string[]): string {
    return readFileSync(fileURLToPath(new URL(segments.join('/'), ROOT)), 'utf8');
}

type Kind = Extract<MasterKind, 'client' | 'contract' | 'department' | 'divisi' | 'employee'>;

const KINDS: Kind[] = ['client', 'contract', 'department', 'divisi', 'employee'];

const CONTROLLERS: Record<Kind, string> = {
    client: 'ClientController',
    contract: 'ContractController',
    department: 'DepartmentController',
    divisi: 'DivisiController',
    employee: 'EmployeeController',
};

/** Kolom yang dipakai legacy untuk mengenali satu baris di daftar. */
const LEGACY_IDENTITY_COLUMNS: Record<Kind, string[]> = {
    client: ['code', 'name'],
    contract: ['code', 'name'],
    department: ['code', 'name'],
    divisi: ['code', 'name'],
    employee: ['code', 'nik', 'nama'],
};

function createForm(blade: string): string {
    const start = blade.indexOf('<form');
    const end = blade.lastIndexOf('</form>');

    return start === -1 || end === -1 ? '' : blade.slice(start, end);
}

/** Nama field input/select/textarea di form tambah legacy. */
function legacyFormFields(kind: Kind): string[] {
    const form = createForm(readRepoFile('resources/views/pages', kind, 'create.blade.php'));
    const names = new Set<string>();

    for (const match of form.matchAll(/name="([a-zA-Z_]+)"/g)) {
        const name = match[1]!;

        // Token CSRF dan method spoofing bukan data form.
        if (name.startsWith('_')) continue;

        names.add(name);
    }

    return [...names];
}

/** Aturan `required` pada blok validate() pertama (method store) legacy. */
function legacyRequiredFields(kind: Kind): string[] {
    const source = readRepoFile('app/Http/Controllers', `${CONTROLLERS[kind]}.php`);
    const start = source.indexOf('->validate([');
    const end = start === -1 ? -1 : source.indexOf(']);', start);

    if (start === -1 || end === -1) return [];

    const block = source.slice(start, end);

    return [...block.matchAll(/'([a-z_]+)'\s*=>\s*'([^']*)'/g)]
        .filter((match) => match[2]!.split('|').includes('required'))
        .map((match) => match[1]!);
}

describe('paritas master data dengan halaman Blade legacy', () => {
    it.each(KINDS)('semua field form tambah %s ada di konfigurasi Astro', (kind) => {
        const config = masterConfig(kind);
        const keys = new Set(config.fields.map((field) => field.key));
        const legacy = legacyFormFields(kind);

        expect(legacy.length).toBeGreaterThan(0);

        for (const field of legacy) {
            expect(keys, `field legacy "${field}" tidak ada di ${kind}`).toContain(field);
        }
    });

    it.each(KINDS)('field wajib di controller %s tetap wajib di form baru', (kind) => {
        const config = masterConfig(kind);
        const required = new Set(
            config.fields
                .filter((field) => field.required || field.requiredOnCreate)
                .map((field) => field.key),
        );
        const legacy = legacyRequiredFields(kind);

        expect(legacy.length).toBeGreaterThan(0);

        for (const field of legacy) {
            // `ttd` legacy wajib saat tambah, opsional saat ubah.
            expect(required, `"${field}" wajib di legacy tetapi opsional di ${kind}`).toContain(
                field,
            );
        }
    });

    it.each(KINDS)('kolom identitas daftar %s tersedia di tabel baru', (kind) => {
        const columns = new Set(masterConfig(kind).columns.map((column) => column.key));

        for (const column of LEGACY_IDENTITY_COLUMNS[kind]) {
            expect(columns).toContain(column);
        }
    });

    it('daftar client menampilkan kontrak seperti tabel bersarang legacy', () => {
        const columns = masterConfig('client').columns.map((column) => column.key);

        expect(columns).toContain('contract_names');
    });
});
