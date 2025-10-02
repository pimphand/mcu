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

class UsersImport implements ToModel, WithStartRow, WithChunkReading //, ShouldQueue
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
                'name' => $row[3],
                'client_id' => Session::get('client_id'),
            ], [
                'code' => sprintf('%s-%s', "DV", str_pad(Divisi::count() + 1, 5, '0', STR_PAD_LEFT))
            ]);

            $departemen =  Department::firstOrCreate([
                'name' => $row[3],
                'client_id' => Session::get('client_id'),
            ], [
                'code' => sprintf('%s-%s', "D", str_pad($sequence + 1, 5, '0', STR_PAD_LEFT))
            ]);

            $participantService = new  ParticipantService;
            $plans = ['U', 'A', 'E', 'S', 'R'];
            $selected = [];
            if (!empty($row[6])) {
                $selected = array_map('trim', explode('+', $row[6]));
            }

            $data = [
                'nik' => $row[1],
                'name' => $row[2],
                'gender' => $row[4],
                'birthday' => $this->convertToISODate($row[5]),
                'phone' => '',
                'status' => '',
                'packet_name' => '',
                'packet_a' => false,
                'packet_b' => false,
                'packet_c' => false,
                'packet_d' => false,
                'packet_e' => false,
                'packet_f' => false,
                'plan_name' => $row[6],
                'plan_u' => false,
                'plan_a' => false,
                'plan_e' => false,
                'plan_s' => false,
                'plan_r' => false,
                'lab_special' => false,
                'divisi_id' => $devisi->id,
                'department_id' => $departemen->id,
                'client_id' => Session::get('client_id'),
                'contract_id' => Session::get('contract_id'),
                'no_form' => (int)$row[0],
            ];

            foreach ($plans as $p) {

                $data['plan_' . strtolower($p)] = in_array($p, $selected) ? 1 : 0;
            }

            $user = $this->userId;
            $data['code'] = sprintf('%s%s', "MCU", str_pad((int)$row[0], 5, '0', STR_PAD_LEFT));
            $data['created_by'] = $user;
            $insert = Participant::updateOrCreate([
                'client_id' => Session::get('client_id'),
                'contract_id' => Session::get('contract_id'),
                'no_form' => (int)$row[0],
            ], $data);
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

    function excelSerialDateToISO($serialDate, $baseDate = '1899-12-30')
    {
        // Set tanggal referensi dinamis dengan Carbon
        $date = Carbon::parse($baseDate)->addDays($serialDate);
        return $date->format('Y-m-d');
    }

    // Fungsi untuk mengonversi tanggal teks `m/d/yy` atau `d/m/Y` ke format ISO `YYYY-MM-DD`
    function convertDateToISO($date)
    {
        if ($date === null || $date === '') {
            return null;
        }

        $date = trim($date);
        // Samakan delimiter ke /
        $normalized = str_replace(['-', '.'], '/', $date);

        // Coba beberapa format umum secara berurutan
        $formats = [
            'Y/m/d',    // 1988/07/02
            'Y/n/j',    // 1988/7/2
            'd/m/Y',    // 02/07/1988 (umum di ID)
            'j/n/Y',    // 2/7/1988
            'm/d/Y',    // 07/02/1988 (US)
            'n/j/Y',    // 7/2/1988 (US)
            'd/m/y',    // 02/07/88
            'j/n/y',    // 2/7/88
            'm/d/y',    // 07/02/88
            'n/j/y',    // 7/2/88
        ];

        foreach ($formats as $format) {
            try {
                $dt = Carbon::createFromFormat($format, $normalized);
                // Validasi parsing tepat tanpa carryover (e.g. 32 menjadi bulan berikutnya)
                if ($dt && $dt->format($format) === $normalized) {
                    return $dt->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // coba format berikutnya
            }
        }

        // Jika masih gagal, coba DateTime parser umum sebagai fallback
        try {
            $dt = Carbon::parse($date);
            return $dt->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    // Fungsi utama untuk mendeteksi dan mengonversi tanggal, baik serial Excel maupun format teks
    // Menambahkan parameter `baseDate` untuk mengonfigurasi tanggal referensi dinamis
    function convertToISODate($value, $baseDate = '1899-12-30')
    {
        // Jika nilai adalah integer (angka), anggap sebagai serial date Excel
        if (is_numeric($value) && $value > 59) { // Di atas 59 untuk melewati bug Excel pada tahun 1900
            return self::excelSerialDateToISO($value, $baseDate);
        }

        // Jika nilai adalah string, coba konversi menggunakan fungsi konversi tanggal string
        return self::convertDateToISO($value);
    }
}
