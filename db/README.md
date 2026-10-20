# Database MCU

Base PostgreSQL untuk Astro/Vue + PostgREST. Skema mengikuti domain Laravel lama, dengan
normalisasi yang diperlukan untuk PostgreSQL dan RLS.

## Isi

```
db/
├── roles.sql                 # role PostgREST + web (anon, authenticated, mcu_authenticator, mcu_auth, mcu_service, mcu_admin)
├── migrations/
│   ├── 0001_schema.sql       # tabel, constraint, index
│   ├── 0002_functions.sql    # JWT claims, RLS helper, sequence, register functions
│   ├── 0003_views.sql        # view api.v_participant_list / v_mcu_progress / v_dashboard_summary
│   ├── 0004_rls.sql          # RLS, policy per role, grant minimal
│   ├── 0005_legacy_compat.sql# kompatibilitas data legacy
│   ├── 0009_print_views.sql  # kolom tambahan v_mcu_progress untuk cetak
│   ├── 0010_employee_signature.sql # view ttd pemeriksa (client_admin/employee)
│   ├── 0011_my_menus.sql     # helper role_id + view api.v_my_menus (sidebar)
│   ├── 0012_menu_registry.sql# registri menu legacy + permission per role
│   └── 0013_upload_views.sql # view daftar hasil upload (lab, radiologi, validasi dokter, umum)
├── seed/
│   ├── 001_roles_menus.sql   # role 1-4 (menu & permission ada di migrasi 0012)
│   └── 002_admin.sql         # admin awal (bcrypt)
├── bootstrap/00-init.sh      # dipakai image postgres saat volume pertama dibuat
├── restore/restore-mysql.sh  # restore dump ke MySQL
├── scripts/                  # bootstrap, migrate, seed, restore-legacy, verify
├── etl/                      # Node ETL MySQL -> PostgreSQL
└── 12 oktober_medical_check_up_2026-10-17_000004.sql.gz
```

> Setelah migrasi yang membuat/mengubah view (mis. `0009`, `0010`), PostgREST perlu memuat ulang
> schema cache. `NOTIFY pgrst, 'reload schema'` cukup untuk koneksi langsung; bila memakai
> pooler Supabase port 6543, restart proses PostgREST agar view baru terdeteksi.

## Fresh install

Dengan PostgreSQL yang sudah berjalan dan user superuser/owner:

```bash
export DATABASE_URL='postgres://postgres:...@127.0.0.1:5432/mcu'
export AUTHENTICATOR_PASSWORD='...'
export MCU_AUTH_PASSWORD='...'
export MCU_SERVICE_PASSWORD='...'
export MCU_ADMIN_PASSWORD='...'
export ADMIN_USERNAME='admin'
export ADMIN_EMAIL='admin@example.test'
export ADMIN_PASSWORD='...'

db/scripts/bootstrap-postgres.sh
```

Atau terpisah:

```bash
DATABASE_URL=... db/scripts/migrate.sh
DATABASE_URL=... ADMIN_USERNAME=admin ADMIN_PASSWORD=... db/scripts/seed.sh
```

Docker:

```bash
cp infra/.env.example infra/.env
# isi seluruh change-me
docker compose --env-file infra/.env -f infra/compose.dev.yml up -d postgres postgrest
```

Seed admin hanya untuk instalasi baru. Saat restore legacy, ETL mengganti isi `users` dengan
akun dari dump (termasuk password hash lama).

## Restore MySQL 12 Oktober

Dump sudah tersedia di `db/12 oktober_medical_check_up_2026-10-17_000004.sql.gz`.

### Opsi A — Docker (otomatis)

Service `mysql` me-mount dump ke `/docker-entrypoint-initdb.d`; restore berjalan saat volume
MySQL pertama kali dibuat.

```bash
cp infra/.env.example infra/.env
docker compose --env-file infra/.env -f infra/compose.dev.yml --profile restore up -d mysql
docker compose --env-file infra/.env -f infra/compose.dev.yml --profile restore run --rm etl
```

Jika volume sudah pernah dibuat dan ingin restore ulang:

```bash
docker compose --env-file infra/.env -f infra/compose.dev.yml --profile restore down -v
```

`down -v` menghapus data lokal MySQL/PostgreSQL. Jangan jalankan di production.

### Opsi B — MySQL yang sudah berjalan

