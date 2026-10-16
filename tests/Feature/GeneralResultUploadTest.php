<?php

namespace Tests\Feature;

use App\Imports\GeneralResultImport;
use App\Models\GeneralResult;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class GeneralResultUploadTest extends TestCase
{
    private const CLIENT_ID = 1;
    private const SESSION_CONTRACT = 1;

    /**
     * Buat file xlsx sungguhan dengan judul kolom template.
     *
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function makeFile(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(GeneralResultImport::HEADINGS, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $path = tempnam(sys_get_temp_dir(), 'hasil-umum-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'hasil-umum.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    private function upload(UploadedFile $file, array $session = []): TestResponse
    {
        return $this->actingAs(User::find(1))
            ->withSession(array_merge([
                'client_id' => self::CLIENT_ID,
                'contract_id' => self::SESSION_CONTRACT,
            ], $session))
            ->post('/participant-upload-umum', ['fileExcel' => $file]);
    }

    private function participant(int $contractId = self::SESSION_CONTRACT): Participant
    {
        $participant = Participant::where('client_id', self::CLIENT_ID)
            ->where('contract_id', $contractId)
            ->whereNotNull('code')
            ->orderBy('id')
            ->first();

        $this->assertNotNull($participant, "Tidak ada peserta kontrak {$contractId} untuk diuji.");

        return $participant;
    }

    public function test_upload_saves_rows_and_reports_skipped_codes(): void
    {
        DB::beginTransaction();

        $file = null;

        try {
            $byCode = $this->participant(1);
            // Peserta lain yang punya nomor formulir, untuk memastikan
            // pencocokan lewat no_form juga jalan (dan tidak menimpa baris pertama).
            $byForm = Participant::where('client_id', self::CLIENT_ID)
                ->where('contract_id', 1)
                ->whereNotNull('no_form')
                ->whereKeyNot($byCode->id)
                ->orderBy('id')
                ->first();
            // Kode peserta dipakai ulang antar kontrak (MCU00002 ada di kontrak 1
            // dan kontrak 4), jadi pilih kode kontrak 4 yang tidak ada di kontrak 1
            // untuk membuktikan pencocokan memang dibatasi kontrak sesi.
            $otherContract = Participant::where('client_id', self::CLIENT_ID)
                ->where('contract_id', 4)
                ->whereNotIn('code', Participant::where('client_id', self::CLIENT_ID)->where('contract_id', 1)->pluck('code'))
                ->orderBy('id')
                ->first();

            $this->assertNotNull($otherContract, 'Tidak ada peserta kontrak 4 dengan kode unik.');

            $this->assertNotNull($byForm, 'Tidak ada peserta dengan nomor formulir.');

            $file = $this->makeFile([
                ['07-Oct-24', $byCode->code, $byCode->name, 'Hasil umum lewat kode MCU'],
                ['08-Oct-24', (string) $byForm->no_form, $byForm->name, 'Hasil umum lewat nomor formulir'],
                ['09-Oct-24', 'MCU-XXXXX', 'PESERTA TIDAK ADA', 'Tidak akan tersimpan'],
                ['09-Oct-24', $otherContract->code, $otherContract->name, 'Peserta kontrak lain'],
                ['10-Oct-24', '', '', ''],
            ]);

            $response = $this->upload($file);

            $response->assertOk()->assertJson([
                'status' => 'success',
                'processed' => 2,
                'skipped' => 2,
            ]);

            $this->assertSame(
                ['MCU-XXXXX', $otherContract->code],
                $response->json('skipped_codes')
            );

            $this->assertSame(2, GeneralResult::count());

            $first = GeneralResult::where('participant_id', $byCode->id)->firstOrFail();
            $this->assertSame('Hasil umum lewat kode MCU', $first->result);
            $this->assertSame($byCode->code, $first->mcu_id);
            $this->assertSame(self::CLIENT_ID, (int) $first->client_id);
            $this->assertSame(1, (int) $first->contract_id);
            $this->assertSame('2024-10-07 00:00:00', $first->mcu_date);

            $second = GeneralResult::where('participant_id', $byForm->id)->firstOrFail();
            $this->assertSame('Hasil umum lewat nomor formulir', $second->result);
            $this->assertSame('2024-10-08 00:00:00', $second->mcu_date);
        } finally {
            DB::rollBack();

            if ($file) {
                @unlink($file->getPathname());
            }
        }
    }

    public function test_upload_falls_back_to_register_date_and_updates_existing_row(): void
    {
        DB::beginTransaction();

        $file = null;

        try {
            $participant = $this->participant(1);
            $registerDate = \Illuminate\Support\Carbon::parse($participant->register_date)->toDateTimeString();

            // Tanpa TGL_MCU: tanggal diambil dari tanggal registrasi peserta.
            $file = $this->makeFile([
                ['', $participant->code, $participant->name, 'Hasil pertama'],
            ]);

            $this->upload($file)->assertOk()->assertJson(['processed' => 1, 'skipped' => 0]);

            $row = GeneralResult::where('participant_id', $participant->id)->firstOrFail();
            $this->assertSame($registerDate, $row->mcu_date);

            // Upload ulang untuk peserta yang sama harus memperbarui, bukan menambah baris.
            $file2 = $this->makeFile([
                ['07-Oct-24', $participant->code, 'NAMA DARI FILE', 'Hasil kedua'],
            ]);

            $this->upload($file2)->assertOk()->assertJson(['processed' => 1, 'skipped' => 0]);

            $this->assertSame(1, GeneralResult::where('participant_id', $participant->id)->count());

            $row->refresh();
            $this->assertSame('Hasil kedua', $row->result);
            $this->assertSame('NAMA DARI FILE', $row->name);
            $this->assertSame('2024-10-07 00:00:00', $row->mcu_date);

            @unlink($file2->getPathname());
        } finally {
            DB::rollBack();

            if ($file) {
                @unlink($file->getPathname());
            }
        }
    }

    public function test_template_headings_match_what_the_import_reads(): void
    {
        $response = $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => self::SESSION_CONTRACT])
            ->get('/participant-upload-umum/template');

        $response->assertOk();
        $this->assertStringContainsString(
            'template-upload-hasil-umum.xlsx',
            (string) $response->headers->get('content-disposition')
        );

        $path = $response->getFile()->getPathname();
        $sheet = IOFactory::load($path)->getActiveSheet();
        $headings = $sheet->rangeToArray('A1:D1')[0];

        $this->assertSame(GeneralResultImport::HEADINGS, $headings);

        // Baris contoh tidak boleh memakai kode peserta sungguhan, supaya
        // template yang diunggah tanpa diubah tidak menimpa data asli.
        $exampleCode = (string) $sheet->getCell('B2')->getValue();
        $this->assertNotSame('', $exampleCode);
        $this->assertFalse(
            Participant::where('code', $exampleCode)->exists(),
            'Kode contoh template dipakai peserta sungguhan.'
        );
    }
}
