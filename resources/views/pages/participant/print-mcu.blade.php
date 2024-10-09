@extends('layouts.main')

@section('title', 'Participant')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('content')
    <style>
        .table-responsive table.textkecil {
            font-size: 10px !important; /* Atur ukuran font kecil dan prioritaskan */
        }

        .table-responsive th.textkecil, .table-responsive td.textkecil {
            font-size: 10px !important; /* Pastikan ukuran font pada header dan sel tabel */
        }

        .table thead th {
            vertical-align: bottom;
        }

        .table th, .table td {
            padding: 0.75rem;
            vertical-align: top
        }
    </style>
    <!-- Basic Tabs starts -->
    <div class="card">

        <div class="card-body">
            <form action="{{ route('participant.print.mcu') }}" method="get">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label" for="fp-range">Tanggal</label>
                        <input type="text" name="filter[date_range]" id="fp-range" class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                    </div>
                    <div class="col-md-3">
                        <label for="" class="form-label">Client</label>
                        <select name="client_id" id="client_id" class="form-select">
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="" class="form-label">Kontrak</label>
                        <select name="filter[contract_id]" id="contract_id" class="form-select">
                            <option value="">Pilih</option>
                            @foreach ($client->contracts as $item)
                                <option value="{{ $item->id }}"
                                    {{ session('contract_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="" class="form-label">Divisi</label>
                        <select name="filter[divisi_id]" id="divisi_id" class="form-select">
                            <option value="">Pilih</option>
                            @foreach ($client->divisi as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label for="" class="form-label text-white">cari</label>
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                </div>
            </form>

            <div class="row mt-2">
                <div class="col-md-3">
                    <input type="text" id="start" class="form-sm form-control" value="1"
                            placeholder="Dari No" />
                </div>
                <div class="col-md-3">
                    <input type="text" id="end" class="form-sm form-control " value="50"
                            placeholder="Sampai No" />
                </div>
                <div class="col-md-6">
                <div class="btn-group" role="group" aria-label="First group">
                    <button type="button" class="btn btn-sm btn-outline-info waves-effect import" data-type="identitas" data-url="{{ route('multiple.identitas') }}">
                     Stiker
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info waves-effect import">
                     Resume
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary waves-effect import"  data-url="{{ route('multiple.pemFisik') }}">
                      Pem. Fisik
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary waves-effect import">
                     Lab
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning waves-effect import" data-url="{{ route('multiple.radiologi') }}">
                     Rad
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning waves-effect import">
                     Audiometri
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success waves-effect import" data-url="{{ route('multiple.ekg') }}">
                     EKG
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success waves-effect import" data-url="{{ route('multiple.spirometri') }}">
                     Spiro
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success waves-effect import" data-url="{{ route('multiple.rectal') }}">
                     Rectal
                    </button>
                  </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive table-responsive small textkecil">
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
                        <th class="border-bottom textkecil border-top hilang">Stiker</th>
                        <th class="border-bottom textkecil border-top hilang">Resume</th>
                        <th class="border-bottom textkecil border-top hilang">Pem. Fisik</th>
                        <th class="border-bottom textkecil border-top hilang">Lab</th>
                        <th class="border-bottom textkecil border-top hilang">Rad</th>
                        <th class="border-bottom textkecil border-top hilang">Audiometri</th>
                        <th class="border-bottom textkecil border-top hilang">EKG</th>
                        <th class="border-bottom textkecil border-top hilang">Spiro</th>
                        <th class="border-bottom textkecil border-top hilang">Rectal</th>
                        <th class="border-bottom textkecil border-top">Gedung</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data as $key => $item)
                        <tr>
                            <td class="border-bottom border-top text-right" nowrap="">{{$key+1}}</td>
                            <td class="border-bottom border-top text-right">
                                BELUM
                            </td>
                            <td class="border-bottom border-top text-center">{{$item->register_date}}</td>
                            <td class="border-bottom border-top text-center">
                                {{$item->code}}
                            </td>
                            <td class="border-bottom border-top text-center">{{$item->nik}}</td>
                            <td class="border-bottom border-top text-left">{{$item->name}} [{{$item->gender}}]<br>{{$item->birthday}}</td>
                            <td class="border-bottom border-top text-left">{{$item->plan_name ?? "-"}}</td>
                            <td class="border-bottom border-top text-center">
                            <a href="#" class="text-center btn btn-sm btn-info btn-biru font-weight-medium btn-circle printStiker" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Stiker"
                            onclick="window.open('{{ route('report.identitas', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">
                            <i class="fa fa-print" aria-hidden="true"></i>
                            </a>

                            </td>
                            <td class="border-bottom border-top text-center hilang">
                            onproses
                                {{-- @if($item->tandaVital->selesai)
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Tanda Vital"
                                    class="text-center btn btn-sm btn-info font-weight-medium btn-circle printFisik" onclick="window.open('{{ route('report.tanda.vital', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');"> <i class="fa fa-print" aria-hidden="true"></i></button>
                               @else
                                    <span class="text-center text-danger">TIDAK</span>
                               @endif --}}
                            </td>
                            <td class="border-bottom border-top text-center hilang">
                               @if($item->pemeriksaanFisik->selesai)
                                <button type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Pemeriksaan Fisik"
                                class="text-center btn btn-sm btn-primary font-weight-medium btn-circle printFisik" onclick="window.open('{{ route('report.pemeriksaan.fisik', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');"> <i class="fa fa-print" aria-hidden="true"></i></button>
                               @else
                                <span class="text-center text-danger">TIDAK</span>
                               @endif
                            </td>
                            <td class="border-bottom border-top text-left hilang">
                                @if($item->laboratorium->selesai)
                                <button type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Laboratorium"
                                class="text-center btn btn-sm btn-primary font-weight-medium btn-circle laboratorium" onclick="window.open('{{ route('report.laboratorium', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');"> <i class="fa fa-print" aria-hidden="true"></i></button>
                               @else
                                <span class="text-center text-danger">TIDAK</span>
                               @endif
                            </td>
                            <td class="border-bottom border-top text-left hilang">
                                @if($item->radiologi->selesai)
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Radiologi"
                                    class="text-center btn btn-sm btn-warning font-weight-medium btn-circle printRad" onclick="window.open('{{ route('report.radiologi', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');"> <i class="fa fa-print" aria-hidden="true"></i></button>
                               @else
                                    <span class="text-center text-danger">TIDAK</span>
                               @endif
                            </td>
                            <td class="border-bottom border-top text-left hilang">
                                @if($item->audiometri->selesai)
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print audiometri"
                                    class="text-center btn btn-sm btn-warning font-weight-medium btn-circle printRad" onclick="window.open('{{ route('report.audiometri', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');"> <i class="fa fa-print" aria-hidden="true"></i></button>
                                @else
                                    <span class="text-center text-danger">TIDAK</span>
                                @endif
                            </td>
                            <td class="border-bottom border-top text-left hilang">
                                @if($item->ekg->selesai)
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print EKG"
                                    class="text-center btn btn-sm btn-success font-weight-medium btn-circle printEkg" onclick="window.open('{{ route('report.ekg', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');"> <i class="fa fa-print" aria-hidden="true"></i></button>
                                @else
                                    <span class="text-center text-danger">TIDAK</span>
                                @endif
                            </td>
                            <td class="border-bottom border-top text-left hilang">
                                @if($item->spirometri->selesai)
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Spirometri"
                                    class="text-center btn btn-sm btn-success font-weight-medium btn-circle printEkg" onclick="window.open('{{ route('report.spirometri', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');"> <i class="fa fa-print" aria-hidden="true"></i></button>
                                @else
                                    <span class="text-center text-danger">TIDAK</span>
                                @endif
                            </td>
                            <td class="border-bottom border-top text-left hilang">
                                @if($item->rectal->selesai)
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Print Rectal"
                                    class="text-center btn btn-sm btn-success font-weight-medium btn-circle printEkg" onclick="window.open('{{ route('report.rectal', $item->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');"> <i class="fa fa-print" aria-hidden="true"></i></button>
                                @else
                                    <span class="text-center text-danger">TIDAK</span>
                                @endif
                            </td>
                            <td class="border-bottom border-top text-center" nowrap=""></td>
                        </tr>
                    @endforeach

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
        let rangePickr = $('.flatpickr-range').flatpickr({
            mode: 'range',
            maxDate: 'today',

        });

        $('.import').click(function(){
            let url = $(this).data('url');
            let start = $('#start').val();
            let end = $('#end').val();

            if($(this).data('type') == "identitas"){
                 url = url + '?start=' + start + '&end=' + end+"filter[date_range]="+ $('#fp-range').val()+`&filter[client_id]=${$('#client_id').val()}`;
            }else{
                url = url + '?start=' + start + '&end=' + end+"filter[date_range]="+ $('#fp-range').val()+`&filter[participant.client_id]=${$('#client_id').val()}`;
            }

            window.open(url, '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');
        });
        $(document).ready(function() {
                // Ambil parameter dari URL
                const urlParams = new URLSearchParams(window.location.search);
                const dateRange = urlParams.get('filter[date_range]');
                
                let date1, date2;

                if (dateRange) {
                    // Jika date_range ada di URL, pecah menjadi dua tanggal
                    [date1, date2] = dateRange.split(' to ');
                } else {
                    // Set tanggal default: dua hari lalu hingga hari ini
                    date1 = moment().subtract(2, 'days').format('YYYY-MM-DD');
                    date2 = moment().format('YYYY-MM-DD');
                }

                // Inisialisasi Flatpickr dengan tanggal default
                let rangePickr = $('.flatpickr-range').flatpickr({
                    mode: 'range',
                    maxDate: 'today',
                    defaultDate: [date1, date2] // Set tanggal default
                });
            });
    </script>
@endsection
