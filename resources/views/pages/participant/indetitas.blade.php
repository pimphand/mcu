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
            <div class="row">
                    <div class="col-md-3">
                        <label class="form-label" for="fp-range">Tanggal</label>
                        <input type="text" name="filter[date_range]" id="fp-range" class="form-control flatpickr-range flatpickr-input active" placeholder="YYYY-MM-DD to YYYY-MM-DD" readonly="readonly">
                    </div>
                    <div class="col-md-3">
                        <label for="" class="form-label text-white">cari</label>

                    <input type="text" id="start" class="form-sm form-control" value="1"
                            placeholder="Dari No" />
                    </div>
                    <div class="col-md-3">
                        <label for="" class="form-label text-white">cari</label>
                        <input type="text" id="end" class="form-sm form-control " value="50"
                                placeholder="Sampai No" />
                    </div>
                    <div class="col-md-1">
                        <label for="" class="form-label text-white">cari</label>
                        <button type="button" class="btn btn-primary waves-effect import" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-secondary" data-bs-original-title="Silahakan pilih tanggal" data-type="identitas" data-url="{{ route('multiple.identitas') }}">
                            Print
                        </button>
                    </div>
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
        $('#fp-range').change(function(){
            $('.import').attr('disabled', false);
        });

        $('.import').click(function(){
            let url = $(this).data('url');
            let start = $('#start').val();
            let end = $('#end').val();

            url = url + '?start=' + start + '&end=' + end+"filter[date_range]="+ $('#fp-range').val()+`&filter[client_id]={{ session('client_id') }}&filter[contract_id]={{ session('contract_id') }}`;
            
            window.open(url, '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');
        });
        $('#title_navbar').html('Stiker Identitas');
    </script>
@endsection