\set ON_ERROR_STOP on

insert into public.users (name, username, email, password_hash, role_id, is_active)
select
  'Administrator',
  :'admin_username',
  nullif(:'admin_email', ''),
  crypt(:'admin_password', gen_salt('bf', 10)),
  r.id,
  true
from public.roles r
where r.level = 1
on conflict (username) do update
  set password_hash = excluded.password_hash,
      email = coalesce(excluded.email, public.users.email),
      role_id = excluded.role_id,
      is_active = true;
