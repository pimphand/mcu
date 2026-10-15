<?php

namespace App\Http\Controllers;

use App\Imports\DoctorValidateImport;
use App\Imports\GeneralResultImport;
use App\Imports\LaboratoriumImport;
use App\Imports\RadiologiImport;
use App\Models\Radiologi;
use App\Models\Laboratorium;
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

    public function getlaboratoriumData(Request $request)
    {
        return Laboratorium::with('participant')->get();
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
        $contractId = Session::get('contract_id');
        $contract = $client->contracts->firstWhere('id', $contractId);

        if ($request->ajax()) {
            $data = QueryBuilder::for(Radiologi::class)
                ->withWhereHas('participant', function ($query) use ($request) {
                    $query->where('client_id', Session::get('client_id'))
                        ->Divisi($request->divisi_id);
                })
                ->DateRange($request->date_range)
                // Kontrak tidak difilter dari request, melainkan dari sesi MCU IN.
                ->Contract($contractId)
                ->orderByDesc('date_mcu')
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
        ];
    }
    public function getGeneralResult(Request $request)
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

            return [
                "data" => $data
            ];
        }
        return view('pages.upload.general_result', compact('client'));
    }

    public function generalResult(Request $request)
    {
        $data = $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls'
        ]);

        Excel::queueImport(new GeneralResultImport(Session::get('client_id')), $request->file('fileExcel'));

        return ['status' => 'success'];
    }
}
