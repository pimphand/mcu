@extends('layouts.main')

@section('title', 'Role')

@section('css')
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header border-bottom p-1">
            <div class="head-label">
                <h6 class="mb-0">List Role</h6>
            </div>
            <div class="dt-action-buttons text-right">
                <div class="dt-buttons d-inline-flex">
                    <div class="input-group input-group" style="margin-top: 5px;">
                        <input type="text" name="table_search" id="sasSearchValue" class="form-control float-right d-none"
                            placeholder="Search">
                        <div class="input-group-append d-none"><button type="submit" id="sasSearch"
                                class="btn btn-default waves-effect waves-float waves-light"><i class="fas fa-search"></i>
                                Search</button></div>
                        {{-- <a href="{{ route('role.create') }}"
                            class="btn btn-success float-right waves-effect waves-float waves-light">Tambah</a> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table mt-1">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Level Role</th>
                            <th>Nama Role</th>
                            <th>Hak Akses</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $key => $item)
                            <tr>
                                <td>{{ $roles->firstItem() + $key }}</td>
                                <td>{{ $item['level'] }}</td>
                                <td>{{ $item['name'] }}</td>
                                <td>
                                    <a href="{{ route('role.permission', $item['id']) }}"
                                        class="btn btn-sm btn-primary">Akses Menu</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {!! $roles->links('partials.pagination') !!}
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
