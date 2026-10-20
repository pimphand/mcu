begin;

alter table public.roles enable row level security;
alter table public.menus enable row level security;
alter table public.permissions enable row level security;
alter table public.users enable row level security;
alter table public.clients enable row level security;
alter table public.contracts enable row level security;
alter table public.departments enable row level security;
alter table public.divisis enable row level security;
alter table public.employees enable row level security;
alter table public.participants enable row level security;
alter table public.tanda_vitals enable row level security;
alter table public.pemeriksaan_fisiks enable row level security;
alter table public.laboratoria enable row level security;
alter table public.radiologis enable row level security;
alter table public.spirometris enable row level security;
alter table public.audiometris enable row level security;
alter table public.rectals enable row level security;
alter table public.ekgs enable row level security;
alter table public.validate_doctors enable row level security;
alter table public.general_results enable row level security;
alter table public.import_jobs enable row level security;
alter table public.audit_logs enable row level security;

drop policy if exists roles_read on public.roles;
create policy roles_read on public.roles
  for select to authenticated using (true);
drop policy if exists roles_write on public.roles;
create policy roles_write on public.roles
  for all to authenticated using (public.is_admin()) with check (public.is_admin());

drop policy if exists menus_read on public.menus;
create policy menus_read on public.menus
  for select to authenticated using (true);
drop policy if exists menus_write on public.menus;
create policy menus_write on public.menus
  for all to authenticated using (public.is_admin()) with check (public.is_admin());

drop policy if exists permissions_read on public.permissions;
create policy permissions_read on public.permissions
  for select to authenticated using (true);
drop policy if exists permissions_write on public.permissions;
create policy permissions_write on public.permissions
  for all to authenticated using (public.is_admin()) with check (public.is_admin());

drop policy if exists clients_read on public.clients;
create policy clients_read on public.clients
  for select to authenticated using (public.can_access_client(id));
drop policy if exists clients_write on public.clients;
create policy clients_write on public.clients
  for all to authenticated using (public.is_admin()) with check (public.is_admin());

drop policy if exists contracts_read on public.contracts;
create policy contracts_read on public.contracts
  for select to authenticated using (public.can_access_client(client_id));
drop policy if exists contracts_write on public.contracts;
create policy contracts_write on public.contracts
  for all to authenticated using (public.can_write_client(client_id))
  with check (public.can_write_client(client_id));

drop policy if exists departments_read on public.departments;
create policy departments_read on public.departments
  for select to authenticated using (public.can_access_client(client_id));
drop policy if exists departments_write on public.departments;
create policy departments_write on public.departments
  for all to authenticated using (public.can_write_client(client_id))
  with check (public.can_write_client(client_id));

drop policy if exists divisis_read on public.divisis;
create policy divisis_read on public.divisis
  for select to authenticated using (public.can_access_client(client_id));
drop policy if exists divisis_write on public.divisis;
create policy divisis_write on public.divisis
  for all to authenticated using (public.can_write_client(client_id))
  with check (public.can_write_client(client_id));

drop policy if exists employees_read on public.employees;
create policy employees_read on public.employees
  for select to authenticated using (public.can_access_client(client_id));
drop policy if exists employees_write on public.employees;
create policy employees_write on public.employees
  for all to authenticated using (public.can_write_client(client_id))
  with check (public.can_write_client(client_id));

drop policy if exists participants_read on public.participants;
create policy participants_read on public.participants
  for select to authenticated using (
    public.is_admin()
    or (
      public.jwt_app_role() in ('client_admin', 'employee')
      and client_id = public.jwt_client_id()
      and contract_id = public.jwt_contract_id()
    )
    or (
      public.jwt_app_role() = 'participant'
      and user_id = public.jwt_user_id()
    )
  );

drop policy if exists participants_write on public.participants;
create policy participants_write on public.participants
  for all to authenticated using (
    public.is_admin()
    or (
      public.can_write_client(client_id)
      and contract_id = public.jwt_contract_id()
    )
  )
  with check (
    public.is_admin()
    or (
      public.can_write_client(client_id)
      and contract_id = public.jwt_contract_id()
    )
  );

do $$
declare
  table_name text;
begin
  foreach table_name in array array[
    'tanda_vitals', 'pemeriksaan_fisiks', 'spirometris', 'audiometris',
    'rectals', 'ekgs'
  ] loop
    execute format('drop policy if exists %I_read on public.%I', table_name, table_name);
    execute format($policy$
      create policy %I_read on public.%I
        for select to authenticated using (
          exists (
            select 1
            from public.participants p
            where p.id = participant_id
              and (
                public.is_admin()
                or (
                  public.jwt_app_role() in ('client_admin', 'employee')
                  and p.client_id = public.jwt_client_id()
                  and p.contract_id = public.jwt_contract_id()
                )
                or (
                  public.jwt_app_role() = 'participant'
                  and p.user_id = public.jwt_user_id()
                )
              )
          )
        )
    $policy$, table_name, table_name);

    execute format('drop policy if exists %I_write on public.%I', table_name, table_name);
    execute format($policy$
      create policy %I_write on public.%I
        for all to authenticated using (
          exists (
            select 1
            from public.participants p
            where p.id = participant_id
              and (
                public.is_admin()
                or (
                  public.can_write_client(p.client_id)
                  and p.contract_id = public.jwt_contract_id()
                )
              )
          )
        ) with check (
          exists (
            select 1
            from public.participants p
            where p.id = participant_id
              and (
                public.is_admin()
                or (
                  public.can_write_client(p.client_id)
                  and p.contract_id = public.jwt_contract_id()
                )
              )
          )
        )
    $policy$, table_name, table_name);
  end loop;
end $$;

