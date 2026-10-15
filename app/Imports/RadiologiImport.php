<?php

namespace App\Imports;

use App\Events\ImportCompleted;
use App\Models\Participant;
use App\Models\Radiologi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class RadiologiImport implements ToModel, WithStartRow, WithChunkReading, WithHeadingRow
{
    use Importable, RegistersEventListeners;

    protected $client_id;
    protected $contract_id;
    protected $totalRowsProcessed = 0;
    protected $totalRowsSkipped = 0;
    protected $skippedCodes = [];

    public function __construct($client_id, $contract_id = null)
    {
        $this->client_id = $client_id;
        $this->contract_id = $contract_id;
    }

    /**
     * @param array $row
     */
    public function model(array $row)
    {
        $code = trim((string) ($row['no_mcu'] ?? ''));

        if ($code === '') {
            return;
        }

        $participant = Participant::query()
            ->where('code', $code)
            ->when($this->client_id, fn ($query) => $query->where('client_id', $this->client_id))
            ->when($this->contract_id, fn ($query) => $query->where('contract_id', $this->contract_id))
            ->orderByDesc('register_date')
            ->orderByDesc('id')
            ->first();

        if (! $participant) {
            $this->totalRowsSkipped++;
            $this->skippedCodes[] = $code;

            Log::warning('Radiologi import: participant not found', [
                'no_mcu' => $code,
                'client_id' => $this->client_id,
                'contract_id' => $this->contract_id,
            ]);

            return;
        }

        Radiologi::updateOrCreate([
            'participant_id' => $participant->id,
        ], [
            'date_mcu' => $this->toDate($row['tgl_mcu'] ?? null),
            'cor' => $row['cor'] ?? null,
            'diafragma_sinus' => $row['diafragma_sinus'] ?? null,
            'pulmo' => $row['pulmo'] ?? null,
            'kesan' => $row['kesan'] ?? null,
            'diperiksa' => $row['pemeriksa'] ?? null,
            'selesai' => 1,
            'employee_id' => 3,
        ]);

        $this->totalRowsProcessed++;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function headingRow(): int
    {
        return 1;
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

    /**
     * The template stores the MCU date as an Excel serial number, so it is
     * converted into a plain date (the radiologis.date_mcu column is a DATE).
     */
    private function toDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            Log::warning('Radiologi import: unreadable date', ['value' => $value]);

            return null;
        }
    }

    public function afterImport()
    {
        // Kirim notifikasi setelah semua data diproses
        Event::dispatch(new ImportCompleted($this->client_id));
    }
}
