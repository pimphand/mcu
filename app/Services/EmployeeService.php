<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmployeeService
{
    const PREFIX = 'E';
    private Employee $employee;
    private User $user;
    public function __construct()
    {
        $this->employee = new Employee;
        $this->user = new User;
    }

    public function query()
    {
        return $this->employee->query();
    }

    public function find(int $id)
    {
        return $this->employee->find($id);
    }

    public function paginate(int $limit = 10)
    {
        $query = $this->employee->query();
        if ($search = request()->get('search')) {
            $query = $query->where(function ($qb) use ($search) {
                $qb->orWhere('nama', 'like', '%' . $search . '%');
                $qb->orWhere('code', 'like', '%' . $search . '%');
                $qb->orWhere('nik', 'like', '%' . $search . '%');
            });
        }
        return $query->latest()->paginate($limit)->withQueryString();
    }

    public function create(array $data)
    {
        $sequence = $this->employee->count();
        $data['code'] = sprintf('%s-%s', self::PREFIX, str_pad($sequence + 1, 5, '0', STR_PAD_LEFT));
        $insert = $this->employee->create($data);
        if ($insert) {
            $user = Auth::user();
            $role = Role::where('level', RoleService::LEVEL_EMPLOYEE)->first();
            $dataUser = [
                'name' => $insert->nama,
                'username' => $insert->code,
                'email' => $insert->email,
                'password' => bcrypt($insert->code),
                'role_id' => $role->id,
                'client_id' => $user->client_id,
                'is_active' => 0
            ];
            User::create($dataUser);
        }
        return $insert;
    }

    public function update(array $data, $id)
    {
        return $this->employee->where(['id' => $id])->update($data);
    }

    public function delete($id)
    {
        $employee = $this->employee->find($id);
        if ($employee) {
            $checkRelation = $this->user->where(['username' => $employee->code])->exists();
            if ($checkRelation) {
                return false;
            }
        }

        return $this->employee->where('id', $id)->delete();
    }

    public function getStatusKaryawan(): array
    {
        return [
            'Tetap' => 'Karyawan dengan status tetap di perusahaan',
            'Kontrak' => 'Karyawan dengan status kontrak sementara',
            'Magang' => 'Karyawan yang sedang menjalani program magang',
            'Penuh Waktu' => 'Karyawan dengan jam kerja penuh waktu',
            'Paruh Waktu' => 'Karyawan dengan jam kerja paruh waktu',
            'Freelance' => 'Karyawan yang bekerja secara lepas',
            'Outsource' => 'Karyawan yang direkrut dari pihak ketiga',
            'Pensiun' => 'Karyawan yang telah pensiun dari pekerjaan',
            'Resign' => 'Karyawan yang telah mengundurkan diri'
        ];
    }

    public function getStatusKtp(): array
    {
        return [
            'Belum Kawin' => 'Belum Kawin',
            'Kawin' => 'Kawin',
            'Cerai Hidup' => 'Cerai Hidup',
            'Cerai Mati' => 'Cerai Mati',
            'Janda' => 'Janda',
            'Duda' => 'Duda',
        ];
    }

    public function getPendidikan(): array
    {
        return [
            'SD' => 'SD',
            'SMP' => 'SMP',
            'SMA' => 'SMA',
            'D1' => 'Diploma 1',
            'D2' => 'Diploma 2',
            'D3' => 'Diploma 3',
            'D4' => 'Diploma 4',
            'S1' => 'Sarjana (S1)',
            'S2' => 'Magister (S2)',
            'S3' => 'Doktor (S3)',
        ];
    }

    public function getAgama(): array
    {
        return [
            'Islam' => 'Islam',
            'Protestan' => 'Protestan',
            'Katolik' => 'Katolik',
            'Hindu' => 'Hindu',
            'Buddha' => 'Buddha',
            'Khonghucu' => 'Khonghucu',
        ];
    }

    public function getJK(): array
    {
        return [
            'L' => 'Laki-laki',
            'P' => 'Perempuan'
        ];
    }

    public function getGolonganDarah(): array
    {
        return [
            'A' => 'A',
            'B' => 'B',
            'AB' => 'AB',
            'O' => 'O',
            'A+' => 'A+',
            'B+' => 'B+',
            'AB+' => 'AB+',
            'O+' => 'O+',
            'A-' => 'A-',
            'B-' => 'B-',
            'AB-' => 'AB-',
            'O-' => 'O-',
        ];
    }

    public function getStatusWargaNegara(): array
    {
        return [
            'WNI' => 'Warga Negara Indonesia',
            'WNA' => 'Warga Negara Asing'
        ];
    }
}