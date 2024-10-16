@extends('layouts.main')

@section('title', 'Edit Contract')

@section('css')
    {{-- vendor css files --}}
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header d-flex">
            <div class="card-title">
                Edit Contract
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('contract.update', $contract->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label mt-1">Client</label>
                                    <select name="client_id" id="client_id" class="form-select" required>
                                        <option value="">Pilih</option>
                                        @foreach ($clients as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('client_id', $contract->client_id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label mt-1">Nomor Kontrak</label>
                                    <input type="text" name="name" class="form-control" placeholder="Masukan Nomor Kontrak"
                                        value="{{ old('name', $contract->name) }}" required>
                                    @error('name')
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
