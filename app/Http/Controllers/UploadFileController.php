<?php

namespace App\Http\Controllers;

use App\Imports\DoctorValidateImport;
use App\Services\ParticipantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

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
}
