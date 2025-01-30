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

class UsersImport implements ToModel, WithStartRow, WithChunkReading
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
            $data = [
                'nik' => $row[1],
                'name' => $row[2],
                'gender' => $row[4],
                'birthday' => $this->convertToISODate($row[5]),
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
                'contract_id' => Session::get('contract_id'),
                'no_form' => (int)$row[0],
            ];
            // dd($data);
            $user = $this->userId;
            $data = $participantService->mapingPaket($data);
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

    // Fungsi untuk mengonversi tanggal teks `m/d/yy` ke format ISO `YYYY-MM-DD`
    function convertDateToISO($date)
    {
        // Tambahkan nol di depan bulan atau hari jika terdiri dari satu digit
        $date = preg_replace('/\b(\d{1})\b/', '0$1', $date);

        // Pisahkan tanggal berdasarkan karakter /
        $parts = explode('/', $date);

        // Pastikan array memiliki 3 elemen untuk menghindari error
        if (count($parts) < 3) {
            // Kembalikan NULL jika format tanggal tidak valid
            return null;
        }

        // Pastikan tahun ditambahkan sebagai empat digit
        if (strlen($parts[2]) === 2) {
            $year = $parts[2] >= 50 ? '19' . $parts[2] : '20' . $parts[2];
            $parts[2] = $year;
        }

        // Susun kembali dalam format YYYY-MM-DD
        return $parts[2] . '-' . $parts[0] . '-' . $parts[1];
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
