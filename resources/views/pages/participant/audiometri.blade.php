<div class="modal-header">
    <h5 class="modal-title" id="modal-edit-detail-title">
        {{ 'Audiometri MCU ID : ' . $participant->code . ' | ' . $participant->name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    @csrf
    @method('put')
    @php
        $audiometri = $participant->audiometri;
    @endphp
    <div class="row mb-2">
        <div class="col-12">
            <div class="form-group">
                <label for="form-label required">Audiometri Telinga Kanan</label>
                <input type="text" name="audiometri_telinga_kanan" id="audiometri_telinga_kanan"
                    class="form-control">
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label for="form-label required">Audiometri Telinga Kiri</label>
                <input type="text" name="audiometri_telinga_kiri" id="audiometri_telinga_kiri" class="form-control">
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-6">
            <div class="form-group">
                <label for="" class="form-label required">Pendengaran Telinga Kanan</label>
                <select name="pendengaran_telinga_kanan" id="pendengaran_telinga_kanan" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getPendengaranTelingaKanan() as $key => $item)
                        <option value="{{ $key }}" {{ $audiometri?->pendengaran_telinga_kanan == $key ? "selected" : ''}}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label for="" class="form-label required">Pendengaran Telinga Kiri</label>
                <select name="pendengaran_telinga_kiri" id="pendengaran_telinga_kiri" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getPendengaranTelingaKiri() as $kiri => $item)
                        <option value="{{ $kiri }}" {{ $audiometri?->pendengaran_telinga_kanan == $kiri ? "selected" : ''}}>{{ $kiri }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-12">
            <div class="form-group">
                <label for="form-label required">Kesimpulan Pem. Audiometri</label>
                <input type="text" name="kesimpulan" value="{{ $audiometri?->kesimpulan }}" id="kesimpulan" class="form-control">
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label for="form-label required">Saran Hasil Pem. Auidometri</label>
                <input type="text" name="saran" value="{{ $audiometri?->saran }}" id="saran" class="form-control">
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-md-4">
            <div class="form-group">
                <label class="form-label required" for="">Petugas Pemeriksa</label>
                <select name="employee_id" id="" class="form-control form-select employee_id" required>
                    @foreach($employees as $employee)
                        <option  value="{{ $employee->id }}" {{$employee->id == $audiometri?->employee?->id ? "selected" : ''  }}>{{ $employee?->nama }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Please select a valid state.</div>
            </div>
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-4">
            <div class="form-group d-flex mt-2">
                <label class="form-check-label h-3 fw-bold" for="">Selesai
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="selesai" {{ $audiometri?->selesai ? 'checked' : '' }} class="form-check-input" value="1" id="selesai" />
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
    <button type="button" class="btn btn-info" id=""
        onclick="window.open('{{ route('report.audiometri', $participant->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">Print</button>
    <button type="submit" class="btn btn-primary" id="submit-edit-detail">Sumbit</button>
</div>

<script>
    // Menggunakan querySelector
    var selectElement = document.querySelector('.employee_id');  // Mengambil elemen pertama dengan class 'employee_id'
    selectElement.value = "1";
    // Jika perlu memicu event 'change'
    var event = new Event('change');
    selectElement.dispatchEvent(event);
</script>
