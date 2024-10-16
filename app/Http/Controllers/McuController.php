<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use App\Services\ContractService;
use App\Services\McuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class McuController extends Controller
{
    private McuService $mcuService;
    private ClientService $clinetService;
    private ContractService $contractService;
    public function __construct(ClientService $clientService, ContractService $contractService, McuService $mcuService)
    {
        $this->clinetService = $clientService;
        $this->contractService = $contractService;
        $this->mcuService = $mcuService;
    }
    public function mcu(Request $request)
    {
        $request->merge(['client_id' => Session::get('client_id') ?? 0]);
        $clients = $this->clinetService->paginate(1000);
        $contracts = $this->contractService->paginate(1000);
        return view('pages.user.mcu', compact('clients', 'contracts'));
    }

    public function mcuIn(Request $request)
    {
        $this->mcuService->mcuIn($request->post('client_id'), $request->post('contract_id'));
        return back()->with('success', 'MCU IN Berhasil.');
    }

    public function mcuOut(Request $request)
    {
        $this->mcuService->mcuOut();
        return back()->with('success', 'MCU OUT Berhasil.');
    }
}
