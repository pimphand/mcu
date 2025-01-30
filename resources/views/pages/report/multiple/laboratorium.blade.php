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
    <title>LABORATORIUM</title>
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

        .text-center {
            text-align: center;
        }


        td {
            padding: 3px;
        }

        .border {
            border: 1px solid black;
            border-collapse: collapse;
            width: 50%;
            padding: 3px;
            font-size: 12px;
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
                        <tbody style="font-size: 12px">
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
                        </tbody>
                    </table>
                </td>
                <td>
                    <table>
                        <tbody style="font-size: 12px">
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
                <th class="border">NILAI NORMAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3" class="border"><b>HEMATOLOGI</b></td>
            </tr>
            <tr>
                <td class="border">Hemoglobin</td>
                <td class="border text-center">
                    {{ $laboratorium->hemoglobin }}</td>
                <td class="border text-center">P: 12 - 16 , L: 13 – 17 g/dl</td>
            </tr>
            <tr>
                <td class="border">Hematokrit</td>
                <td class="border text-center">
                    {{ $laboratorium->hematokrit }}</td>
                <td class="border text-center">P: 35–47% , L: 40–52%</td>
            </tr>
            <tr>
                <td class="border">Lekosit</td>
                <td class="border text-center">
                    {{ $laboratorium->lekosit }}</td>
                <td class="border text-center">5.000 / µl – 10.000 / µ</td>
            </tr>
            <tr>
                <td class="border">Trombosit</td>
                <td class="border text-center">
                    {{ $laboratorium->trombosit }}</td>
                <td class="border text-center">150.000 – 450.000 / µl</td>
            </tr>
            <tr>
                <td class="border">Eritrosit</td>
                <td class="border text-center">
                    {{ $laboratorium->eritrosit }}</td>
                <td class="border text-center">P: 4,0 – 5,5 juta / µl , L: 4,5 – 6,0 juta / µl</td>
            </tr>
            <tr>
                <td class="border">Basofil</td>
                <td class="border text-center">
                    {{ $laboratorium->basofil }}</td>
                <td class="border text-center">0 – 1 %</td>
            </tr>
            <tr>
                <td class="border">Eosinofil</td>
                <td class="border text-center">
                    {{ $laboratorium->eosinofil }}</td>
                <td class="border text-center">1 – 3 %</td>
            </tr>
            <tr>
                <td class="border">Batang</td>
                <td class="border text-center">
                    {{ $laboratorium->batang }}</td>
                <td class="border text-center">2 – 6 %</td>
            </tr>
            <tr>
                <td class="border">Segmen</td>
                <td class="border text-center">
                    {{ $laboratorium->segmen }}</td>
                <td class="border text-center">50 – 70 %</td>
            </tr>
            <tr>
                <td class="border">Limfosit</td>
                <td class="border text-center">
                    {{ $laboratorium->limfosit }}</td>
                <td class="border text-center">20 – 40 %</td>
            </tr>
            <tr>
                <td class="border">Monosit</td>
                <td class="border text-center">
                    {{ $laboratorium->monosit }}</td>
                <td class="border text-center">2 – 6%</td>
            </tr>
            <tr>
                <td colspan="3" class="border"><b>IMMUNO SEROLOGI</b></td>
            </tr>
            <tr>
                <td colspan="3" class="border"><b>HEPATITIS</b></td>
            </tr>
            @if($laboratorium->sgot)
{{--            <tr>--}}
{{--                <td class="border">SGOT</td>--}}
{{--                <td class="border text-center">--}}
{{--                    {{ $laboratorium->sgot }}</td>--}}
{{--                <td class="border text-center">P: < 31 µl, L: < 34 µl</td>--}}
{{--            </tr>--}}
            @endif
            @if($laboratorium->sgpt)
            <tr>
                <td class="border">SGPT</td>
                <td class="border text-center">
                    {{ $laboratorium->sgpt }}</td>
                <td class="border text-center">0 - 45 µl</td>
            </tr>
            @endif
            @if($laboratorium->hbsag)
            <tr>
                <td class="border">HBsAg Kualitatif</td>
                <td class="border text-center">
                    {{ $laboratorium->hbsag }}</td>
                <td class="border text-center">Negatif</td>
            </tr>
            @endif
            @if($laboratorium->ureum)
{{--                <tr>--}}
{{--                    <td class="border">Ureum</td>--}}
{{--                    <td class="border text-center">--}}
{{--                        {{ $laboratorium->ureum }}</td>--}}
{{--                    <td class="border text-center">10 - 40 mg</td>--}}
{{--                </tr>--}}
            @endif
            @if($laboratorium->ureum)
            <tr>
                <td class="border">Creatinin</td>
                <td class="border text-center">
                    {{ $laboratorium->ureum }}</td>
                <td class="border text-center">P: < 0.6 - 1.2 mg/dL, L: < 0.6 - 1.4 mg/dL</td>
            </tr>
            @endif
            
            <tr>
                <td colspan="3" class="border"><b>URINE LENGKAP</b></td>
            </tr>
            <tr>
                <td class="border">Reduksi</td>
                <td class="border text-center">
                    {{ $laboratorium->reduksi }}</td>
                <td class="border text-center">Negatif</td>
            </tr>
            <tr>
                <td class="border">Berat Jenis</td>
                <td class="border text-center">
                    {{ $laboratorium->berat_jenis }}</td>
                <td class="border text-center"></td>
            </tr>
            <tr>
                <td class="border">PH / Reaksi</td>
                <td class="border text-center">
                    {{ $laboratorium->ph_reaksi }}</td>
                <td class="border text-center"></td>
            </tr>
            <tr>
                <td class="border">Warna</td>
                <td class="border text-center">
                    {{ $laboratorium->warna }}</td>
                <td class="border text-center"></td>
            </tr>
            <tr>
                <td class="border">Kekeruhan</td>
                <td class="border text-center">
                    {{ $laboratorium->kekeruhan }}</td>
                <td class="border text-center"></td>
            </tr>
            <tr>
                <td class="border">Urobilinogen</td>
                <td class="border text-center">
                    {{ $laboratorium->urobilinogen }}</td>
                <td class="border text-center">Normal</td>
            </tr>
            <tr>
                <td class="border">Bilirubin</td>
                <td class="border text-center">
                    {{ $laboratorium->bilirubin }}</td>
                <td class="border text-center">Negatif</td>
            </tr>
            <tr>
                <td class="border">Eritrosit</td>
                <td class="border text-center">
                    {{ $laboratorium->eritrosit_urine }}</td>
                <td class="border text-center">Negatif</td>
            </tr>
            <tr>
                <td class="border">Keton</td>
                <td class="border text-center">
                    {{ $laboratorium->keton }}</td>
                <td class="border text-center">Negatif</td>
            </tr>
            <tr>
                <td class="border">Protein</td>
                <td class="border text-center">
                    {{ $laboratorium->protein }}</td>
                <td class="border text-center">Negatif</td>
            </tr>
            <tr>
                <td class="border">Sedimen – Epitel </td>
                <td class="border text-center">
                    {{ $laboratorium->sedimen_epitel }}</td>
                <td class="border text-center">Negatif</td>
            </tr>
            <tr>
                <td class="border">Sedimen – Eritrosit </td>
                <td class="border text-center">
                    {{ $laboratorium->sedimen_eritrosit }}</td>
                <td class="border text-center">0 – 1 / Lpb</td>
            </tr>
            <tr>
                <td class="border">Sedimen – Leukosit </td>
                <td class="border text-center">
                    {{ $laboratorium->sedimen_leukosit }}</td>
                <td class="border text-center">0 – 5 / Lpb</td>
            </tr>

            <tr>
                <td class="border">Sedimen - Bakteri</td>
                <td class="border text-center">
                    {{ $laboratorium->sedimen_bakteri }}</td>
                <td class="border text-center">Negatif</td>
            </tr>

            <tr>
                <td class="border">Sedimen – Kristal </td>
                <td class="border text-center">
                    {{ $laboratorium->sedimen_kristal }}</td>
                <td class="border text-center">Negatif</td>
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
@endforeach
