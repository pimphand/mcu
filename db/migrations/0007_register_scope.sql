begin;

-- Register tunggal wajib mengikuti kontrak aktif di klaim JWT, bukan hanya id
-- peserta dari body. RLS `participants_write` tidak menolong untuk admin
-- (`is_admin()` melewati cek client), sedangkan daftar peserta di UI sudah
-- dibatasi kontrak — jadi RPC-nya harus menolak peserta kontrak lain.
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

  -- `PTxyz` dipetakan PostgREST menjadi status HTTP xyz. Baris yang tidak
  -- terlihat oleh RLS dianggap tidak ada, bukan error server.
  if not found then
    raise exception 'participant % not found', p_participant_id
      using errcode = 'PT404';
  end if;

  if v_participant.client_id is distinct from public.jwt_client_id()
     or v_participant.contract_id is distinct from public.jwt_contract_id() then
    raise exception 'peserta % berada di luar client/kontrak aktif', p_participant_id
      using errcode = '42501';
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

commit;
