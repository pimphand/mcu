@extends('layouts.main')

@section('title', 'Edit Karyawan')

@section('css')
    {{-- vendor css files --}}
@endsection

@section('content')
    <!-- Basic Tabs starts -->
    <div class="card">
        <div class="card-header d-flex">
            <div class="card-title">
                Edit Karyawan
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('employee.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label mt-1">NIK</label>
                                    <input type="text" name="nik" class="form-control" placeholder="nik"
                                        value="{{ old('nik', $employee->nik) }}" required>
                                    @error('nik')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Nama</label>
                                    <input type="text" name="nama" class="form-control" placeholder="nama"
                                        value="{{ old('nama', $employee->nama) }}" required>
                                    @error('nama')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-control form-select"
                                        required>
                                        @foreach ($employeeMaster->getJK() as $key => $item)
                                            <option value="{{ $key }}"
                                                {{ old('jenis_kelamin', $employee->jenis_kelamin) == $key ? 'selected' : '' }}>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jenis_kelamin')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Agama</label>
                                    <select name="agama" id="agama" class="form-control form-select" required>
                                        <option value="-t">-</option>
                                        @foreach ($employeeMaster->getAgama() as $key => $item)
                                            <option value="{{ $key }}"
                                                {{ old('agama', $employee->agama) == $key ? 'selected' : '' }}>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('agama')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Status</label>
                                    <select name="status" id="status" class="form-control form-select" required>
                                        @foreach ($employeeMaster->getStatusKTP() as $key => $item)
                                            <option value="{{ $key }}"
                                                {{ old('status', $employee->status) == $key ? 'selected' : '' }}>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" class="form-control"
                                        placeholder="tanggal lahir"
                                        value="{{ old('tanggal_lahir', $employee->tanggal_lahir) }}" required>
                                    @error('tanggal_lahir')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" class="form-control"
                                        placeholder="tempat lahir"
                                        value="{{ old('tempat_lahir', $employee->tempat_lahir) }}" required>
                                    @error('tempat_lahir')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label mt-1">Alamat</label>
                                    <textarea name="alamat" id="alamat" class="form-control" cols="30" rows="1" placeholder="alamat">{!! old('alamat', $employee->alamat) !!}</textarea>
                                    @error('alamat')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Golongan Darah</label>
                                    <select name="golongan_darah" id="golongan_darah" class="form-control form-select"
                                        required>
                                        <option value="-">-</option>
                                        @foreach ($employeeMaster->getGolonganDarah() as $key => $item)
                                            <option value="{{ $key }}"
                                                {{ old('golongan_darah', $employee->golongan_darah) == $key ? 'selected' : '' }}>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('golongan_darah')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Rhesus</label>
                                    <input type="text" name="rhesus" class="form-control" placeholder="rhesus"
                                        value="{{ old('rhesus', $employee->rhesus) }}">
                                    @error('rhesus')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Pendidikan</label>
                                    <select name="pendidikan" id="pendidikan" class="form-control form-select" required>
                                        <option value="-">-</option>
                                        @foreach ($employeeMaster->getPendidikan() as $key => $item)
                                            <option value="{{ $key }}"
                                                {{ old('pendidikan', $employee->pendidikan) == $key ? 'selected' : '' }}>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pendidikan')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Telp</label>
                                    <input type="tel" name="telp" class="form-control" placeholder="telp"
                                        value="{{ old('telp', $employee->telp) }}" required>
                                    @error('telp')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="email"
                                        value="{{ old('email', $employee->email) }}">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Status Karyawan</label>
                                    <select name="pendidikan" id="pendidikan" class="form-control form-select" required>
                                        <option value="-">-</option>
                                        @foreach ($employeeMaster->getStatusKaryawan() as $key => $item)
                                            <option value="{{ $key }}"
                                                {{ old('pendidikan', $employee->pendidikan) == $key ? 'selected' : '' }}>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pendidikan')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label mt-1">Unit/Bagian</label>
                                    <input type="text" name="unit" class="form-control" placeholder="unit"
                                        value="{{ old('unit', $employee->unit) }}">
                                    @error('unit')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Jabatan</label>
                                    <input type="text" name="jabatan" class="form-control" placeholder="jabatan"
                                        value="{{ old('jabatan', $employee->jabatan) }}">
                                    @error('jabatan')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Profesi</label>
                                    <input type="text" name="profesi" class="form-control" placeholder="profesi"
                                        value="{{ old('profesi', $employee->profesi) }}">
                                    @error('profesi')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Profesi Detail</label>
                                    <input type="text" name="profesi_detail" class="form-control"
                                        placeholder="profesi detail"
                                        value="{{ old('profesi_detail', $employee->profesi_detail) }}">
                                    @error('profesi_detail')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Status Warga Negara</label>
                                    <select name="warga_negara" id="warga_negara" class="form-control form-select"
                                        required>
                                        @foreach ($employeeMaster->getStatusWargaNegara() as $key => $item)
                                            <option value="{{ $key }}"
                                                {{ old('warga_negara', $employee->warga_negara) == $key ? 'selected' : '' }}>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('warga_negara')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Nama Bank</label>
                                    <input type="text" name="nama_bank" class="form-control" placeholder="input.."
                                        value="{{ old('nama_bank', $employee->nama_bank) }}">
                                    @error('nama_bank')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label mt-1">Nomor Rekening</label>
                                    <input type="text" name="nomor_rekening" class="form-control"
                                        placeholder="input.."
                                        value="{{ old('nomor_rekening', $employee->nomor_rekening) }}">
                                    @error('nomor_rekening')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer justify-content-end">
                        <a href="{{ route('employee.index') }}" class="btn btn-outline-bitbucket">Kembali</a>
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
