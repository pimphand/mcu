<?php

namespace App\Imports;

use App\Models\Laboratorium;
use App\Models\Participant;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class LaboratoriumImport implements ToModel, WithStartRow, WithChunkReading, WithHeadingRow
{
    protected $client_id;
    protected $contract_id;
    protected $totalRowsProcessed = 0;
    protected $totalRowsSkipped = 0;
    protected $skippedCodes = [];
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
        // Kolom "Code" pada file hasil lab bisa berisi nomor formulir peserta
        // (1, 2, 3, ...) maupun kode MCU (MCU00001). Keduanya harus bisa cocok,
        // kalau tidak seluruh baris akan dilewati tanpa jejak.
        $code = trim((string) ($row['code'] ?? ''));

        if ($code !== '') {
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
            ];

            // Angka murni ("1", "2") adalah nomor formulir peserta; selain itu
            // dianggap kode MCU ("MCU00001"). Keduanya harus bisa cocok.
            $isFormNumber = ctype_digit($code);

            $pemeriksaan = Participant::query()
                ->when($this->client_id, fn ($query) => $query->where('client_id', $this->client_id))
                ->when($this->contract_id, fn ($query) => $query->where('contract_id', $this->contract_id))
                ->when(
                    $isFormNumber,
                    fn ($query) => $query->where('no_form', (int) $code),
                    fn ($query) => $query->where('code', $code)
                )
                ->orderByDesc('register_date')
                ->orderByDesc('id')
                ->first();

            if (! $pemeriksaan) {
                $this->skip($code, 'peserta tidak ditemukan pada kontrak aktif');

                return;
            }

            // Kontrak pemeriksaan mengikuti kontrak peserta yang cocok,
            // supaya daftar laboratorium bisa difilter per kontrak sesi MCU IN.
            $data['contract_id'] = $pemeriksaan->contract_id;
            // Nomor formulir peserta yang cocok, supaya baris hasil lab bisa
            // ditelusuri kembali ke form MCU-nya.
            $data['form_no'] = $pemeriksaan->no_form;
            // Pemeriksa lab dari file dipetakan ke tabel employees; kalau
            // namanya belum terdaftar maka dibuatkan baru, bukan ke dokter lain.
            // Baru dipanggil setelah peserta cocok, supaya baris yang dilewati
            // tidak ikut membuat data karyawan.
            $data['employee_id'] = $this->employeeService->findOrCreateIdByName($row['pemeriksa_lab'] ?? null, $this->client_id);

            $pemeriksaan->laboratorium()->updateOrCreate(['participant_id' => $pemeriksaan->id], $data);

            $this->totalRowsProcessed++;
        }
    }

    /**
     * Catat baris yang dilewati supaya respons upload tidak lagi mengaku
     * sukses padahal tidak ada data yang tersimpan.
     */
    protected function skip(string $code, string $reason): void
    {
        $this->totalRowsSkipped++;
        $this->skippedCodes[] = $code;

        Log::warning('Laboratorium import: row skipped', [
            'code' => $code,
            'reason' => $reason,
            'client_id' => $this->client_id,
            'contract_id' => $this->contract_id,
        ]);
    }

    public function getTotalRowsProcessed(): int
    {
        return $this->totalRowsProcessed;
    }

    public function getTotalRowsSkipped(): int
    {
        return $this->totalRowsSkipped;
    }

    /**
     * @return array<int, string>
     */
    public function getSkippedCodes(): array
    {
        return $this->skippedCodes;
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
