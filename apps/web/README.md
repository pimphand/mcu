# @mcu/web

Base aplikasi MCU: **Astro 7 (SSR) + Vue 3 islands + PostgreSQL/PostgREST**.

## Menjalankan

```bash
cd apps/web
cp .env.example .env
npm install
npm run dev
```

`.env` dibaca oleh `npm run dev` (Vite), `npm run preview`, dan `npm start` (`--env-file`).
Setelah mengubah `.env`, **restart** `npm run dev`; nilai lama tetap dipakai sampai proses
server dijalankan ulang. Kalau `AUTH_DATABASE_URL`/`JWT_SECRET` tidak terbaca, login menjawab
`Autentikasi belum dikonfigurasi` (`GET /api/health` menampilkan `auth: not_configured`).

Web dapat berjalan tanpa database. Halaman `/` dan `/api/health` tetap tersedia; login dan data
peserta memerlukan PostgreSQL + PostgREST dan seed user (lihat `infra/README.md`).

`npm run dev` **tidak** menjalankan PostgREST. Sidebar memuat menu dari `api.v_my_menus`, jadi
jalankan PostgREST di terminal lain dari root repo:

```bash
set -a; . infra/.env; set +a
set -a; . apps/web/.env; set +a
db/scripts/postgrest.sh
```

Biarkan proses itu berjalan. Bila PostgREST mati, sidebar menampilkan pesan "Layanan data
(PostgREST) tidak dapat dihubungi" dan tabel data tidak bisa dimuat. Modul upload hasil juga
memerlukan migrasi `db/migrations/0013_upload_views.sql` untuk membuat view daftar hasil.

