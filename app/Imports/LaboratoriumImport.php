<?php

namespace App\Imports;

use App\Models\Laboratorium;
use App\Models\Participant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class LaboratoriumImport implements ToModel, WithStartRow, WithChunkReading, WithHeadingRow, ShouldQueue
{
    protected $client_id;

    public function __construct($client_id)
    {
        $this->client_id = $client_id;
    }

    /**
     * @param array $row
     */
    public function model(array $row)
    {
        if ($row['code']!= null){
            $data = [
                "name" => $row['name'],
                "hemoglobin" => $row['hemoglobin'],
                "hematokrit" => $row['hematokrit'],
                "lekosit" => $row['lekosit'],
                "trombosit" => $row['trombosit'],
                "eritrosit" => $row['eritrosit'],
                "basofil" => $row['basofil'],
                "eosinofil" => $row['eosinofil'],
                "batang" => $row['nbatang'],
                "segmen" => $row['nsegmen'],
                "limfosit" => $row['limfosit'],
                "monosit" => $row['monosit'],
                "sgpt" => $row['sgpt'],
                "creatinin" => $row['creatinin'],
                "glukosa_puasa" => $row['glukosa_puasa'],
                "cholesterol_total" => $row['cholesterol_total'],
                "asam_urat" => $row['asam_urat'],
                "sgot" => $row['sgot'],
                "ureum" => $row['ureum'],
                "berat_jenis" => $row['beratjenis'],
                "ph_reaksi" => $row['phreaksi'],
                "warna" => $row['warna'],
                "kekeruhan" => $row['kekeruhan'],
                "urobilinogen" => $row['urobilinogen'],
                "bilirubin" => $row['bilirubin'],
                "eritrosit_urine" => $row['eritrosit_urine'],
                "keton" => $row['keton'],
                "protein" => $row['protein'],
                "sedimen_epitel" => $row['sedimenepitel'],
                "sedimen_eritrosit" => $row['sedimeneritrosit'],
                "sedimen_leukosit" => $row['sedimenleukosit'],
                "sedimen_bakteri" => $row['sedimenbakteri'],
                "sedimen_kristal" => $row['sedimenkristal'],
                "user_lab" => $row['user_lab'],
                "lab_date" => $row['lab_date'],
                "kesimpulan" => $row['kesimpulan_lab'],
                "pemeriksa_lab" => $row['pemeriksa_lab'],
                "reduksi" => $row['reduksi'],
            ];
            $pemeriksaan = Participant::with('laboratorium')->where('code',$row['code'])->first();
            if ($pemeriksaan) {
                $pemeriksaan->laboratorium->update($data);
                $pemeriksaan->save();
            }
        }
    }

    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize(): int
    {
        return 2;
    }
}
