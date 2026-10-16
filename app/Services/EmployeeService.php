<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeeService
{
    const PREFIX = 'E';
    private Employee $employee;
    private User $user;

    /** @var array<string, int> */
    private array $resolvedEmployeeIds = [];

    /** @var array<int, string> */
    private array $createdEmployees = [];

    public function __construct()
    {
        $this->employee = new Employee;
        $this->user = new User;
    }

    /**
     * Cari employee berdasarkan namanya; bila belum terdaftar maka dibuat baru.
     * Dipakai proses import supaya hasil pemeriksaan tidak salah ditempelkan
     * ke dokter lain yang sudah ada.
     */
    public function findOrCreateByName(?string $nama, ?int $clientId = null): ?Employee
    {
        $nama = $this->normalizeName($nama);

        if ($nama === null) {
            return null;
        }

        $employee = $this->findByName($nama, $clientId);

        if ($employee) {
            return $employee;
        }

        $employee = $this->employee->create([
            'nama' => $nama,
            'code' => $this->generateCode(),
            'client_id' => $clientId,
        ]);

        $this->createdEmployees[$employee->id] = $employee->nama;

        Log::info('Employee dibuat otomatis dari import', [
            'employee_id' => $employee->id,
            'nama' => $employee->nama,
            'client_id' => $clientId,
        ]);

        return $employee;
    }

    /**
     * Cari employee tanpa membuat data baru.
     *
     * Nama pemeriksa di file hasil pemeriksaan sering ditulis tanpa gelar
     * ("Murni Islamiati") padahal di master karyawan sudah ada lengkap dengan
     * gelarnya ("Murni Islamiati, A.Md.A.K"). Karena itu nama dicocokkan dua
     * tahap: nama yang sama persis, lalu nama yang hanya berbeda pada gelar
     * setelah tanda koma. Bila ada beberapa karyawan yang cocok, urutannya:
     * nama lengkap dengan gelar, nama yang sama persis dengan file, karyawan
     * milik client sesi MCU IN, lalu id terkecil. Dengan begitu hasil
     * pemeriksaan tidak menempel ke nama yang cuma diketik ulang dari file.
     */
    public function findByName(?string $nama, ?int $clientId = null): ?Employee
    {
        $nama = $this->normalizeName($nama);

        if ($nama === null) {
            return null;
        }

        $base = $this->withoutTitle($nama) ?: $nama;
        $like = addcslashes($base, '%_\\') . ',%';

        $candidates = $this->employee->newQuery()
            ->where(function ($query) use ($base, $like) {
                $query->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($base)])
                    ->orWhereRaw('LOWER(TRIM(nama)) LIKE ?', [mb_strtolower($like)]);
            })
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        $lower = mb_strtolower($nama);

        return $candidates->sortBy(function (Employee $employee) use ($lower, $clientId) {
            $stored = mb_strtolower(trim((string) $employee->nama));

            // Karyawan milik client sesi MCU IN lebih diprioritaskan daripada
            // nama sama pada client lain; id dipakai supaya urutannya stabil.
            $sameClient = $clientId === null
                ? $employee->client_id === null
                : (int) $employee->client_id === $clientId;

            return sprintf(
                '%d%d%d%08d',
                str_contains($stored, ',') ? 0 : 1,
                $stored === $lower ? 0 : 1,
                $sameClient ? 0 : 1,
                $employee->id
            );
        })->first();
    }

    /**
     * Versi cache dari findOrCreateByName: satu nama hanya di-query/dibuat
     * sekali walau muncul ratusan kali dalam satu file import.
     */
    public function findOrCreateIdByName(?string $nama, ?int $clientId = null): ?int
    {
        $nama = $this->normalizeName($nama);

        if ($nama === null) {
            return null;
        }

        $key = mb_strtolower($nama);

        if (! array_key_exists($key, $this->resolvedEmployeeIds)) {
            $this->resolvedEmployeeIds[$key] = $this->findOrCreateByName($nama, $clientId)->id;
        }

        return $this->resolvedEmployeeIds[$key];
    }

    /**
     * Nama employee yang dibuat otomatis selama proses ini berjalan.
     *
     * @return array<int, string>
     */
    public function getCreatedEmployees(): array
    {
        return array_values($this->createdEmployees);
    }

    private function normalizeName(?string $nama): ?string
    {
        $nama = preg_replace('/\s+/', ' ', trim((string) $nama));

        return $nama === '' ? null : $nama;
    }

    /**
     * Nama tanpa gelar: "Murni Islamiati, A.Md.A.K" -> "Murni Islamiati".
     * Hanya gelar setelah tanda koma yang dilepas supaya gelar depan yang
     * memang bagian dari nama (mis. "dr.") tidak ikut terhapus.
     */
    private function withoutTitle(string $nama): string
    {
        return trim((string) preg_replace('/\s*,.*$/su', '', $nama));
    }

    private function generateCode(): string
    {
        $sequence = $this->employee->count();

        do {
            $sequence++;
            $code = sprintf('%s-%s', self::PREFIX, str_pad((string) $sequence, 5, '0', STR_PAD_LEFT));
        } while ($this->employee->where('code', $code)->exists());

        return $code;
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