@extends('layouts.main')

@section('title', 'Upload Laboratorium')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .table-responsive table.textkecil {
            font-size: 10px !important; /* Atur ukuran font kecil dan prioritaskan */
        }

        .table-responsive thead th.textkecil, .table-responsive td.textkecil {
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
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
           <h3> Upload Laboratorium</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('participant.print.mcu') }}" method="get">
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
                        <button type="button" class="btn btn-primary" id="cari">Cari</button>
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="col-md-12 mt-2">
                    <h3>Import Data</h3>
                    <form id="formUpload" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Pilih File Excel</label>
                                    <input type="file" name="fileExcel" class="">
                                    <button class="btn btn-success left" id="upload_btn" type="button">
                                        <i class="mdi mdi-cloud-upload"></i>
                                        Upload
                                    </button>
                                    <a href="https://docs.google.com/spreadsheets/d/1ibg_2adBljBhzZFHnQFDXWQEJ8B0-MXozow6ab6lbfQ/edit?usp=sharing" target="_blank" class="btn btn-success left" >
                                        <i class="mdi mdi-cloud-upload"></i>
                                        Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div id="progress">
                        <div id="progress-bar" style="width: 0%; background-color: green; height: 25px;"></div>
                    </div>
                </div>
            </div>
            <div class="table-responsive small textkecil">
                <table id="tabelLap"  class="table customize-table table-bordered mb-0 v-middle textkecil">
                    <thead>
                    <tr style="font-size: 10px">
                        <th class="border-bottom border-top textkecil">No.</th>
                        <th class="border-bottom border-top textkecil">TANGGAL MCU</th>
                        <th class="border-bottom border-top textkecil">code</th>
                        <th class="border-bottom border-top textkecil">name</th>
                        <th class="border-bottom border-top textkecil">Hemoglobin</th>
                        <th class="border-bottom border-top textkecil">Hematokrit</th>
                        <th class="border-bottom border-top textkecil">Lekosit</th>
                        <th class="border-bottom border-top textkecil">Trombosit</th>
                        <th class="border-bottom border-top textkecil">Eritrosit</th>
                        <th class="border-bottom border-top textkecil">Basofil</th>
                        <th class="border-bottom border-top textkecil">Eosinofil</th>
                        <th class="border-bottom border-top textkecil">NBatang</th>
                        <th class="border-bottom border-top textkecil">NSegmen</th>
                        <th class="border-bottom border-top textkecil">Limfosit</th>
                        <th class="border-bottom border-top textkecil">Monosit</th>
                        <th class="border-bottom border-top textkecil">SGPT</th>
                        <th class="border-bottom border-top textkecil">Creatinin</th>
                        <th class="border-bottom border-top textkecil">glukosa_puasa</th>
                        <th class="border-bottom border-top textkecil">cholesterol_total</th>
                        <th class="border-bottom border-top textkecil">asam_urat</th>
                        <th class="border-bottom border-top textkecil">sgot</th>
                        <th class="border-bottom border-top textkecil">ureum</th>
                        <th class="border-bottom border-top textkecil">BeratJenis</th>
                        <th class="border-bottom border-top textkecil">PHReaksi</th>
                        <th class="border-bottom border-top textkecil">Warna</th>
                        <th class="border-bottom border-top textkecil">Kekeruhan</th>
                        <th class="border-bottom border-top textkecil">Urobilinogen</th>
                        <th class="border-bottom border-top textkecil">Bilirubin</th>
                        <th class="border-bottom border-top textkecil">Eritrosit_urine</th>
                        <th class="border-bottom border-top textkecil">Keton</th>
                        <th class="border-bottom border-top textkecil">Protein</th>
                        <th class="border-bottom border-top textkecil">SedimenEpitel</th>
                        <th class="border-bottom border-top textkecil">SedimenEritrosit</th>
                        <th class="border-bottom border-top textkecil">SedimenLeukosit</th>
                        <th class="border-bottom border-top textkecil">SedimenBakteri</th>
                        <th class="border-bottom border-top textkecil">SedimenKristal</th>
                        <th class="border-bottom border-top textkecil">user_lab</th>
                        <th class="border-bottom border-top textkecil">lab_date</th>
                        <th class="border-bottom border-top textkecil">kesimpulan_lab</th>
                        <th class="border-bottom border-top textkecil">pemeriksa_lab</th>
                        <th class="border-bottom border-top textkecil">Reduksi</th>
                    </tr>
                    </thead>
                    <tbody id="tableBody">

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
    @php
        $date1 = now()->subDay()->format('Y-m-d');
        $date2 = now()->format('Y-m-d');
        if (request()->get('date_range')) {
            $date1 = explode(' to ', request()->get('date_range'))[0];
            $date2 = explode(' to ', request()->get('date_range'))[1] ?? null;
        }
    @endphp
    <script>
        let rangePickr = $('.flatpickr-range').flatpickr({
            mode: 'range',
            maxDate: 'today',
            defaultDate: ["{{ $date1 }}", "{{ $date2 }}"]
        });
    </script>

    <script type="module">
        $("#upload_btn").click(function (e){
            e.preventDefault();  // Mencegah form submit otomatis

            // Ambil form dengan id 'formUpload'
            let form = $('#formUpload')[0];  // Ambil elemen form
            let formData = new FormData(form);  // Buat objek FormData dari elemen form

            $.ajax({
                url: "{{ route('upload.laboratorium') }}",  // URL untuk mengirim data
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#upload_btn').attr('disabled', true).text('Uploading...');
                },
                success: function(response) {
                    alert('Data sedang di proses')
                },
                error: function(xhr, status, error) {
                    alert('Upload failed. Please try again.');
                },
                complete: function() {
                    // Reset tombol setelah selesai
                    $('#upload_btn').attr('disabled', false).text('Upload');
                }
            });
        });
        $('#cari').click(function (e){
            let url = "{{ route('upload.laboratorium') }}"
            url = url + "?date_range="+ $('#fp-range').val()+`&client_id=${$('#client_id').val()}`;
            $.ajax({
                url: url,  // URL untuk mengirim data
                type: 'get',
                beforeSend: function() {
                    $('#cari').attr('disabled', true).text('Diproses...');
                },
                success: function(response) {
                    var tableRows = '';

                    // Iterasi data lab
                    $.each(labData, function(index, item) {
                        tableRows += `<tr>
                            <td class="border-bottom border-top text-right">${index + 1}</td>
                            <td class="border-bottom border-top text-right">${item.date_mcu}</td>
                            <td class="border-bottom border-top">${item.participant.code}</td>
                            <td class="border-bottom border-top">${item.participant.name}</td>
                            <td class="border-bottom border-top">${item.hemoglobin}</td>
                            <td class="border-bottom border-top">${item.hematokrit}</td>
                            <td class="border-bottom border-top">${item.lekosit}</td>
                            <td class="border-bottom border-top">${item.trombosit}</td>
                            <td class="border-bottom border-top">${item.eritrosit}</td>
                            <td class="border-bottom border-top">${item.basofil}</td>
                            <td class="border-bottom border-top">${item.eosinofil}</td>
                            <td class="border-bottom border-top">${item.nbatang}</td>
                            <td class="border-bottom border-top">${item.nsegmen}</td>
                            <td class="border-bottom border-top">${item.limfosit}</td>
                            <td class="border-bottom border-top">${item.monosit}</td>
                            <td class="border-bottom border-top">${item.sgpt}</td>
                            <td class="border-bottom border-top">${item.creatinin}</td>
                            <td class="border-bottom border-top">${item.glukosa_puasa}</td>
                            <td class="border-bottom border-top">${item.cholesterol_total}</td>
                            <td class="border-bottom border-top">${item.asam_urat}</td>
                            <td class="border-bottom border-top">${item.sgot}</td>
                            <td class="border-bottom border-top">${item.ureum}</td>
                            <td class="border-bottom border-top">${item.berat_jenis}</td>
                            <td class="border-bottom border-top">${item.ph_reaksi}</td>
                            <td class="border-bottom border-top">${item.warna}</td>
                            <td class="border-bottom border-top">${item.kekeruhan}</td>
                            <td class="border-bottom border-top">${item.urobilinogen}</td>
                            <td class="border-bottom border-top">${item.bilirubin}</td>
                            <td class="border-bottom border-top">${item.eritrosit_urine}</td>
                            <td class="border-bottom border-top">${item.keton}</td>
                            <td class="border-bottom border-top">${item.protein}</td>
                            <td class="border-bottom border-top">${item.sedimen_epitel}</td>
                            <td class="border-bottom border-top">${item.sedimen_eritrosit}</td>
                            <td class="border-bottom border-top">${item.sedimen_leukosit}</td>
                            <td class="border-bottom border-top">${item.sedimen_bakteri}</td>
                            <td class="border-bottom border-top">${item.sedimen_kristal}</td>
                            <td class="border-bottom border-top">${item.user_lab}</td>
                            <td class="border-bottom border-top">${item.lab_date}</td>
                            <td class="border-bottom border-top">${item.kesimpulan_lab}</td>
                            <td class="border-bottom border-top">${item.pemeriksa_lab}</td>
                            <td class="border-bottom border-top">${item.reduksi}</td>
                        </tr>`;
                    });

                    // Memasukkan baris yang telah dibuat ke dalam tbody
                    $('#tableBody').html(tableRows);
                },
                error: function(xhr, status, error) {
                    alert('Upload failed. Please try again.');
                },
                complete: function() {
                    $('#cari').attr('disabled', false).text('Cari');
                }
            });
        })
    </script>
@endsection
