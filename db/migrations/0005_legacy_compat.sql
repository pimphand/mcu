begin;

-- Legacy memakai ulang kode peserta (MCU00001) di kontrak berbeda, dan
-- register_number lama tidak dijamin unik. Kode unik harus per client+kontrak.
alter table public.participants
  drop constraint if exists participants_code_key;

drop index if exists public.participants_register_number_uidx;

create index if not exists participants_register_number_idx
  on public.participants (client_id, contract_id, register_number)
  where register_number is not null;

create unique index if not exists participants_client_contract_code_uidx
  on public.participants (client_id, contract_id, code);

-- Data legacy menyimpan tensi sebagai "120/80", bukan integer.
alter table public.tanda_vitals
  alter column tekanan_darah type text using tekanan_darah::text;

grant usage on schema public to mcu_admin;
grant usage on schema api to mcu_admin;
grant usage on schema api to mcu_service;

commit;
