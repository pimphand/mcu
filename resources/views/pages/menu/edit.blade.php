@extends('layouts/contentLayoutMaster')

@section('title', 'Edit Menu')

@section('vendor-style')
    {{-- vendor css files --}}
@endsection

@section('page-style')
    {{-- Page Css files --}}
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header d-flex">
        </div>
        <div class="card-body">
            <form action="{{ route('menu.update', $result['data']['id']) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')
                <input type="hidden" name="id" value="{{ $result['data']['id'] }}">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label mt-1">Induk Menu</label>
                                            <select name="parent_id" id="parent" class="form-control">
                                                <option value="">--Tidak Ada--</option>
                                                @foreach ($parents as $item)
                                                    <option value="{{ $item['id'] }}"
                                                        {{ old('parent_id', $result['data']['parent_id']) == $item['id'] && $result['data']['parent_id'] != $result['data']['id'] ? 'selected' : '' }}>
                                                        {{ $item['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('parent_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">Nama </label>
                                            <input type="text" name="name" class="form-control" placeholder="nama"
                                                value="{{ old('name', $result['data']['name']) }}" required>
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">Urutan </label>
                                            <input type="number" name="sort_order" class="form-control" placeholder="0"
                                                value="{{ old('sort_order', $result['data']['sort_order']) }}" required>
                                            @error('sort_order')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">URL</label>
                                            <input type="text" name="url" class="form-control" placeholder="url"
                                                value="{{ old('url', $result['data']['url']) }}" required>
                                            @error('url')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">Icon </label>
                                            <input type="text" name="icon" class="form-control"
                                                placeholder="image, users, package, list"
                                                value="{{ old('icon', $result['data']['icon']) }}" required>
                                            @error('icon')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">Tampilkan ?</label><br>
                                            <input type="checkbox" name="is_active" value="1"
                                                {{ $result['data']['is_active'] ? 'checked' : '' }}>
                                            @error('is_active')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer justify-content-end">
                                <a href="{{ route('menu.index') }}" class="btn btn-danger">Batal</a>
                                <button type="submit" class="btn btn-success">Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection('content')

@section('vendor-script')
    {{-- vendor files --}}
@endsection
@section('page-script')
    {{-- Page js files --}}
@endsection
