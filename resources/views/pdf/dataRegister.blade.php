<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Data Register</title>
    <style>
        body {
            font-family: sans-serif;
        }
        a {
            color: #000066;
            text-decoration: none;
        }
        table {
            border-collapse: collapse;
            border: solid;
            width: 100%;
        }
        thead {
            vertical-align: bottom;
            text-align: center;
            font-weight: bold;
        }
        tfoot {
            text-align: center;
            font-weight: bold;
        }
        th, td {
            text-align: left;
            padding-left: 0.20em;
            padding-right: 0.20em;
            padding-top: 0.20em;
            padding-bottom: 0.20em;
            vertical-align: top;
            border: 1px solid black; /* Border for table cells */
        }
        tbody tr:nth-child(odd) {
            background-color: #f2f2f2; /* Light grey background for odd rows */
        }
        tbody tr:nth-child(even) {
            background-color: #ffffff; /* White background for even rows */
        }
    </style>
</head>

<body>
<div style="align-content: center">
    Data Registrasi Peserta MCU
</div>
<table class="table mt-1 textkecil" id="table">
    <thead>
    <tr>
        <th>No.</th>
        <th>Tgl Register</th>
        <th>MCU ID</th>
        <th>NIK</th>
        <th>Nama Pasien</th>
        <th>Tgl Lahir</th>
        <th>JK</th>
        <th>Bagian/ Unit</th>
        <th>Perusahaan</th>
        <th>Gedung</th>
        <th>Paket MCU</th>
        <th>TTV</th>
        <th>Pemeriksaan Fisik</th>
        <th>Lab</th>
        <th>Rad</th>
        <th>Audiometri</th>
        <th>EKG</th>
        <th>Spiro</th>
        <th>Rectal</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $index => $item)
        <tr>
            <td>{{$index+1}}</td>
            <td>{{$item->register_date}}</td>
            <td>{{$item->code}}</td>
            <td>{{$item->nik}}</td>
            <td>{{$item->name}}</td>
            <td nowrap="">{{$item->birthday}}</td>
            <td>{{$item->gender}}</td>
            <td nowrap="">{{$item->divisi->name}} - {{$item->department->name}}</td>
            <td>{{$item->client->name}}</td>
            <td>{{$item->gedung}}</td>
            <td>{{$item->packet_name}}</td>
            <td>{{$item->tandaVital?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td>{{$item->pemeriksaanFisik?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td>{{$item->laboratorium?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td>{{$item->radiologi?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td>{{$item->audiometri?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td>{{$item->ekg?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td>{{$item->spirometri?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
            <td>{{$item->rectal?->selesai == 1 ? "SELESAI" : "TIDAK"}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>

</html>
