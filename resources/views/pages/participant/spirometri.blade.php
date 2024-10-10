@php
    $spirometri = $participant->spirometri;
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal-edit-detail-title">
        {{ 'Spirometri MCU ID ' . $participant->code . ' | ' . $participant->name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    @csrf
    @method('put')
    <div class="row">
        <div class="col-12">
            <p>Hasil Pem. Spirometri</p>
        </div>
        <div class="col-md-2">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">NORMAL
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="normal" class="form-check-input hasil-spirometri" id="normal" {{ $spirometri?->normal ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">RESTRICTIVE
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="restrictive" class="form-check-input hasil-spirometri" id="restrictive" {{ $spirometri?->restrictive ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">OBSTRUCTIVE
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="obstructive" class="form-check-input hasil-spirometri" id="obstructive" {{ $spirometri?->obstructive ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">MIXED
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="mixed" class="form-check-input hasil-spirometri" id="mixed" {{ $spirometri?->mixed ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">LAINNYA
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="lainnya" class="form-check-input hasil-spirometri" id="lainnya" {{ $spirometri?->lainnya ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <input type="text" name="hasil" id="hasil" class="form-control" value="{{ $spirometri?->hasil }}">
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="form-group">
                <label class="form-label required" for="">Petugas Pemeriksa</label>
                <select name="employee_id" id="" class="employee_id form-control form-select" required>
                    @foreach($employees as $employee)
                        <option  value="{{ $employee->id }}" {{$employee->id == $spirometri?->employee?->id ? "selected" : ''  }}>{{ $employee?->nama }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Please select a valid state.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="" class="form-label">Retriksi</label>
                <select name="retriksi" id="retriksi" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getRetriksi() as $key => $item)
                        <option value="{{ $key }}" {{ $spirometri?->retriksi == $key ? 'selected' : null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="" class="form-label">Obstruksif</label>
                <select name="obstruksif" id="obstruksif" class="form-select">
                    <option value="">Pilih</option>
                    @foreach ($participants->getObstruksif() as $key => $item)
                        <option value="{{ $key }}" {{ $spirometri?->obstruksif == $key ? 'selected' :  null }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Selesai
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="selesai" class="form-check-input" id="selesai" {{ $spirometri?->selesai ? 'checked' : null }} />
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-danger _btn_danger" data-bs-dismiss="modal">Batal</button>
    <button type="button" class="btn btn-info" id="" onclick="window.open('{{ route('report.spirometri', $participant->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">Print</button>
    <button type="submit" class="btn btn-primary" id="submit-edit-detail">Submit</button>
</div>


<script>
    // Menggunakan querySelector
    var selectElement = document.querySelector('.employee_id');  // Mengambil elemen pertama dengan class 'employee_id'
    selectElement.value = "1";
    // Jika perlu memicu event 'change'
    var event = new Event('change');
    selectElement.dispatchEvent(event);
</script>