```bash
export MYSQL_HOST=127.0.0.1
export MYSQL_PORT=3306
export MYSQL_USER=mcu
export MYSQL_PASSWORD='...'
export MYSQL_DATABASE=medical_check_up

db/restore/restore-mysql.sh
```

Script akan membuat database, melakukan `gzip -dc ... | mysql`, lalu menampilkan jumlah baris.

### ETL ke PostgreSQL

Setelah PostgreSQL dimigrasikan dan MySQL berisi dump:

```bash
export DATABASE_URL='postgres://mcu_admin:...@127.0.0.1:5433/mcu'
export MYSQL_URL='mysql://mcu:...@127.0.0.1:3307/medical_check_up'

node db/etl/migrate.mjs --mysql "$MYSQL_URL" --truncate
```

### Satu perintah: restore + migrasi + verifikasi

`db/scripts/restore-legacy.sh` menjalankan seluruh alur. Mode default membaca dump lewat MySQL:

```bash
DATABASE_URL='postgres://postgres:...@127.0.0.1:5432/mcu' \
AUTHENTICATOR_PASSWORD=... MCU_AUTH_PASSWORD=... \
MCU_SERVICE_PASSWORD=... MCU_ADMIN_PASSWORD=... \
ADMIN_USERNAME=admin ADMIN_PASSWORD='...' \
MYSQL_HOST=127.0.0.1 MYSQL_PORT=3306 MYSQL_USER=mcu MYSQL_PASSWORD='...' \
BOOTSTRAP=1 db/scripts/restore-legacy.sh
```

Variabel yang dikenali:

| Variabel | Arti |
|---|---|
| `BOOTSTRAP=1` | jalankan `bootstrap-postgres.sh` dulu (roles, migrasi, seed admin) |
| `ETL_SOURCE=dump` | ETL membaca file `.sql.gz` langsung, melewati MySQL |
| `ETL_SOURCE=mysql` | (default) restore dump ke MySQL lalu ETL membaca MySQL |
| `VERIFY=0` | lewati `db/scripts/verify.sh` di akhir |
| argumen ke-1 | path dump, default `db/12 oktober_medical_check_up_2026-10-17_000004.sql.gz` |

ETL juga bisa dipanggil langsung tanpa MySQL (berguna untuk uji cepat):

```bash
DATABASE_URL='postgres://mcu_admin:...@127.0.0.1:5432/mcu' \
node db/etl/migrate.mjs \
  --dump 'db/12 oktober_medical_check_up_2026-10-17_000004.sql.gz' \
  --truncate
```

Gunakan `--dry-run` untuk memvalidasi pemetaan tanpa menulis.

### Verifikasi hasil restore

```bash
DATABASE_URL='postgres://postgres:...@127.0.0.1:5432/mcu' db/scripts/verify.sh
```

Script ini mencetak jumlah baris per tabel, memeriksa integritas dasar (gender, hasil tanpa
peserta), lalu menguji RLS lewat role `authenticated`: admin harus melihat semua peserta,
employee hanya kombinasi `client_id` + `contract_id` di klaim JWT-nya, peserta hanya dirinya,
dan konteks asing harus menghasilkan 0 baris.

## Yang dimigrasikan

| Legacy MySQL | PostgreSQL | Catatan |
|---|---|---|
| `clients`, `contracts`, `departments`, `divisis` | sama | ID dipertahankan |
| `employees` | `employees` | ID, kode, ttd, bank dipertahankan |
| `users` | `users` | `password` -> `password_hash`; `$2y$` -> `$2a$` |
| `participants` | `participants` | gender dinormalisasi ke `L`/`P`; kode unik per client+kontrak |
| `tanda_vitals` | `tanda_vitals` | `tekanan_darah` tetap teks `120/80` |
| `pemeriksaan_fisiks` | `pemeriksaan_fisiks` | duplikat peserta diambil baris terbaru |
| `laboratoria` | `laboratoria` | termasuk `form_no`, `client_id`, `contract_id` |
| `radiologis` | `radiologis` | `date_mcu`, `diperiksa` |
| `spirometris`, `audiometris`, `rectals`, `ekgs` | sama | hasil kosong tetap aman |
| `validate_doctors`, `general_results` | sama | `client_id` legacy dipetakan ke ID klien |
| `roles`, `menus`, `permissions` | **tidak diimpor** | memakai seed baru; menu legacy menunjuk route Laravel yang tidak ada |
| `migrations`, `jobs`, `failed_jobs`, `password_reset_tokens`, `personal_access_tokens` | **tidak diimpor** | infrastruktur Laravel |

