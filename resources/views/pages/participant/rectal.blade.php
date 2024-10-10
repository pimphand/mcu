@php
    $rectal = $participant->rectal;
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal-edit-detail-title">
        {{ 'Rectal MCU ID ' . $participant->code . ' | ' . $participant->name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    @csrf
    @method('put')
    <div class="row">
        <h5>RECTAL SWAB</h5>
    </div>
    <div class="row mb-1">
        <div class="col-md-2">
            <label for="" class="form-label required">Salmonella Thypi</label>
        </div>
        <div class="col-md-4">
            <input type="text" name="salmonella_thypi" id="salmonella_thypi" class="form-control" value="{{ $rectal?->salmonella_thypi }}">
        </div>
        <div class="col-md-4">
            Negatif
        </div>
    </div>
    <div class="row mb-1">
        <div class="col-md-2">
            <label for="" class="form-label required">Shigella SP</label>
        </div>
        <div class="col-md-4">
            <input type="text" name="shigella_sp" id="shigella_sp" class="form-control"value="{{ $rectal?->shigella_sp }}">
        </div>
        <div class="col-md-4">
            Negatif
        </div>
    </div>
    <div class="row mb-1">
        <div class="col-md-2">
            <label for="" class="form-label required">E. Coli Pathogen</label>
        </div>
        <div class="col-md-4">
            <input type="text" name="e_coli_pathogen" id="e_coli_pathogen" class="form-control" value="{{ $rectal?->e_coli_pathogen }}">
        </div>
        <div class="col-md-4">
            Negatif
        </div>
    </div>
    <div class="row mb-1">
        <div class="col-md-6">
            <div class="form-group">
                <label for="" class="form-label">Kesimpulan Hasil</label>
                <textarea name="kesimpulan" id="kesimpulan" cols="30" rows="2" class="form-control">{{ $rectal?->kesimpulan }}</textarea>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="form-label required" for="">Petugas Pemeriksa</label>
                <select name="employee_id" id="" class="form-control form-select employee_id" required>
                    {{-- <option value="{{ $rectal?->employee?->id }}">{{ $rectal?->employee?->nama }}</option> --}}
                    @foreach($employees as $employee)
                        <option  value="{{ $employee->id }}" {{$employee->id == $rectal?->employee?->id ? "selected" : ''  }}>{{ $employee?->nama }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Please select a valid state.</div>
            </div>
        </div>
    </div>
    <div class="row mb-1">
        <div class="col-md-12">
            <div class="form-group d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Selesai
                    &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="selesai" class="form-check-input" id="selesai" {{ $rectal?->selesai ? 'checked' : null }} />
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-danger _btn_danger" data-bs-dismiss="modal">Batal</button>
    <button type="button" class="btn btn-info" id="" onclick="window.open('{{ route('report.rectal', $participant->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">Print</button>
    <button type="submit" class="btn btn-primary" id="submit-edit-detail">Submit</button>
</div>


<script>
    // Menggunakan querySelector
    var selectElement = document.querySelector('.employee_id');
    selectElement.value = "2";
    // Jika perlu memicu event 'change'
    var event = new Event('change');
    selectElement.dispatchEvent(event);
</script>
