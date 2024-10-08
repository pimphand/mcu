@php
    $ekg = $participant->ekg;
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal-edit-detail-title">
        {{ 'EKG MCU ID : ' . $participant->code . ' | ' . $participant->name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    @csrf
    @method('put')
    <div class="row">
        <div class="col-md-3">
            <div class="form-group d-flex mt-2">
                <label class="form-check-label h-3 fw-bold" for="">Takikardi
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="takikardi" class="form-check-input" id="takikardi" {{ $ekg->takikardi ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group d-flex mt-2">
                <label class="form-check-label h-3 fw-bold" for="">Bradikardi
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="bradikardi" class="form-check-input" id="bradikardi" {{ $ekg->bradikardi ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group d-flex mt-2">
                <label class="form-check-label h-3 fw-bold" for="">Aritmia
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="aritmia" class="form-check-input" id="aritmia" {{ $ekg->aritmia ? 'checked' : null }} />
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group d-flex mt-2">
                <label class="form-check-label h-3 fw-bold" for="">Aresst
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="aresst" class="form-check-input" id="aresst" {{ $ekg->aresst ? 'checked' : null }} />
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-4">
            <div class="form-group">
                <label for="" class="form-label">Penemuan Lain</label>
                <input type="text" name="penemuan_lain" id="penemuan_lain" class="form-control" value="{{ $ekg->penemuan_lain }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group d-flex mt-2">
                <label class="form-check-label h-3 fw-bold" for="">Keadaan Jantung Normal
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="keadaan_jantung_normal" class="form-check-input"
                        id="keadaan_jantung_normal" {{ $ekg->keadaan_jantung_normal ? 'checked' : null }} />
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-12">
            <div class="form-group">
                <label for="" class="form-label required">Kesimpulan EKG</label>
                <input type="text" name="kesimpulan" id="kesimpulan" class="form-control" value="{{ $ekg->kesimpulan }}">
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-4">
            <div class="form-group">
                <label class="form-label required" for="">Petugas Pemeriksa</label>
                <select name="employee_id" id="" class="form-control form-select employee_id" required>
                    @foreach($employees as $employee)
                        <option  value="{{ $employee->id }}" {{$employee->id == $ekg->employee?->id ? "selected" : ''  }}>{{ $employee?->nama }}</option>
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
                    <input type="checkbox" name="selesai" class="form-check-input" id="selesai" {{ $ekg->selesai ? 'checked' : null }} />
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
    <button type="button" class="btn btn-info" id="" onclick="window.open('{{ route('report.ekg', $participant->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">Print</button>
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