drop policy if exists laboratoria_read on public.laboratoria;
create policy laboratoria_read on public.laboratoria
  for select to authenticated using (
    public.is_admin()
    or (
      public.jwt_app_role() in ('client_admin', 'employee')
      and client_id = public.jwt_client_id()
      and contract_id = public.jwt_contract_id()
    )
    or (
      public.jwt_app_role() = 'participant'
      and exists (
        select 1 from public.participants p
        where p.id = participant_id and p.user_id = public.jwt_user_id()
      )
    )
  );
drop policy if exists laboratoria_write on public.laboratoria;
create policy laboratoria_write on public.laboratoria
  for all to authenticated using (
    public.is_admin()
    or (
      public.can_write_client(client_id)
      and contract_id = public.jwt_contract_id()
    )
  )
  with check (
    public.is_admin()
    or (
      public.can_write_client(client_id)
      and contract_id = public.jwt_contract_id()
    )
  );

drop policy if exists radiologis_read on public.radiologis;
create policy radiologis_read on public.radiologis
  for select to authenticated using (
    public.is_admin()
    or (
      public.jwt_app_role() in ('client_admin', 'employee')
      and exists (
        select 1 from public.participants p
        where p.id = participant_id
          and p.client_id = public.jwt_client_id()
          and p.contract_id = public.jwt_contract_id()
      )
    )
    or (
      public.jwt_app_role() = 'participant'
      and exists (
        select 1 from public.participants p
        where p.id = participant_id and p.user_id = public.jwt_user_id()
      )
    )
  );
drop policy if exists radiologis_write on public.radiologis;
create policy radiologis_write on public.radiologis
  for all to authenticated using (
    exists (
      select 1 from public.participants p
      where p.id = participant_id
        and (
          public.is_admin()
          or (
            public.can_write_client(p.client_id)
            and p.contract_id = public.jwt_contract_id()
          )
        )
    )
  ) with check (
    exists (
      select 1 from public.participants p
      where p.id = participant_id
        and (
          public.is_admin()
          or (
            public.can_write_client(p.client_id)
            and p.contract_id = public.jwt_contract_id()
          )
        )
    )
  );

drop policy if exists general_results_read on public.general_results;
create policy general_results_read on public.general_results
  for select to authenticated using (
    public.is_admin()
    or (
      public.jwt_app_role() in ('client_admin', 'employee')
      and client_id = public.jwt_client_id()
      and contract_id = public.jwt_contract_id()
    )
    or (
      public.jwt_app_role() = 'participant'
      and participant_id is not null
      and exists (
        select 1 from public.participants p
        where p.id = participant_id and p.user_id = public.jwt_user_id()
      )
    )
  );
drop policy if exists general_results_write on public.general_results;
create policy general_results_write on public.general_results
  for all to authenticated using (
    public.is_admin()
    or (
      public.can_write_client(client_id)
      and contract_id = public.jwt_contract_id()
    )
  )
  with check (
    public.is_admin()
    or (
      public.can_write_client(client_id)
      and contract_id = public.jwt_contract_id()
    )
  );

drop policy if exists validate_doctors_read on public.validate_doctors;
create policy validate_doctors_read on public.validate_doctors
  for select to authenticated using (
    public.is_admin()
    or (
      public.can_access_client(client_id)
      and contract_id = public.jwt_contract_id()
    )
  );
drop policy if exists validate_doctors_write on public.validate_doctors;
create policy validate_doctors_write on public.validate_doctors
  for all to authenticated using (
    public.is_admin()
    or (
      public.can_write_client(client_id)
      and contract_id = public.jwt_contract_id()
    )
  )
  with check (
    public.is_admin()
    or (
      public.can_write_client(client_id)
      and contract_id = public.jwt_contract_id()
    )
  );

drop policy if exists import_jobs_read on public.import_jobs;
create policy import_jobs_read on public.import_jobs
  for select to authenticated using (
    public.is_admin()
    or (
      public.can_access_client(client_id)
      and contract_id = public.jwt_contract_id()
    )
  );
drop policy if exists import_jobs_write on public.import_jobs;
create policy import_jobs_write on public.import_jobs
  for all to authenticated using (
    public.is_admin()
    or (
      public.can_write_client(client_id)
      and contract_id = public.jwt_contract_id()
    )
  )
  with check (
    public.is_admin()
    or (
      public.can_write_client(client_id)
      and contract_id = public.jwt_contract_id()
    )
  );

drop policy if exists audit_logs_read on public.audit_logs;
create policy audit_logs_read on public.audit_logs
  for select to authenticated using (public.is_admin());

grant usage on schema public to anon, authenticated;
grant usage on schema api to anon, authenticated;

grant select, insert, update, delete on all tables in schema public to authenticated;
grant usage, select on all sequences in schema public to authenticated;
revoke all on public.users from anon, authenticated;

grant select on all tables in schema api to authenticated;

revoke all on all functions in schema public from public;
revoke all on all functions in schema public from anon;
grant execute on all functions in schema public to authenticated;
grant execute on all functions in schema public to mcu_service;

grant usage on schema public, api to mcu_auth;
-- mcu_auth dipakai endpoint server saja (read-only):
--   users            -> verifikasi login
--   roles            -> level/role user untuk klaim JWT
--   clients, contracts -> pilihan konteks MCU IN/OUT
grant select on public.users to mcu_auth;
grant select on public.roles to mcu_auth;
grant select on public.clients to mcu_auth;
grant select on public.contracts to mcu_auth;

 grant usage on schema public, api to mcu_service;
 grant usage on schema public, api to mcu_admin;

grant all on all tables in schema public to mcu_service;
grant all on all sequences in schema public to mcu_service;
grant all on all tables in schema public to mcu_admin;
grant all on all sequences in schema public to mcu_admin;
grant execute on all functions in schema public to mcu_admin;

commit;
