@extends('layouts.main')

@section('title', 'Participant')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header">
        </div>
        <div class="card-body">
            <form action="{{ route('participant.print.mcu') }}" method="get">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label" for="fp-range">Tanggal</label>
                        <input type="text" name="date_range" id="fp-range" class="form-control flatpickr-range"
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
                        <select name="contract_id" id="contract_id" class="form-select">
                            <option value="">Pilih</option>
                            @foreach ($client->contracts as $item)
                                <option value="{{ $item->id }}"
                                    {{ session('contract_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="" class="form-label">Divisi</label>
                        <select name="divisi_id" id="divisi_id" class="form-select">
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
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/picker.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/picker.date.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/picker.time.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/legacy.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>
    @php
        $date1 = now()->subDay()->format('Y-m-d');
        $date2 = now()->format('Y-m-d');
        if (request()->get('date_range')) {
            $date1 = explode(' to ', request()->get('date_range'))[0];
            $date2 = explode(' to ', request()->get('date_range'))[1] ?? null;
        }
    @endphp
    <script>
        let rangePickr = $('.flatpickr-range').flatpickr({
            mode: 'range',
            maxDate: 'today',
            defaultDate: ["{{ $date1 }}", "{{ $date2 }}"]
        });
    </script>
@endsection
