<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Services\ParticipantService;
use Illuminate\Http\Request;
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
}
