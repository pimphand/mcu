begin;

-- Pastikan helper akses data tetap seketat 0002 (migrasi ini hanya
-- melonggarkan daftar pilihan konteks, bukan akses data).
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

-- Akun operasional yang belum ditautkan ke client (mis. akun dokter klinik
-- `dr_dini`, yang di data legacy memang `users.client_id = NULL`) harus tetap
-- bisa memilih client & kontrak di MCU IN/OUT seperti aplikasi lama.
--
-- Karena itu dibuat fungsi khusus untuk *daftar pilihan konteks*, bukan
-- melonggarkan `can_access_client`:
--   * hanya policy `clients_read` dan `contracts_read` yang memakainya;
--   * department/divisi/employee dan data peserta tetap mengikuti
--     `can_access_client` yang mensyaratkan klaim client (least privilege,
--     mis. `employees.nomor_rekening` tidak ikut terbuka);
--   * akun yang sudah ditautkan ke client tetap hanya melihat client-nya.
create or replace function public.can_select_client(p_client_id bigint)
returns boolean
language sql
stable
set search_path = pg_catalog, public
as $$
  select case
    when public.is_admin() then true
    when public.jwt_app_role() in ('client_admin', 'employee')
      then p_client_id is not null
        and (
          public.jwt_client_id() is null
          or p_client_id = public.jwt_client_id()
        )
    else false
  end;
$$;

drop policy if exists clients_read on public.clients;
create policy clients_read on public.clients
  for select to authenticated using (public.can_select_client(id));

drop policy if exists contracts_read on public.contracts;
create policy contracts_read on public.contracts
  for select to authenticated using (public.can_select_client(client_id));

commit;
