begin;

-- Registri menu kanonik + permission awal per level role.
--
-- Sebelumnya daftar menu hidup di kode (`apps/web/src/lib/nav.ts`) sebagai
-- jembatan sementara. Sekarang `menus` + `permissions` menjadi sumber tunggal
-- yang dibaca sidebar lewat `api.v_my_menus` (migrasi 0011).
--
-- Struktur, label, ikon, urutan, dan flag `is_active` disalin dari
-- `MenuSeeder` legacy (`database/seeders/MenuSeeder.php`) sehingga id menu
-- tetap sama dengan aplikasi lama dan mudah ditelusuri. Id dipertahankan
-- eksplisit karena `parent_id` menunjuk ke sana.
--
-- Catatan: `menus`/`permissions` belum pernah dimigrasi ETL (`db/etl` hanya
-- memindahkan data peserta & hasil), jadi migrasi ini yang memilikinya.

insert into public.roles (level, name, is_active)
values
  (1, 'Administrator', true),
  (2, 'Admin Client', true),
  (3, 'Admin Karyawan', true),
  (4, 'Peserta', true)
on conflict (level) do update
  set name = excluded.name,
      is_active = excluded.is_active;

delete from public.permissions;
delete from public.menus;

insert into public.menus (id, parent_id, name, icon, url, sort_order, is_active)
values
  (1, null, 'Home', 'home', '/', 1, true),
  (2, null, 'MCU IN/OUT', 'log-in', '/mcu-in-out', 2, true),
  (3, null, 'MCU', 'folder-plus', '#', 3, true),
  (4, null, 'Laporan', 'file-text', '#', 4, true),
  (5, null, 'Master Data', 'database', '#', 5, true),
  (6, null, 'Administrator', 'monitor', '#', 6, true),
  (7, 3, 'Data Peserta', 'circle', '/participant', 7, true),
  (8, 3, 'Register', 'circle', '/participant-register', 8, true),
  (9, 3, 'Print Hasil MCU', 'circle', '/participant-print-mcu', 9, true),
  (10, 3, 'Print Identitas', 'circle', '/participant-print-identitas', 10, true),
  (11, 3, 'Print Peserta', 'circle', '/participant-print', 11, false),
  (12, 3, 'Kartu Peserta', 'circle', '/participant-kartu', 12, false),
  (13, 3, 'Sticker Peserta', 'circle', '/participant-sticker', 13, false),
  (14, 3, 'Upload Validasi Dokter', 'circle', '/participant-upload-validasi-dokter', 14, true),
  (15, 3, 'Upload Hasil EKG', 'circle', '/participant-upload-ekg', 15, false),
  (16, 3, 'Upload Hasil Audiometri', 'circle', '/participant-upload-audiometri', 16, false),
  (17, 3, 'Upload Hasil Spiro', 'circle', '/participant-upload-spiro', 17, false),
  (18, 3, 'Validasi Dokter', 'circle', '/participant-validasi-dokter', 18, false),
  (19, 3, 'Upload Hasil Laboratorium', 'circle', '/participant-upload-laboratorium', 19, true),
  (20, 3, 'Upload Hasil Radiologi', 'circle', '/participant-upload-radiologi', 20, true),
  (21, 3, 'Upload Hasil Umum', 'circle', '/participant-upload-umum', 21, true),
  (22, 4, 'Rekap Register', 'circle', '/report-recap-register', 22, true),
  (23, 4, 'Data Register', 'circle', '/report-data-register', 23, true),
  (24, 4, 'Progress', 'circle', '/report-progress', 24, false),
  (25, 4, 'Hasil', 'circle', '/report-hasil', 25, true),
  (26, 4, 'Rekap Invoice', 'circle', '/report-recap-invoice', 26, false),
  (27, 4, 'Double Register', 'circle', '/report-double-register', 27, false),
  (28, 4, 'Data Invoice', 'circle', '/report-data-invoice', 28, false),
  (29, 4, 'Data Register Kurang', 'circle', '/report-data-register-kurang', 29, false),
  (30, 4, 'Ibu Hamil', 'circle', '/report-ibu-hamil', 30, false),
  (31, 4, 'Pasien Sprio', 'circle', '/report-pasien-spiro', 31, false),
  (32, 4, 'Pasien Rectal', 'circle', '/report-pasien-rectal', 32, false),
  (33, 4, 'Pasien EKG', 'circle', '/report-pasien-ekg', 33, false),
  (34, 4, 'Pasien Audiometri', 'circle', '/report-pasien-audiometri', 34, false),
  (35, 4, 'Pasien Indikasi Unfit', 'circle', '/report-indikasi-unfit', 35, false),
  (36, 4, 'Pasien Disabilitas', 'circle', '/report-pasien-disabilitas', 36, false),
  (37, 4, 'Tensi > 140', 'circle', '/report-tensi', 37, false),
  (38, 4, 'Grafik', 'circle', '/report-grafik', 38, false),
  (39, 4, 'Statistik', 'circle', '/report-statistik', 39, false),
  (40, 4, 'Cari Hasil', 'circle', '/report-cari-hasil', 40, false),
  (41, 4, 'Hasil per MCU', 'circle', '/report-hasil-per-mcu', 41, false),
  (42, 5, 'Master Department', 'circle', '/department', 42, true),
  (43, 5, 'Master Divisi', 'circle', '/divisi', 43, true),
  (44, 5, 'Master Karyawan', 'circle', '/employee', 44, true),
  (45, 6, 'Role', 'circle', '/role', 45, true),
  (46, 6, 'Client', 'circle', '/client', 46, true),
  (47, 6, 'User', 'circle', '/user', 47, true),
  (48, 6, 'Menu', 'list', '/menu', 48, true);

-- Id eksplisit di atas tidak memajukan sequence identity; samakan agar insert
-- menu berikutnya (menu CRUD) tidak bentrok.
select setval(pg_get_serial_sequence('public.menus', 'id'), (select max(id) from public.menus));

-- Permission awal menyamai sidebar sebelumnya: level 1-3 melihat seluruh menu,
-- level 4 (Peserta) hanya Home karena portal peserta belum ada di base ini.
-- Menambah/mengurangi akses cukup lewat tabel ini; kode hanya membaca.
insert into public.permissions (role_id, menu_id, is_view, is_add, is_edit, is_delete)
select r.id, m.id, true, true, true, r.level = 1
from public.roles r
cross join public.menus m
where r.level in (1, 2, 3)
on conflict (role_id, menu_id) do update
  set is_view = excluded.is_view,
      is_add = excluded.is_add,
      is_edit = excluded.is_edit,
      is_delete = excluded.is_delete;

insert into public.permissions (role_id, menu_id, is_view, is_add, is_edit, is_delete)
select r.id, m.id, true, false, false, false
from public.roles r
join public.menus m on m.id = 1
where r.level = 4
on conflict (role_id, menu_id) do update
  set is_view = true,
      is_add = false,
      is_edit = false,
      is_delete = false;

commit;
