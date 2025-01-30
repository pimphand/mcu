@extends('layouts.main')

@section('title', 'Employee')

@section('css')
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header border-bottom p-1">
            <div class="head-label">
                <h6 class="mb-0">List Karyawan </h6>
            </div>
            <form action="{{ route('employee.index') }}" method="get">
                <div class="dt-action-buttons text-right">
                    <div class="dt-buttons d-inline-flex">
                        <div class="input-group input-group" style="margin-top: 5px;">
                            <input type="text" name="search" value="{{ request()->get('search') }}" id="sasSearchValue"
                                class="form-control float-right" placeholder="kode, nama employee">
                            <div class="input-group-append">
                                <button type="submit" id="sasSearch"
                                    class="btn btn-outline-facebook waves-effect waves-float waves-light">
                                    <i data-feather="search"></i>
                                    Search</button>
                            </div>
                            <a href="{{ route('employee.create') }}"
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
                            <th>Aksi</th>
                            <th>Kode</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>JK</th>
                            <th>Alamat</th>
                            <th>Tempat Lahir</th>
                            <th>Tgl Lahir</th>
                            <th>Agama</th>
                            <th>Darah</th>
                            <th>Rhesus</th>
                            <th>Pendidikan</th>
                            <th>Unit</th>
                            <th>Jabatan</th>
                            <th>Profesi</th>
                            <th>Profesi D.</th>
                            <th>WNi/WNA</th>
                            <th>Bank</th>
                            <th>No Rek.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $key => $item)
                            <tr>
                                <td>{{ $employees->firstItem() + $key }}</td>
                                <td>
                                    <a href="{{ route('employee.edit', $item->id) }}" class="btn btn-sm btn-outline-dark">
                                        <i data-feather='edit'></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger delete"
                                        data-url="{{ route('employee.delete', $item->id) }}">
                                        <i data-feather='trash'></i>
                                    </a>
                                </td>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->nik }}</td>
                                <td>{{ $item->nama }}</td>
                                <td>{{ $item->jenis_kelamin }}</td>
                                <td>{{ $item->alamat }}</td>
                                <td>{{ $item->tempat_lahir }}</td>
                                <td>{{ $item->tanggal_lahir }}</td>
                                <td>{{ $item->agama }}</td>
                                <td>{{ $item->golongan_darah }}</td>
                                <td>{{ $item->rhesus }}</td>
                                <td>{{ $item->pendidikan }}</td>
                                <td>{{ $item->unit }}</td>
                                <td>{{ $item->jabatan }}</td>
                                <td>{{ $item->profesi }}</td>
                                <td>{{ $item->profesi_detail }}</td>
                                <td>{{ $item->warga_negara }}</td>
                                <td>{{ $item->nama_bank }}</td>
                                <td>{{ $item->nomor_rekening }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {!! $employees->links('partials.pagination') !!}
        </div>
    </div>
    <!-- Basic Tabs ends -->

    {{-- modal delete --}}
    <form action="" id="form-delete" method="post">
        @csrf
        <div class="modal fade" id="modal-delete" tabindex="-1" aria-labelledby="modal-delete" aria-hidden="true">
            @method('delete')
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title h2">Konfirmasi!</h5>
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
        </div>
    </form>
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
