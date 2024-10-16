<?php

namespace App\Imports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class GeneralResultImport implements ToModel, WithStartRow, WithChunkReading, WithHeadingRow, ShouldQueue
{
    protected $client_id;

    public function __construct($client_id)
    {
        $this->client_id = $client_id;
    }

    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        //
    }

    public function startRow(): int
    {
        return 2; // Start reading data from row 6
    }

    public function chunkSize(): int
    {
        return 100; // Process 2 rows at a time
    }

    public function headingRow(): int
    {
        return 1; // Set the heading row to row 6
    }
}
