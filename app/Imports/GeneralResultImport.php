<?php

namespace App\Imports;

use App\Models\GeneralResult;
use App\Models\Participant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class GeneralResultImport implements ToModel, WithStartRow, WithChunkReading, WithHeadingRow
{
    /**
     * Judul kolom yang dipakai template unduhan. Template mengambil daftar ini
     * langsung dari sini supaya file template dan import tidak pernah berbeda.
     *
     * @var array<int, string>
     */
    public const HEADINGS = ['TGL_MCU', 'NO_MCU', 'NAMA', 'HASIL_UMUM'];

    /**
     * Kolom yang boleh dipakai sebagai isi hasil umum, berurutan dari yang
     * paling utama. File dari template memakai hasil_umum.
     *
     * @var array<int, string>
     */
    private const RESULT_KEYS = ['hasil_umum', 'diagnosa_akhir', 'hasil', 'kesan'];

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
        // Kolom NO_MCU bisa berisi kode MCU (MCU00001) atau nomor formulir
        // peserta (1, 2, 3, ...). Baris tanpa kode dilewati tanpa dihitung
        // sebagai baris gagal supaya baris kosong di template tidak jadi alarm.
        $code = trim((string) ($row['no_mcu'] ?? ''));

        if ($code === '') {
            return;
        }

        $isFormNumber = ctype_digit($code);

        $participant = Participant::query()
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

        if (! $participant) {
            $this->skip($code, 'peserta tidak ditemukan pada kontrak aktif');

            return;
        }

        GeneralResult::updateOrCreate([
            'participant_id' => $participant->id,
        ], [
            'client_id' => $this->client_id,
            // Kontrak mengikuti peserta yang cocok supaya daftar hasil umum
            // bisa difilter per kontrak sesi MCU IN.
            'contract_id' => $participant->contract_id,
            'name' => $this->name($row, $participant),
            'mcu_id' => $participant->code,
            'result' => $this->result($row),
            'mcu_date' => $this->toDate($row['tgl_mcu'] ?? null) ?? $this->registerDate($participant),
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
     * @param array $row
     */
    private function name(array $row, Participant $participant): string
    {
        $name = trim((string) ($row['nama'] ?? ''));

        return $name !== '' ? $name : (string) $participant->name;
    }

    /**
     * @param array $row
     */
    private function result(array $row): ?string
    {
        foreach (self::RESULT_KEYS as $key) {
            $value = trim((string) ($row[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function registerDate(Participant $participant): ?string
    {
        if (empty($participant->register_date)) {
            return null;
        }

        return Carbon::parse($participant->register_date)->toDateTimeString();
    }

    /**
     * Template menyimpan tanggal sebagai teks (07-Oct-24) maupun sebagai
     * serial number Excel, jadi keduanya harus bisa dibaca.
     */
    private function toDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d H:i:s');
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            Log::warning('General result import: unreadable date', ['value' => $value]);

            return null;
        }
    }

    /**
     * Catat baris yang dilewati supaya respons upload tidak mengaku sukses
     * padahal tidak ada data yang tersimpan.
     */
    protected function skip(string $code, string $reason): void
    {
        $this->totalRowsSkipped++;
        $this->skippedCodes[] = $code;

        Log::warning('General result import: row skipped', [
            'no_mcu' => $code,
            'reason' => $reason,
            'client_id' => $this->client_id,
            'contract_id' => $this->contract_id,
        ]);
    }
}
