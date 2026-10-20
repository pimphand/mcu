/**
 * Konfigurasi halaman master data. Satu sumber dipakai oleh halaman, tabel,
 * form, dan endpoint supaya nama kolom/field tidak berbeda antar lapisan.
 */

export type MasterKind =
    'client' | 'contract' | 'department' | 'divisi' | 'employee' | 'user' | 'role' | 'menu';

export type MasterFieldType =
    'text' | 'textarea' | 'email' | 'number' | 'date' | 'select' | 'boolean' | 'password' | 'file';

export type MasterField = {
    key: string;
    label: string;
    type?: MasterFieldType;
    required?: boolean;
    /** Hanya wajib saat membuat baris baru (mis. password user). */
    requiredOnCreate?: boolean;
    maxLength?: number;
    min?: number;
    max?: number;
    step?: number;
    placeholder?: string;
    help?: string;
    /** Sumber opsi select yang diambil server-side. */
    optionSource?: 'clients' | 'roles' | 'rootMenus';
    /** Opsi statis untuk select. */
    options?: Array<{ value: string; label: string }>;
    /** Setengah lebar pada layar besar. */
    half?: boolean;
};

export type MasterColumn = {
    key: string;
    label: string;
    format?: 'date' | 'datetime' | 'boolean';
};

export type MasterConfig = {
    kind: MasterKind;
    title: string;
    description: string;
    path: string;
    /** URL menu legacy untuk cek permission `is_view/is_add/...`. */
    legacyUrl: string;
    resource: string;
    /** `users` memakai endpoint server karena tabel users tidak diekspos. */
    mode: 'postgrest' | 'users';
    searchColumns: string[];
    defaultSort: string;
    sortOptions: Array<{ value: string; label: string }>;
    columns: MasterColumn[];
    fields: MasterField[];
    /** Kontrak memakai soft delete (`deleted_at`). */
    softDelete?: boolean;
    /** Field file tanda tangan (karyawan). */
    signature?: boolean;
};

/** Satu baris master data (hasil PostgREST atau query service). */
export type MasterRow = Record<string, unknown> & { id: number };

export type MasterOption = { value: string; label: string };

export type MasterOptions = {
    clients: MasterOption[];
    roles: MasterOption[];
    rootMenus: MasterOption[];
};

const nameField = (key = 'name', label = 'Nama'): MasterField => ({
    key,
    label,
    required: true,
    maxLength: 255,
});

const clientOption: MasterField = {
    key: 'client_id',
    label: 'Client',
    type: 'select',
    optionSource: 'clients',
    required: true,
    half: true,
    help: 'Daftar mengikuti client yang boleh diakses role Anda.',
};

