@extends('layouts.main')

@section('title', 'Print MCU')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('content')
    <style>
        .table-responsive table.textkecil {
            font-size: 10px !important;
            /* Atur ukuran font kecil dan prioritaskan */
        }

        .table-responsive th.textkecil,
        .table-responsive td.textkecil {
            font-size: 10px !important;
            /* Pastikan ukuran font pada header dan sel tabel */
        }

        .table thead th {
            vertical-align: bottom;
        }

        .table th,
        .table td {
            padding: 0.5rem 0.75rem;
            vertical-align: top;
        }

        /* Tombol cetak per pemeriksaan dibuat rapat supaya kolomnya tidak melebar. */
        #table td.aksi {
            white-space: nowrap;
        }
    </style>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Print / Cetak MCU</h3>
            <span class="badge rounded-pill bg-light-secondary text-body">
                Kontrak: {{ $contract->name ?? 'Belum ada kontrak aktif' }}
            </span>
        </div>

        <div class="card-body">
            {{-- Filter dikirim lewat GET supaya hasil cetak bisa dibuka ulang dari URL. --}}
            <form action="{{ route('participant.print.mcu') }}" method="get">
                <div class="row  align-items-end">

                    {{-- Tanggal register peserta --}}
                    <div class="col-md-3">
                        <label class="form-label" for="fp-range">Tanggal Register</label>
                        <input type="text" name="filter[date_range]" id="fp-range"
                            class="form-control flatpickr-range" value="{{ $dateRange }}"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                    </div>

                    {{-- Client selalu mengikuti client pada sesi MCU IN. --}}
                    <div class="col-md-2">
                        <label for="client_id" class="form-label">Client</label>
                        <select name="client_id" id="client_id" class="form-select">
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        </select>
                    </div>

                    {{-- Kontrak: default kontrak yang sedang aktif di MCU IN. --}}
                    <div class="col-md-3" style="display:none">
                        <label for="contract_id" class="form-label">Kontrak</label>
                        <select name="filter[contract_id]" id="contract_id" class="form-select">
                            @foreach ($client->contracts as $item)
                                <option value="{{ $item->id }}" {{ (int) $contractId === (int) $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Divisi --}}
                    <div class="col-md-2">
                        <label for="divisi_id" class="form-label">Divisi</label>
                        <select name="filter[divisi_id]" id="divisi_id" class="form-select">
                            <option value="">Semua Divisi</option>
                            @foreach ($client->divisi as $item)
                                <option value="{{ $item->id }}" {{ (int) $divisiId === (int) $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol aksi --}}
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill" id="cari">

                                Cari
                            </button>
                            <a href="{{ route('participant.print.mcu') }}" class="btn btn-outline-secondary flex-fill" id="reset">
                                {{-- <i class="fas fa-undo me-1"></i> --}}
                                Reset
                            </a>
                        </div>
                    </div>

                </div>
            </form>

            <div class="row g-3 align-items-end ">
                <div class="col-md-2">
                    <label for="start" class="form-label">Dari No.</label>
                    <input type="number" min="1" id="start" class="form-control" value="1" placeholder="Dari No" />
                </div>
                <div class="col-md-2">
                    <label for="end" class="form-label">Sampai No.</label>
                    <input type="number" min="1" id="end" class="form-control" value="50" placeholder="Sampai No" />
                </div>
                <div class="col-md-8">
                    <div class="btn-group flex-wrap" role="group" aria-label="Cetak massal">
                        {{-- Stiker/Resume/Audiometri memakai filter peserta langsung,
                             tombol lain memakai filter lewat relasi participant.*. --}}
                        <button type="button" class="btn btn-sm btn-outline-info waves-effect import" data-scope="peserta"
                            data-url="{{ route('multiple.identitas') }}">
                            Stiker
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info waves-effect import" data-scope="peserta"
                            data-url="{{ route('multiple.resume') }}">
                            Resume
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary waves-effect import" data-scope="hasil"
                            data-url="{{ route('multiple.pemFisik') }}">
                            Pem. Fisik
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary waves-effect import" data-scope="hasil"
                            data-url="{{ route('multiple.laboratorium') }}">
                            Lab
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning waves-effect import" data-scope="hasil"
                            data-url="{{ route('multiple.radiologi') }}">
                            Rad
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning waves-effect import" data-scope="peserta"
                            data-url="{{ route('multiple.audiometri') }}">
                            Audiometri
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success waves-effect import" data-scope="hasil"
                            data-url="{{ route('multiple.ekg') }}">
                            EKG
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success waves-effect import" data-scope="hasil"
                            data-url="{{ route('multiple.spirometri') }}">
                            Spiro
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success waves-effect import" data-scope="hasil"
                            data-url="{{ route('multiple.rectal') }}">
                            Rectal
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive textkecil">
                <table class="dt-responsive table mt-1 textkecil" id="table">
                    <thead>
                        <tr style="text-align: center">
                            <th class="border-bottom textkecil border-top">No</th>
                            <th class="border-bottom textkecil border-top">Validasi Dokter</th>
                            <th class="border-bottom textkecil border-top">Tgl Register</th>
                            <th class="border-bottom textkecil border-top">MCU ID</th>
                            <th class="border-bottom textkecil border-top">NIK</th>
                            <th class="border-bottom textkecil border-top">Nama Pasien</th>
                            <th class="border-bottom textkecil border-top">Paket MCU</th>
                            <th class="border-bottom textkecil border-top">Stiker</th>
                            <th class="border-bottom textkecil border-top">Resume</th>
                            <th class="border-bottom textkecil border-top">Pem. Fisik</th>
                            <th class="border-bottom textkecil border-top">Lab</th>
                            <th class="border-bottom textkecil border-top">Rad</th>
                            <th class="border-bottom textkecil border-top">Audiometri</th>
                            <th class="border-bottom textkecil border-top">EKG</th>
                            <th class="border-bottom textkecil border-top">Spiro</th>
                            <th class="border-bottom textkecil border-top">Rectal</th>
                            <th class="border-bottom textkecil border-top">Gedung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $key => $item)
                            <tr>
                                <td class="border-bottom border-top text-right" nowrap>{{ $key + 1 }}</td>
                                <td class="border-bottom border-top text-center">
                                    @if ($item->validateDoctor)
                                        <span class="badge rounded-pill bg-light-success">VALID</span>
                                    @else
                                        <span class="badge rounded-pill bg-light-danger">BELUM</span>
                                    @endif
                                </td>
                                <td class="border-bottom border-top text-center" nowrap>
                                    {{ $item->register_date ? date('d-m-Y', strtotime($item->register_date)) : '-' }}
                                </td>
                                <td class="border-bottom border-top text-center" nowrap>{{ $item->code }}</td>
                                <td class="border-bottom border-top text-center" nowrap>{{ $item->nik }}</td>
                                <td class="border-bottom border-top text-left">
                                    {{ $item->name }} [{{ $item->gender }}]<br>{{ $item->birthday }}
                                </td>
                                <td class="border-bottom border-top text-left">{{ $item->plan_name ?? '-' }}</td>

                                {{-- Stiker selalu bisa dicetak karena datanya dari identitas peserta. --}}
                                <td class="border-bottom border-top text-center aksi">
                                    <button type="button" class="btn btn-sm btn-info btn-biru font-weight-medium btn-circle"
                                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Stiker"
                                        onclick="window.open('{{ route('report.identitas', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">
                                        <i class="fa fa-print" aria-hidden="true"></i>
                                    </button>
                                </td>

                                {{-- Resume dicetak setelah dokter memvalidasi hasilnya. --}}
                                <td class="border-bottom border-top text-center aksi">
                                    @if ($item->validateDoctor)
                                        <button type="button" class="btn btn-sm btn-info font-weight-medium btn-circle"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Resume"
                                            onclick="window.open('{{ route('report.resume', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">
                                            <i class="fa fa-print" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <span class="text-danger">TIDAK</span>
                                    @endif
                                </td>

                                <td class="border-bottom border-top text-center aksi">
                                    @if ($item->pemeriksaanFisik?->selesai)
                                        <button type="button" class="btn btn-sm btn-primary font-weight-medium btn-circle"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Pemeriksaan Fisik"
                                            onclick="window.open('{{ route('report.pemeriksaan.fisik', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">
                                            <i class="fa fa-print" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <span class="text-danger">TIDAK</span>
                                    @endif
                                </td>

                                <td class="border-bottom border-top text-center aksi">
                                    @if ($item->laboratorium?->selesai)
                                        <button type="button" class="btn btn-sm btn-primary font-weight-medium btn-circle"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Laboratorium"
                                            onclick="window.open('{{ route('report.laboratorium', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">
                                            <i class="fa fa-print" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <span class="text-danger">TIDAK</span>
                                    @endif
                                </td>

                                <td class="border-bottom border-top text-center aksi">
                                    @if ($item->radiologi?->selesai)
                                        <button type="button" class="btn btn-sm btn-warning font-weight-medium btn-circle"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Radiologi"
                                            onclick="window.open('{{ route('report.radiologi', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">
                                            <i class="fa fa-print" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <span class="text-danger">TIDAK</span>
                                    @endif
                                </td>

                                <td class="border-bottom border-top text-center aksi">
                                    @if ($item->audiometri?->selesai)
                                        <button type="button" class="btn btn-sm btn-warning font-weight-medium btn-circle"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Audiometri"
                                            onclick="window.open('{{ route('report.audiometri', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">
                                            <i class="fa fa-print" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <span class="text-danger">TIDAK</span>
                                    @endif
                                </td>

                                <td class="border-bottom border-top text-center aksi">
                                    @if ($item->ekg?->selesai)
                                        <button type="button" class="btn btn-sm btn-success font-weight-medium btn-circle"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print EKG"
                                            onclick="window.open('{{ route('report.ekg', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">
                                            <i class="fa fa-print" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <span class="text-danger">TIDAK</span>
                                    @endif
                                </td>

                                <td class="border-bottom border-top text-center aksi">
                                    @if ($item->spirometri?->selesai)
                                        <button type="button" class="btn btn-sm btn-success font-weight-medium btn-circle"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Spirometri"
                                            onclick="window.open('{{ route('report.spirometri', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">
                                            <i class="fa fa-print" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <span class="text-danger">TIDAK</span>
                                    @endif
                                </td>

                                <td class="border-bottom border-top text-center aksi">
                                    @if ($item->rectal?->selesai)
                                        <button type="button" class="btn btn-sm btn-success font-weight-medium btn-circle"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Rectal"
                                            onclick="window.open('{{ route('report.rectal', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">
                                            <i class="fa fa-print" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <span class="text-danger">TIDAK</span>
                                    @endif
                                </td>

                                {{-- Gedung belum punya kolomnya di tabel participants, jadi ditulis "-". --}}
                                <td class="border-bottom border-top text-center" nowrap>-</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="17" class="text-center">
                                    Tidak ada peserta pada kontrak, divisi, dan rentang tanggal yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/picker.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/picker.date.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/picker.time.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/legacy.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>

    <script>
        // Satu kali inisialisasi saja: input tanggal sudah berisi rentang yang
        // benar-benar dipakai server, jadi tidak ada tanggal "palsu" di layar.
        $('.flatpickr-range').flatpickr({
            mode: 'range',
            maxDate: 'today'
        });

        // Cetak massal: kirim rentang nomor beserta filter yang sedang aktif.
        $('.import').click(function() {
            let url = $(this).data('url');

            // Tombol tanpa tujuan cetak tidak melakukan apa pun.
            if (!url) {
                return;
            }

            // Endpoint hasil pemeriksaan memakai filter lewat relasi peserta
            // (participant.*), sedangkan data peserta memakai filter langsung.
            let scope = $(this).data('scope') === 'peserta' ? '' : 'participant.';

            let params = new URLSearchParams();
            params.set('start', $('#start').val() || 1);
            params.set('end', $('#end').val() || 50);
            params.set('client_id', $('#client_id').val());
            params.set('filter[' + scope + 'client_id]', $('#client_id').val());

            let contractId = $('#contract_id').val();
            let divisiId = $('#divisi_id').val();

            if (contractId) {
                params.set('filter[' + scope + 'contract_id]', contractId);
            }
            if (divisiId) {
                params.set('filter[' + scope + 'divisi_id]', divisiId);
            }

            params.set('filter[' + scope + 'date_range]', $('#fp-range').val());

            window.open(url + '?' + params.toString(), '',
                'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');
        });
    </script>

    <script>
        var pusher = new Pusher('23ac53d7303716c97001', {
            cluster: 'ap1'
        });

        var channel = pusher.subscribe('report-channel');
        channel.bind('pdf-generated', function(data) {
            Swal.fire({
                icon: 'success',
                title: 'Report generated',
                text: data.filename
            });
        });
    </script>
@endsection