`GET /api/health` benar-benar mencoba koneksi database autentikasi: `auth: unreachable` berarti
`AUTH_DATABASE_URL` terisi tetapi tidak bisa dihubungi (login akan menjawab 502 "Layanan
autentikasi sedang tidak tersedia"). Bila log server menampilkan `password authentication failed
for user "mcu_auth"`, password di URL `.env` tidak cocok dengan database — samakan dengan
`MCU_AUTH_PASSWORD`/`MCU_SERVICE_PASSWORD` di `infra/.env`, bukan dengan mengganti role atau host. Penyebab tersering di Supabase: host `db.<ref>.supabase.co`
hanya punya record **AAAA (IPv6)**. Mesin tanpa IPv6 harus memakai pooler IPv4
`postgresql://<role>.<ref>:<password>@aws-0-<region>.pooler.supabase.com:5432/postgres?sslmode=require`,
atau mengaktifkan add-on IPv4 pada project.

Pemeriksaan `Origin` untuk request JSON (`POST`/`PUT`/`PATCH`/`DELETE`) membandingkan header
`Origin` dengan `Host`/`X-Forwarded-Host` yang dipakai browser, ditambah `PUBLIC_APP_URL` sebagai
cadangan (`src/lib/security.ts`). Membuka aplikasi lewat `127.0.0.1`, `localhost`, atau reverse
proxy yang mengakhiri TLS tetap bisa login; origin situs lain tetap ditolak `403`.

## Perintah

| Perintah                 | Fungsi                                                |
| ------------------------ | ----------------------------------------------------- |
| `npm run dev`            | Dev server di `http://localhost:4321`                 |
| `npm run check`          | `astro check` + ESLint + Prettier check + Vitest      |
| `npm run build`          | Build SSR ke `dist/`                                  |
| `npm run preview`        | Jalankan build di `dist/` dengan `.env` (`PORT=4321`) |
| `npm run security:audit` | `npm audit --omit=dev`                                |

## Endpoint base

| Endpoint                      | Status                                              |
| ----------------------------- | --------------------------------------------------- |
| `GET /api/health`             | Aktif                                               |
| `POST /api/auth/login`        | Aktif jika `AUTH_DATABASE_URL` + `JWT_SECRET` diisi |
| `POST /api/auth/logout`       | Aktif                                               |
| `GET /api/auth/me`            | Aktif                                               |
| `POST /api/mcu/in`            | Aktif                                               |
| `POST /api/mcu/out`           | Aktif                                               |
| `ANY /api/db/[...path]`       | Proxy PostgREST dengan allowlist + JWT              |
| `GET /api/master/[kind]`      | Daftar master (JSON) untuk pencarian/pagination SPA |
| `POST /api/master/[kind]`     | CRUD master data (JSON untuk island, redirect 303)  |
| `POST /api/master/permission` | Simpan matriks izin role (admin)                    |

## Halaman

| Route                         | Isi                                                                                      |
| ----------------------------- | ---------------------------------------------------------------------------------------- |
| `/app`                        | Dashboard ringkas + status sistem                                                        |
| `/app/mcu-in-out`             | Pilih client/kontrak (klaim JWT baru)                                                    |
| `/app/participant`            | Daftar peserta kontrak aktif                                                             |
| `/app/participant/[id]`       | Detail peserta: identitas, progres 7 langkah, tab hasil pemeriksaan                      |
| `/app/participant/register`   | Register tunggal & massal, filter belum/sudah register                                   |     | `/app/participant/scan` | Cari kode MCU (`?code=` untuk pemindai barcode) |
| `/app/print/mcu`              | Hub Print Hasil MCU: filter tanggal/divisi + tombol cetak per peserta/massal             |
| `/app/print/identitas`        | Filter Print Identitas: tanggal register + rentang No. Form                              |
| `/app/print/identitas/print`  | Stiker identitas massal 150 × 100 mm (satu peserta per halaman)                          |
| `/app/print/[type]/[id]`      | Halaman cetak 9 jenis hasil (stiker, resume, fisik, lab, rad, audio, EKG, spiro, rectal) |
| `/app/print/batch/[type]`     | Cetak massal per rentang No. Form (satu peserta per halaman)                             |
| `/app/upload/laboratorium`    | Upload hasil laboratorium + daftar hasil kontrak aktif                                   |
| `/app/upload/radiologi`       | Upload hasil radiologi + daftar hasil                                                    |
| `/app/upload/validate-dokter` | Upload validasi dokter + salin kesimpulan ke pemeriksaan fisik                           |
| `/app/upload/umum`            | Upload hasil umum + daftar hasil                                                         |
| `/app/master/[resource]`      | Master client, kontrak, department, divisi, karyawan, user, role, menu                   |
| `/app/master/role/[id]`       | Matriks izin menu per role                                                               |

### Print Identitas

`/app/print/identitas` mengikuti halaman legacy `pages/participant/indetitas.blade.php`:
filter tanggal register dan rentang No. Form, lalu tombol **Print** membuka halaman cetak.
Halaman `/app/print/identitas/print` mencetak satu stiker per halaman dengan kertas
**150 × 100 mm** dan margin **1,2 cm** (default DomPDF legacy). Isinya sama dengan
`report/multiple/identitas.blade.php`: MCU ID, NIK, nama [gender], tanggal lahir, usia,
department, paket MCU dari `plan_name`, dan tanggal register `d-m-Y`; baris Status tidak
dicetak, QR 150 px, dan nomor **No Form N** di kanan bawah. Data dibaca dari `participants`
(bukan `v_mcu_progress`) karena view legacy memakai `plan_name`.

### Cetak hasil MCU

Ukuran kertas mengikuti view legacy (`resources/views/pages/report`): hasil pemeriksaan
**21 × 33 cm** dengan margin `1cm 1cm 1cm 2.5cm`; audiometri/EKG/spirometri/rectal **A4** dan
stiker **150 × 100 mm**, keduanya dengan margin default DomPDF `1.2cm`. Aturan per jenis ada di
`printPageConfig()` (`src/lib/print.ts`). `PrintLayout` menerima `contentWidth` = lebar kertas −
margin kiri/kanan (`17.5cm`, `18.6cm`, `12.6cm`) supaya tabel tidak terpotong margin kanan.

Stiker identitas dan resume punya tata letak sendiri di `ReportSheet.astro`: stiker memakai
header ringkas + QR besar di samping data peserta, resume dicetak dua halaman (`MEDICAL RECORD`
lalu `KESIMPULAN DAN SARAN`). Jenis lain memakai peta `reports` dan props `ReportProps`
(`participant`, `row`, `summary`, `doctor`); laboratorium & rectal mencetak kolom NILAI NORMAL
dari `src/lib/print-lab.ts`, sedangkan jenis yang hanya beda field memakai `HasilTable.vue`.
Menambah jenis cetak baru = buat satu komponen + daftarkan di peta tersebut.

Header mencetak QR berisi `{contract_id}-{kode peserta}-{jenis cetak}` (`qrcode`, ukuran 65 seperti legacy) dan
blok tanda tangan memakai `employees.ttd` dari baris hasil (`employee_id`). Berkas tanda tangan
disajikan lewat `/app/print/signature/[file]` (butuh sesi, path traversal ditolak). Upload baru
disimpan di `SIGNATURE_DIR` (default `storage/ttd`, privat) setelah MIME dicek dari magic bytes,
ukuran dibatasi 2 MB, dan gambar di-re-encode dengan `sharp`; berkas legacy di `public/ttd` tetap
dibaca sebagai fallback. Bila berkas tidak ada, dipakai `public/images/ttd-kosong.png` seperti legacy.

Proxy `/api/db/*` menempelkan `client_id`/`contract_id` dari klaim sesi untuk
`v_participant_list` dan `v_mcu_progress`; nilai dari query string browser diabaikan. Register
tunggal (`rpc/register_participant`) menolak peserta di luar kontrak aktif.

## Daftar peserta

`/app/participant` menampilkan `api.v_participant_list` (scope client/kontrak dari klaim sesi)
dengan kolom MCU ID, NIK, nama, JK, tgl lahir, divisi, department, kontrak, plan, paket, dan
status register. Filter mengikuti modal legacy: pencarian bebas mencocokkan kode **atau** nama,
NIK dan tanggal lahir cocok persis, nama memakai `ilike`, gender `L`/`P`, switch plan
U/A/E/S/R, lab khusus, dan paket A–F.

- State filter dan terjemahannya ke parameter PostgREST ada di
  `src/lib/participant-filters.ts`; `ParticipantFilterForm.vue` hanya menyusun draft,
  `ParticipantTable.vue` yang memuat data.
- Filter dan halaman ditulis ke URL (`?plan_u=1&gender=L&page=2`) lewat
  `history.replaceState`, dan diurai di server (`index.astro`) supaya tautan yang dibagikan
  sudah terpasang saat island dihidrasi.
- `src/lib/participant-parity.test.ts` memastikan setiap field filter Blade legacy
  (`resources/views/pages/participant/filter.blade.php`) punya padanan dan kolom yang dipakai
  daftar memang ada di `db/migrations/0003_views.sql`.
- Catatan paritas: `ParticipantService::paginate` legacy membaca nama field yang salah
  (`plant_u`, `lab_khusus`, `paket_*`), jadi switch plan U, lab khusus, dan paket di legacy
  tidak pernah berpengaruh; versi Astro memakai nama field form (`plan_u`, `lab_special`,
  `packet_*`) sehingga filternya benar-benar diterapkan.

## Master data

Satu halaman generik `/app/master/[resource]` melayani client, kontrak, department, divisi,
karyawan, user, role, dan menu. Konfigurasi field/kolom ada di `src/lib/master-data.ts`; validasi
Zod diturunkan dari konfigurasi yang sama (`src/lib/master-validation.ts`). Semua daftar memakai
pagination server-side, pencarian, dan sort yang di-allowlist.

- Tabel master ditulis lewat PostgREST memakai JWT pengguna, sehingga RLS tetap berlaku.
- `users` di-revoke dari role `authenticated`; CRUD user memakai koneksi `mcu_service` dengan
  pemeriksaan izin per client dan aturan role di server (`src/lib/master-write.ts`).
- Aksi POST diarahkan ke `/api/master/[kind]`; matriks izin role disimpan lewat
  `/api/master/permission` (admin saja). Island meminta `Accept: application/json` untuk
  daftar (`GET`) dan hasil simpan tanpa reload halaman.
- Field pilihan (`client_id`, role, menu induk) memakai dropdown pencarian `MasterSelect.vue`,
  pengganti select2 legacy; wajib-isi diperiksa di browser (`MasterModal.vue`) dan diulang di
  server (`master-validation.ts`).
- `src/lib/master-parity.test.ts` membaca form tambah dan aturan `required` controller Blade
  legacy (`resources/views/pages/<kind>`, `app/Http/Controllers/*Controller.php`) supaya field
  master tetap sepadan.
- Kode otomatis `C00001`, `KT00001`, `D-00001`, `DV-00001`, `E-00001` dibuat sequence database
  lewat `db/migrations/0014_master_code_defaults.sql`. **Migrasi ini wajib diterapkan** sebelum
  menambah client/kontrak/department/divisi/karyawan; tanpa itu insert gagal karena kolom
  `code` NOT NULL belum punya default.

## Tema

Tampilan mengikuti tema **Vuexy** lama: primary `#7367f0`, background `#f8f8f8`, teks `#6e6b7b`, heading `#5e5873`, border `#ebe9f1`, dan font **Montserrat** self-hosted. Token ada di `src/styles/global.css`; komponen dasar `.vx-card`, `.vx-btn-primary`, `.vx-btn-outline`, `.vx-input`, dan `.vx-label`. Logo disalin ke `public/logo-drdini.png`.

Jangan memuat Google Fonts dari browser (CSP `font-src 'self'`) dan jangan mengganti palet/logo tanpa persetujuan. Referensi visual lama ada di `resources/views` dan `public/app-assets` di root repo.

## Keamanan

- Cookie `mcu_session`: httpOnly, SameSite=Lax, Secure saat HTTPS.
- JWT HS256, umur 8 jam, `token_version` untuk pencabutan sesi.
- `security.checkOrigin` aktif; request JSON yang mengubah data juga diperiksa `Origin`.
- Security headers + CSP (`frame-ancestors` lewat header).
- Rate limit login in-memory (ganti Redis/nginx untuk multi-instance).
- Proxy hanya mengizinkan resource di `src/lib/postgrest.ts`; `users` tidak pernah diekspos.

## Struktur

```
src/
├── components/     # Vue islands, ikon Astro, dan reports/ untuk cetak
├── layouts/        # Base, App, Auth
├── lib/            # auth, db, postgrest, security, format
├── pages/          # halaman + /api
├── middleware.ts   # origin check, sesi, proteksi /app
└── styles/         # Tailwind
```
