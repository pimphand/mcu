<div class="modal-header">
    <h5 class="modal-title" id="modal-edit-detail-title">
        Filter Data Peserta MCU {{ session('client_code') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <h3>Biodata</h3>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label class="form-label required" for="">NIK</label>
                <input type="text" name="nik" id="nik" class="form-control"
                    value="{{ request()->get('nik') }}" placeholder="input..">
                <div class="invalid-feedback" id="msg-nik">Tidak boleh kosong.</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label class="form-label required" for="">nama</label>
                <input type="text" name="name" id="name" class="form-control"
                    value="{{ request()->get('name') }}" placeholder="input..">
                <div class="invalid-feedback" id="msg-name">Tidak boleh kosong.</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label class="form-label required" for="">Jenis Kelamin</label>
                <select name="gender" id="gender" class="form-control form-select">
                    <option value="">Pilih</option>
                    @foreach ($participant->getJK() as $key => $item)
                        <option value="{{ $key }}"
                            {{ old('gender', request()->get('gender')) == $key ? 'selected' : '' }}>
                            {{ $item }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback" id="msg-gender">Tidak boleh kosong</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label class="form-label required" for="">Tanggal Lahir</label>
                <input type="date" name="birthday" id="birthday" class="form-control"
                    value="{{ request()->get('birthday') }}" placeholder="input..">
                <div class="invalid-feedback" id="msg-birthday">Tidak boleh kosong</div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12">
            <h3>Paket MCU</h3>
        </div>
        <div class="col-md-2 d-flex">
            <label class="form-check-label h-3 fw-bold" for="">U &nbsp;</label>
            <div class="form-check form-check-success form-switch ml-2">
                <input type="checkbox" name="plan_u" class="form-check-input plan-x" id="plan_u"
                    {{ request()->get('plan_u') ? 'checked' : '' }} />
            </div>
        </div>
        <div class="col-md-2 d-flex">
            <label class="form-check-label h-3 fw-bold" for="">A &nbsp;</label>
            <div class="form-check form-check-success form-switch ml-2">
                <input type="checkbox" name="plan_a" class="form-check-input plan-x" id="plan_a"
                    {{ request()->get('plan_a') ? 'checked' : '' }} />
            </div>
        </div>
        <div class="col-md-2 d-flex">
            <label class="form-check-label h-3 fw-bold" for="">E &nbsp;</label>
            <div class="form-check form-check-success form-switch ml-2">
                <input type="checkbox" name="plan_e" class="form-check-input plan-x" id="plan_e"
                    {{ request()->get('plan_e') ? 'checked' : '' }} />
            </div>
        </div>
        <div class="col-md-2 d-flex">
            <label class="form-check-label h-3 fw-bold" for="">S &nbsp;</label>
            <div class="form-check form-check-success form-switch ml-2">
                <input type="checkbox" name="plan_s" class="form-check-input plan-x" id="plan_s"
                    {{ request()->get('plan_s') ? 'checked' : '' }} />
            </div>
        </div>
        <div class="col-md-2 d-flex">
            <label class="form-check-label h-3 fw-bold" for="">R &nbsp;</label>
            <div class="form-check form-check-success form-switch ml-2">
                <input type="checkbox" name="plan_r" class="form-check-input plan-x" id="plan_r"
                    {{ request()->get('plan_r') ? 'checked' : '' }} />
            </div>
        </div>
        <div class="col-md-2 d-flex">
            <label class="form-check-label h-3 fw-bold" for="">Lab Khusus &nbsp;</label>
            <div class="form-check form-check-success form-switch ml-2">
                <input type="checkbox" name="lab_special" class="form-check-input plan-x" id="lab_special"
                    {{ request()->get('lab_special') ? 'checked' : '' }} />
            </div>
        </div>
    </div>
    @if (!request('is_register_page'))
        <div class="row mt-3">
            <div class="col-md-2 d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Paket A &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="packet_a" class="form-check-input packet-x" id="packet_a"
                        data-packet="a" {{ request()->get('packet_a') ? 'checked' : '' }} />
                </div>
            </div>
            <div class="col-md-2 d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Paket B &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="packet_b" class="form-check-input packet-x" id="packet_b"
                        data-packet="b" {{ request()->get('packet_b') ? 'checked' : '' }} />
                </div>
            </div>
            <div class="col-md-2 d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Paket C &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="packet_c" class="form-check-input packet-x" id="packet_c"
                        data-packet="c" {{ request()->get('packet_c') ? 'checked' : '' }} />
                </div>
            </div>
            <div class="col-md-2 d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Paket D &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="packet_d" class="form-check-input packet-x" id="packet_d"
                        data-packet="d" {{ request()->get('packet_d') ? 'checked' : '' }} />
                </div>
            </div>
            <div class="col-md-2 d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Paket E &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="packet_e" class="form-check-input packet-x" id="packet_e"
                        data-packet="e" {{ request()->get('packet_e') ? 'checked' : '' }} />
                </div>
            </div>
            <div class="col-md-2 d-flex">
                <label class="form-check-label h-3 fw-bold" for="">Paket F &nbsp;</label>
                <div class="form-check form-check-success form-switch ml-2">
                    <input type="checkbox" name="packet_f" class="form-check-input packet-x" id="packet_f"
                        data-packet="f" {{ request()->get('packet_f') ? 'checked' : '' }} />
                </div>
            </div>
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
    <button type="submit" class="btn btn-primary">Cari Peserta</button>
</div>