Detail normalisasi:

- `gender` legacy (`LAKI- LAKI`, `Laki - laki`, `Perempuan`, `P`, dst.) menjadi `L`/`P`.
- `users.created_at`/`updated_at` yang `NULL` diisi waktu migrasi karena kolom baru `NOT NULL`.
- `participants.code` seperti `MCU00001` dipakai ulang di kontrak berbeda; constraint baru
  `unique (client_id, contract_id, code)`.
- `register_number` legacy tidak dijamin unik; index-nya non-unique.
- `pemeriksaan_fisiks` peserta 536 punya dua baris; ETL menyimpan baris dengan `updated_at`
  terbaru dan melaporkan satu duplikat.
- `users` legacy `$2y$` diganti prefix `$2a$` agar kompatibel dengan `bcryptjs`; hash tidak berubah.

## Seed

`db/seed/001_roles_menus.sql` hanya membuat role level 1 Administrator, 2 Admin Client,
3 Admin Karyawan, dan 4 Peserta supaya akun bisa ditautkan.

Menu & permission tidak lagi di-seed di sana, melainkan di
`db/migrations/0012_menu_registry.sql`: 48 menu dengan label, ikon, urutan, dan `is_active`
sama seperti `MenuSeeder` legacy (id dipertahankan) plus permission per level role. Sidebar
membacanya lewat view `api.v_my_menus` (migrasi `0011`), yang memfilter menu sesuai `role_id`
di klaim JWT. Level 1-3 melihat seluruh menu; level 4 (Peserta) hanya `Home`.

`db/seed/002_admin.sql` memakai `crypt(..., gen_salt('bf', 10))` (bcrypt) dan aman dijalankan
ulang untuk username yang sama.

## Verifikasi setelah ETL

```sql
select count(*) from public.participants;              -- 708
select count(*) from public.laboratoria;                -- 371
select count(*) from public.radiologis;                 -- 364
select count(*) from public.tanda_vitals;               -- 368
select count(*) from public.pemeriksaan_fisiks;         -- 368 (1 duplikat digabung)
select count(*) from api.v_mcu_progress;
```

Uji RLS (klaim JWT):

```sql
begin;
set local role authenticated;
select set_config(
  'request.jwt.claims',
  '{"app_role":"employee","user_id":10,"client_id":1,"contract_id":4,"level":3}',
  true
);
select count(*) from api.v_participant_list; -- 337, hanya kontrak 4
rollback;
```

Admin tanpa client/kontrak melihat semua (708). Peserta hanya melihat baris dengan
`participants.user_id` miliknya.

## PostgREST

PostgREST mengekspos dua schema (`db-schemas = "api,public"`):

- schema `api` — view ringkas: `v_participant_list`, `v_mcu_progress`, `v_dashboard_summary`;
- schema `public` — tabel domain dan RPC `register_participant` / `register_all`.

Semua view `security_invoker = true`, sehingga RLS tabel di bawahnya tetap berlaku. PostgREST
me-resolve nama resource tanpa schema **hanya** pada schema pertama (`api`), jadi permintaan ke
tabel `public` wajib memakai header `Accept-Profile: public` (dan `Content-Profile: public` untuk
tulis). Header itu dipasang oleh BFF Astro berdasarkan allowlist di
`apps/web/src/lib/postgrest.ts`; browser tidak bisa memilih schema sendiri.

`public.users` di-`revoke` dari `authenticated`, jadi kalaupun diminta akan ditolak (403).

JWT harus memuat `role: "authenticated"` dan klaim `app_role`, `user_id`, `client_id`,
`contract_id`, `level`, `token_version`. Jangan mengekspos port PostgREST ke internet; browser
hanya memanggil BFF Astro `/api/db/*`.

## PostgreSQL terkelola (Supabase)

Tujuan `DATABASE_URL` bisa berupa Supabase. Yang perlu diperhatikan:

1. **Jangan pakai nama role `authenticator`.** Supabase sudah memakai role itu untuk PostgREST
   miliknya; `db/roles.sql` default-nya membuat `mcu_authenticator`. Ganti dengan
   `psql --set authenticator_role=nama_lain` bila perlu.
