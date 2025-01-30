@extends('layouts/contentLayoutMaster')

@section('title', 'Menu')

@section('css')
    {{-- vendor css files --}}
    <link rel="stylesheet" href="{{ asset('vendors/css/forms/select/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/css/tables/datatable/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/css/tables/datatable/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header border-bottom p-1">
            <div class="dt-action-buttons text-right">
                <div class="dt-buttons d-inline-flex">
                    <div class="input-group input-group" style="margin-top: 5px;">
                        <input type="text" name="table_search" id="sasSearchValue"
                            class="form-control float-right d-none" placeholder="Search">
                        <div class="input-group-append d-none"><button type="submit" id="sasSearch"
                                class="btn btn-default waves-effect waves-float waves-light"><i class="fas fa-search"></i>
                                Search</button></div>
                        {{-- <a href="{{ route('menu.create') }}"
                            class="btn btn-success float-right waves-effect waves-float waves-light">Tambah</a> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Induk</th>
                            <th>Nama</th>
                            <th>URL</th>
                            <th>Icon</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            {{-- <th>Aksi</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $no = App\Helpers\Helpers::getIncrementNumberFromPagination($result);
                        @endphp
                        @foreach ($menus as $key => $item)
                            <tr>
                                <td>{{ $no++ }}</td>
                                {{-- <td>{{ $item['parent_id'] }}</td> --}}
                                <td>{{ $item['parent'] }}</td>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['url'] }}</td>
                                <td>{{ $item['icon'] }}</td>
                                <td>{{ $item['sort_order'] }}</td>
                                <td>{{ $item['is_active'] == 1 ? 'Aktif' : 'Non Aktif' }}</td>
                                {{-- <td>
                                    <a href="{{ route('menu.edit', $item['id']) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <button data-url="{{ route('menu.destroy', $item['id']) }}"
                                        class="btn btn-sm btn-danger delete">Hapus</button>
                                </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            @include('pages.pagination.index', ['result' => $result, 'route' => 'menu.index'])
        </div>
    </div>
    <!-- Basic Tabs ends -->

    {{-- modal delete --}}
    <div class="modal fade" id="modal-delete" tabindex="-1" aria-labelledby="modal-delete" aria-hidden="true">
        <form action="" id="form-delete" method="post">
            @csrf
            @method('delete')
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body row gap-1 py-2">
                        <p>Yakin ingin hapus data ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection


@section('vendor-script')
    {{-- vendor files --}}
    <script src="{{ asset('vendors/js/tables/datatable/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendors/js/tables/datatable/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('vendors/js/tables/datatable/responsive.bootstrap5.min.js') }}"></script>
@endsection
@section('page-script')
    {{-- Page js files --}}
    <script>
        $(function(){
            // button delete
            $('table').on('click', '.delete', function() {
                const url = $(this).data('url');
                $("#form-delete").attr('action', url)
                $("#modal-delete").modal("show")
            })

        })
    </script>
@endsection
