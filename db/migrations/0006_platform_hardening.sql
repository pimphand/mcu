begin;

-- Platform terkelola (mis. Supabase) memberi grant default kepada `anon`,
-- `authenticated`, dan `service_role` untuk setiap objek baru di schema
-- `public`, dan mengaktifkan RLS otomatis pada tabel baru. Tabel MCU memuat
-- data medis, jadi:
--
--   * `anon` tidak boleh punya akses apa pun. Hanya `authenticated` yang
--     dilindungi policy RLS di 0004, sedangkan `anon` tidak punya policy;
--     grant default-nya tetap harus dicabut.
--   * `service_role` (BYPASSRLS) dicabut bila ada, supaya kunci service
--     platform tidak bisa melewati RLS dan membaca data medis.
--   * Role server milik aplikasi (`mcu_service`, `mcu_admin`) tetap penuh.
--
-- Statement ini juga aman di PostgreSQL biasa (self-hosted): `anon` selalu ada
-- dan `service_role` hanya disentuh bila role-nya memang ada.

revoke all on all tables in schema public from anon;
revoke all on all sequences in schema public from anon;
revoke all on all functions in schema public from anon;
revoke all on all tables in schema api from anon;

do $$
begin
  if exists (select 1 from pg_roles where rolname = 'service_role') then
    execute 'revoke all on all tables in schema public from service_role';
    execute 'revoke all on all sequences in schema public from service_role';
    execute 'revoke all on all functions in schema public from service_role';
    execute 'revoke all on all tables in schema api from service_role';
  end if;
end $$;

-- Cegah objek baru di `public` otomatis terbuka lagi untuk anon/service_role.
alter default privileges in schema public revoke all on tables from anon;
alter default privileges in schema public revoke all on sequences from anon;
alter default privileges in schema public revoke execute on functions from anon;

do $$
begin
  if exists (select 1 from pg_roles where rolname = 'service_role') then
    execute 'alter default privileges in schema public revoke all on tables from service_role';
    execute 'alter default privileges in schema public revoke all on sequences from service_role';
    execute 'alter default privileges in schema public revoke execute on functions from service_role';
  end if;
end $$;

commit;
