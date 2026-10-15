<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Radiologi;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RadiologiUploadTest extends TestCase
{
    /**
     * Template Radiologi.xlsx holds 329 MCU rows of contract "12 OKTOBER 2026" (id 4).
     */
    public function test_upload_radiologi_stores_rows_for_the_active_contract(): void
    {
        $clientId = 1;
        $contractId = 4;
        $examinerName = 'dr. Nicha Stevia Sp. Rad';
        // Dokter pemeriksa mungkin sudah pernah dibuat oleh upload sebelumnya.
        $examinerExisted = Employee::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($examinerName)])->exists();
        $rowsBefore = Radiologi::count();

        DB::beginTransaction();

        try {
            $file = new UploadedFile(
                base_path('Template Radiologi.xlsx'),
                'Template Radiologi.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true
            );

            $response = $this->actingAs(User::find(1))
                ->withSession(['client_id' => $clientId, 'contract_id' => $contractId])
                ->post('/participant-upload-radiologi', ['fileExcel' => $file]);

            $response->assertOk();
            $response->assertJson(['status' => 'success']);

            $stored = Radiologi::whereHas('participant', function ($query) use ($contractId) {
                $query->where('contract_id', $contractId);
            });

            $this->assertSame(329, (clone $stored)->count(), 'Response was successful but no radiologi row was stored.');

            $sample = (clone $stored)->whereHas('participant', fn ($query) => $query->where('code', 'MCU00001'))->first();
            $this->assertNotNull($sample);
            $this->assertSame($contractId, (int) $sample->contract_id, 'contract_id radiologi tidak ikut terisi dari peserta.');
            $this->assertSame('2026-10-12', substr((string) $sample->date_mcu, 0, 10));
            $this->assertSame('dr. Nicha Stevia Sp. Rad', $sample->diperiksa);
            $this->assertSame(1, (int) $sample->selesai);

            // Pemeriksa harus dipetakan ke namanya sendiri, bukan dokter lain.
            $examiner = Employee::where('nama', 'dr. Nicha Stevia Sp. Rad')->first();
            $this->assertNotNull($examiner, 'Pemeriksa dari file tidak dibuatkan di tabel employees.');
            $this->assertNotSame(3, $examiner->id);
            $this->assertSame($examiner->id, (int) $sample->employee_id);
            $this->assertSame(0, (clone $stored)->where('employee_id', 3)->count(), 'Masih ada baris yang menunjuk ke dokter lain.');
            $this->assertSame([$examiner->id], (clone $stored)->pluck('employee_id')->unique()->values()->all());
            $this->assertSame(
                $examinerExisted ? [] : [$examinerName],
                $response->json('created_doctors'),
                'created_doctors tidak sesuai dengan kondisi awal tabel employees.'
            );
        } finally {
            DB::rollBack();
        }

        $this->assertSame($rowsBefore, Radiologi::count(), 'Test data leaked outside of the transaction.');
    }

    public function test_upload_radiologi_reports_rows_it_could_not_match(): void
    {
        DB::beginTransaction();

        try {
            $file = new UploadedFile(
                base_path('Template Radiologi.xlsx'),
                'Template Radiologi.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true
            );

            $response = $this->actingAs(User::find(1))
                ->withSession(['client_id' => 1, 'contract_id' => 9])
                ->post('/participant-upload-radiologi', ['fileExcel' => $file]);

            $response->assertOk();
            $response->assertJson([
                'status' => 'success',
                'processed' => 0,
                'skipped' => 329,
            ]);

            $this->assertSame(0, Radiologi::whereHas('participant', fn ($query) => $query->where('contract_id', 9))->count());
        } finally {
            DB::rollBack();
        }
    }
}
