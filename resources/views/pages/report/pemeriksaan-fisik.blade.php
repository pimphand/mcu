<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PEMERIKSAAN FISIK</title>
    <style>
        table {
            width: 100%;
        }
        @page {
            size: 21cm 33cm;
        }

        .text-center {
            text-align: center;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        td {

        }

        .border {
            border: 1px solid black;
            border-collapse: collapse;
            width: 50%;
            padding: 3px;
            font-size: 13px;
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
                        <tbody style="font-size:12px">
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
                        <tbody style="font-size:12px">
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
                        </tbody>
                    </table>
                </td>
            </tr>
        </body>
    </table>
    @php
        $tandaVital = $participant->tandaVital;
        $pemeriksaanFisik = $participant->pemeriksaanFisik;
    @endphp
    <table style="border-collapse: collapse;font-size:12px">
        <thead>
            <tr>
                <th class="border">PEMERIKSAAN</th>
                <th class="border">HASIL</th>
            </tr>
        </thead>
        <tbody style="font-size:12px">
            <tr>
                <td colspan="2" class="border">TANDA VITAL</td>
            </tr>
            <tr>
                <td class="border">Keluhan Utama</td>
                <td class="border text-center">
                    {{ $tandaVital->keluhan_utama_text }}</td>

            </tr>
            <tr>
                <td class="border">Riwayat Penyakit Sekarang </td>
                <td class="border text-center">
                    {{ $tandaVital->riwayat_penyakit_sekarang_text  }}</td>

            </tr>
            <tr>
                <td class="border">Riwayat Penyakit Terdahulu</td>
                <td class="border text-center">
                    {{ $tandaVital->riwayat_penyakit_terdahulu_text }}</td>

            </tr>
            <tr>
                <td class="border">Alergi</td>
                <td class="border text-center">
                    {{ $tandaVital->alergi_text }}</td>

            </tr>
            <tr>
                <td class="border">Merokok</td>
                <td class="border text-center">
                    {{ $tandaVital->merokok ? 'YA' : 'TIDAK' }}</td>

            </tr>
            <tr>
                <td class="border">Konsumsi Alkohol</td>
                <td class="border text-center">
                    {{ $tandaVital->komsumsi_alkohol ? 'YA' : 'TIDAK' }}</td>

            </tr>
            <tr>
                <td class="borde$pemeriksaanFisikr">Riwayat Trauma</td>
                <td class="border text-center">
                    {{ $tandaVital->riwayat_trauma_text }}</td>

            </tr>
            <tr>
                <td class="border">Tinggi Badan</td>
                <td class="border text-center">
                    {{ $tandaVital->tinggi_badan }} cm</td>

            </tr>
            <tr>
                <td class="border">Berat Badan</td>
                <td class="border text-center">
                    {{ $tandaVital->berat_badan }} kg</td>

            </tr>
            <tr>
                <td class="border">IMT</td>
                <td class="border text-center">
                    {{ $tandaVital->imt }}</td>

            </tr>
            <tr>
                <td class="border">Nilai IMT</td>
                <td class="border text-center">
                    {{ $tandaVital->imt_nilai }}</td>

            </tr>
            <tr>
                <td class="border">Tekanan Darah</td>
                <td class="border text-center">
                    {{ $tandaVital->tekanan_darah }} mmHg</td>

            </tr>
            <tr>
                <td class="border">Frekuensi Nadi</td>
                <td class="border text-center">
                    {{ $tandaVital->frekuensi_nadi }} x/menit</td>

            </tr>
            <tr>
                <td class="border">Suhu</td>
                <td class="border text-center">
                    {{ $tandaVital->suhu }} Celcius</td>

            </tr>
            <tr>
                <td class="border">Frekuensi Nafas</td>
                <td class="border text-center">
                    {{ $tandaVital->frekuensi_nafas }} x/menit</td>

            </tr>
            <tr>
                <td class="border">Ibu Hamil</td>
                <td class="border text-center">
                    {{ $tandaVital->ibu_hamil ? 'YA' : 'TIDAK' }}</td>

            </tr>
            <tr>
                <td class="border">Vaksin Hepatitis</td>
                <td class="border text-center">
                    {{ $tandaVital->vaksin_hepatitis ? 'YA' : 'TIDAK' }}</td>

            </tr>
            <tr>
                <td class="border">Vaksin Tetanus</td>
                <td class="border text-center">
                    {{ $tandaVital->vaksin_tetanus ? 'YA' : 'TIDAK' }}</td>

            </tr>
            <tr>
                <td colspan="2" class="border">KEADAAN UMUM</td>
            </tr>
            <tr>
                <td class="border">Keadaan Umum</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->keadaan_umum }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">KEPALA</td>
            </tr>
            <tr>
                <td class="border">Kepala</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->kepala_text }}</td>
            </tr>
            <tr>
                <td class="border">Hidung</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->hidung }}</td>
            </tr>
            <tr>
                <td class="border">Mata</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->mata }}</td>
            </tr>
            <tr>
                <td class="border">Pupil</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->pupil }}</td>
            </tr>
            <tr>
                <td class="border">Visus</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->visus }}</td>
            </tr>
            <tr>
                <td class="border">Buta Warna</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->buta_warna }}</td>
            </tr>
            <tr>
                <td class="border">Telinga</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->telinga }}</td>
            </tr>
            <tr>
                <td class="border">kelainan Telinga</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->kelainan_telinga }}</td>
            </tr>
            <tr>
                <td class="border">Gigi</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->gigi }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">MULUT</td>
            </tr>
            <tr>
                <td class="border">Bibir</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->bibir }}</td>
            </tr>
            <tr>
                <td class="border">Lidah</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->lidah }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">TENGGOROKAN</td>
            </tr>
            <tr>
                <td class="border">Tenggorokan</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->tenggorokan_text }}</td>
            </tr>
            <tr>
                <td class="border">Faring</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->faring }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">LEHER</td>
            </tr>
            <tr>
                <td class="border">Leher KGB</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->leher_kgb }}</td>
            </tr>
            <tr>
                <td class="border">Leher JVP</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->leher_jvp }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">THORAX JANTUNG</td>
            </tr>
            <tr>
                <td class="border">Inspeksi</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->jantung_inspeksi }}</td>
            </tr>
            <tr>
                <td class="border">Auskultasi</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->jantung_auskultasi }}</td>
            </tr>
            <tr>
                <td class="border">Palpasi</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->jantung_palpasi }}</td>
            </tr>
            <tr>
                <td class="border">Perkusi</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->jantung_perkusi }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">THORAX PARU-PARU</td>
            </tr>
            <tr>
                <td class="border">Inspeksi</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->paru_inspeksi }}</td>
            </tr>
            <tr>
                <td class="border">Auskultasi Vasikuler</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->paru_auskultasi_vasikuler }}</td>
            </tr>
            <tr>
                <td class="border">Auskultasi Ronkhi</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->paru_auskultasi_ronkhi }}</td>
            </tr>
            <tr>
                <td class="border">Auskultasi Wheezing</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->paru_auskultasi_wheezing }}</td>
            </tr>
            <tr>
                <td class="border">Palpasi</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->paru_palpasi }}</td>
            </tr>
            <tr>
                <td class="border">Perkusi</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->paru_perkusi }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">EXTREMITAS</td>
            </tr>
            <tr>
                <td class="border">Reflex Fisiologis Atas</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->reflex_fisiologis_atas }}</td>
            </tr>
            <tr>
                <td class="border">Reflex Phatologis Atas</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->reflex_phatologis_atas }}</td>
            </tr>
            <tr>
                <td class="border">Reflex Fisiologis Bawah</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->reflex_fisiologis_bawah }}</td>
            </tr>
            <tr>
                <td class="border">Reflex Phatologis Bawah</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->reflex_phatologis_bawah }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">LABORATORIUM (TERLAMPIR)</td>
            </tr>
            <tr>
                <td class="border">Hasil Laboratorium </td>
                <td class="border text-center">
                    {{ $participant->laboratorium?->kesimpulan }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">HASIL PHOTO THORAX (TERLAMPIR)</td>
            </tr>
            <tr>
                <td class="border">Hasil dan Kesan</td>
                <td class="border text-center">
                    {{ $participant->radiologi?->kesan }}</td>
            </tr>
            <tr>
                <td class="border">Neurologis</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->neurologis_text == 'BDN' ? 'DALAM BATAS NORMAL' : $pemeriksaanFisik->neurologis_text }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">HASIL AUDIOMETRI (TERLAMPIR)</td>
            </tr>
            <tr>
                <td class="border">Pendengaran Telinga Kanan </td>
                <td class="border text-center">
                    {{ $participant->audiometri->audiometri_telinga_kanan ?? "TIDAK DIPERIKSA" }}</td>
            </tr>
            <tr>
                <td class="border">Pendengaran Telinga Kiri </td>
                <td class="border text-center">
                    {{ $participant->audiometri->audiometri_telinga_kiri ?? "TIDAK DIPERIKSA" }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">EKG (TERLAMPIR)</td>
            </tr>
            <tr>
                <td class="border">Kesimpulan EKG </td>
                <td class="border text-center">
                    {{ $participant->ekg->kesimpulan ?? "TIDAK DIPERIKSA" }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">SPIROMETRI (TERLAMPIR)</td>
            </tr>
            <tr>
                <td class="border">Kesimpulan Spiro </td>
                <td class="border text-center">
                    {{ $participant->spirometri->hasil ?? "TIDAK DIPERIKSA" }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">RECTAL SWAB (TERLAMPIR)</td>
            </tr>
            <tr>
                <td class="border">Salmonella Thypi</td>
                <td class="border text-center">
                    {{ $participant->rectal->salmonella_thypi ?? "TIDAK DIPERIKSA"  }}</td>
            </tr>
            <tr>
                <td class="border">Shigella SP</td>
                <td class="border text-center">
                    {{ $participant->rectal->shigella_sp ?? "TIDAK DIPERIKSA" }}</td>
            </tr>
            <tr>
                <td class="border">E. Coli Pathogen</td>
                <td class="border text-center">
                    {{ $participant->rectal->e_coli_pathogen ?? "TIDAK DIPERIKSA"  }}</td>
            </tr>
            <tr>
                <td colspan="2" class="border">HASIL MCU</td>
            </tr>
            <tr>
                <td class="border">Kesimpulan</td>
                <td class="border text-center">
                    {{ $pemeriksaanFisik->kesimpulan ?? "TIDAK DIPERIKSA"  }}</td>
            </tr>
            <tr>
                <td class="border">Saran</td>
                <td class="border text-center">{!! $pemeriksaanFisik->saran !!}</td>
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
                                    <br>

                                    <img src="{{ asset($pemeriksaanFisik->employee?->ttd) }}" width="150"
                                        alt="img" alt="img">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">{{ $pemeriksaanFisik->employee?->nama }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
