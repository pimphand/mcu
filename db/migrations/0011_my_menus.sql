begin;

-- `role_id` ikut di klaim JWT (lihat `apps/web/src/lib/auth/session.ts`), jadi
-- view bisa memfilter menu sesuai role pemanggil tanpa parameter dari klien.
create or replace function public.jwt_role_id()
returns bigint
language sql
stable
set search_path = pg_catalog, public
as $$
  select nullif(public.jwt_claim('role_id'), '')::bigint;
$$;

-- Menu + permission milik role pemanggil, dipakai sidebar `AppLayout.astro`.
-- `security_invoker = true` supaya policy RLS `menus`/`permissions` tetap
-- berlaku, dan `role_id` diambil dari klaim — bukan dari query string — agar
-- pengguna tidak bisa meminta menu role lain.
create or replace view api.v_my_menus
with (security_invoker = true)
as
select
  m.id as menu_id,
  m.parent_id,
  m.name,
  m.url,
  m.icon,
  m.sort_order,
  m.is_active,
  pr.is_view,
  pr.is_add,
  pr.is_edit,
  pr.is_delete
from public.menus m
join public.permissions pr on pr.menu_id = m.id
where pr.role_id = public.jwt_role_id()
  and pr.is_view;

grant select on api.v_my_menus to authenticated;

commit;
