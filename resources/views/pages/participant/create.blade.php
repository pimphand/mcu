@csrf
<div class="row">
    <div class="col-12">
        <h3>Biodata</h3>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label" for="">MCU ID</label>
            <input type="text" name="mcu_id" class="form-control" placeholder="autogenerate" readonly>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label required" for="">NIK</label>
            <input type="text" name="nik" id="nik" class="form-control" placeholder="input..">
            <div class="invalid-feedback" id="msg-nik">Tidak boleh kosong.</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label required" for="">nama</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="input..">
            <div class="invalid-feedback" id="msg-name">Tidak boleh kosong.</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label required" for="">Jenis Kelamin</label>
            <select name="gender" id="gender" class="form-control form-select">
                @foreach ($participant->getJK() as $key => $item)
                    <option value="{{ $key }}" {{ old('gender') == $key ? 'selected' : '' }}>
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
            <input type="date" name="birthday" id="birthday" class="form-control" placeholder="input..">
            <div class="invalid-feedback" id="msg-birthday">Tidak boleh kosong</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label" for="">Telp. Seluler</label>
            <input type="tel" name="phone" id="phone" class="form-control" placeholder="input..">
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <h3>Kepegawaian</h3>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label required" for="">Divisi</label>
            <select name="divisi_id" id="divisi_id" class="form-control form-select" required>
            </select>
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label required" for="">Department</label>
            <select name="department_id" id="department_id" class="form-select select2" required>
            </select>
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label" for="">Status Karyawan</label>
            <select name="status" id="status" class="form-select select2">
                <option value="">Pilih</option>
                @foreach ($participant->getStatusKaryawan() as $key => $item)
                    <option value="{{ $key }}" {{ old('status') == $key ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label required" for="">Perusahaan</label>
            <input type="hidden" name="client_id" id="client_id" value="{{ session('client_id') }}"
                class="form-control" placeholder="input..">
            <input type="text" value="{{ session('client_code') }}" class="form-control" readonly>
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label required" for="">Kontrak ID</label>
            <input type="hidden" name="contract_id" id="contract_id" value="{{ session('contract_id') }}"
                class="form-control" placeholder="input..">
            <input type="text" value="{{ session('contract_code') }}" class="form-control" readonly>
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <h3>Paket MCU</h3>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">U &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="plan_u" class="form-check-input plan-x" id="plan_u" />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">A &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="plan_a" class="form-check-input plan-x" id="plan_a" />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">E &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="plan_e" class="form-check-input plan-x" id="plan_e" />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">S &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="plan_s" class="form-check-input plan-x" id="plan_s" />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">R &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="plan_r" class="form-check-input plan-x" id="plan_r" />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Lab Khusus &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="lab_special" class="form-check-input plan-x" id="lab_special" />
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label" for="">Nama Paket</label>
            <input type="text" name="packet_name" id="packet_name" class="form-control" placeholder="input..">
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-label" for="">Plant</label>
            <input type="text" name="plan_name" id="plan_name" class="form-control" placeholder="input..">
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
</div>
<div class="row mt-2">
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Paket A &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="packet_a" class="form-check-input packet-x" id="packet_a"
                data-packet="a" />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Paket B &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="packet_b" class="form-check-input packet-x" id="packet_b"
                data-packet="b" />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Paket C &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="packet_c" class="form-check-input packet-x" id="packet_c"
                data-packet="c" />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Paket D &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="packet_d" class="form-check-input packet-x" id="packet_d"
                data-packet="d" />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Paket E &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="packet_e" class="form-check-input packet-x" id="packet_e"
                data-packet="e" />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Paket F &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="packet_f" class="form-check-input packet-x" id="packet_f"
                data-packet="f" />
        </div>
    </div>
</div>
