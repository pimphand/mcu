@csrf
@method('put')
<div class="row">
    <div class="col-12">
        <h3>Biodata</h3>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label" for="">MCU ID</label>
            <input type="text" name="mcu_id" value="{{ $participant->code }}" class="form-control"
                placeholder="autogenerate" readonly>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label required" for="">NIK</label>
            <input type="text" name="nik" id="nik" value="{{ $participant->nik }}" class="form-control"
                placeholder="input..">
            <div class="invalid-feedback" id="msg-nik">Tidak boleh kosong.</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label required" for="">nama</label>
            <input type="text" name="name" id="name" value="{{ $participant->name }}" class="form-control"
                placeholder="input..">
            <div class="invalid-feedback" id="msg-name">Tidak boleh kosong.</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label required" for="">Jenis Kelamin</label>
            <select name="gender" id="gender" class="form-control form-select">
                @foreach ($participants->getJK() as $key => $item)
                    <option value="{{ $key }}"
                        {{ old('gender', $participant->gender) == $key ? 'selected' : '' }}>
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
            <input type="date" name="birthday" id="birthday" value="{{ $participant->birthday }}"
                class="form-control" placeholder="input..">
            <div class="invalid-feedback" id="msg-birthday">Tidak boleh kosong</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label" for="">Telp. Seluler</label>
            <input type="tel" name="phone" id="phone" value="{{ $participant->phone }}" class="form-control"
                placeholder="input..">
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
                <option value="{{ $participant->divisi?->id }}">{{ $participant->divisi?->name }}</option>
            </select>
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label required" for="">Department</label>
            <select name="department_id" id="department_id" class="form-select select2" required>
                <option value="{{ $participant->department?->id }}">{{ $participant->department?->name }}</option>
            </select>
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label" for="">Status Karyawan</label>
            <select name="status" id="status" class="form-select select2">
                <option value="">Pilih</option>
                @foreach ($participants->getStatusKaryawan() as $key => $item)
                    <option value="{{ $key }}"
                        {{ old('status', $participant->status) == $key ? 'selected' : '' }}>
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
            <input type="text" value="{{ $participant->client?->code }}" class="form-control" readonly>
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label required" for="">Kontrak ID</label>
            <input type="text" value="{{ $participant->contract?->code }}" class="form-control" readonly>
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
            <input type="checkbox" name="plan_u" class="form-check-input plan-x" id="plan_u"
                {{ $participant->plan_u ? 'checked' : '' }} />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">A &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="plan_a" class="form-check-input plan-x" id="plan_a"
                {{ $participant->plan_a ? 'checked' : '' }} />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">E &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="plan_e" class="form-check-input plan-x" id="plan_e"
                {{ $participant->plan_e ? 'checked' : '' }} />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">S &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="plan_s" class="form-check-input plan-x" id="plan_s"
                {{ $participant->plan_s ? 'checked' : '' }} />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">R &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="plan_r" class="form-check-input plan-x" id="plan_r"
                {{ $participant->plan_r ? 'checked' : '' }} />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Lab Khusus &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="lab_special" class="form-check-input plan-x" id="lab_special"
                {{ $participant->lab_special ? 'checked' : '' }} />
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label" for="">Nama Paket</label>
            <input type="text" name="packet_name" id="packet_name" value="{{ $participant->packet_name }}"
                class="form-control" placeholder="input..">
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-label" for="">Plant</label>
            <input type="text" name="plan_name" id="plan_name" value="{{ $participant->plan_name }}" class="form-control" placeholder="input..">
            <div class="invalid-feedback">Please select a valid state.</div>
        </div>
    </div>
</div>
<div class="row mt-2">
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Paket A &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="packet_a" class="form-check-input packet-x" id="packet_a" data-packet="a"
                {{ $participant->packet_a ? 'checked' : '' }} />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Paket B &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="packet_b" class="form-check-input packet-x" id="packet_b" data-packet="b"
                {{ $participant->packet_b ? 'checked' : '' }} />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Paket C &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="packet_c" class="form-check-input packet-x" id="packet_c" data-packet="c"
                {{ $participant->packet_c ? 'checked' : '' }} />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Paket D &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="packet_d" class="form-check-input packet-x" id="packet_d" data-packet="d"
                {{ $participant->packet_d ? 'checked' : '' }} />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Paket E &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="packet_e" class="form-check-input packet-x" id="packet_e" data-packet="e"
                {{ $participant->packet_e ? 'checked' : '' }} />
        </div>
    </div>
    <div class="col-md-2 d-flex">
        <label class="form-check-label h-3 fw-bold" for="">Paket F &nbsp;</label>
        <div class="form-check form-check-success form-switch ml-2">
            <input type="checkbox" name="packet_f" class="form-check-input packet-x" id="packet_f" data-packet="f"
                {{ $participant->packet_f ? 'checked' : '' }} />
        </div>
    </div>
</div>
