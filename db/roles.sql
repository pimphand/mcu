\set ON_ERROR_STOP on

-- Nama role login untuk PostgREST.
--
-- Default `mcu_authenticator`, BUKAN `authenticator`: di platform terkelola
-- (Supabase) nama `authenticator` sudah dipakai PostgREST platform dan
-- password-nya diatur oleh platform. Menimpanya dari sini akan mematikan Data
-- API Supabase. Bisa diganti: psql --set authenticator_role=nama_lain.
\if :{?authenticator_role}
\else
  \set authenticator_role mcu_authenticator
\endif

-- Role aplikasi. `anon`/`authenticated` dibuat hanya bila belum ada, supaya
-- role milik platform tidak diubah.
select format('create role %I nologin noinherit', r.name)
  from (values ('anon'), ('authenticated'), ('mcu_auth'), ('mcu_service'), ('mcu_admin'), (:'authenticator_role')) as r(name)
 where not exists (select 1 from pg_roles where rolname = r.name)
\gexec

-- Role login PostgREST: hanya boleh berpindah role, tanpa akses tabel sendiri.
select format('alter role %I with login noinherit password %L', :'authenticator_role', :'authenticator_password')
\gexec

-- Role server-side: bypass RLS, hanya dipakai dari server (bukan browser).
select format('alter role %I with login password %L bypassrls', 'mcu_auth', :'auth_password')
\gexec

select format('alter role %I with login password %L bypassrls', 'mcu_service', :'service_password')
\gexec

select format('alter role %I with login password %L bypassrls', 'mcu_admin', :'admin_password')
\gexec

-- PostgREST boleh berpindah ke role aplikasi.
select format('grant %I, %I, %I to %I', 'anon', 'authenticated', 'mcu_service', :'authenticator_role')
\gexec
