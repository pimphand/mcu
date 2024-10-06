<div class="modal-header">
    <h5 class="modal-title" id="modal-edit-detail-title">
        {{ 'Unggah Foto Via Komputer | MCU ID : ' . $participant->code . ' | ' . $participant->name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body" style="height: 300px;">
    @csrf
    @method('put')
    <div class="row justify-content-center my-5">
        <div class="col-md-4">
            <div class="form-group">
                <label for="" class="form-label">Foto Peserta</label>
                <input type="file" name="photo" id="photo" accept="image/*" class="form-control-file">
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
    <button type="submit" class="btn btn-primary" id="submit-edit-detail">Sumbit</button>
</div>
