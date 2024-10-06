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

        // if ($clinetId = \Session::get('client_id')) {
        //     $query = $query->where('client_id', $clinetId);
        // }

        // if ($contractId = \Session::get('contract_id')) {
        //     $query = $query->where('contract_id', $contractId);
        // }

        if (request('is_register_page')) {
            $query = $query->whereNotNull('register_date');
        }

        // $query = $query->where('client_id', Auth::user()->client_id);
        return $query->latest()->paginate($limit)->withQueryString();
    }

    public function create(array $data)
    {
        \DB::beginTransaction();
        try {
            $user = Auth::user();
            $data = $this->mapingPaket($data);
            $sequence = $this->participant->count();
            $data['code'] = sprintf('%s%s', self::PREFIX, str_pad($sequence + 1, 5, '0', STR_PAD_LEFT));
            $data['created_by'] = $user->id;
            $insert = $this->participant->create($data);
            $role = Role::where('level', RoleService::LEVEL_PARTICIPANT)->first();
            $insertUser = User::create([
                'name' => $data['name'],
                'username' => $data['code'],
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
            TandaVital::create($dataParticipant);
            PemeriksaanFisik::create($dataParticipant);
            Laboratorium::create($dataParticipant);
            Radiologi::create($dataParticipant);
            Audiometri::create($dataParticipant);
            Spirometri::create($dataParticipant);
            Rectal::create($dataParticipant);
            Ekg::create($dataParticipant);
            \DB::commit();
            return $insert;
        } catch (\Throwable $th) {
            \DB::rollBack();
            return false;
        }

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
        $data['merokok'] = isset($data['merokok']);
        $data['alergi'] = isset($data['alergi']);
        $data['keluhan_utama'] = isset($data['keluhan_utama']);
        $data['konsumsi_alkohol'] = isset($data['konsumsi_alkohol']);
        $data['riwayat_penyakit_sekarang'] = isset($data['riwayat_penyakit_sekarang']);
        $data['riwayat_penyakit_terdahulu'] = isset($data['riwayat_penyakit_terdahulu']);
        $data['riwayat_trauma'] = isset($data['riwayat_trauma']);
        $data['selesai'] = isset($data['selesai']);
        $data['ttv_diperiksa'] = isset($data['ttv_diperiksa']);
        $data['vaksin_hepatitis'] = isset($data['vaksin_hepatitis']);
        $data['vaksin_tetanus'] = isset($data['vaksin_tetanus']);
        $data['ibu_hamil'] = isset($data['ibu_hamil']);
        $updated = TandaVital::where(['participant_id' => $participantId])->update($data);
        return ['success' => $updated];
    }

    public function updateEkg(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;
        $data['aresst'] = isset($data['aresst']);
        $data['aritmia'] = isset($data['aritmia']);
        $data['bradikardi'] = isset($data['bradikardi']);
        $data['keadaan_jantung_normal'] = isset($data['keadaan_jantung_normal']);
        $data['selesai'] = isset($data['selesai']);
        $data['takikardi'] = isset($data['takikardi']);
        $updated = Ekg::where(['participant_id' => $participantId])->update($data);
        return ['success' => $updated];
    }

    public function updateRectal(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;
        $data['selesai'] = isset($data['selesai']);
        $updated = Rectal::where(['participant_id' => $participantId])->update($data);
        return ['success' => $updated];
    }

    public function updateAudiometri(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;
        $data['selesai'] = isset($data['selesai']);
        $updated = Audiometri::where(['participant_id' => $participantId])->update($data);
        return ['success' => $updated];
    }
    public function updateSpirometri(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;
        $data['selesai'] = isset($data['selesai']);
        $data['lainnya'] = isset($data['lainnya']);
        $data['mixed'] = isset($data['mixed']);
        $data['normal'] = isset($data['normal']);
        $data['obstructive'] = isset($data['obstructive']);
        $data['restrictive'] = isset($data['restrictive']);
        $updated = Spirometri::where(['participant_id' => $participantId])->update($data);
        return ['success' => $updated];
    }

    public function updateRadiologi(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;
        $data['selesai'] = isset($data['selesai']);
        $updated = Radiologi::where(['participant_id' => $participantId])->update($data);
        return ['success' => $updated];
    }
    public function updateLaboratorium(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;
        $data['selesai'] = isset($data['selesai']);
        $updated = Laboratorium::where(['participant_id' => $participantId])->update($data);
        return ['success' => $updated];
    }
    public function updatePemeriksaanFisik(array $data, $participantId)
    {
        $user = Auth::user();
        $data['updated_by'] = $user->id;
        $data['audiometri_diperiksa'] = isset($data['audiometri_diperiksa']);
        $data['ekg_diperiksa'] = isset($data['ekg_diperiksa']);
        $data['ekg_bdn'] = isset($data['ekg_bdn']);
        $data['ekg_tidak_diperiksa'] = isset($data['ekg_tidak_diperiksa']);
        $data['fisik_diperiksa'] = isset($data['fisik_diperiksa']);
        $data['kepala'] = isset($data['kepala']);
        $data['lab_diperiksa'] = isset($data['lab_diperiksa']);
        $data['neurologis_bdn'] = isset($data['neurologis_bdn']);
        $data['neurologis_tidak_diperiksa'] = isset($data['neurologis_tidak_diperiksa']);
        $data['radiologi_diperiksa'] = isset($data['radiologi_diperiksa']);
        $data['rectal_diperiksa'] = isset($data['rectal_diperiksa']);
        $data['selesai_visus'] = isset($data['selesai_visus']);
        $data['spiro_diperiksa'] = isset($data['spiro_diperiksa']);
        $data['tenggorokan'] = isset($data['tenggorokan']);
        $data['visus_diperiksa'] = isset($data['visus_diperiksa']);
        $data['selesai'] = isset($data['selesai']);
        $updated = PemeriksaanFisik::where(['participant_id' => $participantId])->update($data);
        return ['success' => $updated];
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
            'SUPEL' => 'SUPEL',
            'TIDAK' => 'TIDAK',
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
            'FIT ON JOB' => 'FIT ON JOB',
            'FIT WITH RETRICTION' => 'FIT WITH RETRICTION',
            'TEMPORARY UNFIT' => 'TEMPORARY UNFIT',
            '---- UNTUK GSI 1 PILIH DIBAWAH INI ----' => '---- UNTUK GSI PILIH DIBAWAH INI ----',
            'GSI1 - FIT WITH JOB' => 'FIT WITH JOB',
            'GSI1 - FIT WITH NOTE' => 'FIT WITH NOTE',
            'GSI1 - TEMPORARY UNFIT' => 'TEMPORARY UNFIT',
            'GSI1 - DEFINITELY UNFIT' => 'DEFINITELY UNFIT',
            'GSI1 - UNFIT' => 'UNFIT',
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
    private function mapingPaket(array $data)
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
}