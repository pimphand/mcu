<?php

namespace App\Imports;

use App\Models\Participant;
use App\Models\PemeriksaanFisik;
use App\Models\ValidateDoctor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

date_default_timezone_set('Asia/Jakarta');
class DoctorValidateImport implements ToModel, WithStartRow, WithChunkReading, WithHeadingRow, ShouldQueue
{
    protected  $client_id;

    public function __construct($client_id)
    {
        $this->client_id = $client_id;
    }
    /**
     * @param array $row
     */
    public function model(array $row)
    {
        $unixTimestamp = ($row['tgl_mcu'] - 25569) * 86400;
        $formattedDate = date("Y-m-d H:i:s", $unixTimestamp - 7 * 3600);

        ValidateDoctor::updateOrCreate([
            'client_id' => $this->client_id,
            'no_mcu' => $row['no_mcu'],
        ], [
            'date_mcu' => $formattedDate,
            'name' => $row['nama'],
            'gender' => $row['gender'],
            'result_mcu' => $row['hasil_mcu'],
            'doctor' => $row['dokter_pemeriksa'],
            'notes' => $row['catatan'],
        ]);

        $pemeriksaan = Participant::with('pemeriksaanFisik')->where('code', $row['no_mcu'])->first();

        if ($pemeriksaan) {
            $pemeriksaan->pemeriksaanFisik->kesimpulan = $row['hasil_mcu'];
            // $pemeriksaan->pemeriksaanFisik->saran = $row['catatan'];
            $pemeriksaan->save();
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
