@extends('layouts.main')

@section('title', 'Divisi')

@section('css')
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
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="dt-responsive table mt-1 textkecil" id="table">
                    <thead>
                        <tr class="textkecil">
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

                    <tbody>
                        <tr align="center">
                            <td class="border-bottom border-top text-center">12-08-2024</td>
                                  <td class="border-bottom border-top text-center">108</td>
                                  <td class="border-bottom border-top text-center">108</td>
                                                                      <td class="border-bottom border-top text-center text-success">59</td>
                                  <td class="border-bottom border-top text-center text-success">49</td>
                                  <td class="border-bottom border-top text-center text-success">0</td>

                                  <td class="border-bottom border-top text-center text-success">108</td>
                                  <td class="border-bottom border-top text-center text-success">108</td>
                                  <td class="border-bottom border-top text-center text-success">108</td>
                                  <td class="border-bottom border-top text-center text-success">107</td>
                                  <td class="border-bottom border-top text-center text-success">0</td>
                                  <td class="border-bottom border-top text-center text-success">0</td>

                                  <td class="border-bottom border-top text-center">59</td>
                                  <td class="border-bottom border-top text-center text-success">59</td>
                                  <td class="border-bottom border-top text-center">37</td>
                                  <td class="border-bottom border-top text-center text-success">37</td>
                                  <td class="border-bottom border-top text-center">82</td>
                                  <td class="border-bottom border-top text-center text-success">82</td>
                                  <td class="border-bottom border-top text-center">0</td>
                                  <td class="border-bottom border-top text-center text-success">0</td>
                                  <td class="border-bottom border-top text-center">1</td>
                              </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function() {
            loadDataTable();
            function loadDataTable() {
                $.get('{{ route('recap.register') }}', function(response) {
                    console.log(response)
                })
            }

        })
    </script>
@endsection
