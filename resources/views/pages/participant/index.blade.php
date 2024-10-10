@extends('layouts.main')

@section('title', 'Participant')

@section('css')
<link rel="stylesheet" href="{{ asset('app-assets/vendors/css/forms/select/select2.min.css') }}">
@endsection

@section('content')
<!-- Basic Tabs starts -->
<div class="card">
    <div class="card-header border-bottom p-1">
        <div class="head-label">
            <button type="button" data-url="{{ route('participant.filter', request()->all()) }}"
                class="btn btn-outline-primary" id="filter">
                <i data-feather='search'></i> Filter
            </button>
            <a href="{{ route('participant.index') }}" class="btn btn-outline-info">
                <i data-feather='refresh-cw'></i> Reset Filter
            </a>
            @if ($isRegisterPage)
            <a href="#" class="btn btn-outline-warning" id="scan-barcode">
                <i data-feather='maximize'></i> Scan Barcode
            </a>
            @endif
        </div>
        <form action="{{ route('participant.index') }}" method="get">
            <div class="dt-action-buttons text-right">
                <div class="dt-buttons d-inline-flex">
                    <div class="input-group input-group" style="margin-top: 5px;">
                        <input type="text" name="search" value="{{ request()->get('search') }}" id="sasSearchValue"
                            class="form-control float-right" placeholder="kode, nama participant">
                        <div class="input-group-append">
                            <button type="submit" id="sasSearch"
                                class="btn btn-outline-facebook waves-effect waves-float waves-light">
                                <i data-feather="search"></i>
                                Search</button>
                        </div>
                        @if ($isRegisterPage)
                        <a href="#" class="btn btn-primary float-right waves-effect waves-float waves-light"
                            id="btn-register">Tambah Register</a>
                        @else
                        <a href="#" class="btn btn-primary float-right waves-effect waves-float waves-light"
                            id="btn-create">Tambah Data</a>
                        <a href="#" class="btn btn-success float-right waves-effect waves-float waves-light"
                            id="btn-import">Import Data</a>
                        @endif
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
                        <th>Aksi</th>
                        @if ($isRegisterPage)
                        <th>Tanggal Register</th>
                        <th>No. Register</th>
                        @endif
                        <th>MCU ID</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>JK</th>
                        <th>Tgl. Lahir</th>
                        <th>Divisi</th>
                        <th>Dept. ID</th>
                        <th>Department</th>
                        <th>Status Kepegawaian</th>
                        <th>Perusahaan</th>
                        <th>Kontrak ID</th>
                        <th>Id</th>
                        <th>U</th>
                        <th>A</th>
                        <th>E</th>
                        <th>S</th>
                        <th>R</th>
                        <th>Lab. Khusus</th>
                        <th>Nama Paket</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($participants as $key => $item)
                    <tr>
                        <td>
                            <a href="{{ route('participant.edit', $item->id) }}" data-bs-toggle="tooltip"
                                data-bs-placement="right" data-bs-original-title="Edit Register "
                                data-url="{{ route('participant.edit', $item->id) }}"
                                class="btn btn-sm btn-outline-dark edit">
                                <i data-feather='edit'></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-danger delete" data-bs-toggle="tooltip"
                                data-bs-placement="right" data-bs-original-title="Hapus Register "
                                data-url="{{ route('participant.delete', $item->id) }}">
                                <i data-feather='trash'></i>
                            </a>
                            <a href="{{ route('report.register', $item->id) }}" target="_blank"
                                class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip"
                                data-bs-placement="right" data-bs-original-title="Print Register ">
                                <i data-feather='file-text'></i>
                            </a>
                        </td>
                        @if ($isRegisterPage)
                        <td>{{ $item->register_date }}</td>
                        <td>{{ $item->register_number }}</td>
                        @endif
                        <td>{{ $item->code }}</td>
                        <td>{{ $item->nik }}</td>
                        <td>
                            <a href="{{ route('participant.detail', $item->id) }}"
                                data-url="{{ route('participant.detail', $item->id) }}" class="btn btn-link detail detail_{{$item->id}}">
                                {{ $item->name }}
                            </a>
                        </td>
                        <td>{{ $item->gender }}</td>
                        <td>{{ $item->birthday }}</td>
                        <td>{{ $item->divisi?->name }}</td>
                        <td>{{ $item->department?->code }}</td>
                        <td>{{ $item->department?->name }}</td>
                        <td>{{ $item->status }}</td>
                        <td>{{ $item->client?->code }}</td>
                        <td>{{ $item->contract?->code }}</td>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->plan_u }}</td>
                        <td>{{ $item->plan_a }}</td>
                        <td>{{ $item->plan_e }}</td>
                        <td>{{ $item->plan_s }}</td>
                        <td>{{ $item->plan_r }}</td>
                        <td>{{ $item->lab_special }}</td>
                        <td>{{ $item->packet_name }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Aksi</th>
                        <th>MCU ID</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>JK</th>
                        <th>Tgl. Lahir</th>
                        <th>Divisi</th>
                        <th>Dept. ID</th>
                        <th>Department</th>
                        <th>Status Kepegawaian</th>
                        <th>Perusahaan</th>
                        <th>Kontrak ID</th>
                        <th>Id</th>
                        <th>U</th>
                        <th>A</th>
                        <th>E</th>
                        <th>S</th>
                        <th>R</th>
                        <th>Lab. Khusus</th>
                        <th>Nama Paket</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {!! $participants->links('partials.pagination') !!}
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
                    <h5 class="modal-title">Konfirmasi!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin hapus data ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- form add --}}
