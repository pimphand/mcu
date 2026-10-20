#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
: "${DATABASE_URL:?DATABASE_URL PostgreSQL wajib}"

PSQL=(psql "$DATABASE_URL" --no-psqlrc --set ON_ERROR_STOP=1)

echo "[verify] jumlah baris"
"${PSQL[@]}" --no-align --tuples-only --field-separator='=' <<'SQL'
select 'clients', count(*) from public.clients
union all select 'users', count(*) from public.users
union all select 'contracts', count(*) from public.contracts
union all select 'employees', count(*) from public.employees
union all select 'participants', count(*) from public.participants
union all select 'tanda_vitals', count(*) from public.tanda_vitals
union all select 'pemeriksaan_fisiks', count(*) from public.pemeriksaan_fisiks
union all select 'laboratoria', count(*) from public.laboratoria
union all select 'radiologis', count(*) from public.radiologis
union all select 'rectals', count(*) from public.rectals
union all select 'roles', count(*) from public.roles
union all select 'menus', count(*) from public.menus
order by 1;
SQL

echo "[verify] integritas data"
"${PSQL[@]}" --no-align --tuples-only --field-separator='=' <<'SQL'
select 'gender_invalid', count(*) from public.participants where gender not in ('L', 'P')
union all select 'participants_tanpa_client', count(*) from public.participants where client_id is null
union all select 'result_tanpa_peserta', (
  select (select count(*) from public.tanda_vitals v
            where not exists (select 1 from public.participants p where p.id = v.participant_id))
       + (select count(*) from public.laboratoria l
            where not exists (select 1 from public.participants p where p.id = l.participant_id))
       + (select count(*) from public.radiologis r
            where not exists (select 1 from public.participants p where p.id = r.participant_id))
)
union all select 'participants_tanpa_baseline', (
  select count(*) from public.participants p
   where not exists (select 1 from public.tanda_vitals v where v.participant_id = p.id)
)
order by 1;
SQL

echo "[verify] RLS: employee/admin/participant + tabel users"
"${PSQL[@]}" <<'SQL'
begin;
set local role authenticated;

do $$
declare
  scope record;
  seen integer;
  expected integer;
  total integer;
  checked integer := 0;
  self_user_id bigint;
begin
  -- Admin (tanpa client/kontrak) harus melihat seluruh peserta.
  perform set_config(
    'request.jwt.claims',
    '{"app_role":"admin","user_id":1,"client_id":null,"contract_id":null,"level":1,"token_version":1}',
    true
  );

  select count(*) into total from public.participants;
  select count(*) into seen from api.v_participant_list;

  if seen <> total then
    raise exception 'admin melihat % baris, harusnya %', seen, total;
  end if;

  raise notice 'admin: % baris (semua peserta)', seen;

  select user_id into self_user_id
    from public.participants
   where user_id is not null
   order by id
   limit 1;

  -- Employee hanya boleh melihat kontrak yang ada di klaim JWT-nya.
  for scope in
    select client_id, contract_id, count(*) as rows
      from public.participants
     group by 1, 2
     order by 1, 2
  loop
    perform set_config(
      'request.jwt.claims',
      json_build_object(
        'app_role', 'employee',
        'user_id', 10,
        'client_id', scope.client_id,
        'contract_id', scope.contract_id,
        'level', 3,
        'token_version', 1
      )::text,
      true
    );

    select count(*) into seen from api.v_participant_list;
    select count(*) into expected
      from public.participants
     where client_id = scope.client_id
       and contract_id = scope.contract_id;

    if seen <> expected then
      raise exception 'employee client=% contract=% melihat %, harusnya %',
        scope.client_id, scope.contract_id, seen, expected;
    end if;

    checked := checked + 1;
  end loop;

  raise notice 'employee: % kombinasi client+kontrak sesuai RLS', checked;

  -- Employee tidak boleh melihat kontrak lain dalam bentuk apa pun.
  perform set_config(
    'request.jwt.claims',
    '{"app_role":"employee","user_id":10,"client_id":-1,"contract_id":-1,"level":3,"token_version":1}',
    true
  );

  select count(*) into seen from api.v_participant_list;

  if seen <> 0 then
    raise exception 'employee dengan konteks asing melihat % baris', seen;
  end if;

  -- Peserta hanya melihat dirinya sendiri.
  if self_user_id is not null then
    perform set_config(
      'request.jwt.claims',
      json_build_object(
        'app_role', 'participant',
        'user_id', self_user_id,
        'client_id', 1,
        'contract_id', 1,
        'level', 4,
        'token_version', 1
      )::text,
      true
    );

    select count(*) into seen from api.v_participant_list;

    if seen > 1 then
      raise exception 'peserta melihat % baris', seen;
    end if;

    raise notice 'peserta: % baris (maksimal dirinya sendiri)', seen;
  else
    raise notice 'peserta: dilewati (tidak ada user_id di data legacy)';
  end if;
end $$;

reset role;
rollback;
SQL

echo "[verify] policy RLS aktif"
"${PSQL[@]}" --no-align --tuples-only --field-separator='=' <<'SQL'
select 'tabel_dengan_rls', count(*) from pg_tables
 where schemaname = 'public' and rowsecurity
union all
select 'policy_public', count(*) from pg_policies where schemaname = 'public'
union all
select 'policy_api', count(*) from pg_policies where schemaname = 'api'
order by 1;
SQL

echo "[verify] selesai"
