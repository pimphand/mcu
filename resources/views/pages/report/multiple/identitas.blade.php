@foreach($data as $index => $participant)
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
    <title>IDENTITAS</title>
    <style>
        @page {
            size: 150mm 100mm;
        }
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

        }
    </style>
</head>

<body>
    <table>
        <tbody>
            <tr>
                <td>
                    <img src="{{ public_path('logo.png') }}" width="60" alt="img" alt="img">
                </td>
                <td class="text-center">
                    <table>
                        <tbody>
                            <tr>
                                <td style="text-align: center;">
                                    <p style="padding: 0px; margin: 0px; font-size: 13px;">KLINIK DR. DINI <br>
		                                MEDICAL CENTER
                                    </p>
                                    <p style="padding: 0px; margin: 0px; font-size: 12px;">No.Izin : 0104220009994 <br>
Jln. Raya Karang Hilir no 815 Desa Karangtengah Kec. Cibadak
Kab.  Sukabumi (0266) 6545065</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center" style="font-size: 10px; color: rgb(157, 155, 155);">
                                    {{ $participant->client?->address }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    {{-- <img src="data:image/png;base64, {!! base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate($participant->code)) !!} "> --}}
                </td>
            </tr>
        </tbody>
    </table>
    <hr>

    <table>
       <tr>
                <td>
                    <table>
                        <tbody style="padding: 0px; margin: 0px; font-size: 11px;">
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
                            <tr>
                                <td>Status</td>
                                <td>:</td>
                                <td>{{ $participant->status }}</td>
                            </tr>
                            <tr>
                                <td>Department</td>
                                <td>:</td>
                                <td>{{ $participant->department?->name }}</td>
                            </tr>
                            <tr>
                                <td>Paket MCU</td>
                                <td>:</td>
                                <td>{{ $participant->packet_name }}</td>
                            </tr>
                            <tr>
                                <td>Tgl Register</td>
                                <td>:</td>
                                <td>{{ $participant->register_date }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <img src="data:image/png;base64, {!! base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->generate(route('login.peserta.token', $participant->code))) !!} ">
                </td>
            </tr>

    </table>

</body>

</html>

@endforeach
