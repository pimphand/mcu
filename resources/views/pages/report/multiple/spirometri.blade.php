@foreach ($data as $index=> $participant)
<!DOCTYPE html>
<html lang="en">
@php
$totalItems = count($data);  // Jumlah total data
$currentPage = $index + 1;
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SPIROMETRI</title>
    <style>
        table {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        td {
            padding: 5px;
        }

        .border {
            border: 1px solid black;
            border-collapse: collapse;
            width: 50%;
            padding: 3px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    @include('pages.report.header', $participant)
    <table>

        <body>
            <tr>
                <td>
                    <table>
                        <tbody>
                            <tr>
                                <td>MCU ID</td>
                                <td>:</td>
                                <td>{{ $participant->code }}</td>
                            </tr>
                            <tr>
                                <td>NIK</td>
                                <td>:</td>
                                <td>{{ $participant->nik }}</td>
                            </tr>
                            <tr>
                                <td>Nama</td>
                                <td>:</td>
                                <td>{{ $participant->name }} [{{ $participant->gender }}]</td>
                            </tr>
                            <tr>
                                <td>Tgl Lahir</td>
                                <td>:</td>
                                <td>{{ $participant->birthday }}</td>
                            </tr>
                            <tr>
                                <td>Usia</td>
                                <td>:</td>
                                <td>{{ \Carbon\Carbon::parse($participant->birthday)->diff(\Carbon\Carbon::now())->format('%y tahun %m bulan  %d hari') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <table>
                        <tbody>
                            <tr>
                                <td>Perusahaan</td>
                                <td>:</td>
                                <td>{{ $participant->client?->name }}</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>:</td>
                                <td>{{ $participant->status }}</td>
                            </tr>
                            <tr>
                                <td>Dept. ID</td>
                                <td>:</td>
                                <td>{{ $participant->department?->code }}</td>
                            </tr>
                            <tr>
                                <td>Department</td>
                                <td>:</td>
                                <td>{{ $participant->department?->name }}</td>
                            </tr>
                            <tr>
                                <td>Tgl Register</td>
                                <td>:</td>
                                <td>{{ $participant->register_date }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </body>
    </table>
    @php
        $spirometri = $participant->spirometri;
    @endphp
    <div class="text-center" style="font-size: 18px; width: 100%; margin-bottom: 10px; margin-top: 10px;">SPIROMETRI</div>
    <table style="border-collapse: collapse;">
        <thead>
            <tr>
                <th class="border">PEMERIKSAAN</th>
                <th class="border">HASIL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border">Hasil Pem. Spirometri</td>
                <td class="border text-center">
                    {{ $spirometri->hasil }}</td>
            </tr>
            <tr>
                <td class="border">Retriksi</td>
                <td class="border text-center">
                    {{ $spirometri->retriksi }}</td>
            </tr>
            <tr>
                <td class="border">Obstruksif</td>
                <td class="border text-center">
                    {{ $spirometri->obstruksif }}</td>
            </tr>
        </tbody>
    </table>
    <table style="margin-top: 10px;">
        <tbody>
            <tr>
                <td style="width: 50%;"></td>
                <td>
                    <table>
                        <tbody>
                            <tr>
                                <td class="text-center">{{ config('app.city') }}, {{ now()->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">Dokter Pemeriksa,</td>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <img src="{{ public_path($spirometri->employee?->ttd ? $spirometri->employee?->ttd : 'images/ttd-kosong.png') }}" width="80" alt="img" alt="img">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">{{ $spirometri->employee?->nama }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Footer yang menunjukkan nomor halaman -->
    <div class="footer" style="position: fixed; bottom: 0; left: 0; right: 0; width: 100%; text-align: end; font-size: 12px;">
        <div class="page-number">
            Print {{ $currentPage }} dari {{ $totalItems }}
        </div>
    </div>
</body>

</html>
@endforeach
