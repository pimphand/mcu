# Infra base MCU

Compose ini hanya untuk database/ETL/PostgREST tahap awal, bukan production.

## 1. Siapkan environment

```bash
cp infra/.env.example infra/.env
```

Ganti semua `change-me`. `JWT_SECRET` harus sama dengan `JWT_SECRET` di
`apps/web/.env`; tanpa itu BFF tidak bisa memakai PostgREST.

## 2. Jalankan PostgreSQL + PostgREST

```bash
docker compose --env-file infra/.env -f infra/compose.dev.yml up -d postgres postgrest
```

Saat volume `pg_data` pertama kali dibuat, `db/bootstrap/00-init.sh` otomatis:

1. membuat role database;
2. menjalankan semua migrasi;
3. seed role/menu/permission;
4. seed admin awal.

Cek:

```bash
docker compose --env-file infra/.env -f infra/compose.dev.yml ps
curl -s http://127.0.0.1:3001/ | head
```

PostgREST hanya di-bind ke `127.0.0.1` (default port 3001), bukan ke internet. Browser tetap
memanggil BFF Astro `/api/db/*`.

## 3. Restore data legacy

Profil `restore` menyalakan MySQL dan menjalankan dump
`db/12 oktober_medical_check_up_2026-10-17_000004.sql.gz` secara otomatis pada volume pertama:

```bash
docker compose --env-file infra/.env -f infra/compose.dev.yml --profile restore up -d mysql
docker compose --env-file infra/.env -f infra/compose.dev.yml --profile restore run --rm etl
```

ETL menunggu MySQL dan PostgreSQL healthy, lalu memindahkan data ke PostgreSQL. Outputnya berisi
jumlah baris per tabel dan hasil verifikasi.

Tanpa Docker, alur yang sama tersedia lewat satu perintah:

```bash
DATABASE_URL='postgres://mcu_admin:...@127.0.0.1:5433/mcu' \
BOOTSTRAP=1 MYSQL_HOST=127.0.0.1 MYSQL_PORT=3307 MYSQL_USER=mcu MYSQL_PASSWORD=... \
db/scripts/restore-legacy.sh
```

Setelah restore selesai, cek data + RLS:

```bash
DATABASE_URL='postgres://mcu_admin:...@127.0.0.1:5433/mcu' db/scripts/verify.sh
```

Kalau ingin mengulang restore dari nol:

```bash
docker compose --env-file infra/.env -f infra/compose.dev.yml --profile restore down -v
```

**`down -v` menghapus volume database lokal.** Jangan dipakai di production.

## 3b. PostgreSQL terkelola (Supabase)

Kalau tidak memakai service `postgres` di compose, arahkan semuanya ke Supabase:

1. Isi `infra/.env`:

```dotenv
DATABASE_URL=postgresql://postgres:<password>@db.<ref>.supabase.co:6543/postgres?sslmode=require
POSTGRES_DB=postgres
PGRST_AUTH_ROLE=mcu_authenticator
AUTHENTICATOR_PASSWORD=... MCU_AUTH_PASSWORD=... MCU_SERVICE_PASSWORD=... MCU_ADMIN_PASSWORD=...
JWT_SECRET=<sama dengan apps/web/.env>
```

2. Bootstrap + restore:

```bash
set -a; . infra/.env; set +a

DATABASE_URL="$DATABASE_URL" AUTHENTICATOR_PASSWORD="$AUTHENTICATOR_PASSWORD" \
MCU_AUTH_PASSWORD="$MCU_AUTH_PASSWORD" MCU_SERVICE_PASSWORD="$MCU_SERVICE_PASSWORD" \
MCU_ADMIN_PASSWORD="$MCU_ADMIN_PASSWORD" ADMIN_USERNAME=admin ADMIN_PASSWORD=... \
db/scripts/bootstrap-postgres.sh

ETL_SOURCE=dump BOOTSTRAP=0 SEED_ADMIN=1 db/scripts/restore-legacy.sh
```

