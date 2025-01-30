@extends('layouts.main')

@section('title', 'Edit Department')

@section('css')
    {{-- vendor css files --}}
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header d-flex">
            <div class="card-title">
                Edit Department
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('department.update', $department->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label mt-1">Nama</label>
                                    <input type="text" name="name" class="form-control" placeholder="nama"
                                        value="{{ old('name', $department->name) }}" required>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer justify-content-end">
                        <a href="{{ route('department.index') }}" class="btn btn-outline-bitbucket">Kembali</a>
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
