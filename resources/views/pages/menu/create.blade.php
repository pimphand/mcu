@extends('layouts.main')

@section('title', 'Tambah Menu')

@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <h4 class="card-title">Tambah Menu</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('menu.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label mt-1">Induk Menu</label>
                                            <select name="parent_id" id="parent_id" class="form-control">
                                                <option value="">— Tanpa induk (menu utama) —</option>
                                                @foreach ($menus as $parent)
                                                    <option value="{{ $parent->id }}"
                                                        {{ old('parent_id', $parentId ?? 0) == $parent->id ? 'selected' : '' }}>
                                                        {{ $parent->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('parent_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">Nama</label>
                                            <input type="text" name="name" class="form-control" placeholder="nama menu"
                                                value="{{ old('name') }}" required>
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">URL</label>
                                            <input type="text" name="url" class="form-control"
                                                placeholder="/contoh  atau  #" value="{{ old('url') }}">
                                            @error('url')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">Icon</label>
                                            <input type="text" name="icon" class="form-control"
                                                placeholder="nama icon feather, contoh: circle" value="{{ old('icon') }}">
                                            @error('icon')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">Urutan</label>
                                            <input type="number" name="sort_order" class="form-control" min="0"
                                                value="{{ old('sort_order', 0) }}" required>
                                            @error('sort_order')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">Tampilkan ?</label><br>
                                            <input type="checkbox" name="is_active" value="1"
                                                {{ old('is_active', 1) ? 'checked' : '' }}>
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
@endsection

@section('js')
@endsection
