<?php

namespace App\Http\Controllers;

use App\Imports\DoctorValidateImport;
use App\Imports\LaboratoriumImport;
use App\Imports\RadiologiImport;
use App\Jobs\TestJob;
use App\Models\Radiologi;
use App\Services\ParticipantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UploadFileController extends Controller
{
    public function validateDoctor(Request $request)
    {
        $data = $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new DoctorValidateImport(Session::get('client_id')), $request->file('fileExcel'));

        return ['status' => 'success'];
    }

    public function getValidateDoctor(Request $request)
    {
        $participantService = new ParticipantService();
        $client = $participantService->getClient();
        return view('pages.upload.validate-dokter', compact('client'));
    }

    public function getlaboratorium(Request $request)
    {
        $participantService = new ParticipantService();
        $client = $participantService->getClient();
        return view('pages.upload.laboratorium', compact('client'));
    }

    public function laboratorium(Request $request)
    {
        $data = $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new LaboratoriumImport(Session::get('client_id')), $request->file('fileExcel'));

        return ['status' => 'success'];
    }

    public function getradiologi(Request $request)
    {
        $participantService = new ParticipantService();
        $client = $participantService->getClient();

        if ($request->ajax()) {
            $data = QueryBuilder::for(Radiologi::class)
                ->withWhereHas('participant', function ($query) use ($request) {
                    $query->where('client_id', Session::get('client_id'))
                        ->DateRange($request->date_range);
                })
                ->get();
            TestJob::dispatch();
            return [
                "data" => $data
            ];
        }
        return view('pages.upload.radiologi', compact('client'));
    }

    public function radiologi(Request $request)
    {
        $data = $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls'
        ]);

        Excel::queueImport(new RadiologiImport(Session::get('client_id')), $request->file('fileExcel'));

        return ['status' => 'success'];
    }
}
