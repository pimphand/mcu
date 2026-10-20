begin;

-- Halaman Print Hasil MCU (paritas `ParticipantController@printMCU`) butuh
-- kolom yang tidak dipakai tab progres: no. form (rentang cetak massal), NIK,
-- nama paket, status validasi dokter, dan status pemeriksaan fisik.
-- `create or replace view` tidak boleh menambah/mengganti nama kolom di
-- tengah, jadi view dibuat ulang (tidak ada objek lain yang bergantung).
drop view if exists api.v_mcu_progress;

create view api.v_mcu_progress
with (security_invoker = true)
as
select
  p.id as participant_id,
  p.code,
  p.nik,
  p.name,
  p.gender,
  p.birthday,
  p.status,
  p.packet_name,
  p.no_form,
  p.register_number,
  p.register_date,
  p.client_id,
  c.name as client_name,
  p.contract_id,
  ct.name as contract_name,
  p.divisi_id,
  d.name as divisi_name,
  p.department_id,
  dep.name as department_name,
  p.plan_u,
  p.plan_a,
  p.plan_e,
  p.plan_s,
  p.plan_r,
  p.lab_special,
  coalesce(tv.selesai, false) as fisik_done,
  coalesce(pf.selesai, false) as physical_done,
  coalesce(lab.selesai, false) as lab_done,
  coalesce(rad.selesai, false) as radiologi_done,
  coalesce(spiro.selesai, false) as spirometri_done,
  coalesce(audio.selesai, false) as audiometri_done,
  coalesce(rectal.selesai, false) as rectal_done,
  coalesce(ekg.selesai, false) as ekg_done,
  exists (
    select 1
      from public.validate_doctors v
     where v.client_id = p.client_id
       and v.no_mcu = p.code
  ) as validated,
  (
    coalesce(tv.selesai, false)::int +
    coalesce(lab.selesai, false)::int +
    coalesce(rad.selesai, false)::int +
    coalesce(spiro.selesai, false)::int +
    coalesce(audio.selesai, false)::int +
    coalesce(rectal.selesai, false)::int +
    coalesce(ekg.selesai, false)::int
  ) as steps_done,
  7 as steps_total,
  round(
    100.0 * (
      coalesce(tv.selesai, false)::int +
      coalesce(lab.selesai, false)::int +
      coalesce(rad.selesai, false)::int +
      coalesce(spiro.selesai, false)::int +
      coalesce(audio.selesai, false)::int +
      coalesce(rectal.selesai, false)::int +
      coalesce(ekg.selesai, false)::int
    ) / 7
  )::int as completion_pct
from public.participants p
left join public.clients c on c.id = p.client_id
left join public.contracts ct on ct.id = p.contract_id
left join public.divisis d on d.id = p.divisi_id
left join public.departments dep on dep.id = p.department_id
left join public.tanda_vitals tv on tv.participant_id = p.id
left join public.pemeriksaan_fisiks pf on pf.participant_id = p.id
left join public.laboratoria lab on lab.participant_id = p.id
left join public.radiologis rad on rad.participant_id = p.id
left join public.spirometris spiro on spiro.participant_id = p.id
left join public.audiometris audio on audio.participant_id = p.id
left join public.rectals rectal on rectal.participant_id = p.id
left join public.ekgs ekg on ekg.participant_id = p.id;

grant select on api.v_mcu_progress to authenticated;

-- PostgREST menyimpan cache skema; minta reload agar kolom baru langsung ada.
notify pgrst, 'reload schema';

commit;