2. **Port koneksi.** Supabase dapat menutup koneksi langsung `5432`; pooler di
   `db.<ref>.supabase.co:6543` tetap terbuka dan menerima role custom (`mcu_auth`, `mcu_service`,
   `mcu_admin`, `mcu_authenticator`). Contoh:
   `postgresql://mcu_auth:<password>@db.<ref>.supabase.co:6543/postgres?sslmode=require`
3. **Prepared statement.** Port `6543` adalah pooler transaksi, jadi jalankan PostgREST dengan
   `PGRST_DB_PREPARED_STATEMENTS=false`. `db/scripts/postgrest.sh` mendeteksi port `6543` dan
   mematikannya otomatis.
4. **TLS.** `sslmode=require` wajib. Driver `pg` (Astro + ETL) menormalkan `sslmode` di
   `apps/web/src/lib/pg.ts` dan `db/etl/migrate.mjs`, karena pg mengartikan `require` sebagai
   `verify-full` dan gagal dengan sertifikat Supabase.
5. **Hardening platform.** `0006_platform_hardening.sql` mencabut grant default Supabase untuk
   `anon` dan `service_role` pada tabel kita (dan mencegah tabel baru terbuka otomatis), sehingga
   kunci service platform tidak bisa melewati RLS.
6. **Scope register.** `0007_register_scope.sql` mengganti `register_participant` agar hanya bisa
   mendaftarkan peserta pada `client_id`/`contract_id` di klaim JWT (403 bila beda kontrak, 404 bila
   baris tidak terlihat RLS). BFF juga menempelkan scope yang sama pada `v_participant_list` dan
   `v_mcu_progress`, jadi parameter `client_id`/`contract_id` dari browser diabaikan.
7. **Print hasil MCU.** `0009_print_views.sql` membuat ulang `api.v_mcu_progress` dengan kolom tambahan untuk hub cetak: `no_form`, `nik`, `packet_name`, `status`, `physical_done`, dan `validated` (ada baris `validate_doctors`). View dibuat ulang (bukan `create or replace`) karena kolom baru disisipkan di tengah; grant SELECT untuk `authenticated` ditambahkan dan PostgREST diminta reload skema.
8. **Pilihan konteks MCU.** `0008_clinic_wide_clients.sql` menambah `can_select_client()` dan memakainya
   hanya untuk policy SELECT `clients` dan `contracts`. Akun staf tanpa `users.client_id` (mis.
   `dr_dini`) bisa melihat seluruh client/kontrak untuk dipilih di MCU IN/OUT seperti legacy, sedangkan
   department, divisi, employee (termasuk nomor rekening), dan data peserta tetap mengikuti
   `can_access_client()` yang mensyaratkan klaim client. Akun yang sudah ditautkan ke client tetap hanya
   melihat client-nya, dan `POST /api/mcu/in` menolak client lain (403).

Urutan lengkap (sudah diuji):

```bash
export DATABASE_URL='postgresql://postgres:<password>@db.<ref>.supabase.co:6543/postgres?sslmode=require'
export AUTHENTICATOR_PASSWORD=... MCU_AUTH_PASSWORD=... MCU_SERVICE_PASSWORD=... MCU_ADMIN_PASSWORD=...
export ADMIN_USERNAME=admin ADMIN_PASSWORD='...'

db/scripts/bootstrap-postgres.sh                 # roles + migrasi + seed
ETL_SOURCE=dump BOOTSTRAP=0 SEED_ADMIN=1 \
  db/scripts/restore-legacy.sh                   # data legacy + akun admin

JWT_SECRET='...' db/scripts/postgrest.sh         # PostgREST untuk BFF
cd apps/web && npm run build && npm start        # BFF (membaca apps/web/.env)
```

## Peran database

| Role | Login | RLS | Kegunaan |
|---|---:|---:|---|
| `mcu_authenticator` | ya | — | login PostgREST, hanya boleh `set role` (default; bisa diubah lewat `authenticator_role`) |
| `anon` | tidak | — | tanpa akses tabel |
| `authenticated` | tidak | ya | role request setelah JWT valid |
| `mcu_auth` | ya | bypass | query server read-only: `users` (login), `roles` (level), `clients` + `contracts` (MCU IN/OUT) |
| `mcu_service` | ya | bypass | tugas server/import |
| `mcu_admin` | ya | bypass | migrasi/ETL, jangan dipakai web |