<form action="{{ route('participant.store') }}" id="form-create" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" id="modal-create" tabindex="-1" aria-labelledby="modal-delete" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Peserta MCU {{ auth()->user()->client?->code }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="content-create">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submit-create">Sumbit</button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- model detail --}}
<div class="modal fade" id="modal-detail" tabindex="-1" aria-labelledby="modal-delete" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Peserta MCU</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="content-detail">

            </div>
        </div>
    </div>
</div>

{{-- form edit detail --}}
<form action="" id="form-edit-detail" method="POST" enctype="multipart/form-data">
    <div class="modal fade" id="modal-edit-detail" tabindex="-1" aria-labelledby="modal-delete" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" id="content-edit-detail">

            </div>
        </div>
    </div>
</form>
<form action="" id="form-import" method="POST" enctype="multipart/form-data">
    <div class="modal fade" id="modal-edit-detail" tabindex="-1" aria-labelledby="modal-delete" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" id="content-edit-detail">

            </div>
        </div>
    </div>
</form>

{{-- modal gigi --}}
<div class="modal fade" id="modal-gigi" tabindex="-1" aria-labelledby="modal-delete" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Keadaan Gigi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="content-gigi">
                <h5 id="nomor-gigi"></h5>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td>
                                <a href="#" id="gigi-karies" class="btn btn-link get-data-gigi"
                                    data-color="danger">Karies (O)</a>
                            </td>
                            <td>
                                <a href="#" id="gigi-tanggal" class="btn btn-link get-data-gigi"
                                    data-color="dark">Tanggal (X)</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-import" tabindex="-1" aria-labelledby="modal-delete" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Peserta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="">
                <form action="{{ route('participant.import') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="form-label required">File Excel</label>
                        <input type="file" name="file" id="file" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="form-label required">Devisi</label>
                        <input type="text" name="devisi" id="devisi" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- form filter --}}
<form action="{{ route('participant.index', request()->all()) }}" id="form-filter" method="GET"
    enctype="multipart/form-data">
    <div class="modal fade" id="modal-filter" tabindex="-1" aria-labelledby="modal-delete" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" id="content-filter">
            </div>
        </div>
    </div>
</form>

{{-- form scan --}}
<form action="#" id="form-scan" method="GET" enctype="multipart/form-data">
    <div class="modal fade" id="modal-scan" tabindex="-1" aria-labelledby="modal-delete" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content" id="content-scan">
                <div class="modal-header">
                    <h5 class="modal-title">Scan Barcode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="form-label required">Barcode atau MCU ID</label>
                        <input type="text" name="mcu_id" id="mcu_id" class="form-control" placeholder="MCU ID">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-scan"
                        data-url="{{ route('participant.scan', 'xxx') }}">Scan</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js')
