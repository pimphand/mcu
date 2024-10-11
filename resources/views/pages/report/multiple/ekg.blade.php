@foreach ($data as $index=> $spirometri)
<!DOCTYPE html>
<html lang="en">
@php
$participant = $spirometri->participant;
$totalItems = count($data);  // Jumlah total data
$currentPage = $index + 1;
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>EKG</title>
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
                                <td>{{ $participant->birthday }} /
                                    {{ \Carbon\Carbon::parse($participant->birthday)->diff(\Carbon\Carbon::now())->format('%y tahun %m bulan  %d hari') }}
                                </td>
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
                                                                <td>{{ $participant->divisi?->name }}</td>

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
        $ekg = $participant->ekg;
    @endphp
    <div class="text-center" style="font-size: 18px; width: 100%; margin-bottom: 10px; margin-top: 10px;">EKG</div>
    <table style="border-collapse: collapse;">
        <thead>
            <tr>
                <th class="border">PEMERIKSAAN</th>
                <th class="border">HASIL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border">Takikardi</td>
                <td class="border text-center">
                    {{ $ekg->takikardi ? 'YA' : 'TIDAK' }}</td>

            </tr>
            <tr>
                <td class="border">Bradikardi</td>
                <td class="border text-center">
                    {{ $ekg->bradikardi ? 'YA' : 'TIDAK' }}</td>

            </tr>
            <tr>
                <td class="border">Aritmia</td>
                <td class="border text-center">
                    {{ $ekg->aritmia ? 'YA' : 'TIDAK' }}</td>

            </tr>
            <tr>
                <td class="border">Aresst</td>
                <td class="border text-center">
                    {{ $ekg->aresst ? 'YA' : 'TIDAK' }}</td>

            </tr>
            <tr>
                <td class="border">Penemuan Lain</td>
                <td class="border text-center">
                    {{ $ekg->penemuan_lain }}</td>

            </tr>
            <tr>
                <td class="border">Keadaan Jantung Normal</td>
                <td class="border text-center">
                    {{ $ekg->keadaan_jantung_normal ? 'YA' : 'TIDAK' }}</td>

            </tr>
            <tr>
                <td class="border">Kesimpulan Hasil EKG </td>
                <td class="border text-center">
                    {!! $ekg->kesimpulan !!}</td>

            </tr>
        </tbody>
    </table>

    <table style="margin-top: 10px;">
        <tbody>
            <tr>
                <td style="width: 50%;">
                </td>
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
                                    <img src="{{ public_path($ekg->employee?->ttd ? $ekg->employee?->ttd : 'images/ttd-kosong.png') }}"
                                        width="80" alt="img" alt="img">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">{{ $ekg->employee?->nama }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
@endforeach
