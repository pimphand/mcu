@extends('layouts.main')

@section('title', 'Tambah Role')

@section('css')
    {{-- vendor css files --}}
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header d-flex">
        </div>
        <div class="card-body">
            <form action="{{ route('role.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                @if (session('error'))
                                    {{ session('error') }}
                                @endif
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label mt-1">Level Role</label>
                                            <select name="level" id="level" class="form-control" required>
                                                <option value="1">Level 1</option>
                                                <option value="2">Level 2</option>
                                                <option value="3">Level 3</option>
                                                <option value="4">Level 4</option>
                                            </select>
                                            @error('level')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">Nama </label>
                                            <input type="text" name="name" class="form-control" placeholder="nama"
                                                value="{{ old('name') }}" required>
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mt-1">Tampilkan ?</label><br>
                                            <input type="checkbox" name="is_active" value="1">
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
