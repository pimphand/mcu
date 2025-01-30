<?php

namespace App\Http\Controllers;

use App\Services\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    private EmployeeService $employeeService;
    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $employees = $this->employeeService->paginate($request->get('limit', 10));

        return view('pages.employee.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employee = $this->employeeService;
        return view('pages.employee.create', compact('employee'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nik' => 'required',
            'nama' => 'required',
            'jenis_kelamin' => '',
            'agama' => '',
            'status' => '',
            'tanggal_lahir' => '',
            'tempat_lahir' => '',
            'alamat' => '',
            'golongan_darah' => '',
            'rhesus' => '',
            'pendidikan' => '',
            'telp' => '',
            'email' => '',
            'unit' => '',
            'jabatan' => '',
            'profesi' => '',
            'profesi_detail' => '',
            'warga_negara' => '',
            'nama_bank' => '',
            'nomor_rekening' => '',
            'ttd' => 'required|mimes:jpg,png,jpeg',
        ]);

        if ($request->hasFile('ttd')) {
            $data['ttd'] = sprintf('storage/%s', $request->file('ttd')->store('ttd'));
        }

        $this->employeeService->create($data);

        return redirect()->route('employee.index')->with('success', 'Data berhasil simpan');
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
        $employee = $this->employeeService->find($id);
        $employeeMaster = $this->employeeService;
        return view('pages.employee.edit', compact('employee', 'employeeMaster'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'nik' => 'required',
            'nama' => 'required',
            'jenis_kelamin' => '',
            'agama' => '',
            'status' => '',
            'tanggal_lahir' => '',
            'tempat_lahir' => '',
            'alamat' => '',
            'golongan_darah' => '',
            'rhesus' => '',
            'pendidikan' => '',
            'telp' => '',
            'email' => '',
            'unit' => '',
            'jabatan' => '',
            'profesi' => '',
            'profesi_detail' => '',
            'warga_negara' => '',
            'nama_bank' => '',
            'nomor_rekening' => '',
            'ttd' => 'mimes:jpg,png,jpeg',
        ]);

        if ($request->hasFile('ttd')) {
            $data['ttd'] = sprintf('storage/%s', $request->file('ttd')->store('ttd'));
        }

        $this->employeeService->update($data, $id);

        return redirect()->route('employee.index')->with('success', 'Data berhasil simpan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->employeeService->delete($id)) {
            return redirect()->route('employee.index')->with('error', 'Data gagal dihapus');
        }
        return redirect()->route('employee.index')->with('success', 'Data berhasil dihapus');
    }

    public function select2(Request $request)
    {
        return $this->employeeService->paginate($request->get('limit', 10));
    }
}
