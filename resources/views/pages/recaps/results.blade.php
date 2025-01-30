@extends('layouts.main')

@section('title', 'Divisi')

@section('css')
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header border-bottom p-1">
            <div class="head-label">
                <h6 class="mb-0">List Divisi </h6>
            </div>
            <form action="{{ route('divisi.index') }}" method="get">
                <div class="dt-action-buttons text-right">
                    <div class="dt-buttons d-inline-flex">
                        <div class="input-group input-group" style="margin-top: 5px;">
                            <input type="text" name="search" value="{{ request()->get('search') }}" id="sasSearchValue"
                                class="form-control float-right" placeholder="kode, nama divisi">
                            <div class="input-group-append">
                                <button type="submit" id="sasSearch"
                                    class="btn btn-outline-facebook waves-effect waves-float waves-light">
                                    <i data-feather="search"></i>
                                    Search</button>
                            </div>
                            <a href="{{ route('divisi.create') }}"
                                class="btn btn-primary float-right waves-effect waves-float waves-light">Tambah</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="dt-responsive table mt-1" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {!! $divisis->links('partials.pagination') !!}
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function() {
            // button delete
            $('table').on('click', '.delete', function() {
                const url = $(this).data('url');
                $("#form-delete").attr('action', url)
                $("#modal-delete").modal("show")
            })

        })
    </script>
@endsection
