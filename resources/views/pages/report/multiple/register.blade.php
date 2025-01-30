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
                    <img src="{{ asset('logo.png') }}" alt="Logo Perusahaan" width="60" height="">
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
            <td style="text-align: left;">: {{ $participant->birthday ?? "-" }}</td>
            <td style="text-align: left;">NAMA DEPT</td>
            <td style="text-align: left;">: {{ $participant->department?->name }}</td>
        </tr>
        <tr>
            <td style="text-align: left;">USIA</td>
            <td style="text-align: left;">: {{ getAgeInYearsMonthsDays($participant->birthday) }}</td>
            <td style="text-align: left;">TGL REGISTRASI</td>
            <td style="text-align: left;">: {{ $participant->register_date }}</td>
        </tr>
    </table>
    <table>
        <td><img src="{{ asset('register-transformed.jpg') }}" width="100%" alt=""></td>
    </table>
    Paket MCU:
    <table style="width: 100%; border-collapse: collapse; font-size:10px; border: 1px solid black;">
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;">
                {{ $participant->tandaVital?->selesai ? 'SELESAI' : 'BELUM' }}
                <br>
                Tanda Vital
            </td>
            <td style="border: 1px solid black;">
                {{ $participant->pemeriksaanFisik?->selesai ? 'SELESAI'
                : 'BELUM' }} <br>
                Pemeriksaan Fisik
                </td>
            <td style="border: 1px solid black;">
                {{ $participant->pemeriksaanFisik?->selesai ? 'SELESAI'
                : 'BELUM' }} <br>
               Visus
                </td>
            <td style="border: 1px solid black;">
                {{ $participant->laboratorium?->selesai ? 'SELESAI' : 'BELUM' }} <br> Laboratorium
            </td>
            <td style="border: 1px solid black;">
                {{ $participant->radiologi?->selesai ? 'SELESAI' :
                'BELUM' }} <br>
                Radiologi
            </td>
            {{-- <td style="border: 1px solid black;">{{ $participant->audiometri?->selesai ? 'SELESAI' : 'BELUM' }}
                <br>
                Audiometri
            </td>
            <td style="border: 1px solid black;">{{ $participant->spirometri?->selesai ? 'SELESAI' : 'BELUM' }}
                <br>Spiro
            </td>
            <td style="border: 1px solid black;">{{ $participant->rectal?->selesai ? 'SELESAI' : 'BELUM' }} <br>
                Rectal
            </td>
            <td style="border: 1px solid black;">
                {{ $participant->ekg?->selesai ? 'SELESAI' : 'BELUM'}} <br>
                EKG
            </td>
            --}}
            <td style="border: 1px solid black;">Konsultasi Ya/Tidak</td>
             <td style="text-align: center;border: 1px solid black;">
                Tanda Tangan Peserta
                <br>
                <br>
                <br>
                <br>
                {{ $participant->name }}
            </td>
        </tr>
    </table>
</body>

</html>
