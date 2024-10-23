<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LABORATORIUM LAB DRIVER</title>
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
                                <td>{{ $participant->status ?? "-" }}</td>
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
                <td colspan="3" class="border">KIMIA DARAH</td>
            </tr>
            <tr>
                <td class="border">Glukosa Puasa</td>
                <td class="border text-center">
                    {{ $laboratorium->glukosa_puasa }}</td>
                <td class="border text-center">
                    0-110 mg/dl </td>
            </tr>
            <tr>
                <td class="border"> Cholesterol Total </td>
                <td class="border text-center">
                    {{ $laboratorium->cholesterol_total }}</td>
                <td class="border text-center">
                    < 200 mg/dl</td>
            </tr>
            <tr>
                <td class="border"> Asam Urat </td>
                <td class="border text-center">
                    {{ $laboratorium->asam_urat }}</td>
                <td class="border text-center">
                    P: < 5.7 mg/dl <br>
                    L: < 7.0 mg/dl 
                </td>
            </tr>
            <tr>
                <td class="border">Glukosa Sewaktu</td>
                <td class="border text-center">
                    {{ $laboratorium->glukosa_sewaktu }}</td>
                <td class="border text-center">
                    < 200 mg/dl</td>
            </tr>
            <tr>
                <td class="border">Trigliserida</td>
                <td class="border text-center">
                    {{ $laboratorium->trigliserida }}</td>
                <td class="border text-center">
                    < 220 mg/dl</td>
            </tr>
            <tr>
                <td class="border">HDL Cholesterol</td>
                <td class="border text-center">
                    {{ $laboratorium->hdl_cholesterol }}</td>
                <td class="border text-center">45-65 mg/dl</td>
            </tr>
            <tr>
                <td class="border">LDL Cholestero</td>
                <td class="border text-center">
                    {{ $laboratorium->ldl_cholestero }}</td>
                <td class="border text-center">130-160 mg/dl</td>
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
