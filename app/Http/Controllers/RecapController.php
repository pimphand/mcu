<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function register(Request $request)
    {
        $data = [];
        $client = $this->participantService->getClient();
        if ($request->ajax()) {
            $data = Participant::select(
                DB::raw('DATE(participants.register_date) as register_date'),
                DB::raw('COUNT(DISTINCT participants.id) as total_participants'), // Count distinct participants
                DB::raw('SUM(CASE WHEN audiometris.selesai = 1 THEN 1 ELSE 0 END) as total_audiometris'),
                DB::raw('SUM(CASE WHEN pemeriksaan_fisiks.selesai = 1 THEN 1 ELSE 0 END) as total_pemeriksaan_fisiks'),
                DB::raw('SUM(CASE WHEN laboratoria.selesai = 1 THEN 1 ELSE 0 END) as total_laboratoria'),
                DB::raw('SUM(CASE WHEN radiologis.selesai = 1 THEN 1 ELSE 0 END) as total_radiologis'),
                DB::raw('SUM(CASE WHEN rectals.selesai = 1 THEN 1 ELSE 0 END) as total_rectals'),
                DB::raw('SUM(CASE WHEN spirometris.selesai = 1 THEN 1 ELSE 0 END) as total_spirometris'),
                DB::raw('SUM(CASE WHEN tanda_vitals.selesai = 1 THEN 1 ELSE 0 END) as total_tanda_vitals'),
                DB::raw('SUM(CASE WHEN ekgs.selesai = 1 THEN 1 ELSE 0 END) as total_ekgs')
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
                ->get();
            return $data;
        }
        return view('pages.recaps.register', compact('data', 'client'));
    }
}
