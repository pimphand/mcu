<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MEDICAL RECORD</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        @page {
            size: 21cm 33cm;
        }
        table {
            width: 100%;
        }
        .container {
            padding: 2px;
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
        }
        .header h3 {
            background-color: #5f8a8b;
            color: #fff;
            padding: 10px;
            border-radius: 10px;
            display: inline-block;
        }
        .info {
            text-align: center;
            margin-bottom: 20px;
        }
        .info p {
            margin: 5px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            /*padding: 10px;*/
            text-align: left;
        }
        .data-table th {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: center;
            border: #0C102A;
            font-weight: bold;
            font-size: 20px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th, .data-table td {
            border: 1px solid black;
            /*padding: 10px;*/
            text-align: left;
        }

        .data-table th {
            background-color: #E0E0E0; /* Light gray background for the header */
            text-align: center;
        }
    </style>
</head>

<body>
@include('pages.report.header', $participant)
<table style="font-size: 12px">
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
                    <td> {{\Carbon\Carbon::parse($participant->birthday)->format('d-m-Y')}} /
                        {{ \Carbon\Carbon::parse($participant->birthday)->diff(\Carbon\Carbon::now())->format('%y tahun %m bulan  %d hari') }}
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
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>

</table>
<div class="container">
    <div class="header">
        <h3>MEDICAL RECORD</h3>
    </div>

    <table class="data-table" style="font-size: 12px">
        <tr>
            <th colspan="4">Data Peserta MCU</th>
        </tr>
        <tr>
             <td  style="border-bottom-color: #fff;border-right-color: #fff"></td>
            <td  style="border-bottom-color: #fff"></td>
            <td style="border-bottom-color: #fff;border-right-color: #fff">Tgl Register : </td>
            <td style="border-bottom-color: #fff">: {{\Carbon\Carbon::parse($participant->register_date)->format('d-m-Y')}}</td>


        </tr>
        <tr>
             <td  style="border-bottom-color: #fff;border-right-color: #fff"></td>
            <td  style="border-bottom-color: #fff"></td>
            <td style="border-bottom-color: #fff;border-right-color: #fff">MCU ID</td>
            <td style="border-bottom-color: #fff">: {{ $participant->code }}</td>
        </tr>
        <tr>
             <td  style="border-bottom-color: #fff;border-right-color: #fff"></td>
            <td  style="border-bottom-color: #fff"></td>
            <td style="border-bottom-color: #fff;border-right-color: #fff">Nama</td>
            <td style="border-bottom-color: #fff">: {{ $participant->name }} [{{ $participant->gender }}]</td>
        </tr>
        <tr>
             <td  style="border-bottom-color: #fff;border-right-color: #fff"></td>
            <td  style="border-bottom-color: #fff"></td>
            <td style="border-bottom-color: #fff;border-right-color: #fff">Tgl Lahir</td>
            <td style="border-bottom-color: #fff">: {{\Carbon\Carbon::parse($participant->birthday)->format('d-m-Y')}}</td>
        </tr>
        <tr>
             <td  style="border-bottom-color: #fff;border-right-color: #fff"></td>
            <td  style="border-bottom-color: #fff"></td>
            <td style="border-bottom-color: #fff;border-right-color: #fff">Umur</td>
            <td style="border-bottom-color: #fff">: {{ \Carbon\Carbon::parse($participant->birthday)->diff(\Carbon\Carbon::now())->format('%y tahun %m bulan  %d hari') }}</td>
        </tr>
        <tr>
             <td  style="border-bottom-color: #fff;border-right-color: #fff"></td>
            <td  style="border-bottom-color: #fff"></td>
            <td style="border-bottom-color: #fff;border-right-color: #fff">Perusahaan</td>
            <td style="border-bottom-color: #fff">: {{ $participant->client->name }}</td>
        </tr>
        <tr>
             <td  style="border-bottom-color: #fff;border-right-color: #fff"></td>
            <td  style="border-bottom-color: #fff"></td>
            <td style="border-bottom-color: #fff;border-right-color: #fff">NIK</td>
            <td style="border-bottom-color: #fff">: {{ $participant->nik }}</td>
        </tr>
        <tr>
             <td  style="border-bottom-color: #fff;border-right-color: #fff"></td>
            <td  style="border-bottom-color: #fff"></td>
            <td style="border-bottom-color: #fff;border-right-color: #fff">Divisi</td>
            <td style="border-bottom-color: #fff">: {{ $participant->divisi?->name}}</td>
        </tr>
        <tr>
             <td  style="border-bottom-color: #fff;border-right-color: #fff"></td>
            <td  style="border-bottom-color: #fff"></td>
            <td style="border-bottom-color: #fff;border-right-color: #fff">Departement</td>
            <td style="border-bottom-color: #fff">: {{ $participant->department?->name }}</td>
        </tr>
        <tr>
            <td  style=";border-right-color: #fff"></td>
            <td ></td>
            <td style="border-right-color: #fff">Paket MCU</td>
            <td>: {{ $participant->department?->packet_name }}</td>
        </tr>
    </table>
    <table>
        <tr style="text-align: center;border-color: #0C102A">
            <td colspan="4" class="footer" style="text-align: center;border-color: #0C102A"> {{ $participant->client?->name }}</td>
        </tr>
    </table>
</div>

</body>

</html>

{{--Page 2--}}
<html>
<head>
    <title>KESIMPULAN DAN SARAN</title>
    <style>
        .center-text {
            text-align: center;
            font-weight: normal;
            margin-top: 20px;
            font-size: 20px;
        }
        .main {
            border: 1px solid black;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        .status-fit {
            background-color: #e0e0e0;
            padding: 5px;
            border-bottom: 1px solid black;
            text-align: left;
        }
        .status-fit h1 {
            margin: 0;
            font-size: 15px;
        }
        .status {
            background-color: #a8d0d0;
            padding: 10px;
            text-align: center;
        }
        .status h2 {
            margin: 0;
            font-size: 20px;
        }
    </style>
</head>
<body>
@include('pages.report.header', $participant)
<div class="center-text">
    KESIMPULAN DAN SARAN
    <hr>
</div>
<table style="font-size: 12px" class="data-table">
    <tr>
        <td>
            <table>
                <tbody>
                <tr>
                    <td style="border: white">MCU ID</td>
                    <td style="border: white">:</td>
                    <td style="border: white">{{ $participant->code }}</td>
                </tr>
                <tr>
                    <td style="border: white">NIK</td>
                    <td style="border: white">:</td>
                    <td style="border: white">{{ $participant->nik }}</td>
                </tr>
                <tr>
                    <td style="border: white">Nama</td>
                    <td style="border: white">:</td>
                    <td style="border: white">{{ $participant->name }} [{{ $participant->gender }}]</td>
                </tr>
                <tr>
                    <td style="border: white">Tgl Lahir</td>
                    <td style="border: white">:</td>
                    <td style="border: white"> {{\Carbon\Carbon::parse($participant->birthday)->format('d-m-Y')}} /
                        {{ \Carbon\Carbon::parse($participant->birthday)->diff(\Carbon\Carbon::now())->format('%y tahun %m bulan  %d hari') }}
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
        <td>
            <table>
                <tbody>
                <tr>
                    <td  style="border: white">Perusahaan</td>
                    <td  style="border: white">:</td>
                    <td  style="border: white">{{ $participant->client?->name }}</td>
                </tr>
                <tr>
                    <td  style="border: white">Dept. ID</td>
                    <td  style="border: white">:</td>
                    <td  style="border: white">{{ $participant->department?->code }}</td>
                </tr>
                <tr>
                    <td style="border: white">Department</td>
                    <td style="border: white">:</td>
                    <td style="border: white">{{ $participant->department?->name }}</td>
                </tr>
                <tr>
                    <td style="border: white">Status</td>
                    <td style="border: white">:</td>
                    <td style="border: white">{{ $participant->status ?? "-" }}</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table >
<br>
<table class="data-table" style="font-size: 12px">
    <tr>
        <th colspan="2">PEMERIKSAAN</th>
        <th>HASIL</th>
    </tr>
    <tr>
        <td colspan="2">LABORATORIUM (TERLAMPIR)</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2">Hasil Laboratorium</td>

        <td>Normal</td>
    </tr>
    <tr>
        <td colspan="2">HASIL PHOTO THORAX (TERLAMPIR)</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2">Hasil dan Kesan</td>

        <td>Cor dan Pulmo tidak tampak kelainan radiologis</td>
    </tr>
    <tr>
        <td colspan="2">AUDIOMETRI (TERLAMPIR)</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2">Pendengaran Telinga Kanan</td>
        <td>TIDAK DIPERIKSA</td>
    </tr>
    <tr>
        <td colspan="2">Pendengaran Telinga Kiri</td>
        <td>TIDAK DIPERIKSA</td>
    </tr>
    <tr>
        <td colspan="2">EKG (TERLAMPIR)</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2">Kesimpulan EKG</td>
        <td>TIDAK DIPERIKSA</td>
    </tr>
    <tr>
        <td colspan="2">SPIROMETRI (TERLAMPIR)</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2">Kesimpulan Spiro</td>

        <td>TIDAK DIPERIKSA</td>
    </tr>
    <tr>
        <td colspan="2">HASIL MCU</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2">Saran</td>
        <td>
            <div class="suggestions">
                <div>- Olah raga teratur</div>
                <div>- Jaga pola makan</div>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td style="word-break: break-word; white-space: normal;">
            {!! str_replace(',', ',<br>', $participant->validateDoctor?->notes) !!}
        </td>
    </tr>
</table>

<div class="main">
    <div class="status-fit">
        <h1>FIT STATUS</h1>
    </div>
    <div class="status">
        <h2>FIT WITH JOB</h2>
    </div>
</div>
<table style="margin-top: 10px;">
    <tbody>
    <tr>
        <td style="width: 70%;"></td>
        <td style="text-align: center">
            <table>
                <tbody>
                <tr>
                    <td style="text-align: center" class="text-center">{{ config('app.city') }}, {{ now()->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td style="text-align: center" class="text-center">Dokter Pemeriksa,</td>
                </tr>
                <tr>
                    <td style="text-align: center" class="text-center">
                        <img src="{{ public_path($participant->pemeriksaanFisik->employee?->ttd ? $participant->pemeriksaanFisik->employee?->ttd : 'images/ttd-kosong.png') }}" width="100" alt="img" alt="img">
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center" class="text-center">{{ $participant->pemeriksaanFisik->employee?->nama }}</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>

