<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use App\Services\ContractService;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    private ClientService $clientService;
    private ContractService $contractService;
    public function __construct(ContractService $contractService, ClientService $clientService)
    {
        $this->clientService = $clientService;
        $this->contractService = $contractService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $contracts = $this->contractService->paginate($request->get('limit', 10));

        return view('pages.contract.index', compact('contracts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = $this->clientService->paginate(1000);
        return view('pages.contract.create', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'client_id' => 'required',
        ]);

        $this->contractService->create($data);

        return redirect()->route('client.index')->with('success', 'Data berhasil simpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $clients = $this->clientService->paginate(1000);
        $contract = $this->contractService->find($id);
        return view('pages.contract.edit', compact('contract', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required',
            'client_id' => 'required',
        ]);

        $this->contractService->update($data, $id);

        return redirect()->route('client.index')->with('success', 'Data berhasil simpan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->contractService->delete($id)) {
            return redirect()->route('client.index')->with('error', 'Data gagal dihapus');
        }
        return redirect()->route('client.index')->with('success', 'Data berhasil dihapus');
    }

    public function select2(Request $request)
    {
        return $this->contractService->paginate();
    }
}
