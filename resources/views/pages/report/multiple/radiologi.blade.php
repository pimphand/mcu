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
    <title>RADIOLOGI</title>
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
        $radiologi = $participant->radiologi;
    @endphp
    <div class="text-center" style="font-size: 18px; width: 100%; margin-bottom: 10px; margin-top: 10px;">RADIOLOGI</div>
    <table style="border-collapse: collapse;">
        <thead>
            <tr>
                <th class="border">PEMERIKSAAN</th>
                <th class="border">HASIL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border">COR</td>
                <td class="border text-center">
                    {!! $radiologi->cor !!}</td>

            </tr>
            <tr>
                <td class="border">Diafragma Sinus </td>
                <td class="border text-center">
                    {!! $radiologi->diafragma_sinus !!}</td>

            </tr>
            <tr>
                <td class="border">Pulmo</td>
                <td class="border text-center">
                    {!! $radiologi->pulmo !!}</td>

            </tr>
            <tr>
                <td class="border">Kesan</td>
                <td class="border text-center">
                    {!! $radiologi->kesan !!}</td>

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
                                    <img src="{{ asset($radiologi->employee?->ttd ? $radiologi->employee?->ttd : 'images/ttd-kosong.png') }}" width="100"
                                        alt="img" alt="img">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">{{ $radiologi->employee?->nama }}</td>
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
