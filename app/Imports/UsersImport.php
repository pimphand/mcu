<?php

namespace App\Imports;

use App\Models\Audiometri;
use App\Models\Department;
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
use App\Services\ParticipantService;
use App\Services\RoleService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use App\Models\Divisi;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class UsersImport implements ToModel, WithStartRow, ShouldQueue, WithChunkReading
{

    protected $userId;
    protected $client_id;
    protected $devisi;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, $client_id, $devisi)
    {
        $this->userId = $userId;
        $this->client_id = $client_id;
        $this->devisi = $devisi;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if ($row[0] != null) {
            $sequence = Department::count();
            $devisi = Divisi::firstOrCreate([
                'name' => $this->devisi,
                'client_id' => $this->client_id,
            ], [
                'code' => sprintf('%s-%s', "DV", str_pad(Divisi::count() + 1, 5, '0', STR_PAD_LEFT))
            ]);

            $departemen =  Department::firstOrCreate([
                'name' => $row[2],
                'client_id' => $this->client_id,
            ], [
                'code' => sprintf('%s-%s', "D", str_pad($sequence + 1, 5, '0', STR_PAD_LEFT))
            ]);

            $participantService = new  ParticipantService;
            $data = [
                'nik' => (int)$row[0],
                'name' => $row[1],
                'gender' => $row[3],
                'birthday' => $this->getBirthdateFromAge((int)$row[4]),
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
                'divisi_id' => $devisi->id,
                'department_id' => $departemen->id,
                'client_id' => Session::get('client_id'),
                'contract_id' => null,
            ];

            $user = $this->userId;
            $data = $participantService->mapingPaket($data);
            $data['code'] = sprintf('%s%s', "MCU", str_pad(User::count() + 1, 5, '0', STR_PAD_LEFT));
            $data['created_by'] = $user;
            $insert = Participant::create($data);
            $role = Role::where('level', RoleService::LEVEL_PARTICIPANT)->first();
            $insertUser = User::create([
                'name' => $data['name'],
                'username' => $data['code'],
                'password' => bcrypt($data['birthday']),
                'role_id' => $role->id,
                'client_id' => $data['client_id'],
                'is_active' => 1
            ]);
            Participant::where(['id' => $insert->id])->update(['user_id' => $insertUser->id]);
            $dataParticipant = [
                'participant_id' => $insert->id,
                'created_by' => $user,
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
        }
    }

    public function startRow(): int
    {
        return 2;
    }

    public function getBirthdateFromAge($age)
    {
        $birthdate = Carbon::now()->subYears($age)->format('Y-m-d');

        return $birthdate;
    }

    public function chunkSize(): int
    {
        return 2;
    }
}
