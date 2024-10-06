<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LABORATORIUM SPGT & UREUM</title>
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
        $laboratorium = $participant->laboratorium;
    @endphp
    <table style="border-collapse: collapse;">
        <thead>
            <tr>
                <th class="border">PEMERIKSAAN</th>
                <th class="border">HASIL</th>
                <th class="border text-center">NILAI NORMAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3" class="border">KIMIA KLINIK</td>
            </tr>
            <tr>
                <td class="border">SGPT</td>
                <td class="border text-center">
                    {{ $laboratorium->sgpt }}</td>
                <td class="border text-center">
                    0 - 45 µ</td>
            </tr>
            <tr>
                <td class="border">Ureum</td>
                <td class="border text-center">
                    {{ $laboratorium->ureum }}</td>
                <td class="border text-center">
                    20 - 40 mg</td>
            </tr>
        </tbody>
    </table>
    <table style="margin-top: 10px;">
        <tbody>
            <tr>
                <td style="width: 50%;">
                    <table>
                        <tbody>
                            <tr>
                                <td>Kesimpulan</td>
                            </tr>
                            <tr>
                                <td>
                                    {!! $laboratorium->kesimpulan !!}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <table>
                        <tbody>
                            <tr>
                                <td class="text-center">{{ config('app.city') }}, {{ now()->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">Pemeriksa,</td>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <img src="{{ public_path($laboratorium->employee?->ttd ? $laboratorium->employee?->ttd : 'images/ttd-kosong.png') }}" width="80"
                                        alt="img" alt="img">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">{{ $laboratorium->employee?->nama }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
