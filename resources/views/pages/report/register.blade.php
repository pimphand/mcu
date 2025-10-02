<html lang="en">
@php
function getAgeInYearsMonthsDays($birthDate)
{
    // Mengubah string tanggal lahir ke objek Carbon
    $birthDate = Carbon\Carbon::parse($birthDate);
    $currentDate = Carbon\Carbon::now();

    // Hitung selisih dalam tahun, bulan, dan hari
    $diffYears = $birthDate->diffInYears($currentDate);
    $diffMonths = $birthDate->copy()->addYears($diffYears)->diffInMonths($currentDate);
    $diffDays = $birthDate->copy()->addYears($diffYears)->addMonths($diffMonths)->diffInDays($currentDate);

    // Bulatkan hasil
    $roundedYears = floor($diffYears);
    $roundedMonths = floor($diffMonths);
    $roundedDays = floor($diffDays);

    // Format hasilnya
return "{$roundedYears} Tahun {$roundedMonths} Bulan {$roundedDays} Hari";
}

$map = [
    'plan_u' => 'U',
    'plan_a' => 'A',
    'plan_e' => 'E',
    'plan_s' => 'S',
    'plan_r' => 'R',
];


$result = collect($participant->toArray())
    ->filter(fn($val, $key) => $val == 1 && isset($map[$key])) // hanya key yang ada di $map dan bernilai 1
    ->keys()
    ->map(fn($key) => $map[$key])
    ->implode(' + ');

echo $result;

@endphp

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convert to PDF</title>
    <style>
        @page {
            size: 21cm 33cm;
        }
    </style>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

        }

        table {
            width: 100%;
            margin: 0;
            /* Pastikan tabel tidak memiliki margin */
            padding: 0;
            /* Pastikan tabel tidak memiliki padding */
            border-collapse: collapse;
            /* Menggabungkan border agar lebih rapat */
        }

        td {}

        .qr-code {
            padding: 0;
            /* Menghilangkan padding pada div QR Code */
            margin: 0;
            /* Menghilangkan margin pada div QR Code */
        }
    </style>
</head>

<body>
    <table style="text-align: center">
        <tr>
            <td>
                <div class="qr-code">
                    <img src="{{ public_path('logo.png') }}" alt="Logo Perusahaan" width="60" height="">
                </div>
            </td>
            <td style="text-align: center">
                <span style="font-size:10px">NO REGISTER</span>
                <br>
                <span style="font-size:17px">{{ $participant->register_number }}</span>
            </td>
            <td style="text-align: center">
                <span style="font-size:10px">MCU ID</span>
                <br>
                <span style="font-size:17px">{{ $participant->code }}</span>
            </td>
            <td>
                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data={{ $participant->code }}"
                        alt="QR Code" width="60" height="60">
                </div>
            </td>
        </tr>
    </table>
    <br>
    <table style="width: 100%; border-collapse: collapse; font-size:10px">
        <tr>
            <td style="text-align: left; width: 20%;">NAMA</td>
            <td style="text-align: left; width: 30%;">: {{ $participant->name }} [{{ $participant->gender }}]</td>
            <td style="text-align: left; width: 20%;">DIVISI</td>
            <td style="text-align: left; width: 30%;">: {{ $participant->divisi->name ?? "-" }}</td>
        </tr>
        <tr>
            <td style="text-align: left;">NIK</td>
            <td style="text-align: left;">: {{ $participant->nik }}</td>
            <td style="text-align: left;">ID DEPT</td>
            <td style="text-align: left;">: -</td>
        </tr>
        <tr>
            <td style="text-align: left;">TGL LAHIR</td>
            <td style="text-align: left;">: {{ date('d-m-Y', strtotime($participant->birthday)) ?? "-" }}</td>
            <td style="text-align: left;">NAMA DEPT</td>
            <td style="text-align: left;">: {{ $participant->department?->name }}</td>
        </tr>
        <tr>
            <td style="text-align: left;">USIA</td>
            <td style="text-align: left;">: {{ getAgeInYearsMonthsDays($participant->birthday) }}</td>
            <td style="text-align: left;">TGL REGISTRASI</td>
            <td style="text-align: left;">: {{ date('d-m-Y', strtotime($participant->register_date)) }}</td>
        </tr>
    </table>
    <table>
        <td><img src="{{ public_path('register-transformed.jpg') }}" width="100%" alt=""></td>
    </table>
    Paket MCU: 
        {{ collect([
            'U' => $participant->plan_u,
            'A' => $participant->plan_a,
            'E' => $participant->plan_e,
            'S' => $participant->plan_s,
            'R' => $participant->plan_r,
        ])->filter()->keys()->implode(' + ') }}
        <table style="width: 100%; border-collapse: collapse; font-size:10px; border: 1px solid black; text-align:center; table-layout: fixed;">
            <tr style="border: 1px solid black;">
                <td style="border: 1px solid black; vertical-align: middle; width:16.66%;">
                    {{ $participant->tandaVital?->selesai ? 'SELESAI' : '' }}
                    <br>
                    Tanda Vital
                    <br><br>                    <br>
                </td>
                <td style="border: 1px solid black; vertical-align: middle; width:16.66%;">
                    {{ $participant->pemeriksaanFisik?->selesai ? 'SELESAI' : '' }} 
                    <br>Pemeriksaan Fisik
                    <br><br>                    <br>
                </td>
                <td style="border: 1px solid black; vertical-align: middle; width:16.66%;">
                    {{ $participant->pemeriksaanFisik?->selesai ? 'SELESAI' : '' }} 
                    <br>Visus
                    <br><br>                    <br>
                </td>
                <td style="border: 1px solid black; vertical-align: middle; width:16.66%;">
                    {{ $participant->laboratorium?->selesai ? 'SELESAI' : '' }} 
                    <br>Laboratorium
                    <br><br>                    <br>
                </td>
                <td style="border: 1px solid black; vertical-align: middle; width:16.66%;">
                    {{ $participant->radiologi?->selesai ? 'SELESAI' : '' }} 
                    <br>Radiologi
                    <br><br>                    <br>
                </td>
                @if ($participant->plan_r)
                    <td style="border: 1px solid black; vertical-align: middle; width:16.66%;">
                        {{ $participant->rectal?->selesai ? 'SELESAI' : '' }} 
                        <br>Rectal
                        <br><br>                    <br>
                    </td>
                @endif
                <td style="border: 1px solid black; vertical-align: middle; width:16.66%;">
                    <br><br>
                    Konsultasi Ya/Tidak
                    <br><br>                    <br>
                </td>
            </tr>
        </table>


</body>

</html>
