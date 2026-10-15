<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeResolverTest extends TestCase
{
    /**
     * Nama yang dijamin belum terdaftar, supaya test tidak bergantung pada
     * data dokter yang sudah ada di database.
     */
    private function unregisteredName(): string
    {
        return 'dr. Uji Resolver ' . Str::random(8);
    }

    public function test_creates_a_new_employee_when_the_name_is_not_registered(): void
    {
        DB::beginTransaction();

        try {
            $before = Employee::count();
            $nama = $this->unregisteredName();
            $service = new EmployeeService;

            $employee = $service->findOrCreateByName($nama, 1);

            $this->assertNotNull($employee);
            $this->assertTrue($employee->wasRecentlyCreated);
            $this->assertSame($nama, $employee->nama);
            $this->assertSame($before + 1, Employee::count());
            $this->assertNotSame(3, $employee->id, 'Pemeriksa baru tidak boleh memakai employee yang sudah ada.');
            $this->assertSame([$nama], $service->getCreatedEmployees());
        } finally {
            DB::rollBack();
        }
    }

    public function test_reuses_the_same_employee_for_the_same_name(): void
    {
        DB::beginTransaction();

        try {
            $nama = $this->unregisteredName();
            $service = new EmployeeService;
            $first = $service->findOrCreateByName($nama, 1);
            $second = $service->findOrCreateByName(mb_strtoupper($nama), 1);

            $this->assertSame($first->id, $second->id);
            $this->assertFalse($second->wasRecentlyCreated);
            $this->assertSame(1, Employee::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($nama)])->count());
        } finally {
            DB::rollBack();
        }
    }

    public function test_matches_existing_employees_instead_of_creating_duplicates(): void
    {
        $existing = Employee::where('nama', 'dr. Ayu Setyowati S, SP.Rad')->first();

        $this->assertNotNull($existing, 'Data dokter contoh tidak ditemukan.');

        DB::beginTransaction();

        try {
            $service = new EmployeeService;
            $employee = $service->findOrCreateByName('  dr. Ayu Setyowati S, SP.Rad ', 1);

            $this->assertSame($existing->id, $employee->id);
            $this->assertFalse($employee->wasRecentlyCreated);
            $this->assertSame([], $service->getCreatedEmployees());
        } finally {
            DB::rollBack();
        }
    }

    public function test_returns_null_when_the_examiner_name_is_empty(): void
    {
        $service = new EmployeeService;

        $this->assertNull($service->findOrCreateIdByName(null));
        $this->assertNull($service->findOrCreateIdByName('   '));
        $this->assertSame([], $service->getCreatedEmployees());
    }

    public function test_cached_resolver_only_creates_one_employee_per_name(): void
    {
        DB::beginTransaction();

        try {
            $before = Employee::count();
            $nama = $this->unregisteredName();
            $service = new EmployeeService;

            $ids = [];
            for ($i = 0; $i < 5; $i++) {
                $ids[] = $service->findOrCreateIdByName($nama, 1);
            }

            $this->assertCount(1, array_unique($ids));
            $this->assertSame($before + 1, Employee::count());
            $this->assertSame([$nama], $service->getCreatedEmployees());
        } finally {
            DB::rollBack();
        }
    }
}
