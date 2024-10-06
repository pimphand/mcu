<?php

namespace App\Http\Controllers;

use App\Services\DivisiService;
use Illuminate\Http\Request;

class DivisiController extends Controller
{
    private DivisiService $divisiService;
    public function __construct(DivisiService $divisiService)
    {
        $this->divisiService = $divisiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $divisis = $this->divisiService->paginate($request->get('limit', 10));

        return view('pages.divisi.index', compact('divisis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.divisi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:divisis,name',
        ]);

        $this->divisiService->create($data);

        return redirect()->route('divisi.index')->with('success', 'Data berhasil simpan');
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
        $divisi = $this->divisiService->find($id);
        return view('pages.divisi.edit', compact('divisi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required|unique:divisis,name,' . $id . ',id',
        ]);

        $this->divisiService->update($data, $id);

        return redirect()->route('divisi.index')->with('success', 'Data berhasil simpan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->divisiService->delete($id)) {
            return redirect()->route('divisi.index')->with('error', 'Data gagal dihapus');
        }
        return redirect()->route('divisi.index')->with('success', 'Data berhasil dihapus');
    }

    /**
     * Display a listing of the resource.
     */
    public function select2(Request $request)
    {
        return $this->divisiService->paginate($request->get('limit', 10));
    }
}
