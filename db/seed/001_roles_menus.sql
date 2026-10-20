begin;

-- Menu & permission tidak lagi di-seed di sini. Registri kanoniknya ada di
-- `db/migrations/0012_menu_registry.sql` (dipakai `api.v_my_menus` untuk
-- sidebar), jadi file ini hanya memastikan role dasar tersedia untuk akun.
-- Seed ini tetap idempoten supaya aman dijalankan berulang.

insert into public.roles (level, name, is_active)
values
  (1, 'Administrator', true),
  (2, 'Admin Client', true),
  (3, 'Admin Karyawan', true),
  (4, 'Peserta', true)
on conflict (level) do update
  set name = excluded.name,
      is_active = excluded.is_active;

commit;
