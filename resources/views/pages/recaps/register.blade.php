@extends('layouts.main')

@section('title', 'Rekap Register')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection
@section('content')
    <style>
        .table-responsive table.textkecil {
            font-size: 12px !important; /* Atur ukuran font kecil dan prioritaskan */
        }

        .table-responsive th.textkecil, .table-responsive td.textkecil {
            font-size: 12px !important; /* Pastikan ukuran font pada header dan sel tabel */
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
        <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label" for="fp-range">Tanggal</label>
                        <input type="text" name="date_range" id="fp-range" class="form-control flatpickr-range"
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
                        <select name="contract_id" id="contract_id" class="form-select">
                            <option value="">Pilih</option>
                            @foreach ($client->contracts as $item)
                                <option value="{{ $item->id }}"
                                    {{ session('contract_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="" class="form-label">Divisi</label>
                        <select name="divisi_id" id="divisi_id" class="form-select">
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
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="dt-responsive table mt-1 textkecil" id="table">
                    <thead>
                        <tr class="textkecil" style="font-weight:500">
                            <th rowspan="2" style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center align-middle">Tgl</th>
                            <th rowspan="2" style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center align-middle">Jml Peserta</th>
                            <th colspan="10" style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">U</th>
                            <th colspan="2" style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Audiometri</th>
                            <th colspan="2" style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">EKG</th>
                            <th colspan="2" style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Spiro</th>
                            <th colspan="2" style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Rectal</th>
                            <th rowspan="2" style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center align-middle">HAMIL</th>
                        </tr>
                        <tr>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Jml</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">FOJ</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">FWR</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">TUF</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">TTV</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Fisik</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Lab</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Rad</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Hep</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Ten</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Jml</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Selesai</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Jml</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Selesai</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Jml</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Selesai</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Jml</th>
                            <th style="font-family:bold;color:#000;border-color:#000" class="border-bottom textkecil border-top border-left border-right text-center">Selesai</th>
                        </tr>
                    </thead>

                    <tbody id="data_table" style="font-weight:400">

                    </tbody>
                    <tbody id="data_total">

                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
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
        $(document).ready(function() {
            $('.flatpickr-range').flatpickr({
                mode: 'range',
                maxDate: 'today',

            });
            $('#cari').click(function (e){
                loadDataTable();
            })

            function loadDataTable() {
                let url = "{{ route('recap.register') }}"

                // Get the selected date range
                let dateRange = $('#fp-range').val();

                // Append date range to the URL if it's selected
                if (dateRange) {
                    url += `?date_range=${dateRange}`;
                }

                // Append client, contract, and division filters to the URL
                let clientId = $('#client_id').val();
                let contractId = $('#contract_id').val();
                let divisiId = $('#divisi_id').val();

                if (clientId) {
                    url += `&client_id=${clientId}`;
                }
                if (contractId) {
                    url += `&contract_id=${contractId}`;
                }
                if (divisiId) {
                    url += `&divisi_id=${divisiId}`;
                }
                $.get(url, function(response) {
                    $("#data_table").html(''); // Clear the table content
                    let totalParticipants = 0;
                    let totalSelesaiPemeriksaanFit = 0;
                    let totalSelesaiPemeriksaanFrw = 0;
                    let totalSelesaiPemeriksaanUnfit = 0;
                    let totalTandaVitals = 0;
                    let totalPemeriksaanFisiks = 0;
                    let totalLaboratoria = 0;
                    let totalRadiologis = 0;
                    let totalField10 = 0;
                    let totalField11 = 0;
                    let totalAudiometris = 0;
                    let totalSelesaiAudiometris = 0;
                    let totalEkgs = 0;
                    let totalSelesaiEkgs = 0;
                    let totalSpirometris = 0;
                    let totalSelesaiSpirometris = 0;
                    let totalRectals = 0;
                    let totalSelesaiRectals = 0;
                    let totalHamil = 0;
                    // Iterate through the response using $.each
                    $.each(response, function(index, data) {
                        totalParticipants += parseInt(data.total_participants);
                        totalSelesaiPemeriksaanFit += parseInt(data.total_selesai_pemeriksaan_fit);
                        totalSelesaiPemeriksaanFrw += parseInt(data.total_selesai_pemeriksaan_frw);
                        totalSelesaiPemeriksaanUnfit += parseInt(data.total_selesai_pemeriksaan_unfit);
                        totalTandaVitals += parseInt(data.total_tanda_vitals);
                        totalPemeriksaanFisiks += parseInt(data.total_pemeriksaan_fisiks);
                        totalLaboratoria += parseInt(data.total_laboratoria);
                        totalRadiologis += parseInt(data.total_radiologis);
                        totalField10 += parseInt(data.field10);
                        totalField11 += parseInt(data.field11);
                        totalAudiometris += parseInt(data.total_audiometris);
                        totalSelesaiAudiometris += parseInt(data.total_selesai_audiometris);
                        totalEkgs += parseInt(data.total_ekgs);
                        totalSelesaiEkgs += parseInt(data.total_selesai_ekgs);
                        totalSpirometris += parseInt(data.total_spirometris);
                        totalSelesaiSpirometris += parseInt(data.total_selesai_spirometris);
                        totalRectals += parseInt(data.total_rectals);
                        totalSelesaiRectals += parseInt(data.total_selesai_rectals);
                        totalHamil += parseInt(data.total_hamil);
                        // Construct the row content dynamically
                        let content = `
                            <tr align="center">
                                <td class="border-bottom border-top text-start">${data.register_date}</td>
                                <td class="border-bottom border-top text-center">${data.total_participants}</td>
                                <td class="border-bottom border-top text-center">${data.total_participants}</td>
                                <td class="border-bottom border-top text-center text-success">${data.total_selesai_pemeriksaan_fit}</td>
                                <td class="border-bottom border-top text-center text-success">${data.total_selesai_pemeriksaan_frw}</td>
                                <td class="border-bottom border-top text-center text-success">${data.total_selesai_pemeriksaan_unfit}</td>
                                <td class="border-bottom border-top text-center text-success">${data.total_tanda_vitals	}</td>
                                <td class="border-bottom border-top text-center text-success">${data.total_pemeriksaan_fisiks	}</td>
                                <td class="border-bottom border-top text-center text-success">${data.total_laboratoria}</td>
                                <td class="border-bottom border-top text-center text-success">${data.total_radiologis}</td>
                                <td class="border-bottom border-top text-center text-success">${data.field10}</td>
                                <td class="border-bottom border-top text-center text-success">${data.field11}</td>
                                <td class="border-bottom border-top text-center">${data.total_audiometris}</td>
                                <td class="border-bottom border-top text-center text-success">${data.total_selesai_audiometris}</td>
                                <td class="border-bottom border-top text-center">${data.total_ekgs}</td>
                                <td class="border-bottom border-top text-center text-success">${data.total_selesai_ekgs	}</td>
                                <td class="border-bottom border-top text-center">${data.total_spirometris}</td>
                                <td class="border-bottom border-top text-center text-success">${data.total_selesai_spirometris	}</td>
                                <td class="border-bottom border-top text-center">${data.total_rectals}</td>
                                <td class="border-bottom border-top text-center text-success">${data.total_selesai_rectals}</td>
                                <td class="border-bottom border-top text-center">${data.total_hamil}</td>
                            </tr>
                            `;

                        // Append the constructed content to the table
                        $("#data_table").append(content);
                    });

                    // After looping through all records, append the total row
                    let totalContent = `
                        <tr align="center">
                            <td class="border-bottom border-top text-start">Total</td>
                            <td class="border-bottom border-top text-center">${totalParticipants}</td>
                            <td class="border-bottom border-top text-center">${totalParticipants}</td>
                            <td class="border-bottom border-top text-center text-success">${totalSelesaiPemeriksaanFit}</td>
                            <td class="border-bottom border-top text-center text-success">${totalSelesaiPemeriksaanFrw}</td>
                            <td class="border-bottom border-top text-center text-success">${totalSelesaiPemeriksaanUnfit}</td>
                            <td class="border-bottom border-top text-center text-success">${totalTandaVitals}</td>
                            <td class="border-bottom border-top text-center text-success">${totalPemeriksaanFisiks}</td>
                            <td class="border-bottom border-top text-center text-success">${totalLaboratoria}</td>
                            <td class="border-bottom border-top text-center text-success">${totalRadiologis}</td>
                            <td class="border-bottom border-top text-center text-success">${totalField10}</td>
                            <td class="border-bottom border-top text-center text-success">${totalField11}</td>
                            <td class="border-bottom border-top text-center">${totalAudiometris}</td>
                            <td class="border-bottom border-top text-center text-success">${totalSelesaiAudiometris}</td>
                            <td class="border-bottom border-top text-center">${totalEkgs}</td>
                            <td class="border-bottom border-top text-center text-success">${totalSelesaiEkgs}</td>
                            <td class="border-bottom border-top text-center">${totalSpirometris}</td>
                            <td class="border-bottom border-top text-center text-success">${totalSelesaiSpirometris}</td>
                            <td class="border-bottom border-top text-center">${totalRectals}</td>
                            <td class="border-bottom border-top text-center text-success">${totalSelesaiRectals}</td>
                            <td class="border-bottom border-top text-center">${totalHamil}</td>
                        </tr>
                    `;

                    // Append the total row to the table
                    $("#data_table").append(totalContent);
                });
            }
        });
    </script>

@endsection
