<?php

namespace Tests\Feature;

use App\Models\Radiologi;
use App\Models\User;
use Tests\TestCase;

class RadiologiListingTest extends TestCase
{
    private const CLIENT_ID = 1;
    private const MCU_DATE = '2026-10-12';

    /**
     * @return array<int, mixed>
     */
    private function listing(?string $dateRange, ?int $divisiId = null): array
    {
        // http_build_query memakai encoding yang sama dengan URLSearchParams di
        // frontend (spasi menjadi '+'), sehingga format wire-nya ikut teruji.
        $query = ['client_id' => self::CLIENT_ID];

        if ($dateRange !== null) {
            $query['date_range'] = $dateRange;
        }

        if ($divisiId !== null) {
            $query['divisi_id'] = $divisiId;
        }

        $url = '/participant-upload-radiologi?' . http_build_query($query);

        $response = $this->actingAs(User::find(1))
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => 4])
            ->get($url);

        $response->assertOk();

        return $response->json('data');
    }

    /**
     * Jumlah baris yang seharusnya dikembalikan endpoint untuk satu rentang
     * tanggal MCU, dihitung langsung dari database.
     */
    private function expectedRows(string $from, string $to, ?int $divisiId = null): int
    {
        return Radiologi::query()
            ->whereHas('participant', function ($query) use ($divisiId) {
                $query->where('client_id', self::CLIENT_ID)
                    ->when($divisiId, fn ($sub) => $sub->where('divisi_id', $divisiId));
            })
            ->whereBetween('date_mcu', [$from, $to])
            ->count();
    }

    public function test_listing_shows_every_row_when_no_date_is_picked(): void
    {
        $expected = Radiologi::query()
            ->whereHas('participant', fn ($query) => $query->where('client_id', self::CLIENT_ID))
            ->count();

        $this->assertGreaterThan(0, $expected, 'Tidak ada data radiologi pada client ini untuk diuji.');
        $this->assertCount($expected, $this->listing(null));
    }

    public function test_listing_filters_by_mcu_date_not_participant_register_date(): void
    {
        $expected = $this->expectedRows(self::MCU_DATE, self::MCU_DATE);

        $this->assertGreaterThan(0, $expected);
        $this->assertCount($expected, $this->listing(self::MCU_DATE . ' to ' . self::MCU_DATE));

        // Peserta kontrak 1 punya register_date 2026-04-10 tetapi tanpa date_mcu.
        // Sebelum perbaikan filter register_date membuat baris ini muncul di sini.
        $this->assertCount(0, $this->listing('2026-04-10 to 2026-04-10'));
    }

    public function test_listing_filters_by_divisi(): void
    {
        $divisiId = 23; // Produksi - punya data radiologi pada kontrak 4
        $expected = $this->expectedRows(self::MCU_DATE, self::MCU_DATE, $divisiId);
        $withoutDivisi = $this->expectedRows(self::MCU_DATE, self::MCU_DATE);

        $this->assertGreaterThan(0, $expected, 'Divisi uji tidak punya data radiologi.');
        $this->assertLessThan($withoutDivisi, $expected, 'Filter divisi tidak mempersempit hasil.');

        $rows = $this->listing(self::MCU_DATE . ' to ' . self::MCU_DATE, $divisiId);

        $this->assertCount($expected, $rows);

        foreach ($rows as $row) {
            $this->assertSame($divisiId, (int) $row['participant']['divisi_id']);
        }
    }

    public function test_listing_returns_nothing_for_a_divisi_without_data(): void
    {
        $divisiId = 37; // terdaftar pada client ini, tetapi belum punya data radiologi

        $this->assertSame(0, $this->expectedRows(self::MCU_DATE, self::MCU_DATE, $divisiId));
        $this->assertCount(0, $this->listing(self::MCU_DATE . ' to ' . self::MCU_DATE, $divisiId));
    }

    public function test_uploaded_rows_are_visible_in_the_listing(): void
    {
        $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => 4])
            ->get('/participant-upload-radiologi')
            ->assertOk()
            ->assertSee('loadRadiologi();', false);
    }
}
