@php
    $pemeriksaanFisik = $participant->pemeriksaanFisik;
@endphp
<div class="modal-header">
    <div class="col-11">
        <h5 class="modal-title" id="modal-edit-detail-title">
            {{ 'Pemeriksaan Fisik MCU : ID ' . $participant->code . ' | ' . $participant->name }}</h5>
    </div>
    <div class="col-1 d-flex justify-content-end">
        <button type="button" class="btn btn-sm btn-gradient-success p-1" id="nilai_normal">Nilai Normal</button>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
</div>
<div class="modal-body">
    @csrf
    @method('put')
    <div class="row">
        <div class="col-12">
            <h4>KEADAAN UMUM</h4>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Keadaan Umum</label>
                <select name="keadaan_umum" id="keadaan_umum" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getKeadaanUmum() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->keadaan_umum == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <h4>KEPALA</h4>
        </div>
        <div class="col-md-1">
            <div class="form-group">
                <label class="form-check-label h-3 fw-bold" for="">Kepala Normochepal &nbsp;</label><br>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="kepala" class="form-check-input" id="kepala" {{ $pemeriksaanFisik->kepala ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label class="form-label" for=""></label>
                <input type="text" name="kepala_text" id="kepala_text" class="form-control" value="{{ $pemeriksaanFisik->kepala_text }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label" for="">Hidung</label>
                <input type="text" name="hidung" id="hidung" class="form-control" value="{{ $pemeriksaanFisik->hidung }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label" for="">Mata</label>
                <input type="text" name="mata" id="mata" class="form-control" value="{{ $pemeriksaanFisik->mata }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Pupil</label>
                <select name="pupil" id="pupil" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getPupil() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->pupil == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label" for="">Visus</label>
                <input type="text" name="visus" id="visus" class="form-control" value="{{ $pemeriksaanFisik->visus }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Buta Warna</label>
                <select name="buta_warna" id="buta_warna" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getButaWarna() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->buta_warna == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label" for="">Telinga</label>
                <input type="text" name="telinga" id="telinga" class="form-control" value="{{ $pemeriksaanFisik->telinga }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Kelainan Telinga</label>
                <select name="kelainan_telinga" id="kelainan_telinga" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getKelainanTelinga() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->kelainan_telinga == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="" class="form-label">Gigi</label>
                <table class="table table-bordered">
                    <tbody>
                        @foreach ($participants->getGigi() as $key => $sub)
                            <tr>

                                @foreach ($sub['KANAN'] as $key2 => $item)
                                    <td class="gigi" id="{{ str_replace(':Karies', '', array_keys($item)[0]) }}"
                                        data-gigi="{{ json_encode($item) }}">{{ $key2 }}</td>
                                @endforeach
                                @foreach ($sub['KIRI'] as $key2 => $item)
                                    <td class="gigi" id="{{ str_replace(':Karies', '', array_keys($item)[0]) }}"
                                        data-gigi="{{ json_encode($item) }}">{{ $key2 }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="" class="form-label text-white">Gigi</label>
                <textarea name="gigi" id="gigi" cols="30" rows="3" class="form-control">{{ $pemeriksaanFisik->gigi }}</textarea>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label" for="">Kode Gigi</label>
                <input type="text" name="kode_gigi" id="kode_gigi" class="form-control" value="{{ $pemeriksaanFisik->kode_gigi }}">
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <h4>MULUT</h4>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label" for="">Bibir</label>
                <input type="text" name="bibir" id="bibir" class="form-control" value="{{ $pemeriksaanFisik->bibir }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label" for="">Lidah</label>
                <input type="text" name="lidah" id="lidah" class="form-control" value="{{ $pemeriksaanFisik->lidah }}">
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <h4>TENGGOROKAN</h4>
        </div>
        <div class="col-md-2">
            <label class="form-check-label h-3 fw-bold" for="">Tenggorakan &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">TAK &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="tenggorokan" class="form-check-input" id="tenggorokan" {{ $pemeriksaanFisik->tenggorokan ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-10 pt-2">
            @foreach ($participants->getTenggorokan()['kanan'] as $item)
                <div class="form-check form-check-inline">
                    <input class="form-check-input tenggorokan" type="checkbox"
                        id="tenggorokanKanan{{ $item }}" data-kode="kanan" data-value="{{ $item }}" />
                    <label class="form-check-label"
                        for="tenggorokanKanan{{ $item }}">{{ $item }}</label>
                </div>
            @endforeach
            <div class="form-check form-check-inline" style="padding-left: 0px;">
                -
            </div>
            @foreach ($participants->getTenggorokan()['kiri'] as $item)
                <div class="form-check form-check-inline">
                    <input class="form-check-input tenggorokan" type="checkbox"
                        id="tenggorokanKiri{{ $item }}" data-kode="kiri" data-value="{{ $item }}" />
                    <label class="form-check-label"
                        for="tenggorokanKiri{{ $item }}">{{ $item }}</label>
                </div>
            @endforeach
        </div>
        <div class="col-md-12 mb-2">
            <label class="form-label" for=""></label>
            <input type="text" name="tenggorokan_text" id="tenggorokan_text" class="form-control" value="{{ $pemeriksaanFisik->tenggorokan_text }}">
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Faring</label>
                <select name="faring" id="faring" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getFaring() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->faring == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <h4>LEHER</h4>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Leher KGB</label>
                <select name="leher_kgb" id="leher_kgb" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getLeherKGB() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->leher_kgb == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Leher JVP</label>
                <select name="leher_jvp" id="leher_jvp" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getLeherJVP() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->leher_jvp == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <h4>THORAX JANTUNG</h4>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label" for="">Inspeksi</label>
                <input type="text" name="jantung_inspeksi" id="jantung_inspeksi" class="form-control" value="{{ $pemeriksaanFisik->jantung_inspeksi }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Auskultasi</label>
                <select name="jantung_auskultasi" id="jantung_auskultasi" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getAuskultasi() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->jantung_auskultasi == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Palpasi</label>
                <select name="jantung_palpasi" id="jantung_palpasi" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getPalpasi() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->jantung_palpasi == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Perkusi</label>
                <select name="jantung_perkusi" id="jantung_perkusi" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getPerkusi() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->jantung_perkusi == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <h4>THORAX PARU-PARU</h4>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Inspeksi</label>
                <select name="paru_inspeksi" id="paru_inspeksi" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getInspeksi() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->paru_inspeksi == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label" for="">Auskultasi Vasikuler</label>
                <input type="text" name="paru_auskultasi_vasikuler" id="paru_auskultasi_vasikuler"
                    class="form-control" value="{{ $pemeriksaanFisik->paru_auskultasi_vasikuler }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Auskultasi Ronkhi</label>
                <input type="text" name="paru_auskultasi_ronkhi" id="paru_auskultasi_ronkhi"
                    class="form-control" value="{{ $pemeriksaanFisik->paru_auskultasi_ronkhi }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Auskultasi Wheezing</label>
                <input type="text" name="paru_auskultasi_wheezing" id="paru_auskultasi_wheezing"
                    class="form-control" value="{{ $pemeriksaanFisik->paru_auskultasi_wheezing }}">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Palpasi</label>
                {{-- <select name="paru_palpasi" id="paru_palpasi" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getPalpasi() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->paru_palpasi == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select> --}}

                <input type="text" name="paru_palpasi" id="paru_palpasi"
                    class="form-control" value="{{ $pemeriksaanFisik->paru_palpasi }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Perkusi</label>
                <select name="paru_perkusi" id="paru_perkusi" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getPerkusi() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->paru_perkusi == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <h4>THORAX ABDOMEN</h4>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Inspeksi</label>
                <select name="abdomen_inspeksi" id="abdomen_inspeksi" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->abdomenInspeksi() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->abdomen_inspeksi == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Auskultasi</label>
                <select name="abdomen_auskultasi" id="abdomen_auskultasi" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getAuskultasi2() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->abdomen_auskultasi == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Palpasi</label>
                <input type="text" name="abdomen_palpasi" id="abdomen_palpasi" class="form-control" value="{{ $pemeriksaanFisik->abdomen_palpasi }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Perkusi</label>
                <select name="abdomen_perkusi" id="abdomen_perkusi" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getPerkusi2() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->abdomen_perkusi == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <h4>EXTREMITAS</h4>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Reflex Fisiologis Atas</label>
                <input type="text" name="reflex_fisiologis_atas" id="reflex_fisiologis_atas"
                    class="form-control" value="{{ $pemeriksaanFisik->reflex_fisiologis_atas }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Reflex Phatologis Atas</label>
                <input type="text" name="reflex_phatologis_atas" id="reflex_phatologis_atas"
                    class="form-control" value="{{ $pemeriksaanFisik->reflex_phatologis_atas }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Reflex Fisiologis Bawah</label>
                <input type="text" name="reflex_fisiologis_bawah" id="reflex_fisiologis_bawah"
                    class="form-control" value="{{ $pemeriksaanFisik->reflex_fisiologis_bawah }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label">Reflex Phatologis Bawah</label>
                <input type="text" name="reflex_phatologis_bawah" id="reflex_phatologis_bawah"
                    class="form-control" value="{{ $pemeriksaanFisik->reflex_phatologis_bawah }}">
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <h4>EKG</h4>
        </div>
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold" for="">EKG &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">TIDAK DIPERIKSA &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="ekg_tidak_diperiksa" class="form-check-input"
                        id="ekg_tidak_diperiksa" {{ $pemeriksaanFisik->ekg_tidak_diperiksa ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold text-white" for="">EKG &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">DBN &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="ekg_bdn" class="form-check-input" id="ekg_bdn" {{ $pemeriksaanFisik->ekg_bdn ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="" class="form-label"></label>
                <input type="text" name="ekg_text" id="ekg_text" class="form-control" value="{{ $pemeriksaanFisik->ekg_text }}">
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <h4>NEUROLOGIS</h4>
        </div>
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold" for="">NEUROLOGIS &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">TIDAK DIPERIKSA &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="neurologis_tidak_diperiksa" class="form-check-input"
                        id="neurologis_tidak_diperiksa" {{ $pemeriksaanFisik->neurologis_tidak_diperiksa ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold text-white" for="">NEUROLOGIS &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">DBN &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="neurologis_bdn" class="form-check-input" id="neurologis_bdn" {{ $pemeriksaanFisik->neurologis_bdn ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="" class="form-label"></label>
                <input type="text" name="neurologis_text" id="neurologis_text" class="form-control" value="{{ $pemeriksaanFisik->neurologis_text }}">
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <h4>ADMINISTRASI</h4>
        </div>
        <div class="col-md-3">
            <label class="form-label required" for="">Petugas Pemeriksa</label>
            <select name="employee_id" id="employee_id" class="form-control form-select" required>
                @foreach($employees as $employee)
                   <option  value="{{ $employee->id }}" {{$employee->id == $pemeriksaanFisik->employee?->id ? "selected" : ''  }}>{{ $employee?->nama }}</option>
                @endforeach
                {{-- <option value="{{ $pemeriksaanFisik->employee?->id }}">{{ $pemeriksaanFisik->employee?->nama }}</option> --}}
            </select>
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold text-white" for=""> &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Fisik Diperiksa &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="fisik_diperiksa" class="form-check-input" id="fisik_diperiksa" {{ $pemeriksaanFisik->fisik_diperiksa ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold text-white" for=""> &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Selesai &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="selesai" class="form-check-input" id="selesai" {{ $pemeriksaanFisik->selesai ? 'checked' : null }} />
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold text-white" for=""> &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Visus Diperiksa &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="visus_diperiksa" class="form-check-input" id="visus_diperiksa" {{ $pemeriksaanFisik->visus_diperiksa ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold text-white" for=""> &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Selesai Visus &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="selesai_visus" class="form-check-input" id="selesai_visus" {{ $pemeriksaanFisik->selesai_visus ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold text-white" for=""> &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Lab Diperiksa &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="lab_diperiksa" class="form-check-input" id="lab_diperiksa" {{ $pemeriksaanFisik->lab_diperiksa ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold text-white" for=""> &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Radiologi Diperiksa &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="radiologi_diperiksa" class="form-check-input"
                        id="radiologi_diperiksa" {{ $pemeriksaanFisik->radiologi_diperiksa ? 'checked' : null }} />
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold text-white" for=""> &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Audiometri Diperiksa &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="audiometri_diperiksa" class="form-check-input"
                        id="audiometri_diperiksa" {{ $pemeriksaanFisik->audiometri_diperiksa ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold text-white" for=""> &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">EKG Diperiksa &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="ekg_diperiksa" class="form-check-input" id="ekg_diperiksa" {{ $pemeriksaanFisik->ekg_diperiksa ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold text-white" for=""> &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Spiro Diperiksa &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="spiro_diperiksa" class="form-check-input" id="spiro_diperiksa" {{ $pemeriksaanFisik->spiro_diperiksa ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-check-label h-3 fw-bold text-white" for=""> &nbsp;</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Rectal Diperiksa &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="rectal_diperiksa" class="form-check-input" id="rectal_diperiksa" {{ $pemeriksaanFisik->rectal_diperiksa ? 'checked' : null }} />
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <h4>HASIL MCU</h4>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="" class="form-label required">Kesimpulan</label>
                <select name="kesimpulan" id="kesimpulan" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getKesimpulan() as $key => $item)
                        <option value="{{ $key }}" {{ $pemeriksaanFisik->kesimpulan == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="" class="form-label">Saran</label>
                <textarea name="saran" id="saran" cols="30" rows="2" class="form-control">{{ $pemeriksaanFisik->saran }}</textarea>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
    <button type="button" class="btn btn-info" onclick="window.open('{{ route('report.pemeriksaan.fisik', $participant->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">Print</button>
    <button type="submit" class="btn btn-primary" id="submit-edit-detail">Sumbit</button>
</div>

<script>
document.getElementById('nilai_normal').addEventListener('click', function (e) {
    console.log(e)
    document.getElementById('keadaan_umum').value = 'Compost Mentis';
    document.getElementById('kepala').checked = true;
    document.getElementById('kepala_text').value = 'Normochepal';
    document.getElementById('hidung').value = 'Polip ( - / - )';
    document.getElementById('mata').value = 'CA( - / - ) SI( - / - )';
    document.getElementById('pupil').value = 'ISOKOR';
    document.getElementById('visus').value = 'Mata Kanan 6/6 , Mata Kiri 6/6';
    document.getElementById('buta_warna').value = 'NORMAL';
    document.getElementById('kelainan_telinga').value = 'NORMAL';
    document.getElementById('gigi').value = 'NORMAL';
    document.getElementById('telinga').value = 'Serumen ( - / - )';
     document.getElementById('tenggorokan').checked = true;
    document.getElementById('tenggorokan_text').value = "TAK";
    document.getElementById('bibir').value = "Sianosis ( - )";
    document.getElementById('lidah').value = "Tyfoid tang ( - )";
    document.getElementById('jantung_inspeksi').value = "IC Tidak Terlihat ( - )";

    document.getElementById('faring').value = 'TIDAK HIPEREMIS';

    document.getElementById('jantung_auskultasi').value = 'BJ I + BJ II NORMAL';
    document.getElementById('jantung_palpasi').value = 'IC TERABA';
    document.getElementById('jantung_perkusi').value = 'DALL';
    document.getElementById('leher_kgb').value = 'PEMBESARAN -';
    document.getElementById('leher_jvp').value = 'MENINGKAT -';

    document.getElementById('paru_inspeksi').value = 'Pergerakan Dada Simetris';

    document.getElementById('paru_auskultasi_vasikuler').value = '+ / +';

    document.getElementById('paru_auskultasi_ronkhi').value = '- / -';
    document.getElementById('paru_auskultasi_wheezing').value = '- / -';
    document.getElementById('paru_palpasi').value = 'Revitasi ( - )';
    document.getElementById('paru_perkusi').value = 'SONOR';
    document.getElementById('abdomen_inspeksi').value = 'SUPEL';
    document.getElementById('abdomen_auskultasi').value = 'BISING USUS NORMAL';
    document.getElementById('abdomen_palpasi').value = 'Nyeri Tekan Epigastrium ( - )';
    document.getElementById('abdomen_perkusi').value = 'TIMPANI';

    //
    document.getElementById('reflex_fisiologis_atas').value = '+ / +';
    document.getElementById('reflex_fisiologis_bawah').value = '+ / +';
    document.getElementById('reflex_phatologis_atas').value = '- / -';
    document.getElementById('reflex_phatologis_bawah').value = '- / -';
    //ekg_bdn
    document.getElementById('ekg_bdn').checked = true;
    document.getElementById('neurologis_bdn').checked = true;
    document.getElementById('fisik_diperiksa').checked = true;
    document.getElementById('selesai').checked = true;
    document.getElementById('ekg_text').value = 'BDN';
    document.getElementById('neurologis_text').value = 'BDN';
});

</script>


<script>
    // Menggunakan querySelector
    var selectElement = document.querySelector('.employee_id');
    selectElement.value = "1";
    // Jika perlu memicu event 'change'
    var event = new Event('change');
    selectElement.dispatchEvent(event);
</script>

