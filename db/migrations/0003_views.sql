begin;

create or replace view api.v_participant_list
with (security_invoker = true)
as
select
  p.id,
  p.code,
  p.nik,
  p.name,
  p.gender,
  p.birthday,
  p.phone,
  p.status,
  p.packet_name,
  p.packet_a,
  p.packet_b,
  p.packet_c,
  p.packet_d,
  p.packet_e,
  p.packet_f,
  p.plan_name,
  p.plan_u,
  p.plan_a,
  p.plan_e,
  p.plan_s,
  p.plan_r,
  p.lab_special,
  p.register_number,
  p.register_date,
  p.no_form,
  p.divisi_id,
  d.name as divisi_name,
  p.department_id,
  dep.name as department_name,
  p.client_id,
  c.name as client_name,
  p.contract_id,
  ct.name as contract_name,
  p.user_id,
  p.created_at,
  p.updated_at
from public.participants p
left join public.divisis d on d.id = p.divisi_id
left join public.departments dep on dep.id = p.department_id
left join public.clients c on c.id = p.client_id
left join public.contracts ct on ct.id = p.contract_id;

create or replace view api.v_mcu_progress
with (security_invoker = true)
as
select
  p.id as participant_id,
  p.code,
  p.name,
  p.gender,
  p.birthday,
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
  coalesce(lab.selesai, false) as lab_done,
  coalesce(rad.selesai, false) as radiologi_done,
  coalesce(spiro.selesai, false) as spirometri_done,
  coalesce(audio.selesai, false) as audiometri_done,
  coalesce(rectal.selesai, false) as rectal_done,
  coalesce(ekg.selesai, false) as ekg_done,
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
left join public.laboratoria lab on lab.participant_id = p.id
left join public.radiologis rad on rad.participant_id = p.id
left join public.spirometris spiro on spiro.participant_id = p.id
left join public.audiometris audio on audio.participant_id = p.id
left join public.rectals rectal on rectal.participant_id = p.id
left join public.ekgs ekg on ekg.participant_id = p.id;

create or replace view api.v_dashboard_summary
with (security_invoker = true)
as
select
  p.client_id,
  c.name as client_name,
  p.contract_id,
  ct.name as contract_name,
  count(*)::int as participants,
  count(*) filter (where p.register_date is not null)::int as registered,
  count(*) filter (where coalesce(tv.selesai, false))::int as fisik_done,
  count(*) filter (where coalesce(lab.selesai, false))::int as lab_done,
  count(*) filter (where coalesce(rad.selesai, false))::int as radiologi_done,
  count(*) filter (where coalesce(spiro.selesai, false))::int as spirometri_done,
  count(*) filter (where coalesce(audio.selesai, false))::int as audiometri_done,
  count(*) filter (where coalesce(rectal.selesai, false))::int as rectal_done,
  count(*) filter (where coalesce(ekg.selesai, false))::int as ekg_done
from public.participants p
left join public.clients c on c.id = p.client_id
left join public.contracts ct on ct.id = p.contract_id
left join public.tanda_vitals tv on tv.participant_id = p.id
left join public.laboratoria lab on lab.participant_id = p.id
left join public.radiologis rad on rad.participant_id = p.id
left join public.spirometris spiro on spiro.participant_id = p.id
left join public.audiometris audio on audio.participant_id = p.id
left join public.rectals rectal on rectal.participant_id = p.id
left join public.ekgs ekg on ekg.participant_id = p.id
group by p.client_id, c.name, p.contract_id, ct.name;

commit;
