@php
    $radiologi = $participant->radiologi;
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal-edit-detail-title">
        {{ 'Radiologi MCU ID ' . $participant->code . ' | ' . $participant->name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    @csrf
    @method('put')
    <div class="row">
        <div class="col-md-12 mb-2">
            <div class="form-group">
                <label for="" class="form-label">COR</label>
                <textarea name="cor" id="cor" cols="30" rows="2" class="form-control">{{ $radiologi->cor }}</textarea>
            </div>
        </div>
        <div class="col-md-6 mb-2">
            <div class="form-group">
                <label for="" class="form-label">Diafragma Sinus</label>
                <textarea name="diafragma_sinus" id="diafragma_sinus" cols="30" rows="2" class="form-control">{{ $radiologi->diafragma_sinus }}</textarea>
            </div>
        </div>
        <div class="col-md-6 mb-2">
            <div class="form-group">
                <label for="" class="form-label">Pulmo</label>
                <textarea name="pulmo" id="pulmo" cols="30" rows="2" class="form-control">{{ $radiologi->pulmo }}</textarea>
            </div>
        </div>
        <div class="col-md-6 mb-2">
            <div class="form-group">
                <label for="" class="form-label">Kesan</label>
                <textarea name="kesan" id="kesan" cols="30" rows="2" class="form-control">{{ $radiologi->kesan }}</textarea>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="form-group">
                <label class="form-label required" for="">Petugas Pemeriksa</label>
                <select name="employee_id"  class="form-control employee_id" required>
                    @foreach ($employees as  $employee)
                        <option value="{{ $employee->id }}" {{$employee->id == $radiologi->employee?->id ? "selected" : ''  }}>{{ $employee->nama }}</option>
                    @endforeach

                </select>
                <div class="invalid-feedback">Please select a valid state.</div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Selesai
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="selesai" class="form-check-input" id="selesai" {{ $radiologi->selesai ? 'checked' : null }} />
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
    <button type="button" class="btn btn-info" onclick="window.open('{{ route('report.radiologi', $participant->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">Print</button>
    <button type="submit" class="btn btn-primary" id="submit-edit-detail">Sumbit</button>
</div>


<script>
  var selectElement = document.querySelector('.employee_id');
    selectElement.value = "4";
    // Jika perlu memicu event 'change'
    var event = new Event('change');
    selectElement.dispatchEvent(event);
</script>
