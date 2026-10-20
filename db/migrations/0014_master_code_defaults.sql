begin;

-- Kode master (client, kontrak, department, divisi, karyawan) dibuat oleh
-- database, bukan count()+1 di aplikasi. Format mengikuti legacy:
--   client      C00001
--   kontrak     KT00001
--   department  D-00001
--   divisi      DV-00001
--   karyawan    E-00001
-- Sequence diselaraskan dengan kode terbesar yang sudah ada sebelum default
-- dipasang, supaya data hasil ETL tidak bentrok saat insert berikutnya.

create sequence if not exists public.client_code_seq;
create sequence if not exists public.contract_code_seq;
create sequence if not exists public.department_code_seq;
create sequence if not exists public.divisi_code_seq;
create sequence if not exists public.employee_code_seq;

select setval(
  'public.client_code_seq',
  coalesce((select max((regexp_match(code, '([0-9]+)$'))[1]::bigint) from public.clients), 0) + 1,
  false
);
select setval(
  'public.contract_code_seq',
  coalesce((select max((regexp_match(code, '([0-9]+)$'))[1]::bigint) from public.contracts), 0) + 1,
  false
);
select setval(
  'public.department_code_seq',
  coalesce((select max((regexp_match(code, '([0-9]+)$'))[1]::bigint) from public.departments), 0) + 1,
  false
);
select setval(
  'public.divisi_code_seq',
  coalesce((select max((regexp_match(code, '([0-9]+)$'))[1]::bigint) from public.divisis), 0) + 1,
  false
);
select setval(
  'public.employee_code_seq',
  coalesce((select max((regexp_match(code, '([0-9]+)$'))[1]::bigint) from public.employees), 0) + 1,
  false
);

alter table public.clients
  alter column code set default ('C' || lpad(nextval('public.client_code_seq')::text, 5, '0'));
alter table public.contracts
  alter column code set default ('KT' || lpad(nextval('public.contract_code_seq')::text, 5, '0'));
alter table public.departments
  alter column code set default ('D-' || lpad(nextval('public.department_code_seq')::text, 5, '0'));
alter table public.divisis
  alter column code set default ('DV-' || lpad(nextval('public.divisi_code_seq')::text, 5, '0'));
alter table public.employees
  alter column code set default ('E-' || lpad(nextval('public.employee_code_seq')::text, 5, '0'));

commit;
