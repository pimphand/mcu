<?php

namespace App\Http\Controllers;

use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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

        $data = $this->storeSignature($request, $data);

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
            // nullable: field ttd yang dibiarkan kosong saat edit tidak boleh
            // menggagalkan validasi.
            'ttd' => 'nullable|mimes:jpg,png,jpeg',
        ]);

        $data = $this->storeSignature($request, $data);

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

    /**
     * Simpan gambar tanda tangan ke public/ttd dan simpan path relatifnya
     * (`ttd/namafile.png`) supaya laporan PDF bisa membacanya lewat
     * public_path() baik di lokal maupun di dalam container Docker.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function storeSignature(Request $request, array $data): array
    {
        if (! $request->hasFile('ttd')) {
            // Form edit selalu mengirim field ttd; pastikan tanda tangan lama
            // tidak tertimpa null ketika tidak ada file baru yang dipilih.
            unset($data['ttd']);

            return $data;
        }

        $file = $request->file('ttd');
        $directory = public_path('ttd');
        File::ensureDirectoryExists($directory);

        $extension = strtolower($file->getClientOriginalExtension() ?: (string) $file->guessExtension());
        $filename = sprintf(
            '%s-%s%s',
            Str::slug($data['nama'] ?? 'ttd') ?: 'ttd',
            Str::random(8),
            $extension !== '' ? '.' . $extension : ''
        );

        $file->move($directory, $filename);

        $data['ttd'] = sprintf('ttd/%s', $filename);

        return $data;
    }
}