export const masterConfigs: Record<MasterKind, MasterConfig> = {
    client: {
        kind: 'client',
        title: 'Master Client',
        description: 'Data perusahaan/client pemakai layanan MCU.',
        path: '/app/master/client',
        legacyUrl: '/client',
        resource: 'clients',
        mode: 'postgrest',
        searchColumns: ['code', 'name'],
        defaultSort: 'created_at.desc',
        sortOptions: [
            { value: 'created_at.desc', label: 'Terbaru' },
            { value: 'name.asc', label: 'Nama (A–Z)' },
            { value: 'code.asc', label: 'Kode (A–Z)' },
        ],
        columns: [
            { key: 'code', label: 'Kode' },
            { key: 'name', label: 'Nama' },
            { key: 'address', label: 'Alamat' },
            { key: 'created_at', label: 'Dibuat', format: 'datetime' },
        ],
        fields: [nameField(), { key: 'address', label: 'Alamat', type: 'textarea' }],
    },
    contract: {
        kind: 'contract',
        title: 'Master Kontrak',
        description: 'Kontrak per client. Kontrak yang dihapus tidak tampil di pilihan MCU IN/OUT.',
        path: '/app/master/contract',
        legacyUrl: '/client',
        resource: 'contracts',
        mode: 'postgrest',
        searchColumns: ['code', 'name'],
        defaultSort: 'created_at.desc',
        sortOptions: [
            { value: 'created_at.desc', label: 'Terbaru' },
            { value: 'name.asc', label: 'Nama (A–Z)' },
            { value: 'code.asc', label: 'Kode (A–Z)' },
        ],
        columns: [
            { key: 'code', label: 'Kode' },
            { key: 'name', label: 'Nama' },
            { key: 'client_name', label: 'Client' },
            { key: 'created_at', label: 'Dibuat', format: 'datetime' },
        ],
        fields: [clientOption, nameField()],
        softDelete: true,
    },
    department: {
        kind: 'department',
        title: 'Master Department',
        description: 'Department per client untuk data peserta.',
        path: '/app/master/department',
        legacyUrl: '/department',
        resource: 'departments',
        mode: 'postgrest',
        searchColumns: ['code', 'name'],
        defaultSort: 'name.asc',
        sortOptions: [
            { value: 'name.asc', label: 'Nama (A–Z)' },
            { value: 'code.asc', label: 'Kode (A–Z)' },
            { value: 'created_at.desc', label: 'Terbaru' },
        ],
        columns: [
            { key: 'code', label: 'Kode' },
            { key: 'name', label: 'Nama' },
            { key: 'client_name', label: 'Client' },
        ],
        fields: [clientOption, nameField()],
    },
    divisi: {
        kind: 'divisi',
        title: 'Master Divisi',
        description: 'Divisi per client untuk data peserta dan laporan.',
        path: '/app/master/divisi',
        legacyUrl: '/divisi',
        resource: 'divisis',
        mode: 'postgrest',
        searchColumns: ['code', 'name'],
        defaultSort: 'name.asc',
        sortOptions: [
            { value: 'name.asc', label: 'Nama (A–Z)' },
            { value: 'code.asc', label: 'Kode (A–Z)' },
            { value: 'created_at.desc', label: 'Terbaru' },
        ],
        columns: [
            { key: 'code', label: 'Kode' },
            { key: 'name', label: 'Nama' },
            { key: 'client_name', label: 'Client' },
        ],
        fields: [clientOption, nameField()],
    },
    employee: {
        kind: 'employee',
        title: 'Master Karyawan',
        description: 'Petugas/pemeriksa MCU beserta tanda tangan untuk cetak hasil.',
        path: '/app/master/employee',
        legacyUrl: '/employee',
        resource: 'employees',
        mode: 'postgrest',
        searchColumns: ['code', 'nama', 'nik'],
        defaultSort: 'nama.asc',
        sortOptions: [
            { value: 'nama.asc', label: 'Nama (A–Z)' },
            { value: 'code.asc', label: 'Kode (A–Z)' },
            { value: 'created_at.desc', label: 'Terbaru' },
        ],
        columns: [
            { key: 'code', label: 'Kode' },
            { key: 'nama', label: 'Nama' },
            { key: 'nik', label: 'NIK' },
            { key: 'profesi', label: 'Profesi' },
            { key: 'telp', label: 'Telepon' },
            { key: 'client_name', label: 'Client' },
        ],
        fields: [
            clientOption,
            { key: 'nik', label: 'NIK', required: true, half: true, maxLength: 50 },
            { key: 'nama', label: 'Nama', required: true, half: true, maxLength: 255 },
            {
                key: 'jenis_kelamin',
                label: 'Jenis kelamin',
                type: 'select',
                half: true,
                options: [
                    { value: 'L', label: 'Laki-laki' },
                    { value: 'P', label: 'Perempuan' },
                ],
            },
            { key: 'tanggal_lahir', label: 'Tanggal lahir', type: 'date', half: true },
            { key: 'tempat_lahir', label: 'Tempat lahir', half: true },
            { key: 'agama', label: 'Agama', half: true },
            { key: 'status', label: 'Status', half: true },
            { key: 'golongan_darah', label: 'Golongan darah', half: true },
            { key: 'rhesus', label: 'Rhesus', half: true },
            { key: 'pendidikan', label: 'Pendidikan', half: true },
            { key: 'telp', label: 'Telepon', half: true },
            { key: 'email', label: 'Email', type: 'email', half: true },
            { key: 'unit', label: 'Unit', half: true },
            { key: 'jabatan', label: 'Jabatan', half: true },
            { key: 'profesi', label: 'Profesi', half: true },
            { key: 'profesi_detail', label: 'Detail profesi', half: true },
            { key: 'warga_negara', label: 'Warga negara', half: true },
            { key: 'nama_bank', label: 'Nama bank', half: true },
            { key: 'nomor_rekening', label: 'Nomor rekening', half: true },
            { key: 'alamat', label: 'Alamat', type: 'textarea' },
            {
                key: 'ttd',
                label: 'Tanda tangan',
                type: 'file',
                help: 'PNG/JPG maksimal 2 MB. Gambar disimpan ulang (resize) dengan nama acak.',
            },
        ],
        signature: true,
    },
    user: {
        kind: 'user',
        title: 'Master User',
        description: 'Akun administrator, admin client, dan karyawan.',
        path: '/app/master/user',
        legacyUrl: '/user',
        resource: 'users',
        mode: 'users',
        searchColumns: ['name', 'username', 'email'],
        defaultSort: 'created_at.desc',
        sortOptions: [
            { value: 'created_at.desc', label: 'Terbaru' },
            { value: 'name.asc', label: 'Nama (A–Z)' },
            { value: 'username.asc', label: 'Username (A–Z)' },
        ],
        columns: [
            { key: 'name', label: 'Nama' },
            { key: 'username', label: 'Username' },
            { key: 'email', label: 'Email' },
            { key: 'role_name', label: 'Role' },
            { key: 'client_name', label: 'Client' },
            { key: 'is_active', label: 'Aktif', format: 'boolean' },
        ],
        fields: [
            {
                key: 'role_id',
                label: 'Role',
                type: 'select',
                optionSource: 'roles',
                required: true,
                half: true,
            },
            {
                key: 'client_id',
                label: 'Client',
                type: 'select',
                optionSource: 'clients',
                half: true,
                help: 'Kosongkan untuk akun klinik yang boleh memilih semua client.',
            },
            { key: 'name', label: 'Nama', required: true, half: true },
            { key: 'username', label: 'Username', required: true, half: true },
            { key: 'email', label: 'Email', type: 'email', required: true, half: true },
            {
                key: 'password',
                label: 'Password',
                type: 'password',
                requiredOnCreate: true,
                half: true,
                help: 'Saat edit, kosongkan bila password tidak diubah.',
            },
            {
                key: 'is_active',
                label: 'Aktif',
                type: 'boolean',
                half: true,
            },
        ],
    },
    role: {
        kind: 'role',
        title: 'Master Role',
        description: 'Role menentukan menu dan tombol yang terlihat. Matriks izin diatur per role.',
        path: '/app/master/role',
        legacyUrl: '/role',
        resource: 'roles',
        mode: 'postgrest',
        searchColumns: ['name'],
        defaultSort: 'level.asc',
        sortOptions: [
            { value: 'level.asc', label: 'Level (1–4)' },
            { value: 'name.asc', label: 'Nama (A–Z)' },
        ],
        columns: [
            { key: 'level', label: 'Level' },
            { key: 'name', label: 'Nama' },
            { key: 'is_active', label: 'Aktif', format: 'boolean' },
        ],
        fields: [
            {
                key: 'level',
                label: 'Level',
                type: 'number',
                required: true,
                half: true,
                min: 1,
                max: 99,
            },
            { key: 'is_active', label: 'Aktif', type: 'boolean', half: true },
            { key: 'name', label: 'Nama', required: true },
        ],
    },
    menu: {
        kind: 'menu',
        title: 'Master Menu',
        description: 'Menu sidebar dua level. Hanya menu aktif yang tampil untuk role terkait.',
        path: '/app/master/menu',
        legacyUrl: '/menu',
        resource: 'menus',
        mode: 'postgrest',
        searchColumns: ['name', 'url'],
        defaultSort: 'sort_order.asc',
        sortOptions: [
            { value: 'sort_order.asc', label: 'Urutan (A–Z)' },
            { value: 'name.asc', label: 'Nama (A–Z)' },
        ],
        columns: [
            { key: 'sort_order', label: 'Urutan' },
            { key: 'name', label: 'Nama' },
            { key: 'url', label: 'URL' },
            { key: 'parent_name', label: 'Induk' },
            { key: 'is_active', label: 'Aktif', format: 'boolean' },
        ],
        fields: [
            {
                key: 'parent_id',
                label: 'Menu induk',
                type: 'select',
                optionSource: 'rootMenus',
                half: true,
                help: 'Hanya menu utama (tanpa induk); kosongkan untuk menu utama.',
            },
            {
                key: 'sort_order',
                label: 'Urutan',
                type: 'number',
                required: true,
                half: true,
                min: 0,
                max: 9999,
            },
            { key: 'name', label: 'Nama', required: true, half: true },
            {
                key: 'icon',
                label: 'Ikon Feather',
                half: true,
                help: 'Contoh: circle, home, users.',
            },
            { key: 'url', label: 'URL', half: true, help: 'URL legacy, contoh /participant.' },
            { key: 'is_active', label: 'Aktif', type: 'boolean', half: true },
        ],
    },
};

export const MASTER_KINDS = Object.keys(masterConfigs) as MasterKind[];

export function masterKind(value: string | undefined): MasterKind | null {
    return value && value in masterConfigs ? (value as MasterKind) : null;
}

export function masterConfig(kind: MasterKind): MasterConfig {
    return masterConfigs[kind];
}
