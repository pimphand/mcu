<?php

namespace App\Http\Controllers;

use App\Exports\DoctorValidateTemplateExport;
use App\Exports\GeneralResultTemplateExport;
use App\Imports\DoctorValidateImport;
use App\Imports\GeneralResultImport;
use App\Imports\LaboratoriumImport;
use App\Imports\RadiologiImport;
use App\Models\GeneralResult;
use App\Models\Laboratorium;
use App\Models\Participant;
use App\Models\Radiologi;
use App\Models\ValidateDoctor;
use App\Services\ParticipantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class UploadFileController extends Controller
{
    /**
     * Kolom laboratoria yang boleh dipakai untuk sortir, mengikuti kolom
     * yang tampil pada tabel. Kolom NBatang/NSegmen memakai batang/segmen.
     *
     * @var array<int, string>
     */
    private const SORTABLE_COLUMNS = [
        'hemoglobin',
        'hematokrit',
        'lekosit',
        'trombosit',
        'eritrosit',
        'basofil',
        'eosinofil',
        'batang',
        'segmen',
        'limfosit',
        'monosit',
        'sgpt',
        'creatinin',
        'glukosa_puasa',
        'cholesterol_total',
        'asam_urat',
        'sgot',
        'ureum',
        'berat_jenis',
        'ph_reaksi',
        'warna',
        'kekeruhan',
        'urobilinogen',
        'bilirubin',
        'eritrosit_urine',
        'keton',
        'protein',
        'sedimen_epitel',
        'sedimen_eritrosit',
        'sedimen_leukosit',
        'sedimen_bakteri',
        'sedimen_kristal',
        'kesimpulan',
        'reduksi',
    ];

    /**
     * Kolom radiologis yang boleh dipakai untuk sortir, mengikuti kolom yang
     * tampil pada tabel. Kolom MCU ID/NAMA/GENDER diambil dari peserta.
     *
     * @var array<int, string>
     */
    private const RADIOLOGI_SORTABLE_COLUMNS = [
        'date_mcu',
        'created_at',
        'cor',
        'diafragma_sinus',
        'pulmo',
        'kesan',
        'diperiksa',
    ];

    public function validateDoctor(Request $request)
    {
        $data = $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls'
        ]);

        $import = new DoctorValidateImport(Session::get('client_id'), Session::get('contract_id'));

        Excel::import($import, $request->file('fileExcel'));

        return [
            'status' => 'success',
            'processed' => $import->getTotalRowsProcessed(),
            'skipped' => $import->getTotalRowsSkipped(),
            'skipped_codes' => $import->getSkippedCodes(),
        ];
    }

    public function getValidateDoctor(Request $request)
    {
        $participantService = new ParticipantService();
        $client = $participantService->getClient();
        $contractId = Session::get('contract_id');
        $contract = $client->contracts->firstWhere('id', $contractId);

        return view('pages.upload.validate-dokter', compact('client', 'contract'));
    }

    /**
     * Daftar hasil validasi dokter kontrak yang sedang aktif.
     *
     * Filter yang didukung (query string):
     * - filter[date_range] : rentang tanggal MCU (kolom date_mcu)
     * - filter[divisi_id]  : divisi peserta
     * - filter[search]     : nama atau kode MCU
     *
     * Sortir memakai parameter `sort`, mis. ?sort=-date_mcu.
     */
    public function getValidateDoctorData(Request $request)
    {
        $data = QueryBuilder::for(ValidateDoctor::class)
            ->allowedFilters([
                AllowedFilter::scope('date_range'),
                AllowedFilter::exact('divisi_id', 'participant.divisi_id'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $term = trim((string) $value);

                    if ($term === '') {
                        return;
                    }

                    $query->where(function (Builder $inner) use ($term) {
                        $inner->where('name', 'like', '%' . $term . '%')
                            ->orWhere('no_mcu', 'like', '%' . $term . '%');
                    });
                }),
            ])
            ->allowedSorts(['date_mcu', 'created_at', 'no_mcu', 'name', 'gender', 'result_mcu', 'doctor'])
            // Peserta di luar client yang sedang aktif tidak pernah ditampilkan.
            ->where('client_id', Session::get('client_id'))
            // Kontrak mengikuti sesi MCU IN, bukan dari query string.
            ->Contract(Session::get('contract_id'))
            ->defaultSort('-created_at')
            ->orderByDesc('id')
            ->get();

        return [
            "data" => $data
        ];
    }

    /**
     * Template Excel untuk upload validasi dokter (judul kolom mengikuti import).
     */
    public function validateDoctorTemplate()
    {
        return Excel::download(new DoctorValidateTemplateExport, 'template-upload-validasi-dokter.xlsx');
    }

    public function getlaboratorium(Request $request)
    {
        $participantService = new ParticipantService();
        $client = $participantService->getClient();
        $contractId = Session::get('contract_id');
        $contract = $client->contracts->firstWhere('id', $contractId);

        return view('pages.upload.laboratorium', compact('client', 'contract'));
    }

    /**
     * Daftar hasil laboratorium kontrak yang sedang aktif.
     *
     * Filter yang didukung (query string):
     * - filter[divisi_id]  : divisi peserta
     * - filter[date_range] : rentang tanggal registrasi peserta (YYYY-MM-DD to YYYY-MM-DD)
     * - filter[search]     : nama atau kode MCU peserta
     *
     * Sortir memakai parameter `sort`, mis. ?sort=name atau ?sort=-hemoglobin.
     */
    public function getlaboratoriumData(Request $request)
    {
        $data = QueryBuilder::for(Laboratorium::class)
            ->allowedFilters([
                AllowedFilter::exact('divisi_id', 'participant.divisi_id'),
                AllowedFilter::scope('date_range', 'participant.date_range'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $term = trim((string) $value);

                    if ($term === '') {
                        return;
                    }

                    $query->whereHas('participant', function (Builder $sub) use ($term) {
                        $sub->where(function (Builder $inner) use ($term) {
                            $inner->where('name', 'like', '%' . $term . '%')
                                ->orWhere('code', 'like', '%' . $term . '%');
                        });
                    });
                }),
            ])
            ->allowedSorts($this->sortRules('laboratoria', self::SORTABLE_COLUMNS, ['code', 'name']))
            // Kontrak tidak difilter dari request, melainkan dari sesi MCU IN.
            ->Contract(Session::get('contract_id'))
            // Peserta di luar client yang sedang aktif tidak pernah ditampilkan.
            ->whereHas('participant', function (Builder $query) {
                $query->where('client_id', Session::get('client_id'));
            })
            ->with('participant')
            ->defaultSort('-id')
            ->get();

        return [
            "data" => $data
        ];
    }

    /**
     * Daftar sortir yang diizinkan: kolom milik tabel hasil ditambah kolom
     * peserta (mis. code/name). Setiap sortir diberi tie-breaker id supaya
     * urutannya stabil.
     *
     * @param  array<int, string>  $columns  kolom tabel hasil
     * @param  array<int, string>  $participantColumns  kolom tabel participants
     * @return array<int, AllowedSort>
     */
    private function sortRules(string $table, array $columns, array $participantColumns): array
    {
        $sorts = [];

        foreach ($participantColumns as $column) {
            $sorts[] = AllowedSort::callback($column, function (Builder $query, bool $descending) use ($table, $column) {
                $this->orderByParticipantColumn($query, $table, $column, $descending);
            });
        }

        foreach ($columns as $column) {
            $sorts[] = AllowedSort::callback($column, function (Builder $query, bool $descending) use ($column) {
                $query->orderBy($column, $descending ? 'desc' : 'asc')->orderBy('id');
            });
        }

        return $sorts;
    }

    /**
     * Sortir memakai kolom pada tabel participants (dipakai untuk code/name).
     */
    private function orderByParticipantColumn(Builder $query, string $table, string $column, bool $descending): void
    {
        $query->orderBy(
            Participant::query()
                ->select($column)
                ->whereColumn('participants.id', $table . '.participant_id'),
            $descending ? 'desc' : 'asc'
        )->orderBy('id');
    }

    public function laboratorium(Request $request)
    {
        $data = $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls'
        ]);

        $import = new LaboratoriumImport(Session::get('client_id'), Session::get('contract_id'));

        Excel::import($import, $request->file('fileExcel'));

        return [
            'status' => 'success',
            'processed' => $import->getTotalRowsProcessed(),
            'skipped' => $import->getTotalRowsSkipped(),
            'skipped_codes' => $import->getSkippedCodes(),
            'created_doctors' => $import->getCreatedDoctors(),
        ];
    }

    public function getradiologi(Request $request)
    {
        $participantService = new ParticipantService();
        $client = $participantService->getClient();
        $contractId = Session::get('contract_id');
        $contract = $client->contracts->firstWhere('id', $contractId);

        if ($request->ajax()) {
            $data = QueryBuilder::for(Radiologi::class)
                ->allowedFilters([
                    AllowedFilter::exact('divisi_id', 'participant.divisi_id'),
                    AllowedFilter::scope('date_range'),
                    AllowedFilter::callback('search', function (Builder $query, $value) {
                        $term = trim((string) $value);

                        if ($term === '') {
                            return;
                        }

                        $query->whereHas('participant', function (Builder $sub) use ($term) {
                            $sub->where(function (Builder $inner) use ($term) {
                                $inner->where('name', 'like', '%' . $term . '%')
                                    ->orWhere('code', 'like', '%' . $term . '%');
                            });
                        });
                    }),
                ])
                ->allowedSorts($this->sortRules('radiologis', self::RADIOLOGI_SORTABLE_COLUMNS, ['code', 'name', 'gender']))
                // Kontrak tidak difilter dari request, melainkan dari sesi MCU IN.
                ->Contract($contractId)
                // Peserta di luar client yang sedang aktif tidak pernah ditampilkan.
                ->whereHas('participant', function (Builder $query) {
                    $query->where('client_id', Session::get('client_id'));
                })
                ->with('participant')
                ->defaultSort('-date_mcu')
                ->orderByDesc('id')
                ->get();

            return [
                "data" => $data
            ];
        }
        return view('pages.upload.radiologi', compact('client', 'contract'));
    }

    public function radiologi(Request $request)
    {
        $data = $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls'
        ]);

        $import = new RadiologiImport(Session::get('client_id'), Session::get('contract_id'));

        Excel::Import($import, $request->file('fileExcel'));

        return [
            'status' => 'success',
            'processed' => $import->getTotalRowsProcessed(),
            'skipped' => $import->getTotalRowsSkipped(),
            'skipped_codes' => $import->getSkippedCodes(),
            'created_doctors' => $import->getCreatedDoctors(),
        ];
    }
    /**
     * Daftar hasil umum kontrak yang sedang aktif.
     *
     * Filter yang didukung (query string):
     * - filter[date_range] : rentang tanggal MCU (kolom mcu_date)
     * - filter[divisi_id]  : divisi peserta
     * - filter[search]     : nama atau kode MCU peserta
     *
     * Sortir memakai parameter `sort`, mis. ?sort=-mcu_date.
     */
    public function getGeneralResult(Request $request)
    {
        $participantService = new ParticipantService();
        $client = $participantService->getClient();
        $contractId = Session::get('contract_id');
        $contract = $client->contracts->firstWhere('id', $contractId);

        if ($request->ajax()) {
            $data = QueryBuilder::for(GeneralResult::class)
                ->allowedFilters([
                    AllowedFilter::scope('date_range'),
                    AllowedFilter::exact('divisi_id', 'participant.divisi_id'),
                    AllowedFilter::callback('search', function (Builder $query, $value) {
                        $term = trim((string) $value);

                        if ($term === '') {
                            return;
                        }

                        $query->where(function (Builder $inner) use ($term) {
                            $inner->where('name', 'like', '%' . $term . '%')
                                ->orWhere('mcu_id', 'like', '%' . $term . '%');
                        });
                    }),
                ])
                ->allowedSorts(['mcu_date', 'created_at', 'mcu_id', 'name'])
                // Peserta di luar client yang sedang aktif tidak pernah ditampilkan.
                ->where('client_id', Session::get('client_id'))
                // Kontrak mengikuti sesi MCU IN, bukan dari query string.
                ->Contract($contractId)
                ->defaultSort('-created_at')
                ->orderByDesc('id')
                ->get();

            return [
                "data" => $data
            ];
        }

        return view('pages.upload.general_result', compact('client', 'contract'));
    }

    public function generalResult(Request $request)
    {
        $data = $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls'
        ]);

        $import = new GeneralResultImport(Session::get('client_id'), Session::get('contract_id'));

        // Import berjalan langsung (bukan lewat queue) supaya respons selalu
        // melaporkan jumlah baris yang benar-benar tersimpan.
        Excel::import($import, $request->file('fileExcel'));

        return [
            'status' => 'success',
            'processed' => $import->getTotalRowsProcessed(),
            'skipped' => $import->getTotalRowsSkipped(),
            'skipped_codes' => $import->getSkippedCodes(),
        ];
    }

    /**
     * Template Excel untuk upload hasil umum (judul kolom mengikuti import).
     */
    public function generalResultTemplate()
    {
        return Excel::download(new GeneralResultTemplateExport, 'template-upload-hasil-umum.xlsx');
    }
}
