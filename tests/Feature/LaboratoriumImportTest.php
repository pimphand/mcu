<?php

namespace Tests\Feature;

use App\Imports\LaboratoriumImport;
use App\Models\Employee;
use App\Models\Laboratorium;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class LaboratoriumImportTest extends TestCase
{
    public function test_import_links_the_examiner_and_fills_client_id(): void
    {
        DB::beginTransaction();

        try {
            $participant = Participant::where('code', 'MCU00001')->where('contract_id', 4)->first();
            $this->assertNotNull($participant, 'Peserta uji tidak ditemukan.');

            $examiner = Employee::where('nama', 'Murni Islamiati, A.Md.A.K')->first();
            $this->assertNotNull($examiner);

            $import = new LaboratoriumImport(1, 4);
            $import->model([
                'code' => 'MCU00001',
                'name' => $participant->name,
                'hemoglobin' => '13.1',
                'pemeriksa_lab' => 'murni islamiati, a.md.a.k', // nama beda kapitalisasi
            ]);

            $lab = Laboratorium::where('participant_id', $participant->id)->first();

            $this->assertNotNull($lab, 'Baris laboratorium tidak tersimpan.');
            $this->assertSame('13.1', $lab->hemoglobin);
            $this->assertSame(1, (int) $lab->client_id);
            $this->assertSame($examiner->id, (int) $lab->employee_id);
            $this->assertSame([], $import->getCreatedDoctors());
        } finally {
            DB::rollBack();
        }
    }

    public function test_import_creates_a_new_employee_for_an_unknown_examiner(): void
    {
        DB::beginTransaction();

        try {
            $participant = Participant::where('code', 'MCU00002')->where('contract_id', 4)->first();
            $this->assertNotNull($participant);

            $before = Employee::count();
            // Nama yang dijamin belum terdaftar agar test tidak bergantung data dokter nyata.
            $examinerName = 'dr. Uji Lab ' . Str::random(8);

            $import = new LaboratoriumImport(1, 4);
            $import->model([
                'code' => 'MCU00002',
                'name' => $participant->name,
                'lekosit' => '7.700',
                'pemeriksa_lab' => $examinerName,
            ]);

            $lab = Laboratorium::where('participant_id', $participant->id)->first();
            $examiner = Employee::where('nama', $examinerName)->first();

            $this->assertNotNull($lab);
            $this->assertNotNull($examiner, 'Pemeriksa lab yang belum terdaftar tidak dibuatkan.');
            $this->assertNotSame(3, $examiner->id);
            $this->assertSame($examiner->id, (int) $lab->employee_id);
            $this->assertSame($before + 1, Employee::count());
            $this->assertSame([$examinerName], $import->getCreatedDoctors());
        } finally {
            DB::rollBack();
        }
    }

    public function test_import_ignores_participants_from_another_contract(): void
    {
        DB::beginTransaction();

        try {
            // Peserta ini hanya terdaftar pada kontrak 3.
            $other = Participant::create([
                'code' => 'ZZ-TEST-99',
                'nik' => '9999999',
                'name' => 'Peserta kontrak 3',
                'gender' => 'L',
                'birthday' => '1990-01-01',
                'client_id' => 1,
                'contract_id' => 3,
            ]);

            $row = [
                'code' => 'ZZ-TEST-99',
                'name' => $other->name,
                'hemoglobin' => '9.9',
                'pemeriksa_lab' => 'Murni Islamiati, A.Md.A.K',
            ];

            // Sesi kontrak 4: peserta kontrak lain harus dilewati.
            (new LaboratoriumImport(1, 4))->model($row);
            $this->assertSame(0, Laboratorium::where('participant_id', $other->id)->count());

            // Sesi kontrak 3: barulah tersimpan.
            (new LaboratoriumImport(1, 3))->model($row);
            $this->assertSame(1, Laboratorium::where('participant_id', $other->id)->count());
        } finally {
            DB::rollBack();
        }
    }
}
