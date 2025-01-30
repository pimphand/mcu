<?php

namespace App\Services;

use App\Models\Audiometri;
use App\Models\Client;
use App\Models\Ekg;
use App\Models\Laboratorium;
use App\Models\Participant;
use App\Models\PemeriksaanFisik;
use App\Models\Radiologi;
use App\Models\Rectal;
use App\Models\Role;
use App\Models\Spirometri;
use App\Models\TandaVital;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ParticipantService
{
    const PREFIX = 'MCU';
    private Participant $participant;
    public function __construct()
    {
        $this->participant = new Participant;
    }

    public function query()
    {
        return $this->participant->query();
    }

    public function find(int $id)
    {
        return $this->participant->with('user', 'divisi', 'department', 'client', 'contract', 'pemeriksaanFisik', 'laboratorium', 'radiologi', 'audiometri', 'spirometri', 'rectal', 'ekg')->find($id);
    }

    public function paginate(int $limit = 10)
    {
        $query = $this->participant->query()->with('divisi', 'department', 'client', 'contract');
        if ($search = request()->get('search')) {
            $query = $query->where(function ($qb) use ($search) {
                $qb->orWhere('name', 'like', '%' . $search . '%');
                $qb->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        if ($nik = request()->get('nik')) {
            $query = $query->where('nik', $nik);
        }

        if ($name = request()->get('name')) {
            $query = $query->where('name', 'LIKE', "%$name%");
        }

        if ($gender = request()->get('gender')) {
            $query = $query->where('gender', $gender);
        }

        if ($birthday = request()->get('birthday')) {
            $query = $query->where('birthday', $birthday);
        }

        if (request()->get('plant_u')) {
            $query = $query->where('plant_u', true);
        }

        if (request()->get('plan_a')) {
            $query = $query->where('plan_a', true);
        }

        if (request()->get('plan_e')) {
            $query = $query->where('plan_e', true);
        }

        if (request()->get('plan_s')) {
            $query = $query->where('plan_s', true);
        }

        if (request()->get('plan_r')) {
            $query = $query->where('plan_r', true);
        }

        if (request()->get('lab_khusus')) {
            $query = $query->where('lab_khusus', true);
        }

        if (request()->get('paket_a')) {
            $query = $query->where('paket_a', true);
        }

        if (request()->get('paket_b')) {
            $query = $query->where('paket_b', true);
        }

        if (request()->get('paket_c')) {
            $query = $query->where('paket_c', true);
        }

        if (request()->get('paket_d')) {
            $query = $query->where('paket_d', true);
        }

        if (request()->get('paket_e')) {
            $query = $query->where('paket_e', true);
        }

        if (request()->get('paket_f')) {
            $query = $query->where('paket_f', true);
        }

        if ($contractId = Session::get('contract_id')) {
            $query = $query->where('contract_id', $contractId);
        }

        if (request('is_register_page')) {
            $query = $query->whereNotNull('register_date');
        }

        $query = $query->where('client_id', Session::get('client_id'));
        return $query->orderBy('no_form', 'asc')->paginate($limit)->withQueryString();
    }

    public function create(array $data)
    {
        \DB::beginTransaction();
        try {
            $user = Auth::user();
            $data = $this->mapingPaket($data);

            // Generate a unique code
            $data['code'] = $this->generateUniqueCode();

            $data['created_by'] = $user->id;
            $insert = $this->participant->create($data);

            $role = Role::where('level', RoleService::LEVEL_PARTICIPANT)->first();
            $insertUser = User::create([
                'name' => $data['name'],
                'username' => $data['code'] . "_" . $insert->id,
                'password' => bcrypt($data['birthday']),
                'role_id' => $role->id,
                'client_id' => $data['client_id'],
                'is_active' => 1
            ]);

            $this->participant->where(['id' => $insert->id])->update(['user_id' => $insertUser->id]);

            $dataParticipant = [
                'participant_id' => $insert->id,
                'created_by' => $user->id,
                'selesai' => false
            ];

            // Add any other logic as needed

            \DB::commit();
            return $insert;
        } catch (\Throwable $th) {
            \DB::rollBack();
            Log::error($th);
            return false;
        }
    }

    /**
     * Generate a unique code for the participant.
     */
    protected function generateUniqueCode()
    {
        $sequence = $this->participant->count();
        $prefix = self::PREFIX;
        $code = sprintf('%s%s', $prefix, str_pad($sequence + 1, 5, '0', STR_PAD_LEFT));

        // Check for duplicates
        while ($this->participant->where('code', $code)->exists()) {
            $sequence++;
            $code = sprintf('%s%s', $prefix, str_pad($sequence + 1, 5, '0', STR_PAD_LEFT));
        }

        return $code;
    }

    public function update(array $data, $id)
    {
        $user = Auth::user();
        $data = $this->mapingPaket($data);
        $data['updated_by'] = $user->id;
        return $this->participant->where(['id' => $id])->update($data);
    }

    public function delete($id)
    {
        return $this->participant->where('id', $id)->delete();
    }

    public function updateRegister(int $id)
    {

        $find = $this->participant->where('id', $id)->whereNotNull('register_number')->first();
        if ($find) {
            return [
                'success' => false,
                'message' => sprintf('Peserta ini sudah Registrasi pada %s', $find->register_date),
            ];
        }

        $sequence = $this->participant->whereNotNull('register_number')->count();
        $data = [
            'register_number' => sprintf('%s_%s_%s', 'R', Carbon::now()->format('dmy'), str_pad($sequence + 1, 3, '0', STR_PAD_LEFT)),
            'register_date' => Carbon::now()
        ];

        $this->participant->where('id', $id)->update($data);

        return [
            'success' => false,
            'message' => 'Peserta ini berhasil di Registrasi',
        ];
    }

    public function updateTandaVital(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;

        // Normalize boolean fields if needed
        $data['merokok'] = isset($data['merokok']) ? 1 : 0;
        $data['alergi'] = isset($data['alergi']) ? 1 : 0;
        $data['keluhan_utama'] = isset($data['keluhan_utama']) ? 1 : 0;
        $data['konsumsi_alkohol'] = isset($data['konsumsi_alkohol']) ? 1 : 0;
        $data['riwayat_penyakit_sekarang'] = isset($data['riwayat_penyakit_sekarang']) ? 1 : 0;
        $data['riwayat_penyakit_terdahulu'] = isset($data['riwayat_penyakit_terdahulu']) ? 1 : 0;
        $data['riwayat_trauma'] = isset($data['riwayat_trauma']) ? 1 : 0;
        $data['selesai'] = isset($data['selesai']) ? 1 : 0;
        $data['ttv_diperiksa'] = isset($data['ttv_diperiksa']) ? 1 : 0;
        $data['vaksin_hepatitis'] = isset($data['vaksin_hepatitis']) ? 1 : 0;
        $data['vaksin_tetanus'] = isset($data['vaksin_tetanus']) ? 1 : 0;
        $data['ibu_hamil'] = isset($data['ibu_hamil']) ? 1 : 0;
        $data['imt_nilai'] = isset($data['imt_nilai']) ? $data['imt_nilai'] : null;

        // Use updateOrCreate to find by participant_id and update or create accordingly
        $tandaVital = TandaVital::updateOrCreate(
            ['participant_id' => $participantId], // Conditions to find the record
            $data // Data to update or create
        );

        return [
            'success' => true,
            'message' => 'Tanda Vital record saved successfully.',
            'data' => $tandaVital,
            'class' => "tandaVital"
        ];
    }

    public function updateEkg(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;
        $data['created_by'] = $user->id;

        // Normalize boolean fields to store 1 or 0
        $booleanFields = ['aresst', 'aritmia', 'bradikardi', 'keadaan_jantung_normal', 'selesai', 'takikardi'];
        foreach ($booleanFields as $field) {
            $data[$field] = isset($data[$field]) ? 1 : 0; // Convert to 1 (true) or 0 (false)
        }

        // Use updateOrCreate to find by participant_id and update or create accordingly
        $updated = Ekg::updateOrCreate(
            ['participant_id' => $participantId], // Conditions to find the record
            $data // Data to update or create
        );

        return ['success' => true, 'data' => $updated, 'class' => "ekg"];
    }

    public function updateRectal(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;

        // Normalize boolean fields if needed
        $data['selesai'] = isset($data['selesai']) ? 1 : 0; // Convert to 1 (true) or 0 (false)

        // Use updateOrCreate to find by participant_id and update or create accordingly
        $rectal = Rectal::updateOrCreate(
            ['participant_id' => $participantId], // Conditions to find the record
            $data // Data to update or create
        );

        return [
            'success' => true,
            'message' => 'Rectal record saved successfully.',
            'data' => $rectal, // Returning the created or updated record
            'class' => "rectal"
        ];
    }

    public function updateAudiometri(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;

        // Normalize boolean fields if needed
        $data['selesai'] = isset($data['selesai']) ? 1 : 0; // Convert to 1 (true) or 0 (false)

        $audiometri = Audiometri::updateOrCreate(
            ['participant_id' => $participantId], // Conditions to find the record
            $data // Data to update or create
        );

        return [
            'success' => true,
            'message' => 'Audiometri record saved successfully.',
            'data' => $audiometri, // Returning the created or updated record
            'class' => "audiometri"
        ];
    }
    public function updateSpirometri(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;

        // Normalize boolean fields if needed
        $data['selesai'] = isset($data['selesai']) ? 1 : 0; // Convert to 1 (true) or 0 (false)
        $data['lainnya'] = isset($data['lainnya']) ? 1 : 0;
        $data['mixed'] = isset($data['mixed']) ? 1 : 0;
        $data['normal'] = isset($data['normal']) ? 1 : 0;
        $data['obstructive'] = isset($data['obstructive']) ? 1 : 0;
        $data['restrictive'] = isset($data['restrictive']) ? 1 : 0;

        // Use updateOrCreate to find by participant_id and update or create accordingly
        $spirometri = Spirometri::updateOrCreate(
            ['participant_id' => $participantId], // Conditions to find the record
            $data // Data to update or create
        );

        return [
            'success' => true,
            'message' => 'Spirometri record saved successfully.',
            'data' => $spirometri, // Returning the created or updated record
            'class' => "spirometri"
        ];
    }

    public function updateRadiologi(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;

        // Normalize the 'selesai' field to boolean
        $data['selesai'] = isset($data['selesai']) ? 1 : 0; // Convert to 1 (true) or 0 (false)

        // Use updateOrCreate to find by participant_id and update or create accordingly
        $radiologi = Radiologi::updateOrCreate(
            ['participant_id' => $participantId], // Conditions to find the record
            $data // Data to update or create
        );

        return [
            'success' => true,
            'message' => 'Radiologi record saved successfully.',
            'data' => $radiologi, // Returning the created or updated record
            'class' => "radiologi"
        ];
    }

    public function updateLaboratorium(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;

        // Normalize the 'selesai' field to boolean
        $data['selesai'] = isset($data['selesai']) ? 1 : 0; // Convert to 1 (true) or 0 (false)
        $data['hbsag'] = isset($data['hbsag']) && $data['hbsag'] == "on" ? "Positif" : "Negatif"; // Convert to 1 (true) or 0 (false)

        // Use updateOrCreate to find by participant_id and update or create accordingly
        $laboratorium = Laboratorium::updateOrCreate(
            ['participant_id' => $participantId], // Conditions to find the record
            $data // Data to update or create
        );

        return [
            'success' => true,
            'message' => 'Laboratorium record saved successfully.',
            'data' => $laboratorium, // Returning the created or updated record
            'class' => "laboratorium"
        ];
    }

    public function updatePemeriksaanFisik(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;

        // Normalize boolean fields
        $data['audiometri_diperiksa'] = isset($data['audiometri_diperiksa']) ? 1 : 0;
        $data['ekg_diperiksa'] = isset($data['ekg_diperiksa']) ? 1 : 0;
        $data['ekg_bdn'] = isset($data['ekg_bdn']) ? 1 : 0;
        $data['ekg_tidak_diperiksa'] = isset($data['ekg_tidak_diperiksa']) ? 1 : 0;
        $data['fisik_diperiksa'] = isset($data['fisik_diperiksa']) ? 1 : 0;
        $data['kepala'] = isset($data['kepala']) ? 1 : 0;
        $data['lab_diperiksa'] = isset($data['lab_diperiksa']) ? 1 : 0;
        $data['neurologis_bdn'] = isset($data['neurologis_bdn']) ? 1 : 0;
        $data['neurologis_tidak_diperiksa'] = isset($data['neurologis_tidak_diperiksa']) ? 1 : 0;
        $data['radiologi_diperiksa'] = isset($data['radiologi_diperiksa']) ? 1 : 0;
        $data['rectal_diperiksa'] = isset($data['rectal_diperiksa']) ? 1 : 0;
        $data['selesai_visus'] = isset($data['selesai_visus']) ? 1 : 0;
        $data['spiro_diperiksa'] = isset($data['spiro_diperiksa']) ? 1 : 0;
        $data['tenggorokan'] = isset($data['tenggorokan']) ? 1 : 0;
        $data['visus_diperiksa'] = isset($data['visus_diperiksa']) ? 1 : 0;
        $data['selesai'] = isset($data['selesai']) ? 1 : 0;

        // Use updateOrCreate to find by participant_id and update or create accordingly
        $pemeriksaanFisik = PemeriksaanFisik::updateOrCreate(
            ['participant_id' => $participantId], // Conditions to find the record
            $data // Data to update or create
        );

        return [
            'success' => true,
            'message' => 'Pemeriksaan Fisik record saved successfully.',
            'data' => $pemeriksaanFisik, // Returning the created or updated record
            'class' => "pemeriksaanFisik"
        ];
    }


    public function updateFotoKamera(array $data, $participantId)
    {
        $image_64 = $data['photo']; //your base64 encoded data

        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);

        // find substring fro replace here eg: data:image/png;base64,

        $image = str_replace($replace, '', $image_64);

        $image = str_replace(' ', '+', $image);

        $imageName = \Str::random(10) . '.' . $extension;

        \Storage::disk('public')->put($imageName, base64_decode($image));

        $data['photo'] = $imageName;
        return $this->updateFotoKomputer($data, $participantId);
    }

    public function updateFotoKomputer(array $data, $participantId)
    {
        $participant = $this->participant->find($participantId);
        $data['photo'] = sprintf('%s/%s', 'storage', $data['photo']);
        $updated = User::where(['id' => $participant->user_id])->update($data);
        return ['success' => $updated];
    }

    public function scan(string $mcuId)
    {
        $check = $this->participant->where('code', $mcuId)->first();
        return ['route' => $check ? route('participant.detail', $check->id) : null];
    }

    public function getClient()
    {
        return Client::with('contracts', 'divisi')->find(\Session::get('client_id'));
    }
    public function getJK(): array
    {
        return [
            'L' => 'Laki-laki',
            'P' => 'Perempuan'
        ];
    }

    public function getStatusKaryawan(): array
    {
        return [
            'Calon Karyawan' => 'Calon Karyawan',
            'Karyawan' => 'Karyawan',
            'Karyawan Baru' => 'Karyawan Baru',
            'Karyawan Kontrak' => 'Karyawan kontrak',
            'Karyawan Lama' => 'Karyawan Lama',
            'Karyawan Tetap' => 'Karyawan Tetap',
        ];
    }

    public function getKeadaanUmum(): array
    {
        return [
            'Compost Mentis' => 'Compost Mentis',
            'Somnolen' => 'Somnolen'
        ];
    }
    public function getButaWarna(): array
    {
        return [
            'NORMAL' => 'NORMAL',
            'BW PARSIAL' => 'BW PARSIAL',
            'BW TOTAL' => 'BW TOTAL',
        ];
    }
    public function getPupil(): array
    {
        return [
            'ISOKOR' => 'ISOKOR',
            'NISOKOR' => 'NISOKOR'
        ];
    }
    public function getKelainanTelinga(): array
    {
        return [
            'NORMAL' => 'NORMAL',
            'KELAINAN KONGENITAL' => 'KELAINAN KONGENITAL'
        ];
    }
    public function getGigi(): array
    {
        return [
            'ATAS' => [
                'KANAN' => [
                    '4' => [
                        'KAA4:Karies' => 'Gigi No: 4 Posisi: KANAN ATAS=Karies',
                        'KAA4:Tanggal' => 'Gigi No: 4 Posisi: KANAN ATAS=Tanggal',
                    ],
                    '3' => [
                        'KAA3:Karies' => 'Gigi No: 3 Posisi: KANAN ATAS=Karies',
                        'KAA3:Tanggal' => 'Gigi No: 3 Posisi: KANAN ATAS=Tanggal',
                    ],
                    '2' => [
                        'KAA2:Karies' => 'Gigi No: 2 Posisi: KANAN ATAS=Karies',
                        'KAA2:Tanggal' => 'Gigi No: 2 Posisi: KANAN ATAS=Tanggal',
                    ],
                    '1' => [
                        'KAA1:Karies' => 'Gigi No: 1 Posisi: KANAN ATAS=Karies',
                        'KAA1:Tanggal' => 'Gigi No: 1 Posisi: KANAN ATAS=Tanggal',
                    ]
                ],
                'KIRI' => [

                    '1' => [
                        'KIA1:Karies' => 'Gigi No: 1 Posisi: KIRI ATAS=Karies',
                        'KIA1:Tanggal' => 'Gigi No: 1 Posisi: KIRI ATAS=Tanggal',
                    ],
                    '2' => [
                        'KIA2:Karies' => 'Gigi No: 2 Posisi: KIRI ATAS=Karies',
                        'KIA2:Tanggal' => 'Gigi No: 2 Posisi: KIRI ATAS=Tanggal',
                    ],
                    '3' => [
                        'KIA3:Karies' => 'Gigi No: 3 Posisi: KIRI ATAS=Karies',
                        'KIA3:Tanggal' => 'Gigi No: 3 Posisi: KIRI ATAS=Tanggal',
                    ],
                    '4' => [
                        'KIA4:Karies' => 'Gigi No: 4 Posisi: KIRI ATAS=Karies',
                        'KIA4:Tanggal' => 'Gigi No: 4 Posisi: KIRI ATAS=Tanggal',
                    ],
                ]
            ],
            'BAWAH' => [
                'KANAN' => [
                    '4' => [
                        'KAB4:Karies' => 'Gigi No: 4 Posisi: KANAN BAWAH=Karies',
                        'KAB4:Tanggal' => 'Gigi No: 4 Posisi: KANAN BAWAH=Tanggal',
                    ],
                    '3' => [
                        'KAB3:Karies' => 'Gigi No: 3 Posisi: KANAN BAWAH=Karies',
                        'KAB3:Tanggal' => 'Gigi No: 3 Posisi: KANAN BAWAH=Tanggal',
                    ],
                    '2' => [
                        'KAB2:Karies' => 'Gigi No: 2 Posisi: KANAN BAWAH=Karies',
                        'KAB2:Tanggal' => 'Gigi No: 2 Posisi: KANAN BAWAH=Tanggal',
                    ],
                    '1' => [
                        'KAB1:Karies' => 'Gigi No: 1 Posisi: KANAN BAWAH=Karies',
                        'KAB1:Tanggal' => 'Gigi No: 1 Posisi: KANAN BAWAH=Tanggal',
                    ]
                ],
                'KIRI' => [

                    '1' => [
                        'KIB1:Karies' => 'Gigi No: 1 Posisi: KIRI BAWAH=Karies',
                        'KIB1:Tanggal' => 'Gigi No: 1 Posisi: KIRI BAWAH=Tanggal',
                    ],
                    '2' => [
                        'KIB2:Karies' => 'Gigi No: 2 Posisi: KIRI BAWAH=Karies',
                        'KIB2:Tanggal' => 'Gigi No: 2 Posisi: KIRI BAWAH=Tanggal',
                    ],
                    '3' => [
                        'KIB3:Karies' => 'Gigi No: 3 Posisi: KIRI BAWAH=Karies',
                        'KIB3:Tanggal' => 'Gigi No: 3 Posisi: KIRI BAWAH=Tanggal',
                    ],
                    '4' => [
                        'KIB4:Karies' => 'Gigi No: 4 Posisi: KIRI BAWAH=Karies',
                        'KIB4:Tanggal' => 'Gigi No: 4 Posisi: KIRI BAWAH=Tanggal',
                    ],
                ]
            ]
        ];
    }
    public function getTenggorokan(): array
    {
        return [
            'kanan' => [
                'T0',
                'T1',
                'T2',
                'T3',
                'T4',
            ],
            'kiri' => [
                'T0',
                'T1',
                'T2',
                'T3',
                'T4',
                'Tenang',
            ]
        ];
    }

    public function getFaring()
    {
        return [
            'HIPEREMIS' => 'HIPEREMIS',
            'TIDAK HIPEREMIS' => 'TIDAK HIPEREMIS',
        ];
    }
    public function getLeherKGB()
    {
        return [
            'PEMBESARAN +' => 'PEMBESARAN +',
            'PEMBESARAN -' => 'PEMBESARAN -',
        ];
    }
    public function getLeherJVP()
    {
        return [
            'MENINGKAT +' => 'MENINGKAT +',
            'MENINGKAT -' => 'MENINGKAT -',
        ];
    }

    public function getInspeksi()
    {
        return [
            'Pergerakan Dada Simetris' => 'Pergerakan Dada Simetris',
            'Pergerakan Dada Tidak Simetris' => 'Pergerakan Dada Tidak Simetris',
        ];
    }

    public function abdomenInspeksi()
    {
        return [
            'SUPEL' => 'SUPEL',
            'TIDAK' => 'TIDAK'
        ];
    }
    public function getAuskultasi()
    {
        return [
            'BJ I + BJ II NORMAL' => 'BJ I + BJ II NORMAL',
            'BJ I + BJ II TIDAK NORMAL' => 'BJ I + BJ II TIDAK NORMAL',
        ];
    }
    public function getAuskultasi2()
    {
        return [
            'BISING USUS NORMAL' => 'BISING USUS NORMAL',
            'BISING USUS MENINGKAT' => 'BISING USUS MENINGKAT',
            'BISING USUS TURUN' => 'BISING USUS TURUN',
        ];
    }
    public function getPalpasi()
    {
        return [
            'IC TERABA' => 'IC TERABA',
            'IC TIDAK TERABA' => 'IC TIDAK TERABA',
        ];
    }
    public function getPerkusi()
    {
        return [
            'SONOR' => 'SONOR',
            'REDUP' => 'REDUP',
            'DALL' => 'DALL',
        ];
    }
    public function getPerkusi2()
    {
        return [
            'TIMPANI' => 'TIMPANI',
            'REDUP' => 'REDUP',
        ];
    }
    public function getKesimpulan()
    {
        return [
            'FIT' => 'FIT',
            'FIT WITH RESTRICTION' => 'FIT WITH RESTRICTION',
            'UNFIT' => 'UNFIT',
            '---- UNTUK GSI 1 PILIH DIBAWAH INI ----' => '---- UNTUK GSI PILIH DIBAWAH INI ----',
            'GSI1 - FIT WITH JOB' => 'FIT WITH JOB',
            'GSI1 - FIT WITH NOTE' => 'FIT WITH NOTE',
            'GSI1 - UNFIT' => 'UNFIT',
            'GSI1 - DEFINITELY UNFIT' => 'DEFINITELY UNFIT',
        ];
    }
    public function getRetriksi()
    {
        return [
            'RETRIKSI RINGAN' => 'RETRIKSI RINGAN',
            'RETRIKSI SEDANG' => 'RETRIKSI SEDANG',
        ];
    }
    public function getObstruktif()
    {
        return [
            'OBSTRUKTIF RINGAN' => 'OBSTRUKTIF RINGAN',
            'OBSTRUKTIF SEDANG' => 'OBSTRUKTIF SEDANG',
        ];
    }

    public function getObstruksif()
    {
        return [
            'OBSTRUKTIF RINGAN' => 'OBSTRUKTIF RINGAN',
            'OBSTRUKTIF SEDANG' => 'OBSTRUKTIF SEDANG',
        ];
    }
    public function getPendengaranTelingaKanan()
    {
        return [
            'Normal: 0-25 dB' => 'Normal: 0-25 dB',
            'Gangguan Ringan: 25-40 dB' => 'Gangguan Ringan: 25-40 dB',
            'Gangguan Sedang: 41-65 dB' => 'Gangguan Sedang: 41-65 dB',
            'Gangguan Berat: 66-90 dB' => 'Gangguan Berat: 66-90 dB',
            'Gangguan Sangat Berat: lebih dari 90 dB' => 'Gangguan Sangat Berat: lebih dari 90 dB',
        ];
    }

    public function getPendengaranTelingaKiri()
    {
        return [
            'Normal: 0-25 dB' => 'Normal: 0-25 dB',
            'Gangguan Ringan: 25-40 dB' => 'Gangguan Ringan: 25-40 dB',
            'Gangguan Sedang: 41-65 dB' => 'Gangguan Sedang: 41-65 dB',
            'Gangguan Berat: 66-90 dB' => 'Gangguan Berat: 66-90 dB',
            'Gangguan Sangat Berat: lebih dari 90 dB' => 'Gangguan Sangat Berat: lebih dari 90 dB',
        ];
    }
    public function mapingPaket(array $data)
    {
        if (isset($data['packet_a'])) {
            $data['packet_a'] = true;
        } else {
            $data['packet_a'] = false;
        }
        if (isset($data['packet_b'])) {
            $data['packet_b'] = true;
        } else {
            $data['packet_b'] = false;
        }
        if (isset($data['packet_c'])) {
            $data['packet_c'] = true;
        } else {
            $data['packet_c'] = false;
        }
        if (isset($data['packet_d'])) {
            $data['packet_d'] = true;
        } else {
            $data['packet_d'] = false;
        }
        if (isset($data['packet_e'])) {
            $data['packet_e'] = true;
        } else {
            $data['packet_e'] = false;
        }
        if (isset($data['packet_f'])) {
            $data['packet_f'] = true;
        } else {
            $data['packet_f'] = false;
        }

        if (isset($data['plan_u'])) {
            $data['plan_u'] = true;
        } else {
            $data['plan_u'] = false;
        }
        if (isset($data['plan_a'])) {
            $data['plan_a'] = true;
        } else {
            $data['plan_a'] = false;
        }
        if (isset($data['plan_e'])) {
            $data['plan_e'] = true;
        } else {
            $data['plan_e'] = false;
        }
        if (isset($data['plan_s'])) {
            $data['plan_s'] = true;
        } else {
            $data['plan_s'] = false;
        }
        if (isset($data['plan_r'])) {
            $data['plan_r'] = true;
        } else {
            $data['plan_r'] = false;
        }
        if (isset($data['lab_special'])) {
            $data['lab_special'] = true;
        } else {
            $data['lab_special'] = false;
        }

        return $data;
    }

    public function import() {}
}
