@extends('layouts.main')

@section('title', 'MCU In/Out')

@section('css')
    <link rel="stylesheet" href="{{ asset('app-assets/vendors/css/forms/select/select2.min.css') }}">
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header border-bottom p-1">
            <div class="head-label">
                <h6 class="mb-0">MCU IN/OUT </h6>
            </div>
        </div>
        <div class="card-body">
            <div class="row justify-content-center my-4">
                <div class="col-md-4">
                    @if (session('success'))
                        <h3 class="text-success">{{ session('success') }}</h3>
                    @endif
                    <form action="{{ session('mcu_in') ? route('mcu.out') : route('mcu.in') }}" method="post">
                        @csrf
                        <div class="form-group mb-2">
                            <label for="" class="form-label">Pilih Client</label>
                            <select name="client_id" id="client_id" class="form-select form-control" required>
                                <option value="">Pilih</option>
                                @foreach ($clients as $item)
                                    <option value="{{ $item->id }}"
                                        {{ session('client_id') == $item->id ? 'selected' : '' }}>{{ $item->code }} |
                                        {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label for="" class="form-label">Pilih Kontrak</label>
                            <select name="contract_id" id="contract_id" class="form-select form-control" required>
                                <option value="">Pilih</option>
                                @foreach ($contracts as $item)
                                    <option value="{{ $item->id }}"
                                        {{ $item->id == session('contract_id') ? 'selected' : '' }}>{{ $item->code }} |
                                        {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group text-center">
                            @if (session('mcu_in'))
                                <button type="submit" class="btn btn-danger">OUT</button>
                            @else
                                <button type="submit" class="btn btn-primary">IN</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Basic Tabs ends -->
@endsection

@section('js')
    <script src="{{ asset('app-assets/vendors/js/forms/select/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            // button delete
            $('table').on('click', '.delete', function() {
                const url = $(this).data('url');
                $("#form-delete").attr('action', url)
                $("#modal-delete").modal("show")
            })
            $('#client_id').select2({
                allowClear: true,
                placeholder: 'Pilih Client',
                width: '100%',
            });
            $('#contract_id').select2({
                allowClear: true,
                placeholder: 'Pilih Contract',
                width: '100%',
            });
            $("#client_id").on('change', function() {
                const clientId = $(this).val() || 0;
                $('#contract_id').select2({
                    allowClear: true,
                    placeholder: 'Pilih Contract',
                    width: '100%',
                    ajax: {
                        url: "{{ route('contract.select2') }}",
                        dataType: 'json',
                        delay: 350,
                        data: function(params) {
                            return {
                                search: params.term,
                                client_id: clientId
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: $.map(data.data, function(item) {
                                    return {
                                        text: `${item.name}`,
                                        id: item.id
                                    }
                                })
                            };
                        },
                        cache: true
                    }
                });
            });
        })
    </script>
@endsection
