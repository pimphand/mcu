<?php

namespace App\Imports;

use App\Models\Participant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class RadiologiImport implements  ToModel, WithStartRow, WithChunkReading, WithHeadingRow, ShouldQueue
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
            if ($row['no_mcu'] != null) {
                $unixTimestamp = ($row['tgl_mcu'] - 25569) * 86400;
                $formattedDate = date("Y-m-d H:i:s", $unixTimestamp - 7 * 3600);
                $data = [
                    'date_mcu' => $formattedDate ?? null, // Date can be processed later if needed
                    'nama' => $row['nama'] ?? null,
                    'gender' => $row['gender'] ?? null,
                    'cor' => $row['cor'] ?? null,
                    'diafragma_sinus' => $row['diafragma_sinus'] ?? null,
                    'pulmo' => $row['pulmo'] ?? null, // Make sure this header matches your Excel file
                    'kesan' => $row['kesan'] ?? null, // Make sure this header matches your Excel file
                    'diperiksa' => $row['diperiksa'] ?? null,
                ];
                $participant = Participant::where('code', $row['no_mcu'])->first();
                if ($participant) {
                    $participant->radiologi->update($data);
                    $participant->save();
                }
            }
        }
    public function startRow(): int
    {
        return 7; // Start reading data from row 6
    }

    public function chunkSize(): int
    {
        return 2; // Process 2 rows at a time
    }

    public function headingRow(): int
    {
        return 6; // Set the heading row to row 6
    }
}
