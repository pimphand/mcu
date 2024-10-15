@extends('layouts.main')

@section('title', 'Participant')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('content')
    <style>
        .table-responsive table.textkecil {
            font-size: 10px !important; /* Atur ukuran font kecil dan prioritaskan */
        }

        .table-responsive th.textkecil, .table-responsive td.textkecil {
            font-size: 10px !important; /* Pastikan ukuran font pada header dan sel tabel */
        }

        .table thead th {
            vertical-align: bottom;
        }

        .table th, .table td {
            padding: 0.75rem;
            vertical-align: top
        }
    </style>
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header">
        </div>
        <div class="card-body">
            <form action="{{ route('report.results') }}" method="get">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label" for="fp-range">Tanggal</label>
                        <input type="text" name="filter[date_range]" id="fp-range" class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                    </div>
                    <div class="col-md-3">
                        <label for="" class="form-label">Client</label>
                        <select name="client_id" id="client_id" class="form-select">
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="" class="form-label">Kontrak</label>
                        <select name="filter[contract_id]" id="contract_id" class="form-select">
                            <option value="">Pilih</option>
                            @foreach ($client->contracts as $item)
                                <option value="{{ $item->id }}"
                                    {{ session('contract_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="" class="form-label">Divisi</label>
                        <select name="filter[divisi_id]" id="divisi_id" class="form-select">
                            <option value="">Pilih</option>
                            @foreach ($client->divisi as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label for="" class="form-label text-white">cari</label>
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                    <div class="col-md-2 mt-1">
                        <button class="btn btn-success export" type="button" data-type="identitas">Simpan Excel</button>
                    </div>
                    
                </div>
            </form>

            
        </div>
        <div class="card-body">
            <div class="table-responsive table-responsive small textkecil">
                <table class="dt-responsive table textkecil" id="table">
                    <thead>
                                <tr>
                                 <th class="border-bottom textkecil border-top">No.</th>
                                  <th class="border-bottom textkecil border-top">Tgl</th>
                                  <th class="border-bottom textkecil border-top">No. Register</th>
                                  <th class="border-bottom textkecil border-top">MCU ID</th>
                                  <th class="border-bottom textkecil border-top">NIK</th>
                                  <th class="border-bottom textkecil border-top">Nama</th>
                                    <th class="border-bottom textkecil border-top">Tgl Lahir</th>
                                  <th class="border-bottom textkecil border-top">JK</th>
                                  <th class="border-bottom textkecil border-top">Dept</th>
                                  <th class="border-bottom textkecil border-top">Bagian/ Unit</th>
                                  <th class="border-bottom textkecil border-top">Perusahaan</th>
								   <th class="border-bottom textkecil border-top">Gedung</th>
                                  <th class="border-bottom textkecil border-top">Paket MCU</th>
                                  <th class="border-bottom textkecil border-top">TTV </th>
                                  <th class="border-bottom textkecil border-top">Keluhan Utama</th>
                                  <th class="border-bottom textkecil border-top">Riwayat Penyakit Sekarang</th>
                                  <th class="border-bottom textkecil border-top">Riwayat Trauma</th>
                                  <th class="border-bottom textkecil border-top">Alergi</th>
                                  <th class="border-bottom textkecil border-top">Merokok</th>
                                  <th class="border-bottom textkecil border-top">Alkohol</th>
                                  <th class="border-bottom textkecil border-top">TB</th>
                                  <th class="border-bottom textkecil border-top">BB</th>
                                  <th class="border-bottom textkecil border-top">IMT</th>
                                  <th class="border-bottom textkecil border-top">Nilai IMT</th>
                                  <th class="border-bottom textkecil border-top">Tekanan Darah</th>
                                  <th class="border-bottom textkecil border-top">Frekuensi Nadi</th>
                                  <th class="border-bottom textkecil border-top">Suhu</th>
                                  <th class="border-bottom textkecil border-top">Frekuensi Nafas</th>
                                  <th class="border-bottom textkecil border-top">Pemeriksaan Fisik </th>
                                  <th class="border-bottom textkecil border-top">Keadaan Umum </th>
                                  <th class="border-bottom textkecil border-top">Kepala</th>
                                  <th class="border-bottom textkecil border-top">Mata</th>
                                  <th class="border-bottom textkecil border-top">Pupil</th>
                                  <th class="border-bottom textkecil border-top">Hidung</th>
                                  <th class="border-bottom textkecil border-top">Bibir</th>
                                  <th class="border-bottom textkecil border-top">Lidah</th>
                                  <th class="border-bottom textkecil border-top">Visus</th>
                                  <th class="border-bottom textkecil border-top">Tenggorokan</th>
                                  <th class="border-bottom textkecil border-top">Faring</th>
                                  <th class="border-bottom textkecil border-top">Buta Warna</th>
                                  <th class="border-bottom textkecil border-top">Telinga</th>
                                  <th class="border-bottom textkecil border-top">Kelainan Telinga</th>
                                  <th class="border-bottom textkecil border-top">Leher KGB</th>
                                  <th class="border-bottom textkecil border-top">Leher JVP</th>
                                  <th class="border-bottom textkecil border-top">Gigi</th>
                                  <th class="border-bottom textkecil border-top">Thorax Jantung Inspeksi</th>
                                  <th class="border-bottom textkecil border-top">Thorax Jantung Auskultasi</th>
                                  <th class="border-bottom textkecil border-top">Thorax Jantung Palpasi</th>
                                  <th class="border-bottom textkecil border-top">Thorax Jantung Perkusi</th>
                                  <th class="border-bottom textkecil border-top">Thorax Paru Inspeksi</th>
                                  <th class="border-bottom textkecil border-top">Thorax Paru Auskultasi Vasikuler</th>
                                  <th class="border-bottom textkecil border-top">Thorax Paru Inspeksi Auskultasi Ronkhi</th>
                                  <th class="border-bottom textkecil border-top">Thorax Paru Inspeksi Auskultasi Wheezing</th>
                                  <th class="border-bottom textkecil border-top">Thorax Paru Inspeksi Palpasi</th>
                                  <th class="border-bottom textkecil border-top">Thorax Paru Inspeksi Perkusi</th>
                                  <th class="border-bottom textkecil border-top">Thorak Abdomen Inspeksi</th>
                                  <th class="border-bottom textkecil border-top">Thorak Abdomen Auskultasi</th>
                                  <th class="border-bottom textkecil border-top">Thorak Abdomen Palpasi</th>
                                  <th class="border-bottom textkecil border-top">Thorak Abdomen Perkusi</th>
                                  <th class="border-bottom textkecil border-top">Extrimitas Fisiologis Atas</th>
                                  <th class="border-bottom textkecil border-top">Extrimitas Fisiologis Bawah</th>
                                  <th class="border-bottom textkecil border-top">Extrimitas Phatologis Atas</th>
                                  <th class="border-bottom textkecil border-top">Extrimitas Phatologis Bawah</th>
                                  <th class="border-bottom textkecil border-top">EKG</th>
                                  <th class="border-bottom textkecil border-top">Neurologis</th>
                                  <th class="border-bottom textkecil border-top">Kesimpulan Pemeriksaan</th>
                                  <th class="border-bottom textkecil border-top">Catatan</th>
                                  <th class="border-bottom textkecil border-top">Lab </th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Hemoglobin</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Reduksi</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Hematokrit</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Lekosit</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Trombosit</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Eritrosit</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Basofil</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Eosinofil </th>
                                 <th class="border-bottom textkecil border-top" nowrap="">N Batang </th>
                                 <th class="border-bottom textkecil border-top" nowrap="">N Segmen</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Limfosit </th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Monosit </th>
                                 <th class="border-bottom textkecil border-top" nowrap="">SGOT</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">SGPT</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Ureum</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Creatinin</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Glukosa Puasa</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Berat Jenis</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">PH / Reaksi </th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Warna </th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Kekeruhan</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Urobilinogen</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Bilirubin</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Eritrosit</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Keton</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Protein</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Sedimen – Epitel</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Sedimen – Eritrosit</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Sedimen – Leukosit</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Sedimen - Bakteri</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Sedimen – Kristal</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Cholesterol Total</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Asam Urat</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Glukosa Sewaktu</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Trigliserida</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">HDL Cholesterol</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">LDL Cholestero</th>
                                 <th class="border-bottom textkecil border-top" nowrap="">Kesimpulan Hasil</th>
                                <th class="border-bottom textkecil border-top">Rad </th>
                                <th class="border-bottom textkecil border-top">COR </th>
                                <th class="border-bottom textkecil border-top">Diafragma Sinus </th>
                                <th class="border-bottom textkecil border-top">Pulmo</th>
                                <th class="border-bottom textkecil border-top">Kesan</th>
                                <th class="border-bottom textkecil border-top">Audiometri </th>
                                 <th class="border-bottom textkecil border-top">Audiometri Telinga Kanan</th>
                                 <th class="border-bottom textkecil border-top">Audiometri Telinga Kiri</th>
                                 <th class="border-bottom textkecil border-top">Pendengaran Telinga Kanan</th>
                                 <th class="border-bottom textkecil border-top">Pendengaran Telinga Kiri</th>
                                 <th class="border-bottom textkecil border-top">Kesimpulan Pem. Audiometri</th>
                                 <th class="border-bottom textkecil border-top">Saran Hasil Pem. Auidometri</th>
                                 <th class="border-bottom textkecil border-top">EKG </th>
                                 <th class="border-bottom textkecil border-top">Takikardi</th>
                                 <th class="border-bottom textkecil border-top">Bradikardi</th>
                                 <th class="border-bottom textkecil border-top">Aritmia</th>
                                 <th class="border-bottom textkecil border-top">Aresst</th>
                                 <th class="border-bottom textkecil border-top">Penemuan Lain</th>
                                 <th class="border-bottom textkecil border-top">Keadaan Jantung Normal</th>
                                 <th class="border-bottom textkecil border-top">Kesimpulan Hasil EKG</th>
                                <th class="border-bottom textkecil border-top">Spiro </th>
                                 <th class="border-bottom textkecil border-top">Hasil Pem. Spirometri</th>
                                 <th class="border-bottom textkecil border-top">Retriksi</th>
                                 <th class="border-bottom textkecil border-top">Obstruksif</th>
                                <th class="border-bottom textkecil border-top">Rectal </th>
                                 <th class="border-bottom textkecil border-top">Salmonella Thypi</th>
                                 <th class="border-bottom textkecil border-top">Shigella SP</th>
                                 <th class="border-bottom textkecil border-top">E. Coli Pathogen</th>
                                 <th class="border-bottom textkecil border-top">Kesimpulan Hasil</th>
                                </tr>
                            </thead>
                    <tbody>
                    @foreach($data as $key=> $item)
                      <tr style="background-color: {{
                                ($item->pemeriksaanFisik?->kesimpulan == "UNFIT") ? '#eadafd' :
                                (($item->pemeriksaanFisik?->kesimpulan == "FIT WITH RESTRICTION") ? '#9bdeee' : '')
                            }}">

                            <td nowrap="">{{ $key +1 }}</td>
                            <td nowrap="">{{ $item->register_date }}</td>
                            <td nowrap="">{{ $item->register_number }}</td>
                            <td nowrap="">{{ $item->code }}</td>
                            <td nowrap="">{{ $item->nik }}</td>
                            <td nowrap="">{{ $item->name }}</td>
                            <td nowrap="">{{ $item->birthday }}</td>
                            <td nowrap="">{{ $item->gender }}</td>
                            <td nowrap="">{{ $item->department?->name }}</td>
                            <td nowrap="">{{ $item->divisi?->name }}</td>
                            <td nowrap="">{{ $item->client?->name }}</td>
                            <td nowrap="">{{ $item->client?->building ?? "-" }}</td>
                            <td nowrap="">{{ $item->packet_name }}</td>
                            {{-- pemerikaan Tanda Vital --}}
                            <td nowrap="">{!! $item->tandaVital?->selesai ? "<span class='text-success'>SELESAI</span>" : "<span class='text-danger'>TIDAK</span>" !!}</td>
                            <td nowrap="">{{ $item->tandaVital?->keluhan_utama_text }}</td>
                            <td nowrap="">{{ $item->tandaVital?->riwayat_penyakit_sekarang_text }}</td>
                            <td nowrap="">{{ $item->tandaVital?->riwayat_penyakit_terdahulu_text }}</td>
                            <td nowrap="">{{ $item->tandaVital?->riwayat_trauma_text }}</td>
                            <td nowrap="">{{ $item->tandaVital?->alergi_text }}</td>
                            <td nowrap="">{{ $item->tandaVital?->alergi_text }}</td>
                            <td nowrap="">{{ $item->tandaVital?->konsumsi_alkohol }}</td>
                            <td nowrap="">{{ $item->tandaVital?->tinggi_badan }}</td>
                            <td nowrap="">{{ $item->tandaVital?->berat_badan }}</td>
                            <td nowrap="">{{ $item->tandaVital?->imt }}</td>
                            <td nowrap="">{{ $item->tandaVital?->imt_nilai }}</td>
                            <td nowrap="">{{ $item->tandaVital?->tekanan_darah }}</td>
                            <td nowrap="">{{ $item->tandaVital?->frekuensi_nadi }}</td>
                            <td nowrap="">{{ $item->tandaVital?->frekuensi_nafas }}</td>
                            {{-- pemerikaan Fisik --}}
                            <td nowrap="">{!! $item->pemeriksaanFisik?->selesai ? "<span class='text-success'>SELESAI</span>" : "<span class='text-danger'>TIDAK</span>" !!}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->keadaan_umum }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->kepala_text }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->mata }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->pupil }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->hidung }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->bibir }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->lidah }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->visus }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->tenggorokan_text }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->faring }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->buta_warna }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->telinga }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->kelainan_telinga }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->leher_kgb }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->leher_jvp }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->gigi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->jantung_inspeksi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->jantung_auskultasi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->jantung_palpasi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->jantung_perkusi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->paru_inspeksi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->paru_auskultasi_vasikuler }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->paru_auskultasi_ronkhi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->paru_auskultasi_wheezing }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->paru_palpasi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->paru_perkusi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->abdomen_inspeksi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->abdomen_auskultasi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->abdomen_palpasi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->abdomen_perkusi }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->reflex_fisiologis_atas }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->reflex_fisiologis_bawah }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->reflex_phatologis_atas }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->reflex_phatologis_bawah }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->ekg_text }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->neurologis_text }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->kesimpulan }}</td>
                            <td nowrap="">{{ $item->pemeriksaanFisik?->saran }}</td>
                            {{-- LAB --}}
                            <td nowrap="">{!! $item->laboratorium?->selesai ? "<span class='text-success'>SELESAI</span>" : "<span class='text-danger'>TIDAK</span>" !!}</td>
                            <td nowrap="">{{ $item->laboratorium?->hemoglobin }}</td>
                            <td nowrap="">{{ $item->laboratorium?->reduksi }}</td>
                            <td nowrap="">{{ $item->laboratorium?->hematokrit }}</td>
                            <td nowrap="">{{ $item->laboratorium?->lekosit }}</td>
                            <td nowrap="">{{ $item->laboratorium?->trombosit }}</td>
                            <td nowrap="">{{ $item->laboratorium?->eritrosit }}</td>
                            <td nowrap="">{{ $item->laboratorium?->basofil }}</td>
                            <td nowrap="">{{ $item->laboratorium?->eosinofil }}</td>
                            <td nowrap="">{{ $item->laboratorium?->batang }}</td>
                            <td nowrap="">{{ $item->laboratorium?->segmen }}</td>
                            <td nowrap="">{{ $item->laboratorium?->limfosit }}</td>
                            <td nowrap="">{{ $item->laboratorium?->monosit }}</td>
                            <td nowrap="">{{ $item->laboratorium?->sgot }}</td>
                            <td nowrap="">{{ $item->laboratorium?->sgot }}</td>
                            <td nowrap="">{{ $item->laboratorium?->ureum }}</td>
                            <td nowrap="">{{ $item->laboratorium?->creatinin }}</td>
                            <td nowrap="">{{ $item->laboratorium?->glukosa_puasa }}</td>
                            <td nowrap="">{{ $item->laboratorium?->berat_jenis }}</td>
                            <td nowrap="">{{ $item->laboratorium?->ph_reaksi }}</td>
                            <td nowrap="">{{ $item->laboratorium?->warna }}</td>
                            <td nowrap="">{{ $item->laboratorium?->kekeruhan }}</td>
                            <td nowrap="">{{ $item->laboratorium?->urobilinogen }}</td>
                            <td nowrap="">{{ $item->laboratorium?->bilirubin }}</td>
                            <td nowrap="">{{ $item->laboratorium?->eritrosit_urine }}</td>
                            <td nowrap="">{{ $item->laboratorium?->keton }}</td>
                            <td nowrap="">{{ $item->laboratorium?->protein }}</td>
                            <td nowrap="">{{ $item->laboratorium?->sedimen_epitel }}</td>
                            <td nowrap="">{{ $item->laboratorium?->sedimen_eritrosit }}</td>
                            <td nowrap="">{{ $item->laboratorium?->sedimen_leukosit }}</td>
                            <td nowrap="">{{ $item->laboratorium?->sedimen_bakteri }}</td>
                            <td nowrap="">{{ $item->laboratorium?->sedimen_kristal }}</td>
                            <td nowrap="">{{ $item->laboratorium?->cholesterol_total }}</td>
                            <td nowrap="">{{ $item->laboratorium?->asam_urat }}</td>
                            <td nowrap="">{{ $item->laboratorium?->glukosa_sewaktu }}</td>
                            <td nowrap="">{{ $item->laboratorium?->trigliserida }}</td>
                            <td nowrap="">{{ $item->laboratorium?->hdl_cholesterol }}</td>
                            <td nowrap="">{{ $item->laboratorium?->ldl_cholestero }}</td>
                            <td nowrap="">{{ $item->laboratorium?->kesimpulan }}</td>
                            {{-- Radiologi --}}
                            <td nowrap="">{!! $item->radiologi?->selesai ? "<span class='text-success'>SELESAI</span>" : "<span class='text-danger'>TIDAK</span>" !!}</td>
                            <td nowrap="">{{ $item->radiologi?->cor }}</td>
                            <td nowrap="">{{ $item->radiologi?->diafragma_sinus }}</td>
                            <td nowrap="">{{ $item->radiologi?->pulmo }}</td>
                            <td nowrap="">{{ $item->radiologi?->kesan }}</td>
                             {{-- audiometri --}}
                            <td nowrap="">{!! $item->audiometri?->selesai ? "<span class='text-success'>SELESAI</span>" : "<span class='text-danger'>TIDAK</span>" !!}</td>
                            <td nowrap="">{{ $item->audiometri?->audiometri_telinga_kanan }}</td>
                            <td nowrap="">{{ $item->audiometri?->audiometri_telinga_kiri }}</td>
                            <td nowrap="">{{ $item->audiometri?->pendengaran_telinga_kanan }}</td>
                            <td nowrap="">{{ $item->audiometri?->pendengaran_telinga_kiri }}</td>
                            <td nowrap="">{{ $item->audiometri?->kesimpulan }}</td>
                            <td nowrap="">{{ $item->audiometri?->saran }}</td>
                             {{-- ekg --}}
                            <td nowrap="">{!! $item->ekg?->selesai ? "<span class='text-success'>SELESAI</span>" : "<span class='text-danger'>TIDAK</span>" !!}</td>
                             <td nowrap="">{{ $item->ekg?->takikardi }}</td>
                             <td nowrap="">{{ $item->ekg?->bradikardi }}</td>
                             <td nowrap="">{{ $item->ekg?->aritmia }}</td>
                             <td nowrap="">{{ $item->ekg?->aresst }}</td>
                             <td nowrap="">{{ $item->ekg?->penemuan_lain }}</td>
                             <td nowrap="">{{ $item->ekg?->keadaan_jantung_normal }}</td>
                             <td nowrap="">{{ $item->ekg?->kesimpulan }}</td>
                             {{-- Spiro --}}
                            <td nowrap="">{!! $item->spirometri?->selesai ? "<span class='text-success'>SELESAI</span>" : "<span class='text-danger'>TIDAK</span>" !!}</td>
                            <td nowrap="">{{ $item->spirometri?->hasil }}</td>
                            <td nowrap="">{{ $item->spirometri?->retriksi }}</td>
                            <td nowrap="">{{ $item->spirometri?->obstruksif }}</td>
                             {{-- Rectal --}}
                            <td nowrap="">{!! $item->rectal?->selesai ? "<span class='text-success'>SELESAI</span>" : "<span class='text-danger'>TIDAK</span>" !!}</td>
                            <td nowrap="">{{ $item->rectal?->salmonella_thypi }}</td>
                            <td nowrap="">{{ $item->rectal?->shigella_sp }}</td>
                            <td nowrap="">{{ $item->rectal?->e_coli_pathogen }}</td>
                            <td nowrap="">{{ $item->rectal?->kesimpulan }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/picker.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/picker.date.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/picker.time.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/legacy.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>

    <script>
        let rangePickr = $('.flatpickr-range').flatpickr({
            mode: 'range',
            maxDate: 'today',
        });

        $('.import').click(function(){
            let url = $(this).data('url');
            let start = $('#start').val();
            let end = $('#end').val();

            if($(this).data('type') == "identitas"){
                 url = url + '?start=' + start + '&end=' + end+"filter[date_range]="+ $('#fp-range').val()+`&filter[client_id]=${$('#client_id').val()}`;
            }else{
                url = url + '?start=' + start + '&end=' + end+"filter[date_range]="+ $('#fp-range').val()+`&filter[participant.client_id]=${$('#client_id').val()}`;
            }

            window.open(url, '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');
        });
    $(document).ready(function() {
        // Ambil parameter dari URL
        const urlParams = new URLSearchParams(window.location.search);
        const dateRange = urlParams.get('filter[date_range]');

        let date1, date2;

        if (dateRange) {
            // Jika date_range ada di URL, pecah menjadi dua tanggal
            [date1, date2] = dateRange.split(' to ');
        } else {
            // Set tanggal default: dua hari lalu hingga hari ini
            date1 = moment().subtract(2, 'days').format('YYYY-MM-DD');
            date2 = moment().format('YYYY-MM-DD');
        }

        // Inisialisasi Flatpickr dengan tanggal default
        let rangePickr = $('.flatpickr-range').flatpickr({
            mode: 'range',
            maxDate: 'today',
            defaultDate: [date1, date2] // Set tanggal default
        });
    });

    $('.export').click(function(){
        let exportButton = $(this); // Save reference to the button
        let originalText = exportButton.text(); // Save original button text
        let url = "{{ route('report.importResultMcu') }}"+'?'+`filter[date_range]=${$('#fp-range').val()}&client_id=${$('#client_id').val()}&contract_id=${$('#contract_id').val()}&divisi_id=${$('#divisi_id').val()}`;
        
        // Disable the button and change the text to show the process is ongoing
        exportButton.prop('disabled', true).text('Sedang di proses...');

        $.get(url, function(data){
            if(data.status == 'success'){
                let countdown = 10; // Set the countdown duration in seconds
                exportButton.prop('disabled', true).text(`dalam ${countdown} detik akan terdownload`);

                let interval = setInterval(() => {
                    countdown--;
                    if (countdown > 0) {
                        exportButton.text(`dalam ${countdown} detik akan terdownload`);
                    } else {
                        clearInterval(interval);
                        window.open(data.download_url, '_blank'); // Trigger the download
                    }
                }, 1000); // Update every second

            } else {
                alert('Data tidak ditemukan');
            }
        })
        .fail(function() {
            alert('Terjadi kesalahan saat memproses permintaan.');
        })
        .always(function() {
            // Re-enable the button and restore the original text
            setTimeout(() => {
                exportButton.prop('disabled', false).text(originalText);
            }, 10000);
        });
    });
    </script>
@endsection
