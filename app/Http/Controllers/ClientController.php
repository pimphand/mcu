<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    private ClientService $clientService;
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $clients = $this->clientService->paginate($request->get('limit', 10));

        return view('pages.client.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.client.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:clients,name',
            'address' => 'required',
        ]);

        $this->clientService->create($data);

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
        $client = $this->clientService->find($id);
        return view('pages.client.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required|unique:clients,name,' . $id . ',id',
            'address' => 'required',
        ]);

        $this->clientService->update($data, $id);

        return redirect()->route('client.index')->with('success', 'Data berhasil simpan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->clientService->delete($id)) {
            return redirect()->route('client.index')->with('error', 'Data gagal dihapus');
        }
        return redirect()->route('client.index')->with('success', 'Data berhasil dihapus');
    }
}
