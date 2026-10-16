<?php

namespace Tests\Feature;

use App\Imports\DoctorValidateImport;
use App\Models\Participant;
use App\Models\PemeriksaanFisik;
use App\Models\User;
use App\Models\ValidateDoctor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ValidateDoctorUploadTest extends TestCase
{
    private const CLIENT_ID = 1;

    private const CONTRACT_ID = 1;

    private const OTHER_CONTRACT = 4;

    private int $sequence = 0;

    /**
     * Peserta uji buatan sendiri supaya pencocokan, nomor formulir, dan
     * pemeriksaan fisiknya bisa dipastikan (tanpa bergantung data lapangan).
     */
    private function makeParticipant(array $attributes = []): Participant
    {
        $this->sequence++;

        return Participant::create(array_merge([
            'code' => 'TMPVALID' . $this->sequence . random_int(1000, 9999),
            'nik' => '8888' . $this->sequence . random_int(1000, 9999),
            'name' => 'PESERTA VALIDASI ' . $this->sequence,
            'gender' => 'L',
            'birthday' => '1990-01-01',
            'client_id' => self::CLIENT_ID,
            'contract_id' => self::CONTRACT_ID,
            'divisi_id' => 1,
            'department_id' => 1,
            'register_date' => '2020-01-01 08:00:00',
            'no_form' => 9500 + $this->sequence,
        ], $attributes));
    }

    /**
     * Buat file xlsx sungguhan dengan judul kolom template.
     *
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function makeFile(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(DoctorValidateImport::HEADINGS, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $path = tempnam(sys_get_temp_dir(), 'validasi-dokter-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'validasi-dokter.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    private function upload(UploadedFile $file, array $session = [])
    {
        return $this->actingAs(User::find(1))
            ->withSession(array_merge([
                'client_id' => self::CLIENT_ID,
                'contract_id' => self::CONTRACT_ID,
            ], $session))
            ->post('/participant-upload-validasi-dokter', ['fileExcel' => $file]);
    }

    public function test_upload_stores_rows_matched_by_mcu_code(): void
    {
        DB::beginTransaction();

        try {
            $participants = [$this->makeParticipant(), $this->makeParticipant(), $this->makeParticipant()];

            // Tanggal MCU ditulis sebagai serial number Excel, seperti file aslinya.
            $rows = [];

            foreach ($participants as $index => $participant) {
                $rows[] = [
                    45600 + $index,
                    $participant->code,
                    $participant->name,
                    $participant->gender,
                    'Kesimpulan ' . ($index + 1),
                    'Catatan ' . ($index + 1),
                    'dr. Uji ' . ($index + 1),
                ];
            }

            $file = $this->makeFile($rows);

            $response = $this->upload($file);

            $response->assertOk();
            $response->assertJson([
                'status' => 'success',
                'processed' => 3,
                'skipped' => 0,
            ]);

            $stored = ValidateDoctor::where('client_id', self::CLIENT_ID)
                ->whereIn('no_mcu', collect($participants)->pluck('code'));

            $this->assertSame(3, (clone $stored)->count(), 'Respons sukses tetapi tidak ada baris yang tersimpan.');

            $sample = (clone $stored)->where('no_mcu', $participants[0]->code)->first();

            $this->assertNotNull($sample, 'Baris untuk kode peserta pertama tidak tersimpan.');
            $this->assertSame(self::CONTRACT_ID, (int) $sample->contract_id, 'contract_id tidak ikut terisi dari peserta.');
            $this->assertSame($participants[0]->name, $sample->name);
            $this->assertSame($participants[0]->gender, $sample->gender);
            $this->assertSame('Kesimpulan 1', $sample->result_mcu);
            $this->assertSame('Catatan 1', $sample->notes);
            $this->assertSame('dr. Uji 1', $sample->doctor);

            $this->assertSame(
                ExcelDate::excelToDateTimeObject(45600)->format('Y-m-d'),
                substr((string) $sample->date_mcu, 0, 10),
                'Serial number Excel tidak terbaca sebagai tanggal MCU.'
            );

            @unlink($file->getPathname());
        } finally {
            DB::rollBack();
        }
    }

    public function test_upload_matches_the_form_number_and_updates_existing_rows(): void
    {
        DB::beginTransaction();

        try {
            $participants = [$this->makeParticipant(), $this->makeParticipant()];

            $first = $this->makeFile([
                ['07-Oct-24', (string) $participants[0]->no_form, $participants[0]->name, $participants[0]->gender, 'Kesimpulan awal', 'Catatan awal', 'dr. Uji'],
                ['2024-10-07', (string) $participants[1]->no_form, $participants[1]->name, $participants[1]->gender, 'Kesimpulan awal', 'Catatan awal', 'dr. Uji'],
            ]);

            $response = $this->upload($first);
            $response->assertOk();
            $response->assertJson(['processed' => 2, 'skipped' => 0]);

            // Nomor formulir dicatat sebagai kode peserta supaya relasi tetap jalan.
            $this->assertSame(
                2,
                ValidateDoctor::whereIn('no_mcu', collect($participants)->pluck('code'))->count(),
                'Pencocokan lewat nomor formulir tidak menyimpan kode peserta.'
            );

            // Tanggal teks (07-Oct-24) atau ISO harus sama-sama terbaca.
            $this->assertSame(
                '2024-10-07',
                substr((string) ValidateDoctor::where('no_mcu', $participants[0]->code)->value('date_mcu'), 0, 10)
            );

            @unlink($first->getPathname());

            // Unggah ulang dengan hasil baru: harus memperbarui, bukan menggandakan.
            $second = $this->makeFile([
                ['07-Oct-24', (string) $participants[0]->no_form, $participants[0]->name, $participants[0]->gender, 'Kesimpulan baru', 'Catatan baru', 'dr. Uji'],
            ]);

            $this->upload($second)->assertOk();

            $this->assertSame(1, ValidateDoctor::where('no_mcu', $participants[0]->code)->count());
            $this->assertSame(
                'Kesimpulan baru',
                ValidateDoctor::where('no_mcu', $participants[0]->code)->value('result_mcu')
            );

            @unlink($second->getPathname());
        } finally {
            DB::rollBack();
        }
    }

    public function test_upload_reports_rows_it_could_not_match(): void
    {
        DB::beginTransaction();

        try {
            $file = $this->makeFile([
                [45600, 'MCUTIDAKADA1', 'Nama Satu', 'L', 'Kesimpulan', 'Catatan', 'dr. Uji'],
                [45601, 'MCUTIDAKADA2', 'Nama Dua', 'P', 'Kesimpulan', 'Catatan', 'dr. Uji'],
            ]);

            // Kontrak tanpa peserta: seluruh baris harus dilaporkan dilewati.
            $response = $this->upload($file, ['contract_id' => 999999]);

            $response->assertOk();
            $response->assertJson([
                'status' => 'success',
                'processed' => 0,
                'skipped' => 2,
            ]);

            $this->assertCount(2, $response->json('skipped_codes'), 'Kode baris yang dilewati tidak dilaporkan.');
            $this->assertSame(0, ValidateDoctor::whereIn('no_mcu', ['MCUTIDAKADA1', 'MCUTIDAKADA2'])->count());

            @unlink($file->getPathname());
        } finally {
            DB::rollBack();
        }
    }

    public function test_upload_is_scoped_to_the_active_contract(): void
    {
        DB::beginTransaction();

        try {
            $other = $this->makeParticipant(['contract_id' => self::OTHER_CONTRACT]);

            $file = $this->makeFile([
                [45600, $other->code, $other->name, $other->gender, 'Kesimpulan', 'Catatan', 'dr. Uji'],
            ]);

            // Sesi memakai kontrak 1, sedangkan peserta berasal dari kontrak lain.
            $response = $this->upload($file);

            $response->assertOk();
            $response->assertJson(['processed' => 0, 'skipped' => 1]);
            $this->assertSame(0, ValidateDoctor::where('no_mcu', $other->code)->count());

            @unlink($file->getPathname());
        } finally {
            DB::rollBack();
        }
    }

    public function test_upload_writes_the_conclusion_without_crashing_when_physical_exam_is_missing(): void
    {
        DB::beginTransaction();

        try {
            $withExam = $this->makeParticipant();
            $withoutExam = $this->makeParticipant();

            $file = $this->makeFile([
                [45600, $withExam->code, $withExam->name, $withExam->gender, 'Kesimpulan fisik', 'Catatan', 'dr. Uji'],
                [45601, $withoutExam->code, $withoutExam->name, $withoutExam->gender, 'Kesimpulan tanpa fisik', 'Catatan', 'dr. Uji'],
            ]);

            PemeriksaanFisik::create([
                'participant_id' => $withExam->id,
                'kesimpulan' => 'Kesimpulan lama',
                'selesai' => 1,
            ]);

            $examCountBefore = PemeriksaanFisik::count();

            $response = $this->upload($file);

            $response->assertOk();
            $response->assertJson(['processed' => 2, 'skipped' => 0]);

            // Kesimpulan harus benar-benar tersimpan pada pemeriksaan fisik.
            $this->assertSame(
                'Kesimpulan fisik',
                PemeriksaanFisik::where('participant_id', $withExam->id)->value('kesimpulan')
            );

            // Peserta tanpa pemeriksaan fisik tidak dibuatkan data pemeriksaan palsu.
            $this->assertSame($examCountBefore, PemeriksaanFisik::count());
            $this->assertNull(
                PemeriksaanFisik::where('participant_id', $withoutExam->id)->first(),
                'Import membuat data pemeriksaan fisik yang belum ada.'
            );
            $this->assertNotNull(
                ValidateDoctor::where('no_mcu', $withoutExam->code)->first(),
                'Baris validasi tidak tersimpan untuk peserta tanpa pemeriksaan fisik.'
            );

            @unlink($file->getPathname());
        } finally {
            DB::rollBack();
        }
    }

    public function test_template_headings_match_what_the_import_reads(): void
    {
        $response = $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => self::CONTRACT_ID])
            ->get('/participant-upload-validasi-dokter/template');

        $response->assertOk();
        $this->assertStringContainsString(
            'template-upload-validasi-dokter.xlsx',
            (string) $response->headers->get('content-disposition')
        );

        $sheet = IOFactory::load($response->getFile()->getPathname())->getActiveSheet();

        $this->assertSame(
            DoctorValidateImport::HEADINGS,
            $sheet->rangeToArray('A1:G1')[0],
            'Judul kolom template tidak sama dengan yang dibaca import.'
        );

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
