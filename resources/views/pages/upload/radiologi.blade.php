@extends('layouts.main')

@section('title', 'Upload Radiologi')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3> Upload Radiologi</h3>
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pilih File Excel</label>
                                    <input type="file" name="fileExcel" class="">
                                    <button class="btn btn-success left" id="upload_btn" type="button">
                                        <i class="mdi mdi-cloud-upload"></i>
                                        Upload
                                    </button>
                                    <a href="https://docs.google.com/spreadsheets/d/1qlsSuNyR7Uf7T8YVIL49Oi_ehLF5DkBTls2JEW5jSJU/edit?usp=sharing" target="_blank" class="btn btn-success left" type="button">
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
                <table id="tabelLap" border="1" class="table customize-table table-bordered mb-0 v-middle ">
                    <thead>
                    <tr>
                        <th class="border-bottom border-top">No.</th>
                        <th class="border-bottom border-top">TANGGAL MCU</th>
                        <th class="border-bottom border-top">TANGGAL UPLOAD</th>
                        <th class="border-bottom border-top">MCU ID</th>
                        <th class="border-bottom border-top">NAMA</th>
                        <th class="border-bottom border-top">GENDER</th>
                        <th class="border-bottom border-top">COR</th>
                        <th class="border-bottom border-top">RFS</th>
                        <th class="border-bottom border-top">PULMO</th>
                        <th class="border-bottom border-top">KESAN</th>
                        <th class="border-bottom border-top">PEMERIKSA</th>
                    </tr>
                    </thead>
                    <tbody id="data">

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

            let form = $('#formUpload')[0];  // Ambil elemen form
            let formData = new FormData(form);  // Buat objek FormData dari elemen form

            $.ajax({
                url: "{{ route('upload.radiologi') }}",  // URL untuk mengirim data
                type: 'POST',
                data: formData,  // Data yang dikirim adalah FormData
                processData: false,  // Jangan proses data
                contentType: false,  // Jangan tentukan jenis konten (biarkan browser melakukannya)
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
                    $('#upload_btn').attr('disabled', false).text('Upload');
                }
            });
        });

        $('#cari').click(function (e){
            let url = "{{ route('upload.radiologi') }}"
            url = url + "?date_range="+ $('#fp-range').val()+`&client_id=${$('#client_id').val()}`;
            $.ajax({
                url: url,  // URL untuk mengirim data
                type: 'get',
                beforeSend: function() {
                    $('#cari').attr('disabled', true).text('Diproses...');
                },
                success: function(response) {
                    let tableRows = '';
                    $.each(response.data, function(index, item) {
                        tableRows += `<tr>
                            <td class="border-bottom border-top text-right" nowrap="">${index + 1}</td>
                            <td class="border-bottom border-top text-right" nowrap="">${item.date_mcu}</td>
                            <td class="border-bottom border-top">${item.created_at.split('T')[0]}</td>
                            <td class="border-bottom border-top">${item.participant.code}</td>
                            <td class="border-bottom border-top">${item.participant.name}</td>
                            <td class="border-bottom border-top">${item.participant.gender}</td>
                            <td class="border-bottom border-top">${item.cor}</td>
                            <td class="border-bottom border-top">${item.diafragma_sinus}</td>
                            <td class="border-bottom border-top">${item.pulmo}</td>
                            <td class="border-bottom border-top">${item.kesan}</td>
                            <td class="border-bottom border-top">${item.diperiksa}</td>
                        </tr>`;
                    });
                    $('#tabelLap tbody').html(tableRows);  // Populate the table body
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
    <script>
        // Inisialisasi Pusher
        var pusher = new Pusher('23ac53d7303716c97001', {
            cluster: 'ap1'
        });

        var channel = pusher.subscribe('notification-channel');
        channel.bind('import-completed', function(data) {
            alert(data.message); // Atau gunakan metode lain untuk menampilkan notifikasi
        });
    </script>

@endsection
