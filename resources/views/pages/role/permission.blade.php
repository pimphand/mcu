@extends('layouts.main')

@section('title', 'Akses Menu')

@section('css')
    {{-- vendor css files --}}
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Induk</th>
                        <th>Menu</th>
                        <th>view</th>
                        <th>add</th>
                        <th>edit</th>
                        <th>delete</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($menus as $item)
                        @isset($item['sub_menu'])
                            <tr>
                                <td>{{ $role['name'] }} | level [{{ $role['level'] }}]</td>
                                <td colspan="2">{{ $item['name'] }}</td>
                                <td colspan="4">
                                    <div class="form-check form-check-success form-switch">
                                        <input type="checkbox" {{ $item['is_view'] ? 'checked' : '' }}
                                            data-permission="is_view" class="permission form-check-input"
                                            id="is_view_{{ $item['menu_id'] }}" data-role="{{ $role['id'] }}"
                                            data-menuid="{{ $item['menu_id'] }}" value="1" />
                                    </div>
                                </td>
                            </tr>
                            @foreach ($item['sub_menu'] as $item)
                                <tr>
                                    <td>{{ $role['name'] }} | level [{{ $role['level'] }}]</td>
                                    <td></td>
                                    <td>{{ $item['name'] }}</td>
                                    <td>
                                        <div class="form-check form-check-success form-switch">
                                            <input type="checkbox" {{ $item['is_view'] ? 'checked' : '' }}
                                                data-permission="is_view" class="permission form-check-input"
                                                id="is_view_{{ $item['menu_id'] }}" data-role="{{ $role['id'] }}"
                                                data-menuid="{{ $item['menu_id'] }}" value="1" />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-check-success form-switch">
                                            <input type="checkbox" {{ $item['is_add'] ? 'checked' : '' }}
                                                data-permission="is_add" class="permission form-check-input"
                                                id="is_add_{{ $item['menu_id'] }}" data-role="{{ $role['id'] }}"
                                                data-menuid="{{ $item['menu_id'] }}" value="1" />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-check-success form-switch">
                                            <input type="checkbox" {{ $item['is_edit'] ? 'checked' : '' }}
                                                data-permission="is_edit" class="permission form-check-input"
                                                id="is_edit_{{ $item['menu_id'] }}" data-role="{{ $role['id'] }}"
                                                data-menuid="{{ $item['menu_id'] }}" value="1" />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-check-success form-switch">
                                            <input type="checkbox" {{ $item['is_delete'] ? 'checked' : '' }}
                                                data-permission="is_delete" class="permission form-check-input"
                                                id="is_delete_{{ $item['menu_id'] }}" data-role="{{ $role['id'] }}"
                                                data-menuid="{{ $item['menu_id'] }}" value="1" />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td>{{ $role['name'] }} | level [{{ $role['level'] }}]</td>
                                <td></td>
                                <td>{{ $item['name'] }}</td>
                                <td>
                                    <div class="form-check form-check-success form-switch">
                                        <input type="checkbox" {{ $item['is_view'] ? 'checked' : '' }}
                                            data-permission="is_view" class="permission form-check-input"
                                            id="is_view_{{ $item['menu_id'] }}" data-role="{{ $role['id'] }}"
                                            data-menuid="{{ $item['menu_id'] }}" value="1" />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-check-success form-switch">
                                        <input type="checkbox" {{ $item['is_add'] ? 'checked' : '' }}
                                            data-permission="is_add" class="permission form-check-input"
                                            id="is_add_{{ $item['menu_id'] }}" data-role="{{ $role['id'] }}"
                                            data-menuid="{{ $item['menu_id'] }}" value="1" />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-check-success form-switch">
                                        <input type="checkbox" {{ $item['is_edit'] ? 'checked' : '' }}
                                            data-permission="is_edit" class="permission form-check-input"
                                            id="is_edit_{{ $item['menu_id'] }}" data-role="{{ $role['id'] }}"
                                            data-menuid="{{ $item['menu_id'] }}" value="1" />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-check-success form-switch">
                                        <input type="checkbox" {{ $item['is_delete'] ? 'checked' : '' }}
                                            data-permission="is_delete" class="permission form-check-input"
                                            id="is_delete_{{ $item['menu_id'] }}" data-role="{{ $role['id'] }}"
                                            data-menuid="{{ $item['menu_id'] }}" value="1" />
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <a href="{{ route('role.index') }}" class="btn btn-danger">Kembali</a>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function() {
            localStorage.setItem('_token', '{{ csrf_token() }}')
            $(".permission").on("change", function() {
                const url = "{{ route('role.permission.store') }}";
                const role_id = $(this).data("role");
                const menu_id = $(this).data("menuid");
                const permission = $(this).data("permission");
                const is_view = $('#is_view_' + menu_id).is(":checked") ? 1 : 0;
                const is_add = $('#is_add_' + menu_id).is(":checked") ? 1 : 0;
                const is_edit = $('#is_edit_' + menu_id).is(":checked") ? 1 : 0;
                const is_delete = $('#is_delete_' + menu_id).is(":checked") ? 1 : 0;
                const token = localStorage.getItem('_token');
                data = {
                    _token: token,
                    role_id: role_id,
                    menu_id: menu_id
                }
                data.is_view = is_view;
                data.is_add = is_add;
                data.is_edit = is_edit;
                data.is_delete = is_delete;

                $.ajax({
                    url: url,
                    data: data,
                    dataType: 'JSON',
                    method: 'POST',
                    success: function(data) {
                        if (data.success) {
                            toastSuccess()
                        } else {
                            toastError()
                        }
                        localStorage.setItem('_token', data._token)
                    }
                })
            })
        })
    </script>
@endsection
