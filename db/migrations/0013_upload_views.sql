begin;

-- Daftar hasil upload (validasi dokter, laboratorium, radiologi, hasil umum).
-- Halaman upload legacy menampilkan satu baris hasil per peserta dengan kolom
-- peserta (kode, nama, gender, divisi) untuk filter dan sortir. View ini
-- menyatukan keduanya supaya browser cukup memanggil satu resource dan scope
-- client/kontrak tetap bisa ditempel BFF.
--
-- `security_invoker` wajib: RLS tabel hasil dan `participants` tetap berlaku
-- untuk role `authenticated`.

create or replace view api.v_lab_listing
with (security_invoker = true)
as
select
  lab.id,
  lab.participant_id,
  p.code,
  p.name as participant_name,
  p.gender,
  p.no_form,
  p.register_date,
  p.divisi_id,
  d.name as divisi_name,
  lab.client_id,
  lab.contract_id,
  lab.employee_id,
  lab.form_no,
  lab.hemoglobin,
  lab.hematokrit,
  lab.lekosit,
  lab.trombosit,
  lab.eritrosit,
  lab.basofil,
  lab.eosinofil,
  lab.batang,
  lab.segmen,
  lab.limfosit,
  lab.monosit,
  lab.sgot,
  lab.sgpt,
  lab.ureum,
  lab.creatinin,
  lab.kesimpulan,
  lab.selesai,
  lab.glukosa_puasa,
  lab.cholesterol_total,
  lab.asam_urat,
  lab.glukosa_sewaktu,
  lab.trigliserida,
  lab.hdl_cholesterol,
  lab.ldl_cholestero,
  lab.reduksi,
  lab.berat_jenis,
  lab.ph_reaksi,
  lab.warna,
  lab.kekeruhan,
  lab.urobilinogen,
  lab.bilirubin,
  lab.eritrosit_urine,
  lab.keton,
  lab.protein,
  lab.sedimen_epitel,
  lab.sedimen_eritrosit,
  lab.sedimen_leukosit,
  lab.sedimen_bakteri,
  lab.sedimen_kristal,
  lab.hbsag,
  lab.user_lab,
  lab.lab_date,
  lab.pemeriksa_lab,
  lab.created_at,
  lab.updated_at
from public.laboratoria lab
join public.participants p on p.id = lab.participant_id
left join public.divisis d on d.id = p.divisi_id;

create or replace view api.v_radiologi_listing
with (security_invoker = true)
as
select
  rad.id,
  rad.participant_id,
  p.code,
  p.name as participant_name,
  p.gender,
  p.no_form,
  p.register_date,
  p.divisi_id,
  d.name as divisi_name,
  p.client_id,
  rad.contract_id,
  rad.employee_id,
  rad.date_mcu,
  rad.cor,
  rad.diafragma_sinus,
  rad.pulmo,
  rad.kesan,
  rad.diperiksa,
  rad.selesai,
  rad.created_at,
  rad.updated_at
from public.radiologis rad
join public.participants p on p.id = rad.participant_id
left join public.divisis d on d.id = p.divisi_id;

create or replace view api.v_validate_doctor_listing
with (security_invoker = true)
as
select
  v.id,
  v.no_mcu as code,
  v.no_mcu,
  coalesce(p.name, v.name) as participant_name,
  v.name,
  coalesce(v.gender, p.gender) as gender,
  p.no_form,
  p.register_date,
  p.divisi_id,
  d.name as divisi_name,
  v.client_id,
  v.contract_id,
  v.date_mcu,
  v.result_mcu,
  v.doctor,
  v.notes,
  v.created_at,
  v.updated_at
from public.validate_doctors v
left join public.participants p
  on p.code = v.no_mcu
 and p.client_id = v.client_id
 and p.contract_id = v.contract_id
left join public.divisis d on d.id = p.divisi_id;

create or replace view api.v_general_result_listing
with (security_invoker = true)
as
select
  g.id,
  g.participant_id,
  p.code,
  p.name as participant_name,
  g.name,
  p.gender,
  p.no_form,
  p.register_date,
  p.divisi_id,
  d.name as divisi_name,
  g.client_id,
  g.contract_id,
  g.mcu_id,
  g.result,
  g.mcu_date,
  g.created_at,
  g.updated_at
from public.general_results g
join public.participants p on p.id = g.participant_id
left join public.divisis d on d.id = p.divisi_id;

grant select on api.v_lab_listing to authenticated;
grant select on api.v_radiologi_listing to authenticated;
grant select on api.v_validate_doctor_listing to authenticated;
grant select on api.v_general_result_listing to authenticated;

-- PostgREST menyimpan cache skema; minta reload agar view baru langsung ada.
notify pgrst, 'reload schema';

commit;
