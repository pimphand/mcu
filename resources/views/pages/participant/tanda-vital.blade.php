@php
    $tandaVital = $participant->tandaVital;
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal-edit-detail-title">
        {{ 'Tanda Vital MCU ID : ' . $participant->code . ' | ' . $participant->name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    @csrf
    @method('put')
    <div class="row">
        <div class="col-md-2">
            <label class="form-label required" for="">Keluhan Utama</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">TAK &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="keluhan_utama" class="form-check-input" id="keluhan_utama"
                        {{ $tandaVital?->keluhan_utama ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="" for=""></label>
                <input type="text" name="keluhan_utama_text" id="keluhan_utama_text" class="form-control"
                    value="{{ $tandaVital?->keluhan_utama_text }}">
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label required" for="">Riwayat Penyakit Sekarang</label><br>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">TAK &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="riwayat_penyakit_sekarang" class="form-check-input"
                        id="riwayat_penyakit_sekarang"
                        {{ $tandaVital?->riwayat_penyakit_sekarang ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="" for=""></label>
                <input type="text" name="riwayat_penyakit_sekarang_text" id="riwayat_penyakit_sekarang_text"
                    class="form-control" value="{{ $tandaVital?->riwayat_penyakit_sekarang_text }}">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2">
            <label class="form-label required" for="">Riwayat Penyakit Terdahulu</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="" style="font-size: 20px;">&CircleMinus;
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="riwayat_penyakit_terdahulu" class="form-check-input"
                        id="riwayat_penyakit_terdahulu"
                        {{ $tandaVital?->riwayat_penyakit_terdahulu ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="" for=""></label>
                <input type="text" name="riwayat_penyakit_terdahulu_text" id="riwayat_penyakit_terdahulu_text"
                    class="form-control" value="{{ $tandaVital?->riwayat_penyakit_terdahulu_text }}">
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label required" for="">Alergi</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">TIDAK &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="alergi" class="form-check-input" id="alergi"
                        {{ $tandaVital?->alergi ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="" for=""></label>
                <input type="text" name="alergi_text" id="alergi_text" class="form-control"
                    value="{{ $tandaVital?->alergi_text }}">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2">
            <label class="form-label required" for="">Merokok</label>
            <div class="form-group d-flex">
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="merokok" class="form-check-input" id="merokok"
                        {{ $tandaVital?->merokok ? 'checked' : null }} />
                    <label class="form-check-label h-3 fw-bold" for="">YA</label>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label required" for="">Konsumsi Alkohol</label>
            <div class="form-group d-flex">
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="konsumsi_alkohol" class="form-check-input" id="konsumsi_alkohol"
                        {{ $tandaVital?->konsumsi_alkohol ? 'checked' : null }} />
                    <label class="form-check-label h-3 fw-bold" for="">YA</label>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label required" for="">Riwayat Trauma</label>
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">TIDAK &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="riwayat_trauma" class="form-check-input" id="riwayat_trauma"
                        {{ $tandaVital?->riwayat_trauma ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="" for=""></label>
                <input type="text" name="riwayat_trauma_text" id="riwayat_trauma_text" class="form-control"
                    value="{{ $tandaVital?->riwayat_trauma_text }}">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label required" for="">Tinggi Badan</label>
                <input type="number" name="tinggi_badan" id="tinggi_badan" class="form-control"
                    value="{{ $tandaVital?->tinggi_badan }}">
                <span class="text-info">cm</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label required" for="">Berat Badan</label>
                <input type="number" name="berat_badan" id="berat_badan" class="form-control"
                    value="{{ $tandaVital?->berat_badan }}">
                <span class="text-info">kg</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label" for="">IMT</label>
                <input type="text" name="imt" id="imt" class="form-control"
                    value="{{ $tandaVital?->imt }}" readonly>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label required" for="">Nilai IMT</label>
                <input type="number" name="imt_nilai" id="imt_nilai" class="form-control"
                    value="{{ $tandaVital?->imt_nilai }}">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label required" for="">Tekanan Darah</label>
                <input type="number" name="tekanan_darah" id="tekanan_darah" class="form-control"
                    value="{{ $tandaVital?->tekanan_darah }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label required" for="">Frekuensi Nadi</label>
                <input type="number" name="frekuensi_nadi" id="frekuensi_nadi" class="form-control"
                    value="{{ $tandaVital?->frekuensi_nadi }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label required" for="">Suhu</label>
                <input type="number" name="suhu" id="suhu" class="form-control"
                    value="{{ $tandaVital?->suhu }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label required" for="">Frekuensi Nafas</label>
                <input type="number" name="frekuensi_nafas" id="frekuensi_nafas" class="form-control"
                    value="{{ $tandaVital?->frekuensi_nafas }}">
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-3">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">TTV Diperiksa &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="ttv_diperiksa" class="form-check-input" id="ttv_diperiksa"
                        {{ $tandaVital?->ttv_diperiksa ? 'checked' : null }} />
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Ibu Hamil &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="ibu_hamil" class="form-check-input" id="ibu_hamil"
                        {{ $tandaVital?->ibu_hamil ? 'checked' : null }} />
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Selesai &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="selesai" class="form-check-input" id="selesai"
                        {{ $tandaVital?->selesai ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label required" for="">Petugas Pemeriksa</label>
            <select name="employee_id" id="" class="form-control form-select employee_id" required>
                {{-- <option value="{{ $tandaVital?->employee?->id }}">{{ $tandaVital?->employee?->nama }}</option> --}}
                @foreach($employees as $employee)
                        <option  value="{{ $employee->id }}" {{$employee->id == $tandaVital?->employee?->id ? "selected" : ''  }}>{{ $employee?->nama }}</option>
                    @endforeach
            </select>
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-3">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Vaksin Hepatitis &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="vaksin_hepatitis" class="form-check-input" id="vaksin_hepatitis"
                        {{ $tandaVital?->vaksin_hepatitis ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Vaksin Tetanus &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="vaksin_tetanus" class="form-check-input" id="vaksin_tetanus"
                        {{ $tandaVital?->vaksin_tetanus ? 'checked' : null }} />
                </div>
            </div>
        </div>
    </div>

</div>
<div class="modal-footer">
    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
    <button type="submit" class="btn btn-primary" id="submit-edit-detail">Sumbit</button>
</div>


<script>
    // Menggunakan querySelector
    var selectElement = document.querySelector('.employee_id');
    selectElement.value = "1";
    // Jika perlu memicu event 'change'
    var event = new Event('change');
    selectElement.dispatchEvent(event);
</script>