3. PostgREST (tanpa Docker, menunjuk ke Supabase):

```bash
set -a; . infra/.env; set +a
db/scripts/postgrest.sh
```

Catatan Supabase:

- `db/roles.sql` memakai `mcu_authenticator`, **bukan** `authenticator`, karena role itu milik
  PostgREST Supabase dan password-nya diatur platform.
- Host `db.<ref>.supabase.co` hanya punya record **AAAA (IPv6)**. Di mesin tanpa IPv6
  koneksi gagal (`ENETUNREACH`/timeout) dan login Astro menjawab 502. Pakai pooler IPv4
  `aws-0-<region>.pooler.supabase.com:5432` (session mode) dengan user `<role>.<ref>`,
  atau pooler transaksi `:6543` yang tidak mendukung prepared statement (skrip
  `db/scripts/postgrest.sh` mematikan `PGRST_DB_PREPARED_STATEMENTS` otomatis bila mendeteksi
  port `6543`).
- `0006_platform_hardening.sql` mencabut grant `anon`/`service_role` pada tabel MCU.
- PostgREST dari GitHub di macOS butuh libpq: set `POSTGREST_BIN` ke binary/wrapper yang
  memuat `libpq` (mis. `DYLD_FALLBACK_LIBRARY_PATH=$(brew --prefix postgresql@18)/lib`).

## 4. Hubungkan Astro

Di `apps/web/.env`:

```dotenv
POSTGREST_URL=http://127.0.0.1:3001
JWT_SECRET=<sama dengan infra/.env>
AUTH_DATABASE_URL=postgres://mcu_auth:<MCU_AUTH_PASSWORD>@127.0.0.1:5433/mcu
```

`AUTH_DATABASE_URL` hanya dipakai server Astro untuk query login. Jangan memberi prefix
`PUBLIC_` pada nilai ini.

Pastikan PostgreSQL yang dipakai Astro adalah instance yang sama dengan PostgREST, dan schema yang
diekspos berisi `api,public`:

```bash
PGRST_DB_SCHEMAS=api,public
```

Schema `api` memuat view (`v_participant_list`, `v_mcu_progress`, `v_dashboard_summary`), schema
`public` memuat tabel domain dan RPC. BFF Astro memilih schema per request lewat header
`Accept-Profile`/`Content-Profile`; resource tanpa schema seperti `contracts` akan 404 bila
`public` tidak diekspos.

Lalu jalankan aplikasi web:

```bash
cd apps/web && npm install && npm run dev
```

Server hasil build (`apps/web/dist`) **tidak** memuat `.env` otomatis. Pakai skrip `start` yang
sudah disediakan (memuat `apps/web/.env`):

```bash
cd apps/web && npm run build && npm start
```

Smoke test paling cepat: login, buka halaman peserta, dan buka pilihan MCU IN (memuat
`/api/db/contracts` dan `/api/db/clients`).

## 5. Tanpa Docker

Gunakan skrip di `db/scripts/`:

```bash
DATABASE_URL='postgres://postgres:...@127.0.0.1:5432/mcu' \
AUTHENTICATOR_PASSWORD=... MCU_AUTH_PASSWORD=... \
MCU_SERVICE_PASSWORD=... MCU_ADMIN_PASSWORD=... \
db/scripts/bootstrap-postgres.sh

DATABASE_URL='postgres://mcu_admin:...@127.0.0.1:5432/mcu' \
MYSQL_URL='mysql://mcu:...@127.0.0.1:3306/medical_check_up' \
node db/etl/migrate.mjs --mysql "$MYSQL_URL" --truncate
```

Detail mapping dan verifikasi ada di `db/README.md`.

## Catatan

- Jangan commit `infra/.env`.
- Jangan memakai `mcu_service`/`mcu_admin` di browser.
- `postgrest` memakai schema `api,public`; tabel `public.users` tidak bisa dibaca lewat PostgREST.
- Restore mengabaikan tabel menu/permission legacy karena route-nya milik Laravel lama.