<script src="{{ asset('app-assets/vendors/js/forms/select/select2.full.min.js') }}"></script>
<script>
    $(function() {

            // button delete
            $('table').on('click', '.delete', function() {
                const url = $(this).data('url');
                $("#form-delete").attr('action', url);
                $("#modal-delete").modal("show");
            });

            $('#scan-barcode').on('click', function() {
                const url = $(this).data('url');
                // $("#form-scan").attr('action', url);
                $("#modal-scan").modal("show");
                setTimeout(() => {
                    $("#mcu_id").focus();
                }, 1000);
            });

            $('#btn-scan').on('click', function(e) {
                e.preventDefault();
                let url = $(this).data('url');
                const mcuId = $("#mcu_id").val();
                if (!mcuId) {
                    toastError('Barcode atau MCU ID belum di input.');
                    return;
                }
                $.ajax({
                    url: url.replace('xxx', mcuId),
                    success: (data) => {
                        if (!data.route) {
                            toastError(`#${mcuId} Data peserta tidak di temukan.`);
                            setTimeout(() => {
                                $("#mcu_id").focus();
                            }, 500);
                            return;
                        }
                        $("#mcu_id").val(null);
                        $("#modal-scan").modal("hide");
                        $("#modal-detail").modal("show");
                        $.ajax({
                            url: data.route,
                            success: (data) => {
                                $('#content-detail').html(data);
                            },
                            error: (e) => {
                                toastError('Internal Server Error');
                            }
                        })
                    },
                    error: (e) => {
                        toastError('Internal Server Error');
                    }
                })
            });

            // button edit
            $('table').on('click', '.edit', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                $("#form-create").attr('action', url);
                $("#modal-create").modal("show");
                $.ajax({
                    url: url,
                    success: (data) => {
                        $('#content-create').html(data);

                        // select2 divisi
                        $('#content-create #divisi_id').select2({
                            allowClear: true,
                            placeholder: 'Pilih Divisi',
                            width: '100%',
                            dropdownParent: '#content-create',
                            ajax: {
                                url: "{{ route('divisi.select2') }}",
                                dataType: 'json',
                                delay: 350,
                                data: function(params) {
                                    return {
                                        search: params.term,
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

                        // select2 divisi
                        $('#content-create #department_id').select2({
                            allowClear: true,
                            placeholder: 'Pilih Department',
                            width: '100%',
                            dropdownParent: '#content-create',
                            ajax: {
                                url: "{{ route('department.select2') }}",
                                dataType: 'json',
                                delay: 350,
                                data: function(params) {
                                    return {
                                        search: params.term,
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

                        eventChange();

                    },
                    error: (e) => {
                        toastError('Internal Server Error');
                    }
                })
            });

            // btn filter
            $('#filter').on('click', function(e) {
                const url = $(this).data('url');
                $("#modal-filter").modal("show");
                $.ajax({
                    url: url,
                    success: (data) => {
                        $('#content-filter').html(data);
                    },
                    error: (e) => {
                        toastError('Internal Server Error');
                    }
                })
            });
            $('#btn-import').on('click', function() {
                $("#modal-import").modal("show");
            });
            // btn create
            $('#btn-create').on('click', function() {
                $("#modal-create").modal("show");

                $.ajax({
                    url: `{{ route('participant.create') }}`,
                    success: (data) => {
                        $('#content-create').html(data);

                        // select2 divisi
                        $('#content-create #divisi_id').select2({
                            allowClear: true,
                            placeholder: 'Pilih Divisi',
                            width: '100%',
                            dropdownParent: '#content-create',
                            ajax: {
                                url: "{{ route('divisi.select2') }}",
                                dataType: 'json',
                                delay: 350,
                                data: function(params) {
                                    return {
                                        search: params.term,
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

                        // select2 divisi
                        $('#content-create #department_id').select2({
                            allowClear: true,
                            placeholder: 'Pilih Department',
                            width: '100%',
                            dropdownParent: '#content-create',
                            ajax: {
                                url: "{{ route('department.select2') }}",
                                dataType: 'json',
                                delay: 350,
                                data: function(params) {
                                    return {
                                        search: params.term,
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

                        eventChange();

                    },
                    error: (e) => {
                        toastError('Internal Server Error');
                    }
                })
            });

            // button store or update
            $('#submit-create').on('click', (e) => {
                const token = $('[name="_token"]').val();
                const nik = $('#content-create #nik').val();
                const name = $('#content-create #name').val();
                const gender = $('#content-create #gender').val();
                const birthday = $('#content-create #birthday').val();
                const phone = $('#content-create #phone').val();
                const divisiId = $('#content-create #divisi_id').val();
                const departmentId = $('#content-create #department_id').val();
                const statusKaryawan = $('#content-create #status').val();

                const packetName = $('#content-create #packet_name').val();
                const packetA = $('#content-create #packet_a').is(':checked') ? 'YA' : 'TIDAK';
                const packetB = $('#content-create #packet_b').is(':checked') ? 'YA' : 'TIDAK';
                const packetC = $('#content-create #packet_c').is(':checked') ? 'YA' : 'TIDAK';
                const packetD = $('#content-create #packet_d').is(':checked') ? 'YA' : 'TIDAK';
                const packetE = $('#content-create #packet_e').is(':checked') ? 'YA' : 'TIDAK';
                const packetF = $('#content-create #packet_f').is(':checked') ? 'YA' : 'TIDAK';

                const planName = $('#content-create #plan_name').val();
                const planU = $('#content-create #plan_u').is(':checked') ? 'YA' : 'TIDAK';
                const planA = $('#content-create #plan_a').is(':checked') ? 'YA' : 'TIDAK';
                const planE = $('#content-create #plan_e').is(':checked') ? 'YA' : 'TIDAK';
                const planS = $('#content-create #plan_s').is(':checked') ? 'YA' : 'TIDAK';
                const planR = $('#content-create #plan_r').is(':checked') ? 'YA' : 'TIDAK';

                const data = {
                    _token: token,
                    nik: nik,
                    name: name,
                    gender: gender,
                    birthday: birthday,
                    phone: phone,
                    divisi_id: divisiId,
                    department_id: departmentId,
                    status: statusKaryawan,
                    packet_name: packetName,
                    packet_a: packetA,
                    packet_b: packetB,
                    packet_c: packetC,
                    packet_d: packetD,
                    packet_e: packetE,
                    packet_f: packetF,
                    plan_name: planName,
                    plan_u: planU,
                    plan_a: planA,
                    plan_e: planE,
                    plan_s: planS,
                    plan_r: planR,
                };
                if (data.nik == '') {
                    $('#content-create #msg-nik').css('display', 'unset');
                    e.preventDefault();
                    return;
                } else {
                    $('#content-create #msg-nik').css('display', 'none');
                }

                if (data.name == '') {
                    $('#content-create #msg-name').css('display', 'unset');
                    e.preventDefault();
                    return;
                } else {
                    $('#content-create #msg-name').css('display', 'none');
                }

                if (data.birthday == '') {
                    $('#content-create #msg-birthday').css('display', 'unset');
                    e.preventDefault();
                    return;
                } else {
                    $('#content-create #msg-birthday').css('display', 'none');
                }
            });

            function eventChange() {

                // const packetNameCahnge = $('#content-create #packet_name').val();
                $('#content-create').on('change', '#packet_a', function(e) {
                    let isChecked = $(this).is(':checked');
                    $('#content-create #plan_u').prop('checked', isChecked);
                    setPaketName();

                })
                $('#content-create').on('change', '#packet_b', function(e) {
                    let isChecked = $(this).is(':checked');
                    $('#content-create #plan_u').prop('checked', isChecked);
                    $('#content-create #plan_r').prop('checked', isChecked);
                    $('#content-create #lab_special').prop('checked', isChecked);
                    setPaketName();

                })
                $('#content-create').on('change', '#packet_c', function(e) {
                    let isChecked = $(this).is(':checked');
                    $('#content-create #plan_u').prop('checked', isChecked);
                    $('#content-create #plan_a').prop('checked', isChecked);
                    setPaketName();

                })
                $('#content-create').on('change', '#packet_d', function(e) {
                    let isChecked = $(this).is(':checked');
                    $('#content-create #plan_u').prop('checked', isChecked);
                    $('#content-create #plan_e').prop('checked', isChecked);
                    $('#content-create #plan_r').prop('checked', isChecked);
                    setPaketName();

                })
                $('#content-create').on('change', '#packet_e', function(e) {
                    let isChecked = $(this).is(':checked');
                    $('#content-create #plan_u').prop('checked', isChecked);
                    $('#content-create #plan_s').prop('checked', isChecked);
                    $('#content-create #lab_special').prop('checked', isChecked);
                    setPaketName();

                })
                $('#content-create').on('change', '#packet_f', function(e) {
                    let isChecked = $(this).is(':checked');
                    $('#content-create #plan_u').prop('checked', isChecked);
                    $('#content-create #plan_a').prop('checked', isChecked);
                    $('#content-create #lab_special').prop('checked', isChecked);
                    setPaketName();
                })

            }

            function setPaketName() {
                let packets = [];
                $('#content-create .packet-x').each(function() {
                    if ($(this).is(':checked')) {
                        packets.push($(this).data('packet').toUpperCase())
                    }
                })
                $('#content-create #packet_name').val(packets.join('+'));
            }

            // button detail
            $('table').on('click', '.detail', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                $("#modal-detail").modal("show");
                $.ajax({
                    url: url,
                    success: (data) => {
                        $('#content-detail').html(data);
                    },
                    error: (e) => {
                        toastError('Internal Server Error');
                    }
                })
            });

            // button update register
            $("#content-detail").on('click', '#update-register', function(e) {
                const url = $(this).data('url');
                $.ajax({
                    url: url,
                    success: (data) => {
                        toastSuccess(data.message);
                    },
                    error: (e) => {
                        toastError('Internal Server Error');
                    }
                })
            })

            // button edit detail
            $('#content-detail').on('click', '.edit-detail', function(e) {
                const url = $(this).data('url');
                $("#modal-edit-detail").modal("show");
                $("#form-edit-detail").attr("action", url);
                $.ajax({
                    url: url,
                    success: (data) => {
                        $('#content-edit-detail').html(data);
                        getEmployee();
                        eventFormEditDetail();
                    },
                    error: (e) => {
                        toastError('Internal Server Error');
                    }
                })
            });
            getEmployee();
            function getEmployee() {
                $('#content-edit-detail #employee_id').select2({
                    allowClear: true,
                    placeholder: 'Pilih Petugas Pemeriksa',
                    width: '100%',
                    dropdownParent: '#content-edit-detail',
                    ajax: {
                        url: "{{ route('employee.select2') }}",
                        dataType: 'json',
                        delay: 350,
                        data: function(params) {
                            return {
                                search: params.term,
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: $.map(data.data, function(item) {
                                    return {
                                        text: `${item.nama}`,
                                        id: item.id
                                    }
                                })
                            };
                        },
                        cache: true
                    }
                });
            }

            $('#content-edit-detail').on('click', '#submit-edit-detail', function(e) {
                e.preventDefault();
                const form = $('#form-edit-detail');

                const url = form.attr('action');
                var dataSerialize = form.serialize();
                if ($('#content-edit-detail #photo').get(0)) {
                    const token = $('#content-edit-detail input[name="_token"]').val();
                    const photo = $('#content-edit-detail #photo').get(0).files[0];
                    dataSerialize = new FormData();
                    dataSerialize.append('_token', token);
                    dataSerialize.append('photo', photo);
                    $.ajax({
                        url: url,
                        method: 'POST',
                        enctype: 'multipart/form-data',
                        processData: false,
                        contentType: false,
                        cache: false,
                        data: dataSerialize,
                        success: (data) => {
                            if (data.success) {
                                toastSuccess('Data berhasil disimpan');
                                if (data.data.selesai == 1){

                                    $('.'+data.class).attr('aria-valuenow', 100);
                                    $('.'+data.class).attr('aria-valuemin', 100);
                                    $('.'+data.class).attr('aria-valuemax', 100);

                                    // Update the width of the progress bar
                                    $('.'+data.class).css('width', '100%');
                                    $('#'+data.class).text('SELESAI')
                                }
                                $('._btn_danger').click()
                                return;
                            }
                            toastError('Data tidak ada perubahan');
                        },
                        error: (e) => {
                            toastError('Internal Server Error');
                        }
                    })

                } else {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: dataSerialize,
                        success: (data) => {
                            if (data.success) {
                                toastSuccess('Data berhasil disimpan');
                                $('._btn_danger').click()
                               if (data.data.selesai == 1){

                                   $('.'+data.class).attr('aria-valuenow', 100);
                                   $('.'+data.class).attr('aria-valuemin', 100);
                                   $('.'+data.class).attr('aria-valuemax', 100);

                                   // Update the width of the progress bar
                                   $('.'+data.class).css('width', '100%');
                                   $('#'+data.class).text('SELESAI')
                               }
                                return;
                            }
                            toastError('Data tidak ada perubahan');
                        },
                        error: (e) => {
                            toastError('Internal Server Error');
                        }
                    })
                }
            });

            function eventFormEditDetail() {
                // event form edit detail TANDA VITAL
                $('#content-edit-detail #keluhan_utama').on('change', function(e) {
                    let isChecked = $(this).is(':checked');
                    $('#content-edit-detail #keluhan_utama_text').val(isChecked ? 'TAK' : null);
                });

                $('#content-edit-detail #riwayat_penyakit_terdahulu').on('change', function(e) {
                    let isChecked = $(this).is(':checked');
                    $('#content-edit-detail #riwayat_penyakit_terdahulu_text').val(isChecked ? '-' : null);
                });

                $('#content-edit-detail #riwayat_penyakit_sekarang').on('change', function(e) {
                    let isChecked = $(this).is(':checked');
                    $('#content-edit-detail #riwayat_penyakit_sekarang_text').val(isChecked ? 'TAK' : null);
                });

                $('#content-edit-detail #alergi').on('change', function(e) {
                    let isChecked = $(this).is(':checked');
                    $('#content-edit-detail #alergi_text').val(isChecked ? 'TIDAK' : null);
                });

                $('#content-edit-detail #riwayat_trauma').on('change', function(e) {
                    let isChecked = $(this).is(':checked');
                    $('#content-edit-detail #riwayat_trauma_text').val(isChecked ? 'TIDAK' : null);
                });

                $('#content-edit-detail #tinggi_badan').on('keyup', function(e) {
                    rumusIMT();
                });
                $('#content-edit-detail #berat_badan').on('keyup', function(e) {
                    rumusIMT();
                });

                function rumusIMT() {
                    const tinggiBadan = parseInt($('#content-edit-detail #tinggi_badan').val()) / 100 || 0;
                    const beratBadan = parseInt($('#content-edit-detail #berat_badan').val());
                    const imt = (tinggiBadan > 0 ? (beratBadan / (tinggiBadan * tinggiBadan)) : 0).toFixed(0);
                    let imtText = '';
                    if (imt > 27) {
                        imtText = 'OBESITAS';
                    }
                    if (imt > 25 && imt <= 27) {
                        imtText = 'GEMUK';
                    }
                    if (imt >= 18.5 && imt <= 25) {
                        imtText = 'NORMAL';
                    }

                    if (imt >= 17 && imt < 18.5) {
                        imtText = 'KURUS';
                    }
                    if (imt < 17) {
                        imtText = 'SANGAT KURUS';
                    }

                    $('#content-edit-detail #imt').val(imtText);
                    $('#content-edit-detail #imt_nilai').val(imt);
                }

                // event form edit SPIROMETRI
                $('#content-edit-detail .hasil-spirometri').on('change', function() {
                    setHasilSpirometri();
                });

                function setHasilSpirometri() {
                    let hasilSpirometri = [];
                    $('#content-edit-detail .hasil-spirometri').each(function() {
                        if ($(this).is(':checked')) {
                            hasilSpirometri.push($(this).attr('id').toUpperCase())
                        }
                    })

                    $('#content-edit-detail #hasil').val(hasilSpirometri.join('+'));
                }

                // event form edit PEMERIKSAAN FISIk
                $('#content-edit-detail #kepala').on('change', function() {
                    const kepalaText = $('#content-edit-detail #kepala_text');
                    $(this).is(':checked') ? kepalaText.val('Normochepal') : kepalaText.val(null);
                });

                $('#content-edit-detail .gigi').on('click', function(e) {
                    const dataGigi = $(this).data('gigi');
                    const dataGigiKey = Object.keys(dataGigi);
                    const dataGigiVal = Object.values(dataGigi);
                    localStorage.setItem('data-gigi-key', dataGigiKey);
                    localStorage.setItem('data-gigi-val', dataGigiVal);
                    $("#nomor-gigi").html(dataGigiVal[0].replace('=Karies', ''))
                    $("#modal-gigi").modal('show');
                })

                $(".get-data-gigi").on("click", function(e) {
                    const color = $(this).data('color');
                    let dataKeyGigi = localStorage.getItem('data-gigi-key').split(',');
                    let dataValGigi = localStorage.getItem('data-gigi-val').split(',');
                    const kodeGigi = $('#content-edit-detail #kode_gigi');
                    const gigi = $('#content-edit-detail #gigi');

                    if (kodeGigi.val()) {
                        var kodeGigiArray = kodeGigi.val().split('+');
                    } else {
                        var kodeGigiArray = [];
                    }

                    if (gigi.val()) {
                        var gigiArray = gigi.val().split('+');
                    } else {
                        var gigiArray = [];
                    }

                    if (color == 'dark') {
                        dataKeyGigi = dataKeyGigi[1];
                        dataValGigi = dataValGigi[1];
                    } else {
                        dataKeyGigi = dataKeyGigi[0];
                        dataValGigi = dataValGigi[0];
                    }

                    kodeGigiArray.push(dataKeyGigi);
                    gigiArray.push(dataValGigi);

                    kodeGigi.val([...new Set(kodeGigiArray)].join('+'));
                    gigi.val([...new Set(gigiArray)].join('+'));

                    var kode = dataKeyGigi.replace(':Karies', '');
                    kode = kode.replace(':Tanggal', '');
                    $('#content-edit-detail #' + kode).removeClass('bg-danger');
                    $('#content-edit-detail #' + kode).removeClass('bg-dark');
                    $('#content-edit-detail #' + kode).addClass('bg-' + color);
                    $("#modal-gigi").modal('hide');
                });

                const tenggorokanText = $("#content-edit-detail #tenggorokan_text");
                $("#content-edit-detail #tenggorokan").on("change", function(e) {
                    const isChecked = $(this).is(':checked');
                    const valueTenggorokanText = isChecked ? 'TAK' : null;
                    tenggorokanText.val(valueTenggorokanText);
                });

                $("#content-edit-detail .tenggorokan").on('change', function() {
                    setTenggorokanText();
                })

                function setTenggorokanText() {
                    let tenggorokanArrayKanan = [];
                    let tenggorokanArrayKiri = [];
                    $("#content-edit-detail .tenggorokan").each(function(e) {
                        if ($(this).is(':checked') && $(this).data('kode') == 'kanan') {
                            tenggorokanArrayKanan.push($(this).data('value'))
                        };

                        if ($(this).is(':checked') && $(this).data('kode') == 'kiri') {
                            tenggorokanArrayKiri.push($(this).data('value'))
                        };
                    });

                    let kanan = [...new Set(tenggorokanArrayKanan)].join('');
                    let kiri = [...new Set(tenggorokanArrayKiri)].join('');
                    if (kanan) {
                        kanan += '-';
                    }
                    tenggorokanText.val(kanan + kiri)
                }

                const ekgText = $("#content-edit-detail #ekg_text");
                $("#content-edit-detail #ekg_tidak_diperiksa").on('change', function() {
                    const isChecked = $(this).is(':checked');
                    let ekgBdnIsChecked = $("#content-edit-detail #ekg_bdn").is(':checked');
                    if (isChecked) {
                        ekgText.val('TIDAK DIPERIKSA');
                    }
                    if (!isChecked) {
                        ekgText.val('');
                    }
                    if (!isChecked && ekgBdnIsChecked) {
                        ekgText.val('BDN');
                    }
                })

                $("#content-edit-detail #ekg_bdn").on('change', function() {
                    const isChecked = $(this).is(':checked');
                    let ekgIsChecked = $("#content-edit-detail #ekg_tidak_diperiksa").is(':checked');
                    if (ekgIsChecked) {
                        ekgText.val('TIDAK DIPERIKSA');
                    }
                    if (!ekgIsChecked && isChecked) {
                        ekgText.val('BDN');
                    }
                    if (!isChecked) {
                        ekgText.val('');
                    }
                })

                const neorologisText = $("#content-edit-detail #neurologis_text");
                $("#content-edit-detail #neurologis_tidak_diperiksa").on('change', function() {
                    const isChecked = $(this).is(':checked');
                    let neorologisBdnIsChecked = $("#content-edit-detail #ekg_bdn").is(':checked');
                    if (isChecked) {
                        neorologisText.val('TIDAK DIPERIKSA');
                    }
                    if (!isChecked) {
                        neorologisText.val('');
                    }
                    if (!isChecked && neorologisBdnIsChecked) {
                        neorologisText.val('BDN');
                    }
                })

                $("#content-edit-detail #neurologis_bdn").on('change', function() {
                    const isChecked = $(this).is(':checked');
                    let neorologisIsChecked = $("#content-edit-detail #neurologis_tidak_diperiksa").is(
                        ':checked');
                    if (neorologisIsChecked) {
                        neorologisText.val('TIDAK DIPERIKSA');
                    }
                    if (!neorologisIsChecked && isChecked) {
                        neorologisText.val('BDN');
                    }
                    if (!isChecked) {
                        neorologisText.val('');
                    }
                })
            }

        })



</script>
<script>


    $('#nilai_normal').click(function (e) {

        $('#keadaanUmum').val('Compost Mentis');
        $('#normokepala').prop('checked', true);
        $('#kepala').val('Normochepal');
        $('#pupil').val('ISOKOR');
        $('#buta_warna').val('NORMAL');
        $('#telinga2').val('Normal');
        $('#tonsiltenggorokan').prop('checked', true);
        $('#tenggorokan').val('TAK');
        $('#faring').val('Tidak Hiperemis');
        $('#leher_kgb').val('PEMBESARAN -');
        $('#leher_jvp').val('MENINGKAT -');
        $('#fisik_jantung_auskultasi').val('BJ I + BJ II Normal');
        $('#fisik_jantung_perkusi').val('Sonor'); //DALL
        $('#fisik_paru_inspeksi').val('Pergerakan Dada Simetris');
        $('#fisik_jantung_palpasi').val('IC Teraba'); //
        $('#fisik_paru_perkusi').val('Sonor');
        $('#fisik_abdomen_inspeksi').val('Supel');
        $('#fisik_abdomen_auskultasi').val('Bising usus Normal');
        $('#fisik_abdomen_perkusi').val('Timpani');
        var pemEkg = "0";
        if (pemEkg == 1) {
            $('#fisekgfisik_ekg').prop('checked', false);
            $('#fisekg2fisik_ekg').prop('checked', true);
            $('#fisik_ekg').val('DBN');
        } else {
            $('#fisekgfisik_ekg').prop('checked', true);
            $('#fisekg2fisik_ekg').prop('checked', false);
        }

        $('#fisekg2neurologis').prop('checked', true);
        $('#neurologis').val('DBN');
        $('#pemeriksa_fisik').val('dr. Dini Desti Susanti');
        $('#cu_fisik_p').prop('checked', true);
        $('#u_fisik_p').val('1');
        $('#cu_fisik').prop('checked', true);
        $('#u_fisik').val('1');

    });

</script>
@endsection
