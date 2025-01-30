@extends('layouts.main')

@section('title', 'Edit Role')

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
            <form action="{{ route('role.update', $role['id']) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')
                <input type="hidden" name="id" value="{{ $role['id'] }}">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label mt-1">Induk Menu</label>
                                            <select name="level" id="level" class="form-control">
                                                <option value="1" {{ $role['level'] == 1 ? 'selected' : '' }}>Level 1</option>
                                                <option value="2" {{ $role['level'] == 2 ? 'selected' : '' }}>Level 2</option>
                                                <option value="3" {{ $role['level'] == 3 ? 'selected' : '' }}>Level 3</option>
                                                <option value="4" {{ $role['level'] == 4 ? 'selected' : '' }}>Level 4</option>
                                                <option value="5" {{ $role['level'] == 5 ? 'selected' : '' }}>Level 5</option>
                                            </select>
                                            @error('level')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">Nama </label>
                                            <input type="text" name="name" class="form-control" placeholder="nama"
                                                value="{{ old('name', $role['name']) }}" required>
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">Tampilkan ?</label><br>
                                            <input type="checkbox" name="is_active" value="1"
                                                {{ $role['is_active'] ? 'checked' : '' }}>
                                            @error('is_active')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer justify-content-end">
                                <a href="{{ route('role.index') }}" class="btn btn-danger">Batal</a>
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
    {{-- vendor files --}}
@endsection
