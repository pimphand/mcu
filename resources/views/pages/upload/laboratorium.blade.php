@extends('layouts.main')

@section('title', 'Upload Laboratorium')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .table-responsive table.textkecil {
            font-size: 10px !important; /* Atur ukuran font kecil dan prioritaskan */
        }

        .table-responsive thead th.textkecil, .table-responsive td.textkecil {
            font-size: 10px !important; /* Pastikan ukuran font pada header dan sel tabel */
        }

        .table thead th {
            vertical-align: bottom;
        }

        .table th, .table td {
            padding: 0.5rem 0.75rem;
            vertical-align: top;
        }

        /* Kolom hasil urine/kesimpulan berisi kalimat, jadi kolomnya diberi
           ruang dan boleh membungkus supaya tabel tidak melebar berlebihan. */
        #tabelLap td.teks-panjang {
            min-width: 180px;
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
            <h3 class="mb-0">Upload Laboratorium</h3>
            <span class="badge rounded-pill bg-light-secondary text-body">
                Kontrak: {{ $contract->name ?? 'Belum ada kontrak aktif' }}
            </span>
        </div>
        <div class="card-body">
            {{-- Filter dikirim lewat AJAX, jadi submit form default dimatikan. --}}
            <form action="{{ route('participant.print.mcu') }}" method="get" onsubmit="return false">
                <div class="row g-3 align-items-end">

                    {{-- Tanggal MCU peserta --}}
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
                                        <a href="https://docs.google.com/spreadsheets/d/1ibg_2adBljBhzZFHnQFDXWQEJ8B0-MXozow6ab6lbfQ/edit?usp=sharing" target="_blank" rel="noopener" class="btn btn-outline-success">
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
                    <tr style="font-size: 10px">
                        <th class="border-bottom border-top textkecil">No.</th>
                        {{-- Klik judul kolom untuk sortir (asc/desc). --}}
                        <th class="border-bottom border-top textkecil sortable" data-sort="code">KODE MCU</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="name">NAMA</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="hemoglobin">Hemoglobin</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="hematokrit">Hematokrit</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="lekosit">Lekosit</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="trombosit">Trombosit</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="eritrosit">Eritrosit</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="basofil">Basofil</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="eosinofil">Eosinofil</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="batang">NBatang</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="segmen">NSegmen</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="limfosit">Limfosit</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="monosit">Monosit</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="sgpt">SGPT</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="creatinin">Creatinin</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="glukosa_puasa">Glukosa Puasa</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="cholesterol_total">Kolesterol Total</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="asam_urat">Asam Urat</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="sgot">SGOT</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="ureum">Ureum</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="berat_jenis">Berat Jenis</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="ph_reaksi">PH Reaksi</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="warna">Warna</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="kekeruhan">Kekeruhan</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="urobilinogen">Urobilinogen</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="bilirubin">Bilirubin</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="eritrosit_urine">Eritrosit Urine</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="keton">Keton</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="protein">Protein</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="sedimen_epitel">Sedimen Epitel</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="sedimen_eritrosit">Sedimen Eritrosit</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="sedimen_leukosit">Sedimen Leukosit</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="sedimen_bakteri">Sedimen Bakteri</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="sedimen_kristal">Sedimen Kristal</th>
                        {{-- user_lab/lab_date/pemeriksa_lab belum punya kolomnya di tabel
                             laboratoria, jadi tetap ditampilkan "-" dan tidak bisa disortir. --}}
                        <th class="border-bottom border-top textkecil">User Lab</th>
                        <th class="border-bottom border-top textkecil">Tanggal Lab</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="kesimpulan">Kesimpulan Lab</th>
                        <th class="border-bottom border-top textkecil">Pemeriksa Lab</th>
                        <th class="border-bottom border-top textkecil sortable" data-sort="reduksi">Reduksi</th>
                    </tr>
                    </thead>
                    <tbody id="tableBody">
                    <tr>
                        <td colspan="40" class="text-center">Memuat data...</td>
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
        // data laboratorium; filter baru aktif setelah rentang tanggal dipilih.
        let rangePickr = $('.flatpickr-range').flatpickr({
            mode: 'range',
            maxDate: 'today'
        });
    </script>

    <script type="module">
        // Kolom hasil lab yang ditampilkan, urut mengikuti judul kolom tabel.
        // Dipakai untuk membuat sel baris data supaya jumlah kolom tidak pernah
        // berbeda dengan judulnya.
        let kolomLaboratorium = [
            'hemoglobin', 'hematokrit', 'lekosit', 'trombosit', 'eritrosit', 'basofil', 'eosinofil',
            'batang', 'segmen', 'limfosit', 'monosit', 'sgpt', 'creatinin', 'glukosa_puasa',
            'cholesterol_total', 'asam_urat', 'sgot', 'ureum', 'berat_jenis', 'ph_reaksi', 'warna',
            'kekeruhan', 'urobilinogen', 'bilirubin', 'eritrosit_urine', 'keton', 'protein',
            'sedimen_epitel', 'sedimen_eritrosit', 'sedimen_leukosit', 'sedimen_bakteri',
            'sedimen_kristal', 'user_lab', 'lab_date', 'kesimpulan', 'pemeriksa_lab', 'reduksi'
        ];

        // Kolom yang isinya kalimat panjang dan boleh membungkus.
        let kolomTeksPanjang = [
            'warna', 'kekeruhan', 'sedimen_epitel', 'sedimen_eritrosit', 'sedimen_leukosit',
            'sedimen_bakteri', 'sedimen_kristal', 'kesimpulan'
        ];

        // Sortir aktif: null berarti urutan default dari server (terbaru dulu).
        let sortColumn = null;
        let sortDirection = 'asc';

        // Hasil lab berupa teks bebas, jadi harus di-escape sebelum masuk HTML.
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
        // pada tombolnya akan menghapus ikon secara permanen.
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

        let loadLaboratorium = function () {
            showSortIndicator();
            let url = "{{ route('get.laboratorium') }}" + "?" + filterParams().toString();

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
                        let cells = '';

                        kolomLaboratorium.forEach(function (kolom) {
                            let extra = kolomTeksPanjang.indexOf(kolom) !== -1 ? ' teks-panjang' : '';
                            cells += `<td class="border-bottom border-top${extra}">${escapeHtml(item[kolom])}</td>`;
                        });

                        tableRows += `<tr>
                            <td class="border-bottom border-top text-right">${index + 1}</td>
                            <td class="border-bottom border-top" nowrap>${escapeHtml(participant.code)}</td>
                            <td class="border-bottom border-top">${escapeHtml(participant.name)}</td>
                            ${cells}
                        </tr>`;
                    });

                    if (!response.data.length) {
                        tableRows = `<tr><td colspan="40" class="text-center">Tidak ada data laboratorium untuk kontrak dan filter yang aktif</td></tr>`;
                    }

                    // Memasukkan baris yang telah dibuat ke dalam tbody
                    $('#tableBody').html(tableRows);
                },
                error: function (xhr, status, error) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memuat data laboratorium. Silakan coba lagi.' });
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
                url: "{{ route('upload.laboratorium') }}",  // URL untuk mengirim data
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
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
                    loadLaboratorium();
                },
                error: function (xhr, status, error) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Upload failed. Please try again.' });
                },
                complete: function () {
                    setUploadState(false);
                }
            });
        });

        $('#cari').click(loadLaboratorium);

        // Klik judul kolom: klik pertama asc, klik berikutnya desc.
        $('#tabelLap').on('click', 'th.sortable', function () {
            let column = $(this).data('sort');

            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'asc';
            }

            loadLaboratorium();
        });

        $('#search').on('keyup', function (e) {
            if (e.key === 'Enter') {
                loadLaboratorium();
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
            loadLaboratorium();
        });

        // Tampilkan data begitu halaman dibuka, mengikuti kontrak sesi MCU IN.
        loadLaboratorium();
    </script>
@endsection
