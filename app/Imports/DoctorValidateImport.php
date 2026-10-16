<?php

namespace App\Imports;

use App\Models\Participant;
use App\Models\ValidateDoctor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class DoctorValidateImport implements ToModel, WithStartRow, WithChunkReading, WithHeadingRow
{
    /**
     * Judul kolom yang dipakai template unduhan. Template mengambil daftar ini
     * supaya file template dan import tidak pernah berbeda.
     *
     * @var array<int, string>
     */
    public const HEADINGS = ['TGL_MCU', 'NO_MCU', 'NAMA', 'GENDER', 'HASIL_MCU', 'CATATAN', 'DOKTER_PEMERIKSA'];

    protected $client_id;
    protected $contract_id;
    protected int $totalRowsProcessed = 0;
    protected int $totalRowsSkipped = 0;

    /** @var array<int, string> */
    protected array $skippedCodes = [];

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
        // Baris tanpa kode peserta dianggap baris kosong, bukan baris gagal.
        $code = trim((string) ($row['no_mcu'] ?? ''));

        if ($code === '') {
            return;
        }

        // Kolom NO_MCU bisa berisi kode MCU (MCU00001) atau nomor formulir
        // peserta (1, 2, 3, ...); keduanya harus bisa cocok. Pencocokan dibatasi
        // client dan kontrak sesi MCU IN supaya baris tidak menimpa peserta lain.
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

        ValidateDoctor::updateOrCreate([
            'client_id' => $this->client_id,
            // Dicatat sebagai kode peserta supaya relasi peserta->validasi dokter
            // (dicocokkan lewat kolom code) tetap bekerja walau file berisi
            // nomor formulir.
            'no_mcu' => $participant->code,
        ], [
            'contract_id' => $participant->contract_id,
            'date_mcu' => $this->toDate($row['tgl_mcu'] ?? null),
            'name' => $this->value($row['nama'] ?? null) ?? $participant->name,
            'gender' => $this->value($row['gender'] ?? null) ?? $participant->gender,
            'result_mcu' => $this->value($row['hasil_mcu'] ?? null),
            'doctor' => $this->value($row['dokter_pemeriksa'] ?? null),
            'notes' => $this->value($row['catatan'] ?? null),
        ]);

        $this->updatePemeriksaanFisik($participant, $row);

        $this->totalRowsProcessed++;
    }

    /**
     * Kesimpulan hasil MCU dipakai pada cetak resume, jadi disimpan ke
     * pemeriksaan fisik peserta. Peserta yang belum punya pemeriksaan fisik
     * dilewati tanpa dibuatkan data pemeriksaan palsu.
     *
     * @param array $row
     */
    protected function updatePemeriksaanFisik(Participant $participant, array $row): void
    {
        $kesimpulan = $this->value($row['hasil_mcu'] ?? null);

        if ($kesimpulan === null) {
            return;
        }

        $fisik = $participant->pemeriksaanFisik;

        if (! $fisik) {
            Log::info('Doctor validate import: pemeriksaan fisik belum ada', [
                'code' => $participant->code,
                'client_id' => $this->client_id,
            ]);

            return;
        }

        $fisik->kesimpulan = $kesimpulan;
        $fisik->save();
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
     * Catat baris yang dilewati supaya respons upload tidak mengaku sukses
     * padahal tidak ada data yang tersimpan.
     */
    protected function skip(string $code, string $reason): void
    {
        $this->totalRowsSkipped++;
        $this->skippedCodes[] = $code;

        Log::warning('Doctor validate import: row skipped', [
            'no_mcu' => $code,
            'reason' => $reason,
            'client_id' => $this->client_id,
            'contract_id' => $this->contract_id,
        ]);
    }

    /**
     * Nilai teks dari file yang sudah dirapikan; sel kosong jadi null supaya
     * tidak menimpa data lama dengan string kosong.
     */
    protected function value($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Template bisa menyimpan tanggal sebagai serial number Excel maupun teks
     * (07-Oct-24), jadi keduanya harus bisa dibaca. Kolom date_mcu bertipe
     * datetime, jadi hasilnya selalu Y-m-d H:i:s.
     */
    protected function toDate($value): ?string
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
            Log::warning('Doctor validate import: unreadable date', ['value' => $value]);

            return null;
        }
    }
}
