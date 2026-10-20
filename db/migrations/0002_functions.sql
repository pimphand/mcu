begin;

create or replace function public.set_updated_at()
returns trigger
language plpgsql
set search_path = pg_catalog, public
as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

do $$
declare
  table_name text;
begin
  foreach table_name in array array[
    'roles', 'menus', 'permissions', 'users', 'clients', 'contracts',
    'departments', 'divisis', 'employees', 'participants', 'tanda_vitals',
    'pemeriksaan_fisiks', 'laboratoria', 'radiologis', 'spirometris',
    'audiometris', 'rectals', 'ekgs', 'validate_doctors', 'general_results',
    'import_jobs'
  ] loop
    execute format('drop trigger if exists set_updated_at on public.%I', table_name);
    execute format(
      'create trigger set_updated_at before update on public.%I for each row execute function public.set_updated_at()',
      table_name
    );
  end loop;
end $$;

create or replace function public.jwt_claim(p_name text)
returns text
language sql
stable
set search_path = pg_catalog, public
as $$
  select nullif(current_setting('request.jwt.claims', true)::jsonb ->> p_name, '');
$$;

create or replace function public.jwt_app_role()
returns text
language sql
stable
set search_path = pg_catalog, public
as $$
  select public.jwt_claim('app_role');
$$;

create or replace function public.jwt_user_id()
returns bigint
language sql
stable
set search_path = pg_catalog, public
as $$
  select nullif(public.jwt_claim('user_id'), '')::bigint;
$$;

create or replace function public.jwt_client_id()
returns bigint
language sql
stable
set search_path = pg_catalog, public
as $$
  select nullif(public.jwt_claim('client_id'), '')::bigint;
$$;

create or replace function public.jwt_contract_id()
returns bigint
language sql
stable
set search_path = pg_catalog, public
as $$
  select nullif(public.jwt_claim('contract_id'), '')::bigint;
$$;

create or replace function public.jwt_level()
returns smallint
language sql
stable
set search_path = pg_catalog, public
as $$
  select nullif(public.jwt_claim('level'), '')::smallint;
$$;

create or replace function public.is_admin()
returns boolean
language sql
stable
set search_path = pg_catalog, public
as $$
  select coalesce(public.jwt_app_role() = 'admin', false)
      or coalesce(public.jwt_level() = 1, false);
$$;

create or replace function public.can_access_client(p_client_id bigint)
returns boolean
language sql
stable
set search_path = pg_catalog, public
as $$
  select case
    when public.is_admin() then true
    when public.jwt_app_role() in ('client_admin', 'employee')
      then p_client_id is not null and p_client_id = public.jwt_client_id()
    else false
  end;
$$;

create or replace function public.can_write_client(p_client_id bigint)
returns boolean
language sql
stable
set search_path = pg_catalog, public
as $$
  select case
    when public.is_admin() then true
    when public.jwt_app_role() in ('client_admin', 'employee')
      then p_client_id is not null and p_client_id = public.jwt_client_id()
    else false
  end;
$$;

create sequence if not exists public.participant_code_seq start with 1;
create sequence if not exists public.employee_code_seq start with 1;
create sequence if not exists public.register_number_seq start with 1;

create or replace function public.next_participant_code()
returns text
language sql
volatile
set search_path = pg_catalog, public
as $$
  select 'MCU' || lpad(nextval('public.participant_code_seq')::text, 5, '0');
$$;

create or replace function public.next_employee_code()
returns text
language sql
volatile
set search_path = pg_catalog, public
as $$
  select 'E-' || lpad(nextval('public.employee_code_seq')::text, 5, '0');
$$;

create or replace function public.next_register_number(p_date date default current_date)
returns text
language sql
volatile
set search_path = pg_catalog, public
as $$
  select 'R' || to_char(p_date, 'YYYYMMDD') || '-' ||
    lpad(nextval('public.register_number_seq')::text, 4, '0');
$$;

create or replace function public.register_participant(p_participant_id bigint)
returns table (participant_id bigint, register_number text, register_date timestamptz)
language plpgsql
security invoker
set search_path = pg_catalog, public
as $$
declare
  v_participant public.participants%rowtype;
  v_number text;
begin
  select p.*
    into v_participant
    from public.participants p
   where p.id = p_participant_id
   for update;

  if not found then
    raise exception 'participant % not found', p_participant_id
      using errcode = 'P0002';
  end if;

  if v_participant.register_date is not null then
    return query
      select v_participant.id, v_participant.register_number, v_participant.register_date;
    return;
  end if;

  v_number := public.next_register_number(current_date);

  update public.participants p
     set register_number = v_number,
         register_date = now()
   where p.id = p_participant_id
  returning p.* into v_participant;

  return query
    select v_participant.id, v_participant.register_number, v_participant.register_date;
end;
$$;

create or replace function public.register_all(p_date date default current_date)
returns integer
language plpgsql
security invoker
set search_path = pg_catalog, public
as $$
declare
  v_count integer;
begin
  update public.participants p
     set register_number = p.no_form::text,
         register_date = p_date
   where p.contract_id is not distinct from public.jwt_contract_id()
     and p.client_id is not distinct from public.jwt_client_id()
     and p.register_date is null
     and p.no_form is not null;

  get diagnostics v_count = row_count;
  return v_count;
end;
$$;

commit;
