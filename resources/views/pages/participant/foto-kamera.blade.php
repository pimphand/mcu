<div class="modal-header">
    <h5 class="modal-title" id="modal-edit-detail-title">
        {{ 'Unggah Foto Via Kamera | MCU ID : ' . $participant->code . ' | ' . $participant->name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    @csrf
    @method('put')
    <div class="row justify-content-center my-2">
        <div class="col-md-4">
            <div id="my_result"></div>
            <p class="text-info">Hasil Tangkap foto muncul disini.</p>
            <input type="hidden" name="photo" id="photo">
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <div id="my_camera" style="width:420px; height:340px;"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <script src="{{ asset('webcam/webcam.js') }}"></script>
        <script language="JavaScript">
            function aktifkanKamera() {
                Webcam.attach('#my_camera');
                document.getElementById('button-snapshot').classList.remove('d-none');
                document.getElementById('tutup-kamera').classList.remove('d-none');
                document.getElementById('aktifkan-kamera').classList.add('d-none');
                document.getElementById('submit-edit-detail').classList.add('disabled');
            }

            function stopKamera() {
                navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false
                    })
                    .then(mediaStream => {
                        const stream = mediaStream;
                        const tracks = stream.getTracks();

                        tracks.forEach(track => track.stop())
                    })
                Webcam.reset();
                document.getElementById('button-snapshot').classList.add('d-none');
                document.getElementById('tutup-kamera').classList.add('d-none');
                document.getElementById('aktifkan-kamera').classList.remove('d-none');
            }

            function take_snapshot() {
                document.getElementById('submit-edit-detail').classList.remove('disabled');
                Webcam.snap(function(data_uri) {
                    document.getElementById('my_result').innerHTML = '<img src="' + data_uri + '"/>';
                    document.getElementById('photo').value = data_uri;
                });
            }
        </script>
    </div>
</div>
<div class="modal-footer">
    <a href="javascript:void(take_snapshot())" class="btn btn-outline-primary d-none" id="button-snapshot">Tankap
        Foto</a>
    <a href="javascript:void(stopKamera())" class="btn btn-outline-danger d-none" id="tutup-kamera">Tutup
        Kamera</a>
    <a href="javascript:void(aktifkanKamera())" class="btn btn-outline-info mr-2" id="aktifkan-kamera">Aktifkan
        Kamera</a>
    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
    <button type="submit" class="btn btn-primary" id="submit-edit-detail">Sumbit</button>
</div>
