# Inventaris Fitur — Aplikasi MCU (Laravel Legacy)

Dokumen ini mendata **seluruh fitur** aplikasi MCU Klinik Dr. Dini yang berjalan di **Laravel 11 + Blade + MySQL** (kode di root repo). Dokumen ini adalah bahan audit untuk proses penulisan ulang ke Astro + Vue + PostgreSQL + PostgREST.

- Rencana & progres recode: [`RECODE_TO_ASTRO_VUE_POSTGREST.md`](./RECODE_TO_ASTRO_VUE_POSTGREST.md).
- Referensi kode & view (`resources/views`, `public/app-assets`) bersifat **read-only**.

| Item | Nilai |
|---|---|
| Aplikasi | MCU (Medical Check Up) Klinik Dr. Dini |
| Stack | Laravel 11, PHP ^8.1, MySQL 8, Blade + Vuexy (Bootstrap 4 + jQuery), Vite |
| Basis kode | Root repo (legacy) |
| Jumlah route (web) | 126 definisi di `routes/web.php` |
| Jumlah controller | 26 file (+ 24 di antaranya dipakai route) |
| Jumlah view Blade | ±100 file |
| Jumlah migrasi | 38 |
| Role | 4 level: Administrator, Admin Client, Admin Karyawan, Peserta |
| API publik | Tidak ada (semua via web/session) |
| Perintah artisan kustom | 2 (`backup:database`, `app:generate-register`) |
| Feature test | 13 file di `tests/Feature` |

