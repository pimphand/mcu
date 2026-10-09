@extends('layouts.main')

@section('title', 'Detail Menu')

@section('css')
@endsection

@section('content')
    <div class="card">
        <div class="card-header border-bottom p-1">
            <div class="head-label">
                <h6 class="mb-0">Detail Menu: {{ $menu->name }}</h6>
            </div>
            <div class="dt-action-buttons text-right">
                <div class="dt-buttons d-inline-flex">
                    <a href="{{ route('menu.index') }}"
                        class="btn btn-outline-danger float-right waves-effect waves-float waves-light">Kembali</a>
                    <a href="{{ route('menu.create', ['parent_id' => $menu->id]) }}"
                        class="btn btn-success float-right waves-effect waves-float waves-light">Tambah Sub Menu</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th style="width: 120px">Nama</th>
                            <td>{{ $menu->name }}</td>
                        </tr>
                        <tr>
                            <th>URL</th>
                            <td>{{ $menu->url }}</td>
                        </tr>
                        <tr>
                            <th>Icon</th>
                            <td>{{ $menu->icon }}</td>
                        </tr>
                        <tr>
                            <th>Urutan</th>
                            <td>{{ $menu->sort_order }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <div class="form-check form-check-success form-switch">
                                    <input type="checkbox" class="form-check-input js-menu-active"
                                        id="menu_toggle_{{ $menu->id }}" value="1"
                                        data-url="{{ route('menu.toggle', $menu->id) }}"
                                        data-label="menu_toggle_label_{{ $menu->id }}"
                                        {{ $menu->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" id="menu_toggle_label_{{ $menu->id }}"
                                        for="menu_toggle_{{ $menu->id }}">
                                        {{ $menu->is_active ? 'Aktif' : 'Non Aktif' }}
                                    </label>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="dt-responsive table mt-1" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>URL</th>
                            <th>Icon</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($children as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->url }}</td>
                                <td>{{ $item->icon }}</td>
                                <td>{{ $item->sort_order }}</td>
                                <td>
                                    <div class="form-check form-check-success form-switch">
                                        <input type="checkbox" class="form-check-input js-menu-active"
                                            id="menu_toggle_{{ $item->id }}" value="1"
                                            data-url="{{ route('menu.toggle', $item->id) }}"
                                            data-label="menu_toggle_label_{{ $item->id }}"
                                            {{ $item->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" id="menu_toggle_label_{{ $item->id }}"
                                            for="menu_toggle_{{ $item->id }}">
                                            {{ $item->is_active ? 'Aktif' : 'Non Aktif' }}
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('menu.edit', $item->id) }}" data-bs-toggle="tooltip"
                                        data-bs-placement="right" data-bs-original-title="Edit Menu"
                                        class="btn btn-sm btn-outline-dark edit">
                                        <i data-feather='edit'></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger delete"
                                        data-url="{{ route('menu.destroy', $item->id) }}" data-bs-toggle="tooltip"
                                        data-bs-placement="right" data-bs-original-title="Hapus Menu">
                                        <i data-feather='trash-2'></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada sub menu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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
                        <p>Yakin ingin hapus menu ini ?</p>
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

            // toggle aktif / non aktif menu (AJAX)
            $('.card-body').on('change', '.js-menu-active', function() {
                const $this = $(this)
                const url = $this.data('url')
                const label = $('#' + $this.data('label'))
                const checked = $this.is(':checked')

                $this.prop('disabled', true)
                $.ajax({
                    url: url,
                    method: 'POST',
                    dataType: 'JSON',
                    data: {
                        _token: '{{ csrf_token() }}',
                        is_active: checked ? 1 : 0
                    },
                    success: function(data) {
                        if (data.success) {
                            label.text(data.is_active ? 'Aktif' : 'Non Aktif')
                            toastSuccess(data.message)
                        } else {
                            $this.prop('checked', !checked)
                            toastError()
                        }
                    },
                    error: function() {
                        $this.prop('checked', !checked)
                        toastError()
                    },
                    complete: function() {
                        $this.prop('disabled', false)
                    }
                })
            })
        })
    </script>
@endsection
