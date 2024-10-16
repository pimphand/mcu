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
    <div class="card">
        <div class="card-body">
            <form method="get">
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
                        <button type="button" id="cari" class="btn btn-primary">Cari</button>
                    </div>
                </div>
            </form>
            <button class="btn-info mt-1 btn" id="excel">Excel</button>
            <button class="btn-info mt-1 btn" id="pdf">PDF</button>

            <h4 class="mt-1 text-center">
                <b>Data Registrasi Peserta MCU</b>
            </h4>
            <div class="table-responsive table-responsive small textkecil">
                <table class="dt-responsive table mt-1 textkecil" id="table">
                    <thead>
                    <tr>
                        <th class="border-bottom border-top textkecil">No.</th>
                        <th class="border-bottom border-top textkecil">Tgl Register</th>
                        <th class="border-bottom border-top textkecil">MCU ID</th>
                        <th class="border-bottom border-top textkecil">NIK</th>
                        <th class="border-bottom border-top textkecil">Nama Pasien</th>
                        <th class="border-bottom border-top textkecil">Tgl Lahir</th>
                        <th class="border-bottom border-top textkecil">JK</th>
                        <th class="border-bottom border-top textkecil">Bagian/ Unit</th>
                        <th class="border-bottom border-top textkecil">Perusahaan</th>
                        <th class="border-bottom border-top textkecil">Gedung</th>
                        <th class="border-bottom border-top textkecil">Paket MCU</th>
                        <th class="border-bottom border-top textkecil hilang">TTV </th>
                        <th class="border-bottom border-top textkecil hilang">Pemeriksaan Fisik </th>
                        <th class="border-bottom border-top textkecil hilang">Lab </th>
                        <th class="border-bottom border-top textkecil hilang">Rad </th>
                        <th class="border-bottom border-top textkecil hilang">Audiometri </th>
                        <th class="border-bottom border-top textkecil hilang">EKG </th>
                        <th class="border-bottom border-top textkecil hilang">Spiro </th>
                        <th class="border-bottom border-top textkecil hilang">Rectal </th>
                    </tr>
                    </thead>
                    <tbody id="data_table">

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <script>
        $(document).ready(function() {
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

            $('#cari').click(function (){
                let url = "{{route('recap.dataRegister')}}"
                    url = url + "?filter[date_range]="+ $('#fp-range').val()+`&filter[client_id]=${$('#client_id').val()}&filter[contract_id]=${$('#contract_id').val()}`;
                $.get(url, function(response) {
                    $("#data_table").html('');
                    $.each(response.data, function(index, data) {
                         let content = `
                            <tr>
                                <td class="border-bottom border-top text-right" nowrap="">${index+1}</td>
                                <td class="border-bottom border-top text-center">${data.register_date	}</td>
                                <td class="border-bottom border-top text-center">${data.code}</td>
                                <td class="border-bottom border-top text-center">${data.nik}</td>
                                <td class="border-bottom border-top text-left">${data.name}</td>
                                <td class="border-bottom border-top text-left" nowrap="">${data.birthday}</td>
                                <td class="border-bottom border-top text-center">${data.gender}</td>
                                <td class="border-bottom border-top text-center">${data.divisi} - ${data.department	}</td>
                                <td class="border-bottom border-top text-center">${data.perusahaan}</td>
                                <td class="border-bottom border-top text-center">${data.gedung}</td>
                                <td class="border-bottom border-top text-left">${data.packet_name}</td>
                                <td class="border-bottom border-top text-left hilang"><span class="text-success">${data.ttv}</span></td>
                                <td class="border-bottom border-top text-left hilang"><span class="text-${data.pemeriksaanFisik == "SELESAI" ? "success":'danger'}">${data.pemeriksaanFisik}</span></td>
                                <td class="border-bottom border-top text-left hilang"><span class="text-${data.laboratorium == "SELESAI" ? "success":'danger'}">${data.laboratorium}</span></td>
                                <td class="border-bottom border-top text-left hilang"><span class="text-${data.radiologi == "SELESAI" ? "success":'danger'}">${data.radiologi}</span></td>
                                <td class="border-bottom border-top text-left hilang"><span class="text-${data.audiometri == "SELESAI" ? "success":'danger'}">${data.audiometri}</span></td>
                                <td class="border-bottom border-top text-left hilang"><span class="text-${data.ekg == "SELESAI" ? "success":'danger'}">${data.ekg}</span></td>
                                <td class="border-bottom border-top text-left hilang"><span class="text-${data.spirometri == "SELESAI" ? "success":'danger'}">${data.spirometri}</span></td>
                                <td class="border-bottom border-top text-left hilang"><span class="text-${data.rectal == "SELESAI" ? "success":'danger'}">${data.rectal	}</span></td>
                            </tr>
                         `

                        $("#data_table").append(content)
                    });
                })
            })

            $('#excel').click(function () {
                let exportButton = $(this); // Save reference to the button
                let originalText = exportButton.text(); // Save original button text
                exportButton.prop('disabled', true).text('Sedang di proses...');
                let url = "{{route('recap.dataRegister')}}"
                url = url + "?filter[date_range]="+ $('#fp-range').val()+`&filter[client_id]=${$('#client_id').val()}&filter[contract_id]=${$('#contract_id').val()}&excel=1`;
                $.get(url, function(data) {
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
                }).fail(function() {
                    alert('Terjadi kesalahan saat memproses permintaan.');
                }).always(function() {
                    // Re-enable the button and restore the original text
                    setTimeout(() => {
                        exportButton.prop('disabled', false).text(originalText);
                    }, 10000);
                });
            })
            $('#pdf').click(function () {
                let exportButton = $(this); // Save reference to the button
                let originalText = exportButton.text(); // Save original button text
                exportButton.prop('disabled', true).text('Sedang di proses...');
                let url = "{{route('recap.dataRegister')}}"
                url = url + "?filter[date_range]="+ $('#fp-range').val()+`&filter[client_id]=${$('#client_id').val()}&filter[contract_id]=${$('#contract_id').val()}&pdf=1`;
                $.get(url, function(data) {
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
                }).fail(function() {
                    alert('Terjadi kesalahan saat memproses permintaan.');
                }).always(function() {
                    // Re-enable the button and restore the original text
                    setTimeout(() => {
                        exportButton.prop('disabled', false).text(originalText);
                    }, 10000);
                });
            })
        });
    </script>
@endsection
