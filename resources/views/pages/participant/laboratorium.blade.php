@php
    $laboratorium = $participant->laboratorium;
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal-edit-detail-title">
        {{ 'Laboratorium MCU ID : ' . $participant->code . ' | ' . $participant->name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    @csrf
    @method('put')
    <div class="row">
        <div class="col-12">
            <p class="text-danger">Catatan : Format desimal menggunakan tanda titik contoh :1,5 di tulis 1.5 | Angka
                bulat tidak menggunakan pemisah, contoh : 1000000</p>
        </div>
    </div>
    <div class="row">
        <div class="col-3 bg-light py-2">
            <div class="row">
                <h5>HEMATOLOGI</h5>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Hemoglobin</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="hemoglobin" id="hemoglobin" value="{{ $laboratorium?->hemoglobin }}" class="form-control">
                </div>
                <div class="col-md-4">
                    P: 12 - 16 , L: 14 - 18 g/dl
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Hematokrit</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="hematokrit" id="hematokrit" value="{{ $laboratorium?->hematokrit }}" class="form-control">
                </div>
                <div class="col-md-4">
                    P: 35–45% , L: 40–50%
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Lekosit</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="lekosit" id="lekosit" value="{{ $laboratorium?->lekosit }}" class="form-control">
                </div>
                <div class="col-md-4">
                    5.000 / µl – 10.000 / µl
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Trombosit</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="trombosit" id="trombosit" value="{{ $laboratorium?->trombosit }}" class="form-control">
                </div>
                <div class="col-md-4">
                    150.000 – 450.000 / µl
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Eritrosit</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="eritrosit" id="eritrosit" value="{{ $laboratorium?->eritrosit }}" class="form-control">
                </div>
                <div class="col-md-4">
                    P: 4,0 – 5,5 juta / µl , L: 4,5 – 6,0 juta / µl
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Basofil</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="basofil" id="basofil" value="{{ $laboratorium?->basofil }}" class="form-control">
                </div>
                <div class="col-md-4">
                    0 – 1 %
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Eosinofil</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="eosinofil" id="eosinofil" value="{{ $laboratorium?->eosinofil }}" class="form-control">
                </div>
                <div class="col-md-4">
                    1 – 3 %
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">N Batang</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="batang" id="batang" value="{{ $laboratorium?->batang }}" class="form-control">
                </div>
                <div class="col-md-4">
                    2 – 6 %
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">N Segmen</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="segmen" id="segmen" value="{{ $laboratorium?->segmen }}" class="form-control">
                </div>
                <div class="col-md-4">
                    50 – 70 %
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Limfosit</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="limfosit" id="limfosit" value="{{ $laboratorium?->limfosit }}" class="form-control">
                </div>
                <div class="col-md-4">
                    20 – 40 %
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Monosit</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="monosit" id="monosit" value="{{ $laboratorium?->monosit }}" class="form-control">
                </div>
                <div class="col-md-4">
                    2 – 6%
                </div>
            </div>
        </div>
        <div class="col-3 bg-light-secondary py-2">
            <div class="row">
                <h5>KIMIA KLINIK</h5>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">SGOT</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="sgot" id="sgot" value="{{ $laboratorium?->sgot }}" class="form-control">
                </div>
                <div class="col-md-4">
                    P: < 31 µl, L: < 34 µl
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">SGPT</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="sgpt" id="sgpt" value="{{ $laboratorium?->sgpt }}" class="form-control">
                </div>
                <div class="col-md-4">
                    0 - 45 µl
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Ureum</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="ureum" id="ureum" value="{{ $laboratorium?->ureum }}" class="form-control">
                </div>
                <div class="col-md-4">
                    20 - 40 mg
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Creatinin</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="creatinin" id="creatinin" value="{{ $laboratorium?->creatinin }}" class="form-control">
                </div>
                <div class="col-md-4">
                    P: < 0.6 - 1.2 mg/dL, L: < 0.6 - 1.4 mg/dL
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-12">
                    <label for="" class="form-label required">Kesimpulan Hasil</label>
                    <textarea name="kesimpulan" id="kesimpulan" cols="30" rows="3" class="form-control">{{ $laboratorium?->kesimpulan }}</textarea>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <label class="form-label required" for="">Petugas Pemeriksa</label>
                    <select name="employee_id" id="" class="form-control form-select employee_id"
                        required>

                        @foreach($employees as $employee)
                        <option  value="{{ $employee->id }}" {{$employee->id == $laboratorium?->employee?->id ? "selected" : ''  }}>{{ $employee?->nama }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Please select a valid state.</div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="form-group d-flex">
                        <label class="form-check-label h-3 fw-bold" for="">Selesai
                            &nbsp;</label>
                        <div class="form-check form-check-success form-switch ml-2">
                            <input type="checkbox" name="selesai" class="form-check-input" id="selesai" {{ $laboratorium?->selesai ? 'checked' : null }} />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-3 bg-light py-2">
            <div class="row">
                <h5>KIMIA DARAH</h5>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Glukosa Puasa</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="glukosa_puasa" id="glukosa_puasa" value="{{ $laboratorium?->glukosa_puasa }}" class="form-control">
                </div>
                <div class="col-md-4">
                    0-110 mg/dl
                </div>
            </div>

            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Cholesterol Total </label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="cholesterol_total" id="cholesterol_total" value="{{ $laboratorium?->cholesterol_total }}" class="form-control">
                </div>
                <div class="col-md-4">
                    < 200 mg/dl
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Asam Urat</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="asam_urat" id="asam_urat" value="{{ $laboratorium?->asam_urat }}" class="form-control">
                </div>
                <div class="col-md-4">
                    P: < 5.7 mg/dl, L: < 7.0 mg/dl
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label">Glukosa Sewaktu</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="glukosa_sewaktu" id="glukosa_sewaktu" value="{{ $laboratorium?->glukosa_sewaktu }}" class="form-control">
                </div>
                <div class="col-md-4">
                    < 200 mg/dl
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label">Trigliserida</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="trigliserida" id="trigliserida" value="{{ $laboratorium?->trigliserida }}" class="form-control">
                </div>
                <div class="col-md-4">
                    < 220 mg/dl
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">HDL Cholesterol</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="hdl_cholesterol" id="hdl_cholesterol" value="{{ $laboratorium?->hdl_cholesterol }}" class="form-control">
                </div>
                <div class="col-md-4">
                    45-65 mg/dl
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">LDL Cholestero</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="ldl_cholestero" id="ldl_cholestero" value="{{ $laboratorium?->ldl_cholestero }}" class="form-control">
                </div>
                <div class="col-md-4">
                    30-160 mg/dl
                </div>
            </div>
        </div>
        <div class="col-3 bg-light-secondary py-2">
            <div class="row">
                <h5>URINE LENGKAP</h5>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Reduksi</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="reduksi" id="reduksi" value="{{ $laboratorium?->reduksi }}" class="form-control">
                </div>
                <div class="col-md-4">
                    Negatif
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Berat Jenis</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="berat_jenis" id="berat_jenis" value="{{ $laboratorium?->berat_jenis }}" class="form-control">
                </div>
                <div class="col-md-4">

                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">PH / Reaksi</label>
                </div>
                <div class="col-md-4">
                    <input type="number" name="ph_reaksi" id="ph_reaksi" value="{{ $laboratorium?->ph_reaksi }}" class="form-control">
                </div>
                <div class="col-md-4">

                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Warna</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="warna" id="warna" value="{{ $laboratorium?->warna }}" class="form-control">
                </div>
                <div class="col-md-4">

                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Kekeruhan</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="kekeruhan" id="kekeruhan" value="{{ $laboratorium?->kekeruhan }}" class="form-control">
                </div>
                <div class="col-md-4">

                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Urobilinogen</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="urobilinogen" id="urobilinogen" value="{{ $laboratorium?->urobilinogen }}" class="form-control">
                </div>
                <div class="col-md-4">
                    Normal
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Bilirubin</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="bilirubin" id="bilirubin" value="{{ $laboratorium?->bilirubin }}" class="form-control">
                </div>
                <div class="col-md-4">
                    Negatif
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Eritrosit</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="eritrosit_urine" id="eritrosit_urine" value="{{ $laboratorium?->eritrosit_urine }}" class="form-control">
                </div>
                <div class="col-md-4">
                    Negatif
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Keton</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="keton" id="keton" value="{{ $laboratorium?->keton }}" class="form-control">
                </div>
                <div class="col-md-4">
                    Negatif
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Protein</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="protein" id="protein" value="{{ $laboratorium?->protein }}" class="form-control">
                </div>
                <div class="col-md-4">
                    Negatif
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Sedimen – Epitel</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="sedimen_epitel" id="sedimen_epitel" value="{{ $laboratorium?->sedimen_epitel }}" class="form-control">
                </div>
                <div class="col-md-4">
                    Negatif
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Sedimen – Eritrosit</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="sedimen_eritrosit" id="sedimen_eritrosit" value="{{ $laboratorium?->sedimen_eritrosit }}" class="form-control">
                </div>
                <div class="col-md-4">
                    0 – 1 / Lpb
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Sedimen – Leukosit</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="sedimen_leukosit" id="sedimen_leukosit" value="{{ $laboratorium?->sedimen_leukosit }}" class="form-control">
                </div>
                <div class="col-md-4">
                    0 – 5 / Lpb
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Sedimen - Bakteri</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="sedimen_bakteri" id="sedimen_bakteri" value="{{ $laboratorium?->sedimen_bakteri }}" class="form-control">
                </div>
                <div class="col-md-4">
                    Negatif
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-4">
                    <label for="" class="form-label required">Sedimen – Kristal</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="sedimen_kristal" id="sedimen_kristal" value="{{ $laboratorium?->sedimen_kristal }}" class="form-control">
                </div>
                <div class="col-md-4">
                    Negatif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-danger _btn_danger" data-bs-dismiss="modal">Batal</button>
    <button type="button" class="btn btn-success" id="" onclick="window.open('{{ route('report.laboratorium.lab.driver', $participant->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">Print Lab Driver</button>
    <button type="button" class="btn btn-warning" id="" onclick="window.open('{{ route('report.laboratorium.sgpt.ureum', $participant->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">Print SGBT & Ureum</button>
    <button type="button" class="btn btn-info" id="" onclick="window.open('{{ route('report.laboratorium', $participant->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">Print</button>
    <button type="submit" class="btn btn-primary" id="submit-edit-detail">Sumbit</button>
</div>

<script>
    // Menggunakan querySelector
    var selectElement = document.querySelector('.employee_id');  // Mengambil elemen pertama dengan class 'employee_id'
    selectElement.value = "2";
    // Jika perlu memicu event 'change'
    var event = new Event('change');
    selectElement.dispatchEvent(event);
</script>
