<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Laboratorium;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class LaboratoriumUploadTest extends TestCase
{
    private const CLIENT_ID = 1;

    // Kontrak yang punya peserta dengan nomor formulir (1, 2, 3, ...).
    private const CONTRACT_ID = 4;

    private const EXAMINER = 'dr. Uji Laboratorium';

    /**
     * Nama pemeriksa yang di file ditulis tanpa gelar, sedangkan di master
     * karyawan sudah terdaftar lengkap dengan gelarnya.
     */
    private const EXAMINER_BASE = 'Pemeriksa Uji Gelar';

    private const EXAMINER_TITLED = self::EXAMINER_BASE . ', A.Md.A.K';

    /**
     * Judul kolom file hasil lab. Kolom "Code" sengaja diisi nomor formulir
     * peserta (bukan kode MCU) karena itu bentuk file yang dipakai di lapangan
     * dan pernah membuat seluruh baris dilewati tanpa jejak.
     *
     * @var array<int, string>
     */
    private const HEADINGS = [
        'Code', 'Nama', 'Hemoglobin', 'Hematokrit', 'Lekosit', 'Trombosit', 'Eritrosit',
        'Basofil', 'Eosinofil', 'NBatang', 'NSegmen', 'Limfosit', 'Monosit', 'SGPT',
        'Creatinin', 'glukosa_puasa', 'cholesterol_total', 'asam_urat', 'sgot', 'ureum',
        'BeratJenis', 'PHReaksi', 'Warna', 'Kekeruhan', 'Urobilinogen', 'Bilirubin',
        'Eritrosit_urine', 'Keton', 'Protein', 'SedimenEpitel', 'SedimenEritrosit',
        'SedimenLeukosit', 'SedimenBakteri', 'SedimenKristal', 'user_lab', 'lab_date',
        'kesimpulan_lab', 'pemeriksa_lab', 'Reduksi', 'hbsag',
    ];

    /**
     * Peserta kontrak uji, diambil dari database seperti file aslinya dulu.
     *
     * @return \Illuminate\Support\Collection<int, Participant>
     */
    private function participants(int $limit = 3)
    {
        $participants = Participant::where('client_id', self::CLIENT_ID)
            ->where('contract_id', self::CONTRACT_ID)
            ->whereNotNull('no_form')
            ->orderBy('no_form')
            ->limit($limit)
            ->get();

        $this->assertCount($limit, $participants, 'Data peserta uji tidak cukup.');

        return $participants;
    }

    /**
     * Susun file xlsx hasil lab untuk peserta yang diberikan.
     *
     * @param  \Illuminate\Support\Collection<int, Participant>  $participants
     */
    private function file($participants, array $hemoglobins = ['17.4', '13.1', '14.0'], ?string $examiner = null): UploadedFile
    {
        $examiner ??= self::EXAMINER;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(self::HEADINGS, null, 'A1');

        $rows = [];

        foreach ($participants as $index => $participant) {
            // Nilai default "-" untuk kolom yang tidak diuji.
            $values = array_fill(0, count(self::HEADINGS), '-');
            $values[array_search('Code', self::HEADINGS, true)] = (string) $participant->no_form;
            $values[array_search('Nama', self::HEADINGS, true)] = $participant->name;
            $values[array_search('Hemoglobin', self::HEADINGS, true)] = $hemoglobins[$index] ?? '14.0';
            $values[array_search('kesimpulan_lab', self::HEADINGS, true)] = 'Normal';
            $values[array_search('pemeriksa_lab', self::HEADINGS, true)] = $examiner;
            $values[array_search('hbsag', self::HEADINGS, true)] = 'Negatif';
            $rows[] = $values;
        }

        $sheet->fromArray($rows, null, 'A2');

        $path = tempnam(sys_get_temp_dir(), 'hasil-lab-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'hasil-lab.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    public function test_upload_stores_lab_rows_matched_by_the_form_number(): void
    {
        $participants = $this->participants();
        $file = $this->file($participants);

        // Pemeriksa mungkin sudah pernah dibuat oleh upload sebelumnya.
        $examinerExisted = Employee::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower(self::EXAMINER)])->exists();

        DB::beginTransaction();

        try {
            $response = $this->actingAs(User::find(1))
                ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => self::CONTRACT_ID])
                ->post('/participant-upload-laboratorium', ['fileExcel' => $file]);

            $response->assertOk();
            $response->assertJson([
                'status' => 'success',
                'processed' => 3,
                'skipped' => 0,
            ]);

            $stored = Laboratorium::where('contract_id', self::CONTRACT_ID)
                ->whereIn('participant_id', $participants->pluck('id'));

            $this->assertSame(
                3,
                (clone $stored)->count(),
                'Respons sukses tetapi tidak ada baris laboratorium yang tersimpan.'
            );

            $first = $participants->first();
            $sample = (clone $stored)->where('participant_id', $first->id)->first();

            $this->assertNotNull($sample, "Baris laboratorium untuk nomor formulir {$first->no_form} tidak tersimpan.");
            $this->assertSame('17.4', trim((string) $sample->hemoglobin));
            // Nilai sel bisa membawa spasi di ujung, jadi dibandingkan setelah di-trim.
            $this->assertSame('Normal', trim((string) $sample->kesimpulan));
            $this->assertSame('Negatif', trim((string) $sample->hbsag));
            $this->assertSame((int) $first->no_form, (int) $sample->form_no);
            $this->assertSame(self::CLIENT_ID, (int) $sample->client_id);
            $this->assertSame(self::CONTRACT_ID, (int) $sample->contract_id);
            $this->assertSame(
                Employee::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower(self::EXAMINER)])->value('id'),
                (int) $sample->employee_id,
                'Pemeriksa lab tidak dipetakan ke employee dengan namanya sendiri.'
            );
            $this->assertSame(
                $examinerExisted ? [] : [self::EXAMINER],
                $response->json('created_doctors')
            );
        } finally {
            DB::rollBack();

            @unlink($file->getPathname());
        }
    }

    /**
     * Pemeriksa di file ditulis tanpa gelar ("Murni Islamiati") padahal di
     * master karyawan sudah ada lengkap dengan gelarnya ("Murni Islamiati,
     * A.Md.A.K"). Upload harus memakai karyawan yang sudah terdaftar itu,
     * bukan membuat karyawan baru dengan nama tanpa gelar.
     */
    public function test_upload_uses_the_employee_whose_name_only_differs_by_its_title(): void
    {
        $participants = $this->participants();
        $file = $this->file($participants, ['17.4', '13.1', '14.0'], self::EXAMINER_BASE);

        // Hanya versi lengkap dengan gelar yang terdaftar di master karyawan.
        $this->assertSame(
            0,
            Employee::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower(self::EXAMINER_BASE)])->count(),
            'Nama tanpa gelar untuk uji ini sudah ada di master, data uji tidak bersih.'
        );

        DB::beginTransaction();

        try {
            $employee = Employee::create([
                'nama' => self::EXAMINER_TITLED,
                'code' => 'E-99998',
                'client_id' => self::CLIENT_ID,
            ]);
            $employeesBefore = Employee::count();

            $response = $this->actingAs(User::find(1))
                ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => self::CONTRACT_ID])
                ->post('/participant-upload-laboratorium', ['fileExcel' => $file]);

            $response->assertOk();
            $response->assertJson([
                'status' => 'success',
                'processed' => 3,
                'skipped' => 0,
            ]);

            $this->assertSame(
                [],
                $response->json('created_doctors'),
                'Pemeriksa yang sudah terdaftar (lengkap dengan gelar) tidak boleh dibuat ulang.'
            );
            $this->assertSame($employeesBefore, Employee::count(), 'Upload membuat karyawan baru.');
            $this->assertSame(
                0,
                Employee::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower(self::EXAMINER_BASE)])->count(),
                'Upload membuat karyawan baru bernama tanpa gelar.'
            );

            $rows = Laboratorium::where('contract_id', self::CONTRACT_ID)
                ->whereIn('participant_id', $participants->pluck('id'))
                ->with('employee')
                ->get();

            $this->assertCount(3, $rows, 'Baris hasil lab tidak tersimpan.');

            foreach ($rows as $row) {
                $this->assertSame(
                    $employee->id,
                    (int) $row->employee_id,
                    'Pemeriksa lab tidak dipetakan ke karyawan dengan gelar yang sudah terdaftar.'
                );
                $this->assertSame(self::EXAMINER_TITLED, $row->employee?->nama);
            }
        } finally {
            DB::rollBack();

            @unlink($file->getPathname());
        }
    }

    /**
     * Bila nama tanpa gelar juga ada di master (sisa upload lama), nama yang
     * lengkap dengan gelarlah yang dipakai supaya hasil pemeriksaan tidak
     * menempel ke karyawan yang cuma diketik ulang dari file.
     */
    public function test_upload_prefers_the_employee_name_that_carries_the_title(): void
    {
        $participants = $this->participants();
        $file = $this->file($participants, ['17.4', '13.1', '14.0'], self::EXAMINER_BASE);

        DB::beginTransaction();

        try {
            // Nama tanpa gelar sengaja dibuat lebih dulu dan milik client sesi
            // supaya hanya urutan prioritas yang menentukan hasilnya.
            $duplicate = Employee::create([
                'nama' => self::EXAMINER_BASE,
                'code' => 'E-99997',
                'client_id' => self::CLIENT_ID,
            ]);
            $titled = Employee::create([
                'nama' => self::EXAMINER_TITLED,
                'code' => 'E-99996',
            ]);

            $response = $this->actingAs(User::find(1))
                ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => self::CONTRACT_ID])
                ->post('/participant-upload-laboratorium', ['fileExcel' => $file]);

            $response->assertOk();
            $response->assertJson([
                'status' => 'success',
                'processed' => 3,
                'skipped' => 0,
            ]);
            $this->assertSame([], $response->json('created_doctors'));

            $rows = Laboratorium::where('contract_id', self::CONTRACT_ID)
                ->whereIn('participant_id', $participants->pluck('id'))
                ->with('employee')
                ->get();

            $this->assertCount(3, $rows, 'Baris hasil lab tidak tersimpan.');

            foreach ($rows as $row) {
                $this->assertSame(
                    $titled->id,
                    (int) $row->employee_id,
                    'Upload memakai karyawan tanpa gelar padahal master sudah punya nama dengan gelar.'
                );
                $this->assertNotSame($duplicate->id, (int) $row->employee_id);
            }
        } finally {
            DB::rollBack();

            @unlink($file->getPathname());
        }
    }

    public function test_upload_reports_rows_it_could_not_match(): void
    {
        // Kontrak tanpa peserta: seluruh baris harus dilaporkan dilewati,
        // bukan mengaku sukses.
        $participants = $this->participants();
        $file = $this->file($participants);
        $employeesBefore = Employee::count();

        DB::beginTransaction();

        try {
            $response = $this->actingAs(User::find(1))
                ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => 9])
                ->post('/participant-upload-laboratorium', ['fileExcel' => $file]);

            $response->assertOk();
            $response->assertJson([
                'status' => 'success',
                'processed' => 0,
                'skipped' => 3,
            ]);

            $this->assertSame(0, Laboratorium::where('contract_id', 9)->count());
            $this->assertCount(
                3,
                $response->json('skipped_codes'),
                'Kode baris yang dilewati tidak dilaporkan.'
            );
            $this->assertSame(
                $employeesBefore,
                Employee::count(),
                'Baris yang dilewati tidak boleh membuat data karyawan baru.'
            );
        } finally {
            DB::rollBack();

            @unlink($file->getPathname());
        }
    }
}
