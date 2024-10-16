@extends('layouts.main')

@section('title', 'Upload Validasi Dokter')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('content')
    <div class="card">
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
                        <button type="submit" class="btn btn-primary">Cari</button>
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
                                    <a class="btn btn-success left" href="https://docs.google.com/spreadsheets/d/1i_LMCnMkPWYx9Nk17PuQaZ1AKHngFVcGPcL7wI0auRE/edit?usp=sharing" target="_blank" type="button">
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
            <h5>Hasil MCUç</h5>
            <div class="table-responsive small textkecil">
                <table id="tabelLap" border="1" class="table customize-table table-bordered mb-0 v-middle">
                    <thead>
                    <tr>
                        <th class="border-bottom border-top">No.</th>
                        <th class="border-bottom border-top">TANGGAL UPLOAD</th>
                        <th class="border-bottom border-top">TANGGAL MCU</th>
                        <th class="border-bottom border-top">MCU ID</th>
                        <th class="border-bottom border-top">NAMA</th>
                        <th class="border-bottom border-top">GENDER</th>
                        <th class="border-bottom border-top">Hasil MCU</th>
                        <th class="border-bottom border-top">Catatan</th>
                        <th class="border-bottom border-top">Dokter Pemeriksa</th>
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

            // Ambil form dengan id 'formUpload'
            let form = $('#formUpload')[0];  // Ambil elemen form
            let formData = new FormData(form);  // Buat objek FormData dari elemen form

            $.ajax({
                url: "{{ route('upload.validasi.dokter') }}",  // URL untuk mengirim data
                type: 'POST',
                data: formData,  // Data yang dikirim adalah FormData
                processData: false,  // Jangan proses data
                contentType: false,  // Jangan tentukan jenis konten (biarkan browser melakukannya)
                beforeSend: function() {
                    // Mungkin tampilkan loading atau disable tombol upload
                    $('#upload_btn').attr('disabled', true).text('Uploading...');
                },
                success: function(response) {
                    alert(`Data berhasil di import`);
                },
                error: function(xhr, status, error) {
                    // Proses jika terjadi error
                    console.log(xhr.responseText);
                    alert('Upload failed. Please try again.');
                },
                complete: function() {
                    // Reset tombol setelah selesai
                    $('#upload_btn').attr('disabled', false).text('Upload');
                }
            });
        });
    </script>


@endsection
