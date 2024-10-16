<?php

namespace App\Http\Controllers;

use App\Exports\DataRegisterExport;
use App\Exports\ResultMcu;
use App\Http\Resources\DataRegisterResource;
use App\Jobs\GeneratePdfJob;
use App\Jobs\TestJob;
use App\Models\Audiometri;
use App\Models\Ekg;
use App\Models\Laboratorium;
use App\Models\Participant;
use App\Models\PemeriksaanFisik;
use App\Models\Radiologi;
use App\Models\Rectal;
use App\Models\Spirometri;
use App\Models\TandaVital;
use App\Services\ParticipantService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RecapController extends Controller
{
    private ParticipantService $participantService;

    public function __construct(ParticipantService $participantService)
    {
        $this->participantService = $participantService;
    }
    public function results(Request $request)
    {
        $data = [];
        $client = $this->participantService->getClient();
        if ($request->filter) {
            $data = QueryBuilder::for(Participant::class)
                ->allowedFilters([
                    AllowedFilter::exact('client_id'),
                    AllowedFilter::exact('contract_id'),
                    AllowedFilter::exact('divisi_id'),
                    AllowedFilter::scope('date_range'),
                ])
                ->get();
        }
        return view('pages.report.results', compact('data', 'client'));
    }

    public function importResultMcu(Request $request)
    {
        $data = QueryBuilder::for(Participant::class)
            ->allowedFilters([
                AllowedFilter::exact('client_id'),
                AllowedFilter::exact('contract_id'),
                AllowedFilter::exact('divisi_id'),
                AllowedFilter::scope('date_range'),
                AllowedFilter::scope('form_range'),
            ])
            ->get();

        // Use a queue to process the export
        $fileName = 'results_' . time() . '.xlsx';

        Queue::push(function () use ($data, $fileName) {
            // Store the file in the 'public' directory
            Excel::store(new ResultMcu($data), 'hasil_mcu/' . $fileName);
        });

        // Return the public URL for the user to download the file
        $fileUrl = Storage::url('hasil_mcu/' . $fileName);

        return response()->json([
            'status' => 'success',
            'download_url' => $fileUrl
        ]);
    }

    public function register(Request $request)
    {
        $data = [];
        $client = $this->participantService->getClient();
        if ($request->ajax()) {
            $data = Participant::select(
                DB::raw('DATE(participants.register_date) as register_date'),
                DB::raw('COUNT(DISTINCT participants.id) as total_participants'), // Count distinct participants
                DB::raw('COUNT(audiometris.id) as total_audiometris'), // Count total audiometris records
                DB::raw('SUM(CASE WHEN audiometris.selesai = 1 THEN 1 ELSE 0 END) as total_selesai_audiometris'),
                DB::raw('COUNT(pemeriksaan_fisiks.id) as total_pemeriksaan_fisiks'), // Count total pemeriksaan fisiks records
                DB::raw('SUM(CASE WHEN pemeriksaan_fisiks.selesai = 1 THEN 1 ELSE 0 END) as total_selesai_pemeriksaan_fisiks'),
                DB::raw('SUM(CASE WHEN pemeriksaan_fisiks.kesimpulan IN ("FIT", "GSI1 - FIT WITH JOB") THEN 1 ELSE 0 END) as total_selesai_pemeriksaan_fit'),
                DB::raw('SUM(CASE WHEN pemeriksaan_fisiks.kesimpulan = "FIT WITH RESTRICTION" THEN 1 ELSE 0 END) as total_selesai_pemeriksaan_frw'),
                DB::raw('SUM(CASE WHEN pemeriksaan_fisiks.kesimpulan = "UNFIT" THEN 1 ELSE 0 END) as total_selesai_pemeriksaan_unfit'),
                DB::raw('COUNT(laboratoria.id) as total_laboratoria'), // Count total laboratoria records
                DB::raw('SUM(CASE WHEN laboratoria.selesai = 1 THEN 1 ELSE 0 END) as total_selesai_laboratoria'),
                DB::raw('COUNT(DISTINCT radiologis.id) as total_radiologis'), // Count total radiologis records
                DB::raw('SUM(CASE WHEN radiologis.selesai = 1 THEN 1 ELSE 0 END) as total_selesai_radiologis'),
                DB::raw('COUNT(rectals.id) as total_rectals'), // Count total rectals records
                DB::raw('SUM(CASE WHEN rectals.selesai = 1 THEN 1 ELSE 0 END) as total_selesai_rectals'),
                DB::raw('COUNT(spirometris.id) as total_spirometris'), // Count total spirometris records
                DB::raw('SUM(CASE WHEN spirometris.selesai = 1 THEN 1 ELSE 0 END) as total_selesai_spirometris'),
                DB::raw('COUNT(tanda_vitals.id) as total_tanda_vitals'), // Count total tanda vitals records
                DB::raw('SUM(CASE WHEN tanda_vitals.selesai = 1 THEN 1 ELSE 0 END) as total_selesai_tanda_vitals'),
                DB::raw('SUM(CASE WHEN tanda_vitals.ibu_hamil = 1 THEN 1 ELSE 0 END) as total_hamil'),
                DB::raw('COUNT(ekgs.id) as total_ekgs'), // Count total ekgs records
                DB::raw('SUM(CASE WHEN ekgs.selesai = 1 THEN 1 ELSE 0 END) as total_selesai_ekgs')
            )
                ->leftJoin('audiometris', 'participants.id', '=', 'audiometris.participant_id')
                ->leftJoin('pemeriksaan_fisiks', 'participants.id', '=', 'pemeriksaan_fisiks.participant_id')
                ->leftJoin('laboratoria', 'participants.id', '=', 'laboratoria.participant_id')
                ->leftJoin('radiologis', 'participants.id', '=', 'radiologis.participant_id')
                ->leftJoin('rectals', 'participants.id', '=', 'rectals.participant_id')
                ->leftJoin('spirometris', 'participants.id', '=', 'spirometris.participant_id')
                ->leftJoin('tanda_vitals', 'participants.id', '=', 'tanda_vitals.participant_id')
                ->leftJoin('ekgs', 'participants.id', '=', 'ekgs.participant_id')
                ->groupBy(DB::raw('DATE(participants.register_date)'))
                ->where(function ($query) use ($request) {
                    $date = explode(' to ', $request->date_range);
                    // dd(count($date));
                    // Jika hanya ada satu tanggal
                    if (count($date) == 1) {
                        $startDate = Carbon::parse($date[0])->startOfDay();
                        $endDate = Carbon::parse($date[0])->endOfDay();
                    } else {
                        // Jika ada dua tanggal
                        $startDate = Carbon::parse($date[0])->startOfDay();
                        $endDate = Carbon::parse($date[1])->endOfDay();
                    }

                    $query->whereBetween('register_date', [$startDate, $endDate]);
                    if ($request->contract_id) {
                        $query->where('contract_id', $request->contract_id);
                    }
                    if ($request->divisi_id) {
                        $query->where('divisi_id', $request->divisi_id);
                    }
                    if ($request->client_id) {
                        $query->where('client_id', $request->client_id);
                    }
                })
                ->get();

            return $data;
        }
        return view('pages.recaps.register', compact('data', 'client'));
    }

    public function dataRegister(Request $request)
    {
        $data = [];
        $client = $this->participantService->getClient();
        if ($request->ajax()) {
            $data = QueryBuilder::for(Participant::class)
                ->allowedFilters([
                    AllowedFilter::exact('client_id'),
                    AllowedFilter::exact('contract_id'),
                    AllowedFilter::exact('divisi_id'),
                    AllowedFilter::scope('date_range'),
                ])
                ->get();
            if ($request->excel){
                // Use a queue to process the export
                $fileName = 'hasil_register_mcu' . time() . '.xlsx';

                Queue::push(function () use ($data, $fileName) {
                    Excel::store(new DataRegisterExport($data), 'hasil_register_mcu/' . $fileName);
                });

                // Return the public URL for the user to download the file
                $fileUrl = Storage::url('hasil_register_mcu/' . $fileName);

                return response()->json([
                    'status' => 'success',
                    'download_url' => $fileUrl
                ]);
            }

            if ($request->pdf){

                $fileName = 'dataRegister_' . time() . '.pdf'; // Customize the file name
//                GeneratePdfJob::dispatch($data, $fileName);
//
//                return [
//                    'status' => 'success',
//                    'download_url' => Storage::url('pdf/' . $fileName)
//                ];

                $mpdf = new \Mpdf\Mpdf();
                $mpdf->WriteHTML(view('exports.dataRegister',['data' => $data]));
                $storagePath = storage_path('app/public/pdf/') . $fileName;
                $mpdf->Output($storagePath, \Mpdf\Output\Destination::FILE);
                $fileUrl = Storage::url('pdf/' . $fileName);
                return [
                    'status' => 'success',
                    'download_url' => $fileUrl
                ];
            }
            return DataRegisterResource::collection($data);
        }
        return view('pages.recaps.dataRegister', compact('data', 'client'));
    }
}
