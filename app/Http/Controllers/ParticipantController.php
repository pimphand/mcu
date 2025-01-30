<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Models\Employee;
use App\Models\Participant;
use App\Services\DivisiService;
use App\Services\ParticipantService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ParticipantController extends Controller
{
    private ParticipantService $participantService;
    private DivisiService $divisiService;

    private $employees;
    public function __construct(ParticipantService $participantService, DivisiService $divisiService)
    {
        $this->participantService = $participantService;
        $this->divisiService = $divisiService;

        $this->employees = Employee::all();
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $participants = $this->participantService->paginate($request->get('limit', 10));
        $isRegisterPage = false;
        return view('pages.participant.index', compact('participants', 'isRegisterPage'));
    }

    public function register(Request $request)
    {
        $request->merge(['is_register_page' => true]);
        $participants = $this->participantService
            ->paginate($request->get('limit', 10));
        $isRegisterPage = true;

        return view('pages.participant.index', compact('participants', 'isRegisterPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $participant = $this->participantService;
        return view('pages.participant.create', compact('participant'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function filter(Request $request)
    {
        $participant = $this->participantService;
        return view('pages.participant.filter', compact('participant'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function scan(string $mcuId)
    {
        return $this->participantService->scan($mcuId);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nik' => 'required',
            'name' => 'required',
            'gender' => 'required',
            'birthday' => 'required|date',
            'phone' => '',
            'status' => '',
            'packet_name' => '',
            'packet_a' => '',
            'packet_b' => '',
            'packet_c' => '',
            'packet_d' => '',
            'packet_e' => '',
            'packet_f' => '',
            'plan_name' => '',
            'plan_u' => '',
            'plan_a' => '',
            'plan_e' => '',
            'plan_s' => '',
            'plan_r' => '',
            'lab_special' => '',
            'divisi_id' => '',
            'department_id' => '',
            'client_id' => '',
            'contract_id' => '',
        ]);

        $this->participantService->create($data);

        return redirect()->route('participant.index')->with('success', 'Data berhasil simpan');
    }

    /**
     * Display the specified resource.
     */
    public function detail(string $id)
    {
        $participant = $this->participantService->find($id);
        // dd($participant->ekg);
        return view('pages.participant.detail', compact('participant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $participants = $this->participantService;
        $participant = $this->participantService->find($id);
        return view('pages.participant.edit', compact('participants', 'participant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'nik' => 'required',
            'name' => 'required',
            'gender' => 'required',
            'birthday' => 'required|date',
            'phone' => '',
            'status' => '',
            'packet_name' => '',
            'packet_a' => '',
            'packet_b' => '',
            'packet_c' => '',
            'packet_d' => '',
            'packet_e' => '',
            'packet_f' => '',
            'plan_name' => '',
            'plan_u' => '',
            'plan_a' => '',
            'plan_e' => '',
            'plan_s' => '',
            'plan_r' => '',
            'lab_special' => '',
            'divisi_id' => '',
            'department_id' => '',
        ]);

        $this->participantService->update($data, $id);

        return redirect()->route('participant.index')->with('success', 'Data berhasil simpan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->participantService->delete($id)) {
            return redirect()->route('participant.index')->with('error', 'Data gagal dihapus');
        }
        return redirect()->route('participant.index')->with('success', 'Data berhasil dihapus');
    }

    public function updateRegister(int $id)
    {
        return $this->participantService->updateRegister($id);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function detailTandaVital(int $id)
    {
        $participant = $this->participantService->find($id);
        $employees = $this->employees;

        return view('pages.participant.tanda-vital', compact('participant', 'employees'));
    }

    public function updateTandaVital(Request $request, int $id)
    {
        $data = $request->validate([
            'merokok' => '',
            'keluhan_utama' => '',
            'keluhan_utama_text' => '',
            'riwayat_penyakit_sekarang' => '',
            'riwayat_penyakit_sekarang_text' => '',
            'riwayat_penyakit_terdahulu' => '',
            'riwayat_penyakit_terdahulu_text' => '',
            'alergi' => '',
            'alergi_text' => '',
            'konsumsi_alkohol' => '',
            'riwayat_trauma' => '',
            'riwayat_trauma_text' => '',
            'tinggi_badan' => '',
            'berat_badan' => '',
            'imt' => '',
            'nilai_imt' => '',
            'tekanan_darah' => '',
            'frekuensi_nadi' => '',
            'suhu' => '',
            'frekuensi_nafas' => '',
            'ttv_diperiksa' => '',
            'ibu_hamil' => '',
            'imt_nilai' => '',
            'selesai' => '',
            'employee_id' => '',
            'vaksin_hepatitis' => '',
            'vaksin_tetanus' => ''
        ]);
        return $this->participantService->updateTandaVital($data, $id);
    }

    public function detailPemeriksaanFisik(int $id)
    {
        $participant = $this->participantService->find($id);
        $participants = $this->participantService;
        $employees = $this->employees;

        return view('pages.participant.pemeriksaan-fisik', compact('participant', 'participants', 'employees'));
    }

    public function updatePemeriksaanFisik(Request $request, int $id)
    {
        $data = $request->validate([
            'keadaan_umum' => '',
            'kepala' => '',
            'kepala_text' => '',
            'hidung' => '',
            'mata' => '',
            'pupil' => '',
            'visus' => '',
            'buta_warna' => '',
            'telinga' => '',
            'kelainan_telinga' => '',
            'gigi' => '',
            'kode_gigi' => '',
            'bibir' => '',
            'lidah' => '',
            'tenggorokan' => '',
            'tenggorokan_text' => '',
            'faring' => '',
            'leher_kgb' => '',
            'leher_jvp' => '',
            'jantung_inspeksi' => '',
            'jantung_auskultasi' => '',
            'jantung_palpasi' => '',
            'jantung_perkusi' => '',
            'paru_inspeksi' => '',
            'paru_auskultasi_vasikuler' => '',
            'paru_auskultasi_ronkhi' => '',
            'paru_auskultasi_wheezing' => '',
            'paru_palpasi' => '',
            'paru_perkusi' => '',
            'abdomen_inspeksi' => '',
            'abdomen_palpasi' => '',
            'abdomen_auskultasi' => '',
            'abdomen_perkusi' => '',
            'reflex_fisiologis_atas' => '',
            'reflex_phatologis_atas' => '',
            'reflex_fisiologis_bawah' => '',
            'reflex_phatologis_bawah' => '',
            'ekg_tidak_diperiksa' => '',
            'ekg_bdn' => '',
            'ekg_text' => '',
            'neurologis_tidak_diperiksa' => '',
            'neurologis_bdn' => '',
            'neurologis_text' => '',
            'employee_id' => '',
            'fisik_diperiksa' => '',
            'selesai' => '',
            'visus_diperiksa' => '',
            'selesai_visus' => '',
            'lab_diperiksa' => '',
            'radiologi_diperiksa' => '',
            'audiometri_diperiksa' => '',
            'ekg_diperiksa' => '',
            'spiro_diperiksa' => '',
            'rectal_diperiksa' => '',
            'kesimpulan' => '',
            'saran' => '',
        ]);
        return $this->participantService->updatePemeriksaanFisik($data, $id);
    }

    public function detailLaboratorium(int $id)
    {
        $participant = $this->participantService->find($id);
        $employees = $this->employees;
        return view('pages.participant.laboratorium', compact('participant', 'employees'));
    }

    public function updateLaboratorium(Request $request, int $id)
    {
        $data = $request->validate([
            'hemoglobin' => '',
            'hematokrit' => '',
            'lekosit' => '',
            'trombosit' => '',
            'eritrosit' => '',
            'basofil' => '',
            'eosinofil' => '',
            'batang' => '',
            'segmen' => '',
            'limfosit' => '',
            'monosit' => '',
            'sgot' => '',
            'sgpt' => '',
            'ureum' => '',
            'creatinin' => '',
            'kesimpulan' => '',
            'employee_id' => '',
            'selesai' => '',
            'glukosa_puasa' => '',
            'cholesterol_total' => '',
            'asam_urat' => '',
            'glukosa_sewaktu' => '',
            'trigliserida' => '',
            'hdl_cholesterol' => '',
            'ldl_cholestero' => '',
            'reduksi' => '',
            'berat_jenis' => '',
            'ph_reaksi' => '',
            'warna' => '',
            'kekeruhan' => '',
            'urobilinogen' => '',
            'bilirubin' => '',
            'eritrosit_urine' => '',
            'keton' => '',
            'protein' => '',
            'sedimen_epitel' => '',
            'sedimen_eritrosit' => '',
            'sedimen_leukosit' => '',
            'sedimen_bakteri' => '',
            'sedimen_kristal' => '',
            'hbsag' => '',
        ]);


        return $this->participantService->updateLaboratorium($data, $id);
    }

    public function detailRadiologi(int $id)
    {
        $participant = $this->participantService->find($id);
        $employees = $this->employees;
        return view('pages.participant.radiologi', compact('participant', 'employees'));
    }

    public function updateRadiologi(Request $request, int $id)
    {
        $data = $request->validate([
            'cor' => '',
            'diafragma_sinus' => '',
            'pulmo' => '',
            'kesan' => '',
            'employee_id' => '',
            'selesai' => '',
        ]);

        return $this->participantService->updateRadiologi($data, $id);
    }

    public function detailAudiometri(int $id)
    {
        $participant = $this->participantService->find($id);
        $participants = $this->participantService;
        $employees = $this->employees;
        return view('pages.participant.audiometri', compact('participant', 'participants', 'employees'));
    }

    public function updateAudiometri(Request $request, int $id)
    {
        $data = $request->validate([
            'audiometri_telinga_kanan' => '',
            'audiometri_telinga_kiri' => '',
            'pendengaran_telinga_kanan' => '',
            'pendengaran_telinga_kiri' => '',
            'kesimpulan' => '',
            'saran' => '',
            'selesai' => '',
            'employee_id' => ''
        ]);
        return $this->participantService->updateAudiometri($data, $id);
    }

    public function detailSpirometri(int $id)
    {
        $participant = $this->participantService->find($id);
        $participants = $this->participantService;
        $employees = $this->employees;
        return view('pages.participant.spirometri', compact('participant', 'participants', 'employees'));
    }

    public function updateSpirometri(Request $request, int $id)
    {
        $data = $request->validate([
            'normal' => '',
            'restrictive' => '',
            'obstructive' => '',
            'mixed' => '',
            'lainnya' => '',
            'hasil' => '',
            'employee_id' => '',
            'retriksi' => '',
            'obstruksif' => '',
            'selesai' => ''
        ]);

        return $this->participantService->updateSpirometri($data, $id);
    }

    public function detailRectal(int $id)
    {
        $participant = $this->participantService->find($id);

        $employees = $this->employees;

        return view('pages.participant.rectal', compact('participant', 'employees'));
    }

    public function updateRectal(Request $request, int $id)
    {
        $data = $request->validate([
            'salmonella_thypi' => '',
            'shigella_sp' => '',
            'e_coli_pathogen' => '',
            'kesimpulan' => '',
            'employee_id' => '',
            'selesai' => ''
        ]);

        return $this->participantService->updateRectal($data, $id);
    }

    public function detailEkg(int $id)
    {
        $participant = $this->participantService->find($id);
        $employees = $this->employees;

        return view('pages.participant.ekg', compact('participant', 'employees'));
    }

    public function updateEkg(Request $request, int $id)
    {
        $data = $request->validate([
            'takikardi' => '',
            'bradikardi' => '',
            'aritmia' => '',
            'aresst' => '',
            'penemuan_lain' => '',
            'keadaan_jantung_normal' => '',
            'kesimpulan' => '',
            'employee_id' => '',
            'selesai' => ''
        ]);

        return $this->participantService->updateEkg($data, $id);
    }

    public function printMCU(Request $request)
    {
        // dd($request->all());
        $data = [];
        $client = $this->participantService->getClient();
        if ($request->filter) {
            $data = QueryBuilder::for(Participant::class)
                ->allowedFilters([
                    AllowedFilter::exact('client_id'),
                    AllowedFilter::exact('contract_id'),
                    AllowedFilter::exact('divisi_id'),
                    AllowedFilter::scope('date_range'),
                ])
                ->defaultSort('no_form')
                ->get();
        }
        return view('pages.participant.print-mcu', compact('client', 'data'));
    }

    public function detailFotoKamera(int $id)
    {
        $participant = $this->participantService->find($id);
        return view('pages.participant.foto-kamera', compact('participant', 'data'));
    }

    public function updateFotoKamera(Request $request, int $id)
    {
        $data = $request->validate([
            'photo' => 'required',
        ]);

        return $this->participantService->updateFotoKamera($data, $id);
    }

    public function detailFotoKomputer(int $id)
    {
        $participant = $this->participantService->find($id);
        return view('pages.participant.foto-komputer', compact('participant'));
    }

    public function updateFotoKomputer(Request $request, int $id)
    {
        $data = $request->validate([
            'photo' => 'mimes:jpg,png,jpeg',
        ]);

        if (!$request->hasFile('photo')) {
            return ['success' => false];
        }
        $data['photo'] = $request->file('photo')->store('photo');
        return $this->participantService->updateFotoKomputer($data, $id);
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::queueImport(new UsersImport(auth()->id(), auth()->user()->client_id, $request->devisi), $request->file('file'));

        return redirect()->route('participant.index')->with('success', 'Data berhasil diimport');
    }
}
