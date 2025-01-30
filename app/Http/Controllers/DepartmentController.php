<?php

namespace App\Http\Controllers;

use App\Services\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    private DepartmentService $departmentService;
    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $departments = $this->departmentService->paginate($request->get('limit', 10));

        return view('pages.department.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.department.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:departments,name',
        ]);

        $this->departmentService->create($data);

        return redirect()->route('department.index')->with('success', 'Data berhasil simpan');
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
        $department = $this->departmentService->find($id);
        return view('pages.department.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required|unique:departments,name,' . $id . ',id',
        ]);

        $this->departmentService->update($data, $id);

        return redirect()->route('department.index')->with('success', 'Data berhasil simpan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->departmentService->delete($id)) {
            return redirect()->route('department.index')->with('error', 'Data gagal dihapus');
        }
        return redirect()->route('department.index')->with('success', 'Data berhasil dihapus');
    }

    /**
     * Display a listing of the resource.
     */
    public function select2(Request $request)
    {
        return $this->departmentService->paginate($request->get('limit', 10));
    }
}
