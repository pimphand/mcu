begin;

-- Tanda tangan pemeriksa pada cetakan hasil diambil dari `employees.ttd` lewat
-- `employee_id` di tabel hasil. Tabel employees memakai RLS per client, sedangkan
-- dokter klinik memiliki `client_id` NULL, sehingga akun client_admin/employee
-- tidak bisa membaca barisnya dan cetakan kehilangan tanda tangan.
--
-- View ini security definer (dimiliki owner migrasi) dan hanya mengembalikan
-- nama + path ttd untuk karyawan yang menandatangani hasil milik peserta yang
-- boleh diakses pemanggil. Kolom sensitif lain (NIK, rekening, alamat) tidak
-- pernah ikut terekspos.
drop view if exists api.v_employee_signature;

create view api.v_employee_signature
with (security_invoker = false)
as
select e.id, e.nama, e.ttd
from public.employees e
where exists (
  select 1
  from (
    select participant_id, employee_id from public.tanda_vitals
    union all
    select participant_id, employee_id from public.pemeriksaan_fisiks
    union all
    select participant_id, employee_id from public.laboratoria
    union all
    select participant_id, employee_id from public.radiologis
    union all
    select participant_id, employee_id from public.audiometris
    union all
    select participant_id, employee_id from public.ekgs
    union all
    select participant_id, employee_id from public.spirometris
    union all
    select participant_id, employee_id from public.rectals
  ) signed
  join public.participants p on p.id = signed.participant_id
  where signed.employee_id = e.id
    and public.can_access_client(p.client_id)
);

grant select on api.v_employee_signature to authenticated;

notify pgrst, 'reload schema';

commit;
