<div class="row">
    <!-- User Sidebar -->
    <div class="col-xl-3 col-lg-4 col-md-4 order-1 order-md-0">
        <!-- User Card -->
        <div class="card">
            <div class="card-body">
                <div class="user-avatar-section">
                    <div class="d-flex align-items-center flex-column">
                        <img class="img-fluid rounded mt-2 mb-2"
                            src="{{ asset($participant->user?->photo ? $participant->user->photo : 'app-assets/images/portrait/small/avatar-s-2.jpg') }}"
                            height="110" width="110" alt="User avatar" />
                        <div class="user-info text-center">
                            <h4>{{ $participant->name }}</h4>
                            <span class="badge bg-light-secondary">{{ $participant->code }}</span>
                        </div>
                    </div>
                </div>
                <div class="info-container">
                    <ul class="list-unstyled">
                        <li class="mb-75">
                            <span>Usia</span><br>
                            <span class="fw-bolder me-25">{{
                                \Carbon\Carbon::parse($participant->birthday)->diff(\Carbon\Carbon::now('Asia/Jakarta'))->format('%y
                                tahun, %m bulan dan %d hari') }}</span>
                        </li>
                        <li class="mb-75">
                            <span>Tgl Lahir</span><br>
                            <span class="fw-bolder me-25">{{ $participant->birthday }}</span>
                        </li>
                        <li class="mb-75">
                            <span>Department</span><br>
                            <span class="fw-bolder me-25">{{ $participant->department?->name }}</span>
                        </li>
                        <li class="mb-75">
                            <span>Devisi</span><br>
                            <span class="fw-bolder me-25">{{ $participant->divisi?->name }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /User Card -->
    </div>
    <!--/ User Sidebar -->

    <!-- User Content -->
    <div class="col-xl-9 col-lg-8 col-md-8 order-0 order-md-1">
        <div class="row">
            <div class="col-md-4">
                <div class="card cursor-pointer edit-detail"
                    data-url="{{ route('participant.detail.tanda.vital', $participant->id) }}"
                    data-title="{{ 'Tanda Vital MCU ID ' . $participant->code . ' | ' . $participant->name }}">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-2">Tanda Vital</h3>
                            <p class="card-text" id="tandaVital">{{ $participant->tandaVital?->selesai ? 'SELESAI' : 'BELUM' }}</p>
                        </div>
                        <div class="avatar bg-light-primary p-50 m-0">
                            <div class="avatar-content">
                                <img class="w-100" src="{{ asset('images/dental-checkup.png') }}" alt="image">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="progress progress-bar-primary ">
                            <div class="progress-bar tandaVital" role="progressbar"
                                aria-valuenow="{{ $participant->tandaVital?->selesai ? 100 : '' }}"
                                aria-valuemin="{{ $participant->tandaVital?->selesai ? 100 :'' }}"
                                aria-valuemax="{{ $participant->tandaVital?->selesai ? 100 : '0' }}" @if($participant->
                                tandaVital?->selesai) style="width: 100%" @endif></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card cursor-pointer edit-detail"
                    data-url="{{ route('participant.detail.pemeriksaan.fisik', $participant->id) }}"
                    data-title="{{ 'Pemeriksaan Fisik MCU ID ' . $participant->code . ' | ' . $participant->name }}">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-2">Pemeriksaan Fisik</h3>
                            <p class="card-text" id="pemeriksaanFisik">{{ $participant->pemeriksaanFisik?->selesai ? 'SELESAI' : 'BELUM' }}
                            </p>
                        </div>
                        <div class="avatar bg-light-primary p-50 m-0">
                            <div class="avatar-content">
                                <img class="w-100" src="{{ asset('images/dental-checkup.png') }}" alt="image">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="progress progress-bar-info">
                            <div class="progress-bar pemeriksaanFisik" role="progressbar"
                                aria-valuenow="{{ $participant->pemeriksaanFisik?->selesai ? 100 : '' }}"
                                aria-valuemin="{{ $participant->pemeriksaanFisik?->selesai ? 100 :'' }}"
                                aria-valuemax="{{ $participant->pemeriksaanFisik?->selesai ? 100 : '0' }}"
                                @if($participant->pemeriksaanFisik?->selesai) style="width: 100%" @endif></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card cursor-pointer edit-detail"
                    data-url="{{ route('participant.detail.laboratorium', $participant->id) }}"
                    data-title="{{ 'Laboratorium MCU ID ' . $participant->code . ' | ' . $participant->name }}">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-2">Laboratorium</h3>
                            <p class="card-text" id="laboratorium">{{ $participant->laboratorium?->selesai ? 'SELESAI' : 'BELUM' }}</p>
                        </div>
                        <div class="avatar bg-light-primary p-50 m-0">
                            <div class="avatar-content">
                                <img class="w-100" src="{{ asset('images/dental-checkup.png') }}" alt="image">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="progress progress-bar-danger">
                            <div class="progress-bar laboratorium" role="progressbar"
                                aria-valuenow="{{ $participant->laboratorium?->selesai ? 100 : '' }}"
                                aria-valuemin="{{ $participant->laboratorium?->selesai ? 100 :'' }}"
                                aria-valuemax="{{ $participant->laboratorium?->selesai ? 100 : '0' }}"
                                @if($participant->laboratorium?->selesai) style="width: 100%" @endif></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card cursor-pointer edit-detail"
                    data-url="{{ route('participant.detail.radiologi', $participant->id) }}"
                    data-title="{{ 'Radiologi MCU ID ' . $participant->code . ' | ' . $participant->name }}">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-2">Radiologi</h3>
                            <p class="card-text" id="radiologi">{{ $participant->radiologi?->selesai ? 'SELESAI' : 'BELUM' }}</p>
                        </div>
                        <div class="avatar bg-light-primary p-50 m-0">
                            <div class="avatar-content">
                                <img class="w-100" src="{{ asset('images/dental-checkup.png') }}" alt="image">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="progress progress-bar-warning">
                            <div class="progress-bar radiologi" role="progressbar"
                                aria-valuenow="{{ $participant->radiologi?->selesai ? 100 : '' }}"
                                aria-valuemin="{{ $participant->radiologi?->selesai ? 100 :'' }}"
                                aria-valuemax="{{ $participant->radiologi?->selesai ? 100 : '0' }}" @if($participant->
                                radiologi?->selesai) style="width: 100%" @endif></div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($participant->plan_a)
            <div class="col-md-4">
                <div class="card cursor-pointer edit-detail"
                    data-url="{{ route('participant.detail.audiometri', $participant->id) }}"
                    data-title="{{ 'Audiometri MCU ID ' . $participant->code . ' | ' . $participant->name }}">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-2">Audiometri</h3>
                            <p class="card-text" id="audiometri">{{ $participant->audiometri?->selesai ? 'SELESAI' : 'BELUM' }}
                            </p>
                        </div>
                        <div class="avatar bg-light-primary p-50 m-0">
                            <div class="avatar-content">
                                <img class="w-100" src="{{ asset('images/dental-checkup.png') }}" alt="image">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="progress progress-bar-success">
                            <div class="progress-bar audiometri" role="progressbar"
                                aria-valuenow="{{ $participant->audiometri?->selesai ? 100 : '' }}"
                                aria-valuemin="{{ $participant->audiometri?->selesai ? 100 :'' }}"
                                aria-valuemax="{{ $participant->audiometri?->selesai ? 100 : '0' }}" @if($participant->
                                audiometri?->selesai) style="width: 100%" @endif></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if ($participant->plan_s)
            <div class="col-md-4">
                <div class="card cursor-pointer edit-detail"
                    data-url="{{ route('participant.detail.spirometri', $participant->id) }}"
                    data-title="{{ 'Spirometri MCU ID ' . $participant->code . ' | ' . $participant->name }}">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-2">Spiro</h3>
                            <p class="card-text" id="spirometri">{{ $participant->spirometri?->selesai ? 'SELESAI' : 'BELUM' }}
                            </p>
                        </div>
                        <div class="avatar bg-light-primary p-50 m-0">
                            <div class="avatar-content">
                                <img class="w-100" src="{{ asset('images/dental-checkup.png') }}" alt="image">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="progress progress-bar-primary">
                            <div class="progress-bar spirometri" role="progressbar"
                                aria-valuenow="{{ $participant->spirometri?->selesai ? 100 : '' }}"
                                aria-valuemin="{{ $participant->spirometri?->selesai ? 100 :'' }}"
                                aria-valuemax="{{ $participant->spirometri?->selesai ? 100 : '0' }}" @if($participant->
                                spirometri?->selesai) style="width: 100%" @endif></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if ($participant->plan_r)
            <div class="col-md-4">
                <div class="card cursor-pointer edit-detail"
                    data-url="{{ route('participant.detail.rectal', $participant->id) }}"
                    data-title="{{ 'Rectal MCU ID ' . $participant->code . ' | ' . $participant->name }}">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-2">Rectal</h3>
                            <p class="card-text" id="rectal">{{ $participant->rectal?->selesai ? 'SELESAI' : 'BELUM' }}</p>
                        </div>
                        <div class="avatar bg-light-primary p-50 m-0">
                            <div class="avatar-content">
                                <img class="w-100" src="{{ asset('images/dental-checkup.png') }}" alt="image">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="progress progress-bar-primary">
                            <div class="progress-bar rectal" role="progressbar"
                                aria-valuenow="{{ $participant->rectal?->selesai ? 100 : '' }}"
                                aria-valuemin="{{ $participant->rectal?->selesai ? 100 :'' }}"
                                aria-valuemax="{{ $participant->rectal?->selesai ? 100 : '0' }}" @if($participant->
                                rectal?->selesai) style="width: 100%" @endif></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if ($participant->plan_e)
            <div class="col-md-4">
                <div class="card cursor-pointer edit-detail"
                    data-url="{{ route('participant.detail.ekg', $participant->id) }}"
                    data-title="{{ 'EKG MCU ID ' . $participant->code . ' | ' . $participant->name }}">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-2">EKG</h3>
                            <p class="card-text" id="ekg">{{ $participant->ekg?->selesai ? 'SELESAI' : 'BELUM' }}</p>
                        </div>
                        <div class="avatar bg-light-primary p-50 m-0">
                            <div class="avatar-content">
                                <img class="w-100" src="{{ asset('images/dental-checkup.png') }}" alt="image">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="progress progress-bar-primary">
                            <div class="progress-bar ekg" role="progressbar"
                                aria-valuenow="{{ $participant->ekg?->selesai ? 100 : '' }}"
                                aria-valuemin="{{ $participant->ekg?->selesai ? 100 :'' }}"
                                aria-valuemax="{{ $participant->ekg?->selesai ? 100 : '0' }}" @if($participant->
                                ekg?->selesai) style="width: 100%" @endif></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="row">
            <div class="col-md-7">
                <a href="#" class="btn btn-primary" id="update-register"
                    data-url="{{ route('participant.update.register', $participant->id) }}">REGISTRASI</a>
                <a href="#" class="btn btn-info"
                    onclick="window.open('{{ route('report.sticker.lab', $participant->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">Print
                    Stiker Lab</a>
                <a href="#" class="btn btn-success"
                    onclick="window.open('{{ route('report.sticker.5pcs', $participant->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">Print
                    Stiker 5 Pcs</a>
                <a href="#" class="btn btn-warning"
                    onclick="window.open('{{ route('report.identitas', $participant->id) }}', '', 'toolbar=yes,scrollbars=yes,resizable=yes,width=900,height=600');">Print
                    Identitas</a>
            </div>
            <div class="col-md-5">
                <a href="#" class="btn btn-dark edit-detail"
                    data-url="{{ route('participant.detail.foto.kamera', $participant->id) }}">Ambil Foto Via
                    Kamera</a>
                <a href="#" class="btn btn-outline-dark edit-detail"
                    data-url="{{ route('participant.detail.foto.komputer', $participant->id) }}">Ambil Foto Via
                    Komputer</a>
            </div>
        </div>
    </div>
    <!--/ User Content -->
</div>

