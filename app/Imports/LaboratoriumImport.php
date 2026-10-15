<?php

namespace App\Imports;

use App\Models\Laboratorium;
use App\Models\Participant;
use App\Services\EmployeeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class LaboratoriumImport implements ToModel, WithStartRow, WithChunkReading, WithHeadingRow, ShouldQueue
{
    protected $client_id;
    protected $contract_id;
    protected EmployeeService $employeeService;

    public function __construct($client_id, $contract_id = null)
    {
        $this->client_id = $client_id;
        $this->contract_id = $contract_id;
        $this->employeeService = new EmployeeService;
    }

    /**
     * @param array $row
     */
    public function model(array $row)
    {

        if (isset($row['code']) && $row['code'] != null) {
            $data = [
                "name" => $row['name'] ?? null,
                "hemoglobin" => $row['hemoglobin'] ?? null,
                "hematokrit" => $row['hematokrit'] ?? null,
                "lekosit" => $row['lekosit'] ?? null,
                "trombosit" => $row['trombosit'] ?? null,
                "eritrosit" => $row['eritrosit'] ?? null,
                "basofil" => $row['basofil'] ?? null,
                "eosinofil" => $row['eosinofil'] ?? null,
                "batang" => $row['nbatang'] ?? null,
                "segmen" => $row['nsegmen'] ?? null,
                "limfosit" => $row['limfosit'] ?? null,
                "monosit" => $row['monosit'] ?? null,
                "sgpt" => $row['sgpt'] ?? null,
                "creatinin" => $row['creatinin'] ?? null,
                "glukosa_puasa" => $row['glukosa_puasa'] ?? null,
                "cholesterol_total" => $row['cholesterol_total'] ?? null,
                "asam_urat" => $row['asam_urat'] ?? null,
                "sgot" => $row['sgot'] ?? null,
                "ureum" => $row['ureum'] ?? null,
                "berat_jenis" => $row['beratjenis'] ?? null,
                "ph_reaksi" => $row['phreaksi'] ?? null,
                "warna" => $row['warna'] ?? null,
                "kekeruhan" => $row['kekeruhan'] ?? null,
                "urobilinogen" => $row['urobilinogen'] ?? null,
                "bilirubin" => $row['bilirubin'] ?? null,
                "eritrosit_urine" => $row['eritrosit_urine'] ?? null,
                "keton" => $row['keton'] ?? null,
                "protein" => $row['protein'] ?? null,
                "sedimen_epitel" => $row['sedimenepitel'] ?? null,
                "sedimen_eritrosit" => $row['sedimeneritrosit'] ?? null,
                "sedimen_leukosit" => $row['sedimenleukosit'] ?? null,
                "sedimen_bakteri" => $row['sedimenbakteri'] ?? null,
                "sedimen_kristal" => $row['sedimenkristal'] ?? null,
                "user_lab" => $row['user_lab'] ?? null,
                "lab_date" => $row['lab_date'] ?? null,
                "kesimpulan" => $row['kesimpulan_lab'] ?? null,
                "pemeriksa_lab" => $row['pemeriksa_lab'] ?? null,
                "reduksi" => $row['reduksi'] ?? null,
                "hbsag" => $row['hbsag'] ?? null,
                "selesai" => 1,
                "client_id" => $this->client_id,
                // Pemeriksa lab dari file dipetakan ke tabel employees; kalau
                // namanya belum terdaftar maka dibuatkan baru, bukan ke dokter lain.
                "employee_id" => $this->employeeService->findOrCreateIdByName($row['pemeriksa_lab'] ?? null, $this->client_id),
            ];

            $pemeriksaan = Participant::query()
                ->where('code', $row['code'])
                ->when($this->client_id, fn ($query) => $query->where('client_id', $this->client_id))
                ->when($this->contract_id, fn ($query) => $query->where('contract_id', $this->contract_id))
                ->orderByDesc('register_date')
                ->orderByDesc('id')
                ->first();

            if ($pemeriksaan) {
                $pemeriksaan->laboratorium()->updateOrCreate(['participant_id' => $pemeriksaan->id], $data);
            }
        } else {
            //            Log::alert("array : ${$row}"  );
        }
    }

    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize(): int
    {
        return 10;
    }

    /**
     * @return array<int, string>
     */
    public function getCreatedDoctors(): array
    {
        return $this->employeeService->getCreatedEmployees();
    }
}