> **Penting — paritas vs menu yang belum dirutekan.** `MenuSeeder` menyimpan daftar menu "Laporan" yang jauh lebih banyak daripada route yang benar-benar ada (invoice, statistik, grafik, dsb.). Sebagian menu menunjuk URL **tanpa route** (dead link / fitur yang belum diimplementasi). Lihat [§10 Peta menu & status route](#10-peta-menu--status-route) — jangan menganggap semua menu sebagai fitur aktif.
>
> **Registri menu kanonik (recode):** Daftar menu disimpan di tabel `menus` + `permissions` (migrasi `db/migrations/0012_menu_registry.sql`, struktur & id mengikuti `MenuSeeder` legacy) dan dibaca sidebar lewat view `api.v_my_menus` (migrasi `0011`). `apps/web/src/lib/nav.ts` hanya memetakan baris view itu ke navigasi Astro — label, urutan, ikon, dan hak akses per role diubah di database, bukan di kode. Item yang belum diport tetap tampil sebagai "soon" dan route legacy → Astro dipetakan di `APP_ROUTES`.

---

## Daftar Isi

1. [Ringkasan domain](#1-ringkasan-domain)
2. [Stack & paket pihak ketiga](#2-stack--paket-pihak-ketiga)
3. [Peta route](#3-peta-route)
4. [Controller & tanggung jawab](#4-controller--tanggung-jawab)
5. [Model & relasi](#5-model--relasi)
6. [Middleware, Kernel & autentikasi](#6-middleware-kernel--autentikasi)
7. [RBAC (role, menu, permission)](#7-rbac-role-menu-permission)
8. [Master opsi form (dropdown)](#8-master-opsi-form-dropdown)
9. [Halaman (Blade view)](#9-halaman-blade-view)
10. [Peta menu & status route](#10-peta-menu--status-route)
11. [Job, Event, Listener, Notification, Service](#11-job-event-listener-notification-service)
12. [Console command & scheduler](#12-console-command--scheduler)
13. [Helper, HTTP Resource, Exception](#13-helper-http-resource-exception)
14. [Skema database & seeder](#14-skema-database--seeder)
15. [Import, export, PDF, QR, cetak](#15-import-export-pdf-qr-cetak)
16. [Storage, filesystem & backup](#16-storage-filesystem--backup)
17. [Integrasi & aset](#17-integrasi--aset)
18. [Automated test](#18-automated-test)
19. [Fitur yang TIDAK ada](#19-fitur-yang-tidak-ada)
20. [Ringkasan per domain](#20-ringkasan-per-domain)

---

## 1. Ringkasan domain

1. **Manajemen master data** — client, kontrak, karyawan, department, divisi, user, role, menu dengan permission granular.
2. **Manajemen peserta** — registrasi, pelacakan, alur pemeriksaan, scan QR, import Excel.
3. **Pemeriksaan medis** — 8 jenis pemeriksaan (Tanda Vital, Pemeriksaan Fisik, Laboratorium, Radiologi, Audiometri, Spirometri, Rectal, EKG) + hasil umum + validasi dokter, plus foto kamera & foto komputer.
4. **Pelaporan** — PDF individual dan gabungan (batch), import/export Excel, stiker, rekap.
5. **Operasional** — pencatatan MCU IN/OUT, upload/import hasil dari sumber eksternal, backup DB terjadwal.
6. **Administrasi** — RBAC dengan matriks permission kustom dan menu hierarkis.

---

## 2. Stack & paket pihak ketiga

### 2.1 Lapisan

| Lapisan | Teknologi |
|---|---|
| Framework | Laravel 11, PHP ^8.1 |
| Database | MySQL 8 (`medical_check_up`) |
| View | Blade + tema Vuexy (Bootstrap 4) + jQuery, Select2, DataTables, Flatpickr, Toastr, SweetAlert2 |
| Build | Vite |
| Auth | Session guard Laravel, Sanctum terpasang, role level 1–4 |
| Realtime | laravel/reverb + Pusher/Echo (dipakai langsung dari job untuk progres/penyelesaian laporan) |
| Storage | Disk lokal publik + Cloudflare R2 (driver S3) untuk backup & opsi arsip |
| Server | Laravel Octane |

### 2.2 Paket `composer.json`

| Paket | Kegunaan |
|---|---|
| `barryvdh/laravel-dompdf` | Generate PDF |
| `mpdf/mpdf` | Alternatif generate PDF |
| `maatwebsite/excel` | Import/export Excel (PhpSpreadsheet) |
| `simplesoftwareio/simple-qrcode` | Generate QR (package); view juga pakai QR API eksternal |
| `spatie/laravel-query-builder` | Filter/sort query lanjutan pada endpoint list |
| `yajra/laravel-datatables-oracle` | DataTables server-side |
| `laravel/sanctum` | API token |
| `laravel/octane` | Application server performa tinggi |
| `laravel/reverb` | WebSocket server |
| `pusher/pusher-php-server` | Realtime event |
| `predis/predis` | Klien Redis (queue/cache) |
| `league/flysystem-aws-s3-v3` | Storage S3/R2 |
| `guzzlehttp/guzzle` | Klien HTTP |

`require-dev`: fakerphp/faker, laravel/pint, laravel/sail, mockery, nunomaduro/collision, phpunit, spatie/laravel-ignition.

### 2.3 File konfigurasi relevan

`config/app, auth, backup (kustom), broadcasting, cache, cors, database, excel, filesystems, hashing, logging, mail, octane, query-builder, queue, reverb, sanctum, services, session, view`.

---

## 3. Peta route

Semua route didefinisikan di `routes/web.php`, memakai named route. Secara umum dibagi menjadi: publik (auth), `auth`, dan `auth + mcu` (butuh konteks client/kontrak aktif).

### 3.1 Autentikasi (tanpa middleware auth)

| Method | URI | Action | Nama |
|---|---|---|---|
| GET | `/auth` | LoginController@index | `login.index` |
| GET | `/auth/peserta/token/{token}` | LoginController@pesertaToken | `login.peserta.token` |
| POST | `/auth` | LoginController@login | `login.post` |
| GET | `/auth/logout` | LoginController@logout | `login.out` |

### 3.2 Dashboard & MCU IN/OUT (`auth`)

| Method | URI | Action | Nama |
|---|---|---|---|
| GET | `/` | DashboardController@index | `welcome.index` |
| GET | `/dashboard` | DashboardController@index | `dashboard.index` |
| GET | `/mcu-in-out` | McuController@mcu | `mcu.index` |
| POST | `/mcu-in-out/in` | McuController@mcuIn | `mcu.in` |
| POST | `/mcu-in-out/out` | McuController@mcuOut | `mcu.out` |

### 3.3 Administrator & master data (`auth`)

**Role** (`/role`) + permission:

| Method | URI | Action | Nama |
|---|---|---|---|
| GET | `/role` | RoleController@index | `role.index` |
| GET | `/role/create` | RoleController@create | `role.create` |
| POST | `/role` | RoleController@store | `role.store` |
| GET | `/role/edit/{id}` | RoleController@edit | `role.edit` |
| PUT | `/role/{id}` | RoleController@update | `role.update` |
| DELETE | `/role/{id}` | RoleController@destroy | `role.destroy` |
| GET | `/role/permission/{roleID}` | RoleController@permission | `role.permission` |
| POST | `/role/permission` | RoleController@permissionCreate | `role.permission.store` |

**Menu** (`/menu`): index, create, detail/{id}, store, edit/{id}, update, toggle/{id}, destroy.

**User** (`/user`): index, create, store, edit (`GET /{id}`), update, delete.

**Client** (`/client`): index, create, store, edit (`GET /{id}`), update, delete.

**Contract** (`/client/contract`): index, create, select2, store, edit (`GET /{id}`), update, delete.

**Employee** (`/employee`): index, select2, create, store, edit (`GET /{id}`), update, delete.

**Department** (`/department`): index, select2, create, store, edit (`GET /{id}`), update, delete.

**Divisi** (`/divisi`): index, select2, create, store, edit (`GET /{id}`), update, delete.

### 3.4 Upload/import hasil (`auth` + `mcu`) — `UploadFileController`

| Method | URI | Nama |
|---|---|---|
| GET | `/participant-upload-validasi-dokter` | `upload.validasi.dokter` |
| POST | `/participant-upload-validasi-dokter` | — |
| GET | `/get-participant-upload-validasi-dokter` | `get.validasi.dokter` |
| GET | `/participant-upload-validasi-dokter/template` | `upload.validasi.dokter.template` |
| GET | `/participant-upload-laboratorium` | `upload.laboratorium` |
| GET | `/get-participant-upload-laboratorium` | `get.laboratorium` |
| POST | `/participant-upload-laboratorium` | — |
| GET | `/participant-upload-radiologi` | `upload.radiologi` |
| POST | `/participant-upload-radiologi` | — |
| GET | `/participant-upload-umum` | `upload.generalResult` |
| POST | `/participant-upload-umum` | — |
| GET | `/participant-upload-umum/template` | `upload.generalResult.template` |

### 3.5 Peserta (`auth` + `mcu`, prefix `participant`)

| Method | URI | Action | Nama |
|---|---|---|---|
| GET | `/participant` | ParticipantController@index | `participant.index` |
| GET | `/participant/create` | ParticipantController@create | `participant.create` |
| GET | `/participant/filter` | ParticipantController@filter | `participant.filter` |
| POST | `/participant` | ParticipantController@store | `participant.store` |
| GET | `/participant/{id}` | ParticipantController@edit | `participant.edit` |
| GET | `/participant/detail/{id}` | ParticipantController@detail | `participant.detail` |
| PUT | `/participant/{id}` | ParticipantController@update | `participant.update` |
| DELETE | `/participant/{id}` | ParticipantController@destroy | `participant.delete` |
| GET | `/participant/register/{id}` | ParticipantController@updateRegister | `participant.update.register` |
| GET | `/participant/register-bulk/{date}` | ParticipantController@updateRegisters | `participant.update.registers` |
| GET | `/participant/scan/{mcuId}` | ParticipantController@scan | `participant.scan` |
| POST | `/participant/import` | ParticipantController@import | `participant.import` |

**Detail pemeriksaan** (GET = form/detail, PUT/POST = simpan):

| URI (prefix `participant/detail`) | Jenis |
|---|---|
| `/tanda-vital/{id}` | Tanda vital |
| `/pemeriksaan-fisik/{id}` | Pemeriksaan fisik |
| `/laboratorium/{id}` | Laboratorium |
| `/radiologi/{id}` | Radiologi |
| `/audiometri/{id}` | Audiometri |
| `/spirometri/{id}` | Spirometri |
| `/rectal/{id}` | Rectal |
| `/ekg/{id}` | EKG |
| `/foto-kamera/{id}` | Foto kamera |
| `/foto-komputer/{id}` (POST untuk simpan) | Foto dari komputer (upload) |

### 3.6 Cetak & register (`auth` + `mcu`)

| Method | URI | Action | Nama |
|---|---|---|---|
| GET | `/participant-print-identitas` | View statis `pages.participant.indetitas` | — |
| GET | `/participant-register` | ParticipantController@register | `participant.register` |
| GET | `/participant-print-mcu` | ParticipantController@printMCU | `participant.print.mcu` |

### 3.7 Laporan PDF per peserta (`auth`, prefix `report/pdf`) — `ReportController`

| URI | Nama |
|---|---|
| `/report/pdf/identitas/{participantId}` | `report.identitas` |
| `/report/pdf/tanda-vital/{participantId}` | `report.tanda.vital` |
| `/report/pdf/pemeriksaan-fisik/{participantId}` | `report.pemeriksaan.fisik` |
| `/report/pdf/laboratorium/{participantId}` | `report.laboratorium` |
| `/report/pdf/laboratorium-lab-driver/{participantId}` | `report.laboratorium.lab.driver` |
| `/report/pdf/laboratorium-sgpt-ureum/{participantId}` | `report.laboratorium.sgpt.ureum` |
| `/report/pdf/radiologi/{participantId}` | `report.radiologi` |
| `/report/pdf/audiometri/{participantId}` | `report.audiometri` |
| `/report/pdf/spirometri/{participantId}` | `report.spirometri` |
| `/report/pdf/rectal/{participantId}` | `report.rectal` |
| `/report/pdf/ekg/{participantId}` | `report.ekg` |
| `/report/pdf/sticker-lab/{participantId}` | `report.sticker.lab` |
| `/report/pdf/sticker-5pcs/{participantId}` | `report.sticker.5pcs` |
| `/report/pdf/report-register/{id}` | `report.register` |
| `/report/pdf/report-resume/{id}` | `report.resume` |

### 3.8 Laporan gabungan (`auth` + `mcu`) — `ReportMultipleController`

`/multiple-spirometri`, `/multiple-identitas`, `/multiple-ekg`, `/multiple-laboratorium`, `/multiple-rectal`, `/multiple-radiologi`, `/multiple-pemeriksaan-fisik`, `/multiple-resume`, `/multiple-audiometri`.

### 3.9 Rekap (`auth` + `mcu`) — `RecapController`

| URI | Nama |
|---|---|
| `/report-hasil` | `report.results` |
| `/report-import-result-mcu` | `report.importResultMcu` |
| `/report-recap-register` | `recap.register` |
| `/report-data-register` | `recap.dataRegister` |

### 3.10 File route lain

- `routes/api.php` — grup `api`; **tidak ada route aktif** (hanya contoh Sanctum yang dikomentari).
- `routes/console.php` — closure command `inspire()`.
- `routes/channels.php` — private channel `App.Models.User.{id}` + otorisasi.

---

## 4. Controller & tanggung jawab

### 4.1 Root `app/Http/Controllers`

| Controller | Dipakai route? | Tanggung jawab |
|---|---|---|
| `Controller` | — | Base controller |
| `AudiometriController` | tidak | Manajemen pemeriksaan audiometri (tidak direferensikan `web.php`) |
| `ClientController` | ya | CRUD client |
| `ContractController` | ya | CRUD kontrak + select2 |
| `DepartmentController` | ya | CRUD department + select2 |
| `DivisiController` | ya | CRUD divisi + select2 |
| `EkgController` | tidak | Manajemen pemeriksaan EKG (tidak dirutekan) |
| `EmployeeController` | ya | CRUD karyawan/petugas + select2 |
| `LaboratoriumController` | tidak | Manajemen pemeriksaan laboratorium (tidak dirutekan) |
| `McuController` | ya | Alur MCU IN/OUT |
| `MenuController` | ya | CRUD menu hierarkis (parent/child) + toggle aktif |
| `ParticipantController` (610 baris) | ya | Inti siklus peserta: CRUD, register tunggal/massal, scan QR, halaman detail semua jenis pemeriksaan, upload foto (kamera/komputer), import peserta dari Excel, cetak daftar MCU |
| `PemeriksaanFisikController` | tidak | Manajemen pemeriksaan fisik (tidak dirutekan) |
| `PermissionController` | tidak | CRUD permission (tidak dirutekan; assignment lewat RoleController) |
| `RadiologiController` | tidak | Manajemen pemeriksaan radiologi (tidak dirutekan) |
| `RecapController` | ya | Rekap/laporan (hasil, import result MCU, recap register, data register) |
| `RectalController` | tidak | Manajemen pemeriksaan rectal (tidak dirutekan) |
| `ReportController` | ya | PDF individual (identitas, tanda vital, fisik, lab, radiologi, audiometri, spirometri, rectal, EKG, stiker, register, resume) via dompdf |
| `ReportMultipleController` | ya | PDF gabungan/batch per jenis + resume |
| `RoleController` | ya | CRUD role + matriks permission (view/add/edit/delete per menu) |
| `SpirometriController` | tidak | Manajemen pemeriksaan spirometri (tidak dirutekan) |
| `TandaVitalController` | tidak | Manajemen tanda vital (tidak dirutekan) |
| `UploadFileController` | ya | Import/upload Excel (lab, radiologi, hasil umum, validasi dokter) + template + DataTables dengan filter/sort |
| `UserController` | ya | CRUD akun user |

### 4.2 `app/Http/Controllers/Auth/`

- **LoginController** — login admin & login peserta (via token), logout, view index.

### 4.3 `app/Http/Controllers/Admin/`

- **DashboardController** — dashboard.

> **Catatan:** `ParticipantController` adalah hub utama; pelaporan dipisah menjadi individual (`ReportController`) dan batch (`ReportMultipleController`); `UploadFileController` memusatkan import Excel hasil. Controller per-jenis-pemeriksaan (Audiometri, Ekg, Lab, Fisik, Radiologi, Rectal, Spirometri, TandaVital) dan `PermissionController` ada di kode tetapi **tidak dipakai route** — kemungkinan sisa/dead code.

---

## 5. Model & relasi

| Model | Tabel | Relasi / catatan |
|---|---|---|
| `Audiometri` | `audiometris` | belongsTo Employee (via `employee_id`) |
| `Client` | `clients` | hasMany Contract |
| `Contract` | `contracts` | belongsTo Client; hasMany Participant; **SoftDeletes** |
| `Department` | `departments` | terkait Client (`client_id`) |
| `Divisi` | `divisis` | terkait peserta/karyawan |
| `Ekg` | `ekgs` | belongsTo Employee |
| `Employee` | `employees` | dipakai sebagai pemeriksa di seluruh jenis pemeriksaan |
| `GeneralResult` | `general_results` | hasil umum |
| `Laboratorium` | `laboratoria` | belongsTo Employee; relasi peserta via FK |
| `Menu` | `menus` | hierarkis (`parent_id`, children), `is_active`, `sort_order`; method `checkAvailabilityMenuPermission()`, `getMenuByRoleID()`, `getMenuByUserRole()`, `buildTree()` |
| `Participant` | `participants` | `$guarded=['id']`; relasi: user, divisi, department, client, contract, tandaVital, pemeriksaanFisik, laboratorium, radiologi, audiometri, spirometri, rectal, ekg, validateDoctor (hasOne); scope: Divisi, DateRange (`YYYY-MM-DD to YYYY-MM-DD`), FormRange; menyimpan flag status kelengkapan pemeriksaan (`selesai`) |
| `PemeriksaanFisik` | `pemeriksaan_fisiks` | belongsTo Employee |
| `Permission` | `permissions` | fillable: `role_id, menu_id, is_view, is_add, is_edit, is_delete` |
| `Radiologi` | `radiologis` | belongsTo Employee |
| `Rectal` | `rectals` | belongsTo Employee |
| `Role` | `roles` | fillable: `level, name, is_active`; level konstan di RoleService |
| `Spirometri` | `spirometris` | belongsTo Employee |
| `TandaVital` | `tanda_vitals` | belongsTo Employee |
| `User` | `users` | Authenticatable; HasApiTokens, HasFactory, Notifiable, **SoftDeletes**; fillable: `username, role_id, is_active, client_id, photo`; belongsTo Role & Client |
| `ValidateDoctor` | `validate_doctors` | dihubungkan ke peserta via code/`no_mcu` |

**Pola relasi:** setiap jenis pemeriksaan adalah tabel terpisah yang direferensikan peserta; Employee ditempel sebagai pemeriksa. Soft delete hanya pada `User` dan `Contract`. Setiap tabel hasil menyimpan flag `selesai` (dipakai recap progres, lihat `DataRegisterResource`).

---

## 6. Middleware, Kernel & autentikasi

### 6.1 Middleware kustom

| Middleware | Alias | Fungsi |
|---|---|---|
| `McuMiddleware` | `mcu` | Cek session `client_id` & `contract_id`. Jika kosong → tampilkan view `pages.user.mcu` (daftar client terpaginasi 1000 + kontrak kosong). Memagari operasi MCU. |
| `Authenticate` | `auth` | Auth standar + menu tree |
| `RedirectIfAuthenticated` | `guest` | Guest middleware |
| `TrustHosts` | — | Ada di kode tetapi **dinonaktifkan** (di-comment di Kernel) |
| Standar Laravel | — | EncryptCookies, VerifyCsrfToken, TrimStrings, TrustProxies, PreventRequestsDuringMaintenance, ValidateSignature |

### 6.2 `app/Http/Kernel.php`

- **Global middleware:** TrustProxies, HandleCors, PreventRequestsDuringMaintenance, ValidatePostSize, TrimStrings, ConvertEmptyStringsToNull (TrustHosts dikomentari).
- **Grup `web`:** EncryptCookies, AddQueuedCookiesToResponse, StartSession, ShareErrorsFromSession, VerifyCsrfToken, SubstituteBindings.
- **Grup `api`:** ThrottleRequests:api + SubstituteBindings (Sanctum stateful dikomentari).
- **Alias:** `mcu`, `auth`, `auth.basic`, `auth.session`, `cache.headers`, `can`, `guest`, `password.confirm`, `precognitive`, `signed`, `throttle`, `verified`.

### 6.3 Fitur autentikasi & rate limit

- Login via **username atau email** + password; checkbox remember me.
- Login peserta via token (`login.peserta.token`).
- Role-based menu visibility via permission.
- Konteks client disimpan di session (`client_id`, `contract_id`) untuk operasi MCU (scoping mirip multi-tenant per client).
- **Rate limiter** `api`: 60 request/menit per user/IP (didefinisikan di `RouteServiceProvider`).
- `AppServiceProvider`: memaksa root URL + skema HTTPS bila `APP_URL` berawalan `https://`.

### 6.4 Verifikasi perilaku middleware & temuan

Hasil audit alur middleware (definisi → Kernel → route):

**Alur yang benar:**
- Semua route kecuali grup `/auth` berada di dalam grup `auth`, sehingga wajib login. `Authenticate` memeriksa `Auth::check()`, menolak user tanpa `role_id`, lalu membagikan menu tree + user ke view.
- `mcu` (alias `McuMiddleware`) dipasang pada grup upload, grup `participant`, serta route `participant-register`, `multiple-*`, `participant-print-mcu`, dan `report-*` (hasIl/register/data-register).
- CSRF (`VerifyCsrfToken`) aktif di grup `web` tanpa pengecualian; cookie session `httpOnly=true`, `sameSite=lax`.
- Rate limiter grup `api` = 60/menit.

**Temuan (perilaku perlu diperbaiki saat recode):**

| # | Temuan | Bukti | Dampak |
|---|---|---|---|
| M1 | Gate MCU memakai logika **`&&`** — hanya memblokir bila `client_id` **dan** `contract_id` sama-sama kosong | `McuMiddleware::handle` | Sesi dengan hanya salah satu terisi (mis. client tanpa kontrak) lolos ke operasi MCU |
| M2 | Gate MCU **mengembalikan view HTTP 200** `pages.user.mcu`, bukan redirect/403 | `McuMiddleware` | URL tetap di route terproteksi; status tidak eksplisit |
| M3 | **Login tanpa rate limit** dan alias `guest` tidak dipakai di route mana pun | `/auth` POST, `RedirectIfAuthenticated` tak terdaftar di route | Rentan brute force; user login bisa mengakses ulang halaman login |
| M4 | **Aksi mengubah data via GET** (CSRF tidak berlaku untuk GET) | `GET /participant/register/{id}`, `GET /participant/register-bulk/{date}`, `GET /participant/scan/{mcuId}`, `GET /auth/logout`, `GET /report-import-result-mcu` (queue export), `GET /report-data-register` (ajax export) | Tidak idempoten & bisa di-CSRF lewat gambar/link |
| M5 | **Tidak ada scoping kepemilikan pada PDF** — `ReportController` mencari peserta murni via `find($id)` | `ReportController@identitas` dll. | IDOR: user login mana pun dapat mencetak report peserta mana pun bila tahu `id` |
| M6 | Route `report/pdf/*` hanya `auth` (tanpa `mcu`) | `routes/web.php` §203 | Report bisa diakses tanpa konteks client/kontrak |
| M7 | `TrustProxies = '*'` (mempercayai semua proxy) dan `TrustHosts` **dinonaktifkan** | `TrustProxies`, `Kernel` | `X-Forwarded-For` dapat dipalsukan (pengaruh rate limit & audit IP); risiko Host header injection |
| M8 | `session.encrypt = false`; `SESSION_SECURE_COOKIE` tidak di-set default | `config/session.php` | Payload session tidak dienkripsi; cookie bisa tidak `Secure` bila env lupa diisi |
| M9 | Middleware `Authenticate` membangun menu tree di setiap request | `Authenticate::handle` | Query menu berulang (performa) |

> Semua temuan ini punya padanan target di `RECODE_TO_ASTRO_VUE_POSTGREST.md` (temuan middleware lama dipetakan di **§2.7**, dan kontrol targetnya di **§18.2**: RLS, rate limit login, cookie flags, `security.checkOrigin`, origin check untuk request yang mengubah data).

---

## 7. RBAC (role, menu, permission)

Implementasi **kustom berbasis tabel**, bukan paket Spatie Permission.

- `Role` — level 1–4 (konstan di `RoleService`: `LEVEL_ADMIN=1`, `LEVEL_CLIENT=2`, `LEVEL_EMPLOYEE=3`, `LEVEL_PARTICIPANT=4`). Seeder: Administrator, Admin Client, Admin Karyawan, Peserta.
- `Menu` — menu hierarkis dengan status aktif; method pembangun tree & fetch menu per role.
- `Permission` — matriks `role_id` × `menu_id` dengan flag `is_view/is_add/is_edit/is_delete`.
- `RoleController` — assignment matriks (GET permission view, POST `permissionCreate` dengan `updateOrCreate`).
- Seeder: `RoleSeeder`, `MenuSeeder`, `PermissionSeeder`.

---

## 8. Master opsi form (dropdown)

Opsi dropdown/select pada form pemeriksaan **tidak berasal dari tabel master**, melainkan dari method enum di service:

- **`ParticipantService`** mengembalikan daftar opsi untuk form: jenis kelamin (JK), status karyawan, keadaan umum, buta warna, pupil, kelainan telinga, gigi, tenggorokan, faring, leher (KGB/JVP), inspeksi, inspeksi abdomen, auskultasi, palpasi, perkusi, kesimpulan, retriksi, obstruktif, pendengaran telinga kanan/kiri, dan `mapingPaket` (pemetaan paket pemeriksaan).
- **`EmployeeService`** mengembalikan opsi: status karyawan, status KTP, pendidikan, agama, jenis kelamin, golongan darah, status warga negara.

> Saat recode, daftar opsi ini harus dipindahkan menjadi sumber data terpusat (enum/tabel referensi) agar tidak terduplikasi di kode service.

---

## 9. Halaman (Blade view)

### 9.1 Layout

- `layouts/main.blade.php` — layout utama (header, navbar, sidebar, content, footer).
- `layouts/auth.blade.php` — layout autentikasi/login.

### 9.2 Modul `resources/views/pages/`

| Folder | Isi |
|---|---|
| `dashboard/` | Dashboard (gaya ecommerce Vuexy) |
| `login/` | Halaman login (logo, remember me) |
| `role/` | Manajemen role (index, matriks permission) |
| `menu/` | CRUD menu (index, create, edit, detail) |
| `user/` | Manajemen user |
| `client/` | Manajemen client |
| `contract/` | Manajemen kontrak |
| `employee/` | Manajemen karyawan |
| `department/` | Manajemen department |
| `divisi/` | Manajemen divisi |
| `participant/` | Peserta: `index`, `create`, `edit`, `detail`, `filter`, `indetitas` (cetak identitas), `print-mcu`, dan form per pemeriksaan: `tanda-vital`, `pemeriksaan-fisik`, `laboratorium`, `radiologi`, `audiometri`, `spirometri`, `rectal`, `ekg`, `foto-kamera`, `foto-komputer` |
| `report/` | Template PDF: `identitas`, `tanda-vital` (via header), `pemeriksaan-fisik`, `laboratorium`, `laboratorium-lab-driver`, `laboratorium-sgpt-ureum`, `radiologi`, `audiometri`, `spirometri`, `rectal`, `ekg`, `sticker-lab`, `sticker-5pcs`, `register`, `resume`, `results`, `header`; subfolder `multiple/` (identitas, lab, radiologi, audiometri, spirometri, rectal, ekg, pemeriksaan-fisik, resume, register, empty) |
| `recaps/` | Rekap: `register`, `dataRegister`, `results` |
| `upload/` | Upload/import: `validate-dokter`, `laboratorium`, `radiologi`, `general_result` |

### 9.3 Lain-lain

- `partials/` — content, footer, header, navbar, pagination, sidebar.
- `exports/` — template export (dataRegister, resultMcu).
- `pdf/` — view terkait PDF (dataRegister).
- `welcome.blade.php`.

### 9.4 Tema

Menggunakan aset tema **Vuexy** (`public/app-assets`), font Montserrat, logo `public/logo-drdini.png`.

---

## 10. Peta menu & status route

`MenuSeeder` (hanya di-seed untuk environment `local`) mendefinisikan menu sidebar. Beberapa menu **belum memiliki route** — ini kandidat fitur yang direncanakan namun belum diimplementasi (dead link), dan **bukan** termasuk scope paritas.

### 10.1 Menu dengan route (fitur aktif)

| Grup | Menu | URL | Status |
|---|---|---|---|
| — | Home | `/` | ✓ |
| — | MCU IN/OUT | `/mcu-in-out` | ✓ |
| Master Data | Master Department | `/department` | ✓ |
| Master Data | Master Divisi | `/divisi` | ✓ |
| Master Data | Master Karyawan | `/employee` | ✓ |
| Administrator | Role | `/role` | ✓ |
| Administrator | Menu | `/menu` | ✓ |
| Administrator | Client | `/client` | ✓ |
| Administrator | User | `/user` | ✓ |
| MCU | Data Peserta | `/participant` | ✓ |
| MCU | Register | `/participant-register` | ✓ |
| MCU | Print Hasil MCU | `/participant-print-mcu` | ✓ |
| MCU | Print Identitas | `/participant-print-identitas` | ✓ |
| MCU | Upload Validasi Dokter | `/participant-upload-validasi-dokter` | ✓ |
| MCU | Upload Hasil Laboratorium | `/participant-upload-laboratorium` | ✓ |
| MCU | Upload Hasil Radiologi | `/participant-upload-radilogi` (typo) | ✓ route asli `/participant-upload-radiologi` |
| MCU | Upload Hasil Umum | `/participant-upload-umum` | ✓ |
| Laporan | Rekap Register | `/report-recap-register` | ✓ |
| Laporan | Data Register | `/report-data-register` | ✓ |
| Laporan | Hasil | `/report-hasil` | ✓ |

### 10.2 Menu tanpa route (belum diimplementasi / dead link)

| Grup | Menu | URL | Catatan |
|---|---|---|---|
| MCU | Print Peserta | `/participant-print` | tidak ada route |
| MCU | Kartu Peserta | `/participant-kartu` | tidak ada route |
| MCU | Sticker Peserta | `/participant-sticker` | stiker ada di `/report/pdf/sticker-*` |
| MCU | Upload Hasil EKG | `/participant-upload-ekg` | tidak ada route/import |
| MCU | Upload Hasil Audiometri | `/participant-upload-audiometri` | tidak ada route/import |
| MCU | Upload Hasil Spiro | `/participant-upload-spiro` | tidak ada route/import |
| MCU | Validasi Dokter | `/participant-validasi-dokter` | tidak ada route |
| Laporan | Progress | `/report-progress` | tidak ada route |
| Laporan | Rekap Invoice | `/report-recap-invoice` | tidak ada route; **tidak ada modul invoice** |
| Laporan | Double Register | `/report-double-register` | tidak ada route |
| Laporan | Data Invoice | `/report-data-invoice` | tidak ada route |
| Laporan | Data Register Kurang | `/report-data-register-kurang` | tidak ada route |
| Laporan | Ibu Hamil | `/report-ibu-hamil` | tidak ada route |
| Laporan | Pasien Sprio | `/report-pasien-spiro` | tidak ada route |
| Laporan | Pasien Rectal | `/report-pasien-rectal` | tidak ada route |
| Laporan | Pasien EKG | `/report-pasien-ekg` | tidak ada route |
| Laporan | Pasien Audiometri | `/report-pasien-audiometri` | tidak ada route |
| Laporan | Pasien Indikasi Unfit | `/report-indikasi-unfit` | tidak ada route |
| Laporan | Pasien Disabilitas | `/report-pasien-disabilitas` | tidak ada route |
| Laporan | Tensi > 140 | `/report-tensi` | tidak ada route |
| Laporan | Grafik | `/report-grafik` | tidak ada route |
| Laporan | Statistik | `/report-statistik` | tidak ada route |
| Laporan | Cari Hasil | `/report-cari-hasil` | tidak ada route |
| Laporan | Hasil per MCU | `/report-hasil-per-mcu` | tidak ada route |

### 10.3 Route tanpa menu (fitur ada, tidak tampil di sidebar)

`/report-import-result-mcu` (route ada, tidak ada menu), `/report/pdf/*`, `/multiple-*`, `/participant/filter`, `/participant/scan`, `/participant/create`, `/dashboard`.

> Untuk recode: putuskan bersama pemilik produk apakah §10.2 masuk scope (fitur baru) atau sengaja diabaikan. Jangan menyalin menu dead link apa adanya.

---

## 11. Job, Event, Listener, Notification, Service

### 11.1 Jobs (`app/Jobs/`)

- `GeneratePdfJob` — job generate PDF.
- `GenerateReportPdfJob` (ShouldQueue) — generate banyak PDF per chunk, simpan ke `Storage::put('public/reports/...')`, buat ZIP (`ZipArchive`), dan memicu **Pusher** saat selesai. Memakai Barryvdh DomPDF.
- `TestJob` — job uji coba.

### 11.2 Events (`app/Events/`)

- `ImportCompleted`
- `ImportProgressUpdated`

### 11.3 Listeners (`app/Listeners/`)

- `ImportCompletedListener`

### 11.4 Notifications (`app/Notifications/`)

- `ReportGeneratedNotification`

### 11.5 Services (`app/Services/`)

| Service | Peran |
|---|---|
| `ParticipantService` | Inti: query/find/paginate, create (kode unik `MCU#####` + akun peserta), update, delete, `updateRegister`, `updateRegisters` (massal), `scan`, lookup peserta, dan delegasi `update<JenisPemeriksaan>`, plus **master opsi form** (§8) |
| `EmployeeService` | `findOrCreateByName`/`findByName`/`findOrCreateIdByName` (normalisasi nama, buang gelar), `create/update/delete`, generate kode, **master opsi karyawan** |
| `McuService` | `mcuIn(clientId, contractId)`, `mcuOut()` (konteks session) |
| `ClientService`, `ContractService`, `DepartmentService`, `DivisiService`, `UserService` | Business logic CRUD masing-masing entitas |
| `RoleService` | Konstan level role |

### 11.6 Providers (`app/Providers/`)

| Provider | Isi |
|---|---|
| `AppServiceProvider` | Memaksa root URL + HTTPS dari `APP_URL` |
| `AuthServiceProvider` | Mapping policy kosong (tidak ada policy) |
| `BroadcastServiceProvider` | `Broadcast::routes()` + `routes/channels.php` |
| `EventServiceProvider` | Mapping `Registered` → `SendEmailVerificationNotification` + `ImportCompletedListener`; `shouldDiscoverEvents()` = false |
| `RouteServiceProvider` | Rate limiter `api` (60/menit); registrasi route web & api |

### 11.7 Tidak ada

- **Policies**, **Observers**, **Repositories**, **Enums** — tidak ditemukan (memakai Eloquent langsung + Service).

---

## 12. Console command & scheduler

`app/Console/Kernel.php`:

- **Scheduler:** `backup:database` dijalankan **setiap hari 00:00 WIB**, `withoutOverlapping()`.

`app/Console/Commands/`:

| Command | Signature | Fungsi |
|---|---|---|
| `BackupDatabase` | `backup:database {--disk=} {--folder=} {--keep-local}` | Dump MySQL via `mysqldump` (`--single-transaction --quick --routines --triggers`), kompres gzip, unggah ke disk (default R2/Cloudflare, folder default `12 oktober`), opsional simpan salinan lokal. Membuat penanda folder untuk S3/R2. |
| `GenerateRegister` | `app:generate-register` | Batch generate PDF register per peserta ke `storage/app/public/reports/{divisi}/{code}.pdf`, menghitung usia (tahun/bulan/hari) |

`routes/console.php`: closure command `inspire()`.

---

## 13. Helper, HTTP Resource, Exception

| File | Isi |
|---|---|
| `app/Helpers/Helper.php` | `getNumber()` (nomor baris DataTables dari page/limit) dan `generateDatatable(array $data, array $params)` (membentuk payload DataTables) |
| `app/Http/Resources/DataRegisterResource.php` | Transform peserta → payload data register: name, code, nik, register_date, birthday, gender, packet_name, gedung, divisi, department, perusahaan, dan status `SELESAI/TIDAK` untuk ttv, pemeriksaan fisik, lab, radiologi, audiometri, spirometri, rectal, ekg |
| `app/Exceptions/Handler.php` | Handler standar (`dontFlash` password) |

---

## 14. Skema database & seeder

### 14.1 Migrasi (38 file = 23 create table + 15 alter)

| Domain | Tabel |
|---|---|
| Core/auth | `users`, `password_reset_tokens`, `roles`, `menus`, `permissions`, `failed_jobs`, `jobs`, `personal_access_tokens` |
| Master data | `clients`, `contracts` (+ soft deletes), `departments`, `employees`, `divisis` |
| MCU core | `participants`, `tanda_vitals`, `pemeriksaan_fisiks`, `laboratoria`, `radiologis`, `spirometris`, `audiometris`, `rectals`, `ekgs`, `general_results`, `validate_doctors` |

**Alterasi penting:** `add_field_role_to_users`, `add_field_client_to_users_table`, penambahan berulang pada `laboratoria` (form, client_id, contract_id), `add_no_form_to_participants`, `add_date_to_radiologis`, `add_contract_id` ke beberapa tabel, `add_soft_deletes_to_contracts`, `add_kacamata_to_periksaan_fisiks`, penambahan `general_results` + `contract_id`.

### 14.2 Seeder

| Seeder | Isi |
|---|---|
| `DatabaseSeeder` | Orkestrator |
| `RoleSeeder` | 4 role (level 1–4) |
| `MenuSeeder` | Struktur menu (hanya env `local`) |
| `PermissionSeeder` | Matriks permission |
| `UserSeeder` | Super Admin (`admin` / `password`, env `local`) |
| `UserFactorySeeder`, `DataDummySeeder` | Data dummy |

---

## 15. Import, export, PDF, QR, cetak

### 15.1 PDF & cetak

**Mesin PDF:** `barryvdh/laravel-dompdf` (PDF per peserta & gabungan), `mpdf/mpdf` (export data register), `setasign/fpdi` + `TCPDI` (gabung berkas PDF audiometri).

#### A. PDF per peserta — `ReportController` (prefix `/report/pdf`, `auth`)

Semua `return $pdf->stream('{code}.pdf')` (inline di browser). View di `resources/views/pages/report/`.

| Endpoint | View | Catatan |
|---|---|---|
| `identitas/{id}` | `report.identitas` | QR login peserta (base64, size 150) |
| `tanda-vital/{id}` | `report.tanda-vital` | **BROKEN** — file view tidak ada; route akan error |
| `pemeriksaan-fisik/{id}` | `report.pemeriksaan-fisik` | |
| `laboratorium/{id}` | `report.laboratorium` | |
| `laboratorium-lab-driver/{id}` | `report.laboratorium-lab-driver` | varian lab driver |
| `laboratorium-sgpt-ureum/{id}` | `report.laboratorium-sgpt-ureum` | varian SGPT/Ureum |
| `radiologi/{id}` | `report.radiologi` | |
| `audiometri/{id}` | `report.audiometri` | |
| `spirometri/{id}` | `report.spirometri` | |
| `rectal/{id}` | `report.rectal` | |
| `ekg/{id}` | `report.ekg` | |
| `sticker-lab/{id}` | `report.sticker-lab` | label lab |
| `sticker-5pcs/{id}` | `report.sticker-5pcs` | label 5 pcs |
| `report-register/{id}` | `report.register` | QR eksternal (qrserver.com); margin 0 |
| `report-resume/{id}` | `report.resume` | |

Header PDF memakai partial `report/header.blade.php` (QR paket `simple-qrcode`, base64 size 65).

**Method tanpa route:** `ReportController@audiometriBulk(array $participantIds)` — gabung banyak PDF audiometri memakai `setasign\Fpdi\Fpdi` + `TCPDI` menjadi satu berkas. Tidak dipanggil route mana pun (dead code).

#### B. PDF gabungan/batch — `ReportMultipleController` (9 endpoint, `auth` + `mcu`)

| Endpoint | View | Sumber data |
|---|---|---|
| `/multiple-identitas` | `report/multiple/identitas` | peserta (stiker) — diberi nomor halaman "No Form N" via `canvas->page_script` |
| `/multiple-resume` | `report/multiple/resume` | peserta yang sudah `validateDoctor` |
| `/multiple-pemeriksaan-fisik` | `report/multiple/pemeriksaan-fisik` | `pemeriksaan_fisiks` `selesai=1` |
| `/multiple-laboratorium` | `report/multiple/laboratorium` | `laboratoria` `selesai=1` |
| `/multiple-radiologi` | `report/multiple/radiologi` | `radiologis` `selesai=1` |
| `/multiple-audiometri` | `report/multiple/audiometri` | peserta dengan audiometri `selesai=1` |
| `/multiple-ekg` | `report/multiple/ekg` | `ekgs` `selesai=1` |
| `/multiple-spirometri` | `report/multiple/spirometri` | `spirometris` `selesai=1` |
| `/multiple-rectal` | `report/multiple/rectal` | `rectals` `selesai=1` |

Karakteristik penting:
- **Rentang nomor** dari query `start` & `end` ("Dari No / Sampai No") → `skip(start-1)->take(end-start+1)`.
- **Scope client + kontrak dipaksa dari session MCU IN** (nilai dari URL diabaikan); filter `divisi_id` + `date_range` boleh dari halaman.
- Jika data kosong → render view `report/multiple/empty` (bukan berkas kosong tanpa keterangan).
- Nama berkas: `{jenis}-{start}-{end}.pdf` (kecuali identitas → `print-stiker-{start}-{end}.pdf`).
- Template `report/multiple/register` ada tetapi **belum dirutekan**.

#### C. Export rekap & data register — `RecapController`

| Aksi | Mekanisme | Output |
|---|---|---|
| Data Register → PDF | **mPDF** (`\Mpdf\Mpdf`) render `exports.dataRegister`, simpan ke `storage/app/public/pdf/`, balas `download_url` (ajax) | `dataRegister_{ts}.pdf` |
| Data Register → Excel | `Queue::push` + `Excel::store(new DataRegisterExport)` ke `hasil_register_mcu/` | `hasil_register_mcu{ts}.xlsx` |
| Import Result MCU → Excel | `Queue::push` + `Excel::store(new ResultMcu)` ke `hasil_mcu/` | `results_{ts}.xlsx` |
| Hasil (`/report-hasil`), Rekap Register (`/report-recap-register`), Data Register (`/report-data-register`) | Halaman HTML (bukan PDF) dengan data via ajax | — |

> `GeneratePdfJob` (view `pdf/dataRegister`) di-import tetapi pemanggilannya dikomentari — tidak aktif.

#### D. Job PDF

| Job | Perilaku |
|---|---|
| `GeneratePdfJob` | Render `pdf.dataRegister` → `Storage::put('pdf/...')`. (Tidak dipanggil aktif.) |
| `GenerateReportPdfJob` | Render `report/multiple/pemeriksaan-fisik` per chunk → `Storage::put('public/reports/...')` → ZIP (`ZipArchive`) → notifikasi **Pusher** |

#### E. Halaman cetak (UI)

- **Print Hasil MCU** (`/participant-print-mcu`): hub cetak. Menyediakan tombol **bulk** (9 endpoint `/multiple-*`) dan tombol **per peserta** untuk 9 jenis (identitas, resume, pemeriksaan fisik, laboratorium, radiologi, audiometri, EKG, spirometri, rectal). Membuka PDF via `window.open`. Catatan: tombol tanda vital & stiker **tidak** ada di hub (konsisten dengan `tanda-vital` yang view-nya hilang).
- **Print Identitas** (`/participant-print-identitas`): view statis `pages.participant.indetitas` (HTML, bukan PDF).

### 15.2 Import Excel (Maatwebsite Excel)

Kelas import: `UsersImport` (peserta), `LaboratoriumImport`, `RadiologiImport`, `DoctorValidateImport`, `GeneralResultImport`. Controller: `ParticipantController@import`, `UploadFileController`.

Karakteristik: pencocokan cerdas `NO_MCU`/`Code` ke `participants.code` **atau** `no_form` (jika angka murni), dibatasi client + kontrak aktif, `updateOrCreate` per peserta; melacak `processed`/`skipped`/`skipped_codes`; pemeriksa di-resolve ke `employees` via `EmployeeService` (findOrCreate).

### 15.3 Export & template Excel

Kelas export: `UsersExport`, `DataRegisterExport`, `ResultMcu`, `DoctorValidateTemplateExport`, `GeneralResultTemplateExport`. Template dapat diunduh dari `UploadFileController`.

### 15.4 Chart

Dashboard mereferensikan apexcharts (`app-assets/vendors/js/charts/apexcharts.min.js`) + CSS dashboard ecommerce. (Menu "Grafik"/"Statistik" belum dirutekan.)

### 15.5 QR Code

Dua cara:
- **Paket `simple-qrcode`** (base64 PNG) di `report/header.blade.php` (size 65, isi `participant->code`) dan di `report/identitas.blade.php` serta `report/multiple/identitas.blade.php` (size 150, isi **URL token login peserta** `route('login.peserta.token', $participant->code)`).
- **QR API eksternal** `api.qrserver.com` (size 60, isi `participant->code`) di `report/register.blade.php` dan `report/multiple/register.blade.php`.

### 15.6 Notifikasi/realtime

- Notification: `ReportGeneratedNotification`.
- Events/listener untuk penyelesaian import.
- Email: konfigurasi SMTP standar; event `Registered` mengirim verifikasi (tidak ada `Mail::` lain di kode).
- Realtime: Laravel Reverb + Pusher; `GenerateReportPdfJob` memicu Pusher saat selesai; broadcast channel privat untuk User (didaftarkan BroadcastServiceProvider, tidak ada pemanggilan `broadcast()` di kode app).

---

## 16. Storage, filesystem & backup

- Default disk `local`; ada disk `public` (visibility public), `s3`, dan **`r2`** (driver S3, Cloudflare R2).
- Laporan/PDF massal disimpan di `storage/app/public/reports/...`; batch register lama menyimpan ke `storage/app/public/reports/{divisi}/{code}.pdf`.
- Foto peserta & tanda tangan karyawan disimpan sebagai file.
- **Backup DB terjadwal** ke R2 (lihat §12), config `config/backup.php` (`disk='r2'`, `folder='12 oktober'`, binary `mysqldump`).

---

## 17. Integrasi & aset

### 17.1 Aset publik

- `public/logo-drdini.png` — logo brand (login, sidebar, auth).
- `public/app-assets/` — aset tema Vuexy (CSS, JS, font, gambar, vendors).

### 17.2 Integrasi khusus

- **DomPDF/MPDF** — generate PDF.
- **Maatwebsite Excel** — olah spreadsheet.
- **Pusher + Laravel Reverb** — notifikasi realtime (progres/penyelesaian job background).
- **Redis/Predis** — backend queue/cache.
- **S3 / Cloudflare R2** — file storage (Flysystem).
- **Octane** — application server performa tinggi.
- **Spatie Query Builder** — filter/sort lanjutan pada endpoint list (`UploadFileController`, query peserta).
- **Yajra DataTables** — tabel data.
- **QR API eksternal** — `api.qrserver.com` di view PDF register.

### 17.3 Sesi/konteks

Konteks MCU butuh `client_id` & `contract_id` di session (dipaksakan `McuMiddleware`). Multi-tenancy per `client_id` di seluruh model/query.

---

## 18. Automated test

`tests/Feature` (13 file) — mengunci perilaku yang sudah teruji dan menjadi acuan parity:

| Test | Perilaku yang diuji |
|---|---|
| `EmployeeResolverTest` | Resolusi nama pemeriksa → employee |
| `EmployeeSignatureTest` | Upload tanda tangan karyawan |
| `GeneralResultListingTest` | Listing hasil umum |
| `GeneralResultUploadTest` | Import hasil umum |
| `LaboratoriumImportTest` | Import laboratorium (pencocokan kode) |
| `LaboratoriumListingTest` | Listing laboratorium |
| `LaboratoriumUploadTest` | Upload laboratorium |
| `PrintMcuTest` | Cetak daftar MCU |
| `RadiologiListingTest` | Listing radiologi |
| `RadiologiUploadTest` | Upload radiologi |
| `ValidateDoctorListingTest` | Listing validasi dokter |
| `ValidateDoctorUploadTest` | Upload validasi dokter |
| `ExampleTest` | Placeholder |

Unit: `tests/Unit/ExampleTest.php`.

---

## 19. Fitur yang TIDAK ada

- **Payment/invoice** — tidak ada model/route/controller/paket (tidak ada Midtrans/Xendit/Stripe). Menu "Rekap Invoice"/"Data Invoice" hanyalah menu tanpa route (§10.2).
- **WhatsApp** — tidak ada integrasi (tidak ada library/service terkait).
- **API publik** — tidak ada route API aktif.
- **Policies** — tidak ada (otorisasi murni lewat RBAC menu/permission + middleware).

---

## 20. Ringkasan per domain

| Domain | Fitur kunci |
|---|---|
| Master data | Client, kontrak, karyawan, department, divisi, user, role, menu + permission granular |
| Peserta | Registrasi, pelacakan, import Excel, scan QR, alur pemeriksaan, akun peserta otomatis |
| Pemeriksaan medis | 8 jenis (tanda vital, fisik, lab, radiologi, audiometri, spirometri, rectal, EKG) + hasil umum + validasi dokter + foto kamera/komputer |
| Pelaporan | PDF individual & batch, import/export Excel, stiker, rekap, resume |
| Operasional | MCU IN/OUT, upload/import hasil eksternal, backup DB terjadwal ke R2 |
| Administrasi | RBAC matriks permission kustom, menu hierarkis, master opsi form |
| Rencana (menu tanpa route) | Progress, invoice, statistik, grafik, dan filter pasien khusus (lihat §10.2) |

Aplikasi ini kaya fitur dengan pelaporan, pemrosesan batch (queue), notifikasi realtime, import/export, dan backup terjadwal. Tidak ada integrasi payment maupun WhatsApp.

---

> Untuk memetakan setiap fitur di atas ke arsitektur target, lihat [`RECODE_TO_ASTRO_VUE_POSTGREST.md`](./RECODE_TO_ASTRO_VUE_POSTGREST.md), khususnya §2 (audit), §8.3 (pemetaan halaman), dan §13 (progres).
