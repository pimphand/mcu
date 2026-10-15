<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeSignatureTest extends TestCase
{
    /** @var array<int, string> */
    private array $uploadedFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->uploadedFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nik' => '9999998',
            'nama' => 'dr. Uji Tanda Tangan',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
        ], $overrides);
    }

    private function makeEmployee(): Employee
    {
        return Employee::create([
            'code' => 'E-99998',
            'nik' => '9999998',
            'nama' => 'dr. Uji Tanda Tangan',
        ]);
    }

    private function trackSignature(Employee $employee): string
    {
        $path = public_path((string) $employee->ttd);
        $this->uploadedFiles[] = $path;

        return $path;
    }

    public function test_edit_page_has_a_signature_field_and_previews_the_current_signature(): void
    {
        $employee = Employee::where('ttd', 'ttd/dr_dini.png')->first();

        $this->assertNotNull($employee, 'Employee dengan ttd contoh tidak ditemukan.');

        $this->actingAs(User::find(1))
            ->get('/employee/' . $employee->id)
            ->assertOk()
            ->assertSee('Gambar TTD')
            ->assertSee('name="ttd"', false)
            ->assertSee($employee->ttd, false);
    }

    public function test_edit_page_tells_when_the_employee_has_no_signature_yet(): void
    {
        $employee = Employee::whereNull('ttd')->first();

        $this->assertNotNull($employee, 'Employee tanpa ttd tidak ditemukan.');

        $this->actingAs(User::find(1))
            ->get('/employee/' . $employee->id)
            ->assertOk()
            ->assertSee('Belum ada gambar tanda tangan')
            ->assertSee('name="ttd"', false);
    }

    public function test_update_saves_a_new_signature_into_public_ttd(): void
    {
        DB::beginTransaction();

        try {
            $employee = $this->makeEmployee();

            $response = $this->actingAs(User::find(1))->put('/employee/' . $employee->id, $this->payload([
                'ttd' => UploadedFile::fake()->image('ttd-baru.png', 200, 80),
            ]));

            $response->assertRedirect(route('employee.index'));

            $employee->refresh();

            $this->assertStringStartsWith('ttd/', (string) $employee->ttd, 'Path ttd tidak mengarah ke folder public/ttd.');
            $this->assertStringContainsString('dr-uji-tanda-tangan', (string) $employee->ttd);
            $this->assertStringEndsWith('.png', (string) $employee->ttd);
            $this->assertFileExists($this->trackSignature($employee));
        } finally {
            DB::rollBack();
        }
    }

    public function test_create_also_stores_the_signature_into_public_ttd(): void
    {
        DB::beginTransaction();

        try {
            $response = $this->actingAs(User::find(1))->post('/employee', $this->payload([
                'nama' => 'dr. Karyawan Baru TTD',
                'ttd' => UploadedFile::fake()->image('ttd-karyawan.png', 200, 80),
            ]));

            $response->assertRedirect(route('employee.index'));

            $employee = Employee::where('nama', 'dr. Karyawan Baru TTD')->first();

            $this->assertNotNull($employee, 'Employee baru tidak tersimpan.');
            $this->assertStringStartsWith('ttd/', (string) $employee->ttd);
            $this->assertFileExists($this->trackSignature($employee));
        } finally {
            DB::rollBack();
        }
    }

    public function test_update_replaces_the_previous_signature(): void
    {
        DB::beginTransaction();

        try {
            $employee = Employee::where('ttd', 'ttd/dr_dini.png')->first();
            $this->assertNotNull($employee);

            $this->actingAs(User::find(1))->put('/employee/' . $employee->id, $this->payload([
                'nama' => 'dr. Dini Desti Susanti MMRS',
                'ttd' => UploadedFile::fake()->image('ttd-pengganti.png', 200, 80),
            ]))->assertRedirect(route('employee.index'));

            $employee->refresh();

            $this->assertNotSame('ttd/dr_dini.png', $employee->ttd);
            $this->assertFileExists($this->trackSignature($employee));
        } finally {
            DB::rollBack();
        }
    }

    public function test_update_without_a_file_keeps_the_existing_signature(): void
    {
        DB::beginTransaction();

        try {
            $employee = Employee::where('ttd', 'ttd/dr_murni.png')->first();
            $this->assertNotNull($employee);
            $before = $employee->ttd;

            // Form edit mengirim field ttd kosong bila admin tidak memilih file.
            $this->actingAs(User::find(1))
                ->put('/employee/' . $employee->id, $this->payload([
                    'nama' => $employee->nama,
                    'ttd' => '',
                ]))
                ->assertRedirect(route('employee.index'));

            $this->assertSame($before, $employee->fresh()->ttd);
        } finally {
            DB::rollBack();
        }
    }

    public function test_update_rejects_a_non_image_signature(): void
    {
        DB::beginTransaction();

        try {
            $employee = Employee::where('ttd', 'ttd/dr_murni.png')->first();
            $this->assertNotNull($employee);

            $this->actingAs(User::find(1))
                ->put('/employee/' . $employee->id, $this->payload([
                    'nama' => $employee->nama,
                    'ttd' => UploadedFile::fake()->create('ttd.txt', 5, 'text/plain'),
                ]))
                ->assertSessionHasErrors('ttd');

            $this->assertSame('ttd/dr_murni.png', $employee->fresh()->ttd);
        } finally {
            DB::rollBack();
        }
    }
}
