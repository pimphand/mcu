@extends('layouts.main')

@section('title', 'Upload Radiologi')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .table-responsive table.textkecil {
            font-size: 10px !important; /* Atur ukuran font kecil dan prioritaskan */
        }

        .table-responsive th.textkecil, .table-responsive td.textkecil {
            font-size: 10px !important; /* Pastikan ukuran font pada header dan sel tabel */
        }

        .table thead th {
            vertical-align: bottom;
        }

        .table th, .table td {
            padding: 0.5rem 0.75rem;
            vertical-align: top;
        }

        /* COR/PULMO/KESAN berisi kalimat panjang, jadi kolomnya diberi ruang
           dan boleh membungkus supaya tabel tidak melebar berlebihan. */
        #tabelLap td.teks-panjang {
            min-width: 240px;
            white-space: normal;
        }

        /* Penanda kolom yang bisa diklik untuk sortir */
        #tabelLap th.sortable {
            cursor: pointer;
            white-space: nowrap;
        }

        #tabelLap th.sortable::after {
            content: "\21C5";
            font-size: 9px;
            margin-left: 4px;
            opacity: .35;
        }

        #tabelLap th.sortable.sorting_asc::after {
            content: "\2191";
            opacity: 1;
        }

        #tabelLap th.sortable.sorting_desc::after {
            content: "\2193";
            opacity: 1;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Upload Radiologi</h3>
            <span class="badge rounded-pill bg-light-secondary text-body">
                Kontrak: {{ $contract->name ?? 'Belum ada kontrak aktif' }}
            </span>
        </div>
        <div class="card-body">
            {{-- Filter dikirim lewat AJAX, jadi submit form default dimatikan. --}}
            <form action="{{ route('participant.print.mcu') }}" method="get" onsubmit="return false">
                <div class="row g-3 align-items-end">

                    {{-- Tanggal MCU --}}
                    <div class="col-md-3">
                        <label for="fp-range" class="form-label">Tanggal MCU</label>
                        <input
                            type="text"
                            name="filter[date_range]"
                            id="fp-range"
                            class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD"
                        >
                    </div>

                    {{-- Client --}}
                    <div class="col-md-2">
                        <label for="client_id" class="form-label">Client</label>
                        <select name="client_id" id="client_id" class="form-select">
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        </select>
                    </div>

                    {{-- Divisi --}}
                    <div class="col-md-2">
                        <label for="divisi_id" class="form-label">Divisi</label>
                        <select name="filter[divisi_id]" id="divisi_id" class="form-select">
                            <option value="">Semua Divisi</option>
                            @foreach ($client->divisi as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pencarian --}}
                    <div class="col-md-2">
                        <label for="search" class="form-label">Nama / Kode MCU</label>
                        <input
                            type="text"
                            name="filter[search]"
                            id="search"
                            class="form-control"
                            placeholder="Cari nama/kode..."
                        >
                    </div>

                    {{-- Tombol aksi: tingginya mengikuti input karena baris ini align-items-end. --}}
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary flex-fill" id="cari">
                                {{-- <i class="fas fa-search me-1" id="cari_icon"></i> --}}
                                <span id="cari_text">Cari</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary flex-fill" id="reset">
                                {{-- <i class="fas fa-undo me-1"></i> --}}
                                Reset
                            </button>
                        </div>
                    </div>

                </div>
            </form>

            <div class="row">
                <div class="col-md-12 mt-3">
                    <h3>Import Data</h3>
                    <form id="formUpload" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="fileExcel">Pilih File Excel</label>
                                    <input type="file" name="fileExcel" id="fileExcel" class="form-control">
                                    <div class="d-flex gap-2 mt-2">
                                        <button class="btn btn-success" id="upload_btn" type="button">
                                            <i class="mdi mdi-cloud-upload" id="upload_icon"></i>
                                            <span id="upload_text">Upload</span>
                                        </button>
                                        <a href="https://docs.google.com/spreadsheets/d/1qlsSuNyR7Uf7T8YVIL49Oi_ehLF5DkBTls2JEW5jSJU/edit?usp=sharing" target="_blank" class="btn btn-outline-success">
                                            <i class="mdi mdi-file-excel"></i>
                                            Template
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive textkecil mt-3">
                <table id="tabelLap" class="table customize-table table-bordered mb-0 v-middle textkecil">
                    <thead>
                    <tr>
                        <th class="border-bottom border-top textkecil">No.</th>
                        {{-- Klik judul kolom untuk sortir (asc/desc). --}}
                        <th class="border-bottom border-top textkecil sortable" data-sort="date_mcu">TANGGAL MCU</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="created_at">TANGGAL UPLOAD</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="code">MCU ID</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="name">NAMA</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="gender">GENDER</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="cor">COR</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="diafragma_sinus">RFS</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="pulmo">PULMO</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="kesan">KESAN</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="diperiksa">PEMERIKSA</th>
                    </tr>
                    </thead>
                    <tbody id="data">
                    <tr>
                        <td colspan="11" class="text-center">Memuat data...</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/picker.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/picker.date.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/picker.time.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/pickadate/legacy.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        // Tanggal sengaja dikosongkan agar tabel langsung menampilkan seluruh
        // data radiologi; filter baru aktif setelah rentang tanggal dipilih.
        let rangePickr = $('.flatpickr-range').flatpickr({
            mode: 'range',
            maxDate: 'today'
        });
    </script>

    <script type="module">
        // Sortir aktif: null berarti urutan default dari server (tanggal MCU terbaru dulu).
        let sortColumn = null;
        let sortDirection = 'asc';

        // COR/PULMO/KESAN berisi teks bebas, jadi harus di-escape sebelum masuk HTML.
        let escapeHtml = function (value) {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        };

        let shortDate = function (value) {
            if (!value) {
                return '-';
            }

            return String(value).replace('T', ' ').split(' ')[0];
        };

        // Parameter filter mengikuti nama yang diizinkan endpoint: filter[date_range],
        // filter[divisi_id], filter[search], plus sort. Parameter kosong tidak dikirim.
        let filterParams = function () {
            let params = new URLSearchParams();
            let dateRange = $('#fp-range').val();
            let divisi = $('#divisi_id').val();
            let search = $.trim($('#search').val());

            if (dateRange) {
                params.set('filter[date_range]', dateRange);
            }
            if (divisi) {
                params.set('filter[divisi_id]', divisi);
            }
            if (search) {
                params.set('filter[search]', search);
            }
            if (sortColumn) {
                params.set('sort', (sortDirection === 'desc' ? '-' : '') + sortColumn);
            }

            return params;
        };

        let showSortIndicator = function () {
            $('#tabelLap th.sortable').removeClass('sorting_asc sorting_desc');

            if (sortColumn) {
                $('#tabelLap th.sortable[data-sort="' + sortColumn + '"]')
                    .addClass(sortDirection === 'desc' ? 'sorting_desc' : 'sorting_asc');
            }
        };

        // Status tombol Cari. Teks dan ikon diubah satu per satu: memakai .text()
        // pada tombolnya akan menghapus ikonnya secara permanen.
        let setCariState = function (loading) {
            $('#cari').prop('disabled', loading);
            $('#cari_text').text(loading ? 'Diproses...' : 'Cari');
            $('#cari_icon').attr('class', loading ? 'fas fa-spinner fa-spin me-1' : 'fas fa-search me-1');
        };

        let setUploadState = function (loading) {
            $('#upload_btn').prop('disabled', loading);
            $('#upload_text').text(loading ? 'Uploading...' : 'Upload');
            $('#upload_icon').attr('class', loading ? 'mdi mdi-loading mdi-spin' : 'mdi mdi-cloud-upload');
        };

        let loadRadiologi = function () {
            showSortIndicator();
            let url = "{{ route('upload.radiologi') }}" + "?" + filterParams().toString();

            $.ajax({
                url: url,
                type: 'get',
                beforeSend: function () {
                    setCariState(true);
                },
                success: function (response) {
                    let tableRows = '';

                    $.each(response.data, function (index, item) {
                        let participant = item.participant || {};

                        tableRows += `<tr>
                            <td class="border-bottom border-top text-right" nowrap>${index + 1}</td>
                            <td class="border-bottom border-top" nowrap>${escapeHtml(shortDate(item.date_mcu))}</td>
                            <td class="border-bottom border-top" nowrap>${escapeHtml(shortDate(item.created_at))}</td>
                            <td class="border-bottom border-top" nowrap>${escapeHtml(participant.code)}</td>
                            <td class="border-bottom border-top">${escapeHtml(participant.name)}</td>
                            <td class="border-bottom border-top">${escapeHtml(participant.gender)}</td>
                            <td class="border-bottom border-top teks-panjang">${escapeHtml(item.cor)}</td>
                            <td class="border-bottom border-top">${escapeHtml(item.diafragma_sinus)}</td>
                            <td class="border-bottom border-top teks-panjang">${escapeHtml(item.pulmo)}</td>
                            <td class="border-bottom border-top teks-panjang">${escapeHtml(item.kesan)}</td>
                            <td class="border-bottom border-top" nowrap>${escapeHtml(item.diperiksa)}</td>
                        </tr>`;
                    });

                    if (!response.data.length) {
                        tableRows = `<tr><td colspan="11" class="text-center">Tidak ada data radiologi untuk kontrak dan filter yang aktif</td></tr>`;
                    }

                    $('#data').html(tableRows);  // Populate the table body
                },
                error: function (xhr, status, error) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memuat data radiologi. Silakan coba lagi.' });
                },
                complete: function () {
                    setCariState(false);
                }
            });
        };

        $("#upload_btn").click(function (e) {
            e.preventDefault();  // Mencegah form submit otomatis

            let form = $('#formUpload')[0];  // Ambil elemen form
            let formData = new FormData(form);  // Buat objek FormData dari elemen form

            $.ajax({
                url: "{{ route('upload.radiologi') }}",  // URL untuk mengirim data
                type: 'POST',
                data: formData,  // Data yang dikirim adalah FormData
                processData: false,  // Jangan proses data
                contentType: false,  // Jangan tentukan jenis konten (biarkan browser melakukannya)
                beforeSend: function () {
                    setUploadState(true);
                },
                success: function (response) {
                    let text = `${response.processed} data berhasil disimpan`;
                    let icon = 'success';

                    if (response.skipped > 0) {
                        icon = response.processed > 0 ? 'warning' : 'error';
                        text += `, ${response.skipped} baris dilewati karena kode peserta tidak ditemukan pada kontrak yang aktif`;
                        if (response.skipped_codes && response.skipped_codes.length) {
                            text += ` (contoh: ${response.skipped_codes.slice(0, 5).join(', ')})`;
                        }
                    }

                    if (response.created_doctors && response.created_doctors.length) {
                        text += `. Pemeriksa baru dibuat di master karyawan: ${response.created_doctors.join(', ')}`;
                    }

                    Swal.fire({ icon: icon, title: 'Info', text: text });
                    // Import berjalan sinkron, jadi tabel bisa langsung disegarkan
                    // supaya hasilnya terlihat tanpa menekan Cari.
                    loadRadiologi();
                },
                error: function (xhr, status, error) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Upload failed. Please try again.' });
                },
                complete: function () {
                    setUploadState(false);
                }
            });
        });

        $('#cari').click(loadRadiologi);

        // Klik judul kolom: klik pertama asc, klik berikutnya desc.
        $('#tabelLap').on('click', 'th.sortable', function () {
            let column = $(this).data('sort');

            if (sortColumn === column) {
                sortDirection = sortDirection === 'desc' ? 'asc' : 'desc';
            } else {
                sortColumn = column;
                sortDirection = 'asc';
            }

            loadRadiologi();
        });

        $('#search').on('keyup', function (e) {
            if (e.key === 'Enter') {
                loadRadiologi();
            }
        });

        $('#reset').click(function () {
            $('#fp-range').val('');
            if (rangePickr) {
                rangePickr.clear();
            }
            $('#divisi_id').val('');
            $('#search').val('');
            sortColumn = null;
            sortDirection = 'asc';
            loadRadiologi();
        });

        // Tampilkan data begitu halaman dibuka (tanpa filter).
        loadRadiologi();
    </script>
    <script>
        // Inisialisasi Pusher
        var pusher = new Pusher('23ac53d7303716c97001', {
            cluster: 'ap1'
        });

        var channel = pusher.subscribe('notification-channel');
        channel.bind('import-completed', function(data) {
            Swal.fire({ icon: 'info', title: 'Notifikasi', text: data.message });
        });
    </script>

@endsection
