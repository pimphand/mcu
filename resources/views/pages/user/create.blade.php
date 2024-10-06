@extends('layouts.main')

@section('title', 'Tambah User')

@section('css')
    {{-- vendor css files --}}
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header d-flex">
            <div class="card-title">
                Tambah User
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label mt-1">Role</label>
                                    <select name="role_id" id="role_id" class="form-control select2" required>
                                        @foreach ($roles as $item)
                                            <option value="{{ $item->id }}">Level {{ $item->level }} /
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Nama</label>
                                    <input type="text" name="name" class="form-control" placeholder="nama"
                                        value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Username </label>
                                    <input type="text" name="username" class="form-control" placeholder="username"
                                        value="{{ old('username') }}" required>
                                    @error('username')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Email </label>
                                    <input type="email" name="email" class="form-control" placeholder="email@gmail.com"
                                        value="{{ old('email') }}" required>
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Password </label>
                                    <input type="password" name="password" class="form-control" placeholder="******"
                                        value="{{ old('password') }}" required>
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Status Aktif ?</label><br>
                                    <div class="form-check form-check-success form-switch">
                                        <input type="checkbox" name="is_active" {{ false ? 'checked' : '' }}
                                            data-permission="is_view" class="permission form-check-input" value="1" />
                                    </div>
                                    @error('is_active')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer justify-content-end">
                        <a href="{{ route('user.index') }}" class="btn btn-outline-bitbucket">Kembali</a>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    {{-- vendor files --}}
@endsection