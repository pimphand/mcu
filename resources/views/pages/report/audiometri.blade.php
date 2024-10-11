<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>audiometri</title>
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
        $audiometri = $participant->audiometri;
    @endphp
    <div class="text-center" style="font-size: 18px; width: 100%; margin-bottom: 10px; margin-top: 10px;">AUDIOMETRI</div>
    <table style="border-collapse: collapse;">
        <thead>
            <tr>
                <th class="border">PEMERIKSAAN</th>
                <th class="border">HASIL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border">Audiometri Telinga Kanan</td>
                <td class="border text-center">
                    {{ $audiometri->audiometri_telinga_kanan }}</td>
            </tr>
            <tr>
                <td class="border">Audiometri Telinga Kiri</td>
                <td class="border text-center">
                    {{ $audiometri->audiometri_telinga_kiri }}</td>
            </tr>
            <tr>
                <td class="border">Pendengaran Telinga Kanan</td>
                <td class="border text-center">
                    {{ $audiometri->pendengaran_telinga_kanan }}</td>
            </tr>
            <tr>
                <td class="border">Pendengaran Telinga Kiri</td>
                <td class="border text-center">
                    {{ $audiometri->pendengaran_telinga_kiri }}</td>
            </tr>
            <tr>
                <td class="border">Kesimpulan Pem. Audiometri</td>
                <td class="border text-center">
                    {{ $audiometri->kesimpulan }}</td>
            </tr>
            <tr>
                <td class="border">Saran Hasil Pem. Auidometri</td>
                <td class="border text-center">
                    {{ $audiometri->saran }}</td>
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
                                    <img src="{{ public_path($audiometri->employee?->ttd ? $audiometri->employee?->ttd : 'images/ttd-kosong.png') }}" width="80" alt="img" alt="img">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">{{ $audiometri->employee?->nama }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
