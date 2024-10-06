@extends('layouts.main')

@section('title', 'Tambah Client')

@section('css')
    {{-- vendor css files --}}
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header d-flex">
            <div class="card-title">
                Tambah Client
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('client.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label mt-1">Nama</label>
                                    <input type="text" name="name" class="form-control" placeholder="nama"
                                        value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Alamat</label>
                                        <textarea name="address" id="address" class="form-control" cols="30" rows="3" placeholder="alamat">{!! old('address') !!}</textarea>
                                    @error('address')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer justify-content-end">
                        <a href="{{ route('client.index') }}" class="btn btn-outline-bitbucket">Kembali</a>
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