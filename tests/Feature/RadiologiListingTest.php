<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Radiologi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RadiologiListingTest extends TestCase
{
    private const CLIENT_ID = 1;
    private const SESSION_CONTRACT = 4;
    private const MCU_DATE = '2026-10-12';

    /**
     * @param  array<string, mixed>  $params  query string yang dikirim frontend
     * @param  array<string, mixed>  $session
     * @return array<int, mixed>
     */
    private function listing(array $params = [], array $session = []): array
    {
        // http_build_query memakai encoding yang sama dengan URLSearchParams di
        // frontend (spasi menjadi '+'), sehingga format wire-nya ikut teruji.
        $url = '/participant-upload-radiologi?' . http_build_query(
            array_merge(['client_id' => self::CLIENT_ID], $params)
        );

        $response = $this->actingAs(User::find(1))
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->withSession(array_merge([
                'client_id' => self::CLIENT_ID,
                'contract_id' => self::SESSION_CONTRACT,
            ], $session))
            ->get($url);

        $response->assertOk();

        return $response->json('data');
    }

    /**
     * Jumlah baris yang seharusnya dikembalikan endpoint untuk satu kombinasi
     * filter, dihitung langsung dari database.
     *
     * @param  array<string, mixed>  $filters
     */
    private function expectedRows(array $filters = [], ?int $contractId = self::SESSION_CONTRACT): int
    {
        return Radiologi::query()
            ->when($contractId, fn ($query, $id) => $query->where('contract_id', $id))
            ->whereHas('participant', function ($query) use ($filters) {
                $query->where('client_id', self::CLIENT_ID)
                    ->when($filters['divisi_id'] ?? null, fn ($sub, $id) => $sub->where('divisi_id', $id));
            })
            ->when($filters['date_range'] ?? null, function ($query, $range) {
                [$from, $to] = array_pad(explode(' to ', $range), 2, null);

                return $query->whereBetween('date_mcu', [$from, $to ?? $from]);
            })
            ->count();
    }

    public function test_listing_only_shows_rows_of_the_active_contract(): void
    {
        $rows = $this->listing();

        $this->assertGreaterThan(0, count($rows), 'Tidak ada data radiologi pada kontrak sesi untuk diuji.');
        $this->assertCount($this->expectedRows(), $rows);

        foreach ($rows as $row) {
            $this->assertSame(self::SESSION_CONTRACT, (int) $row['contract_id']);
        }
    }

    public function test_listing_filters_by_mcu_date_not_participant_register_date(): void
    {
        $filters = ['date_range' => self::MCU_DATE . ' to ' . self::MCU_DATE];
        $expected = $this->expectedRows($filters);

        $this->assertGreaterThan(0, $expected);
        $this->assertCount($expected, $this->listing($filters));

        // Peserta kontrak lain punya register_date 2026-04-10 tetapi tanpa
        // date_mcu, jadi tidak boleh muncul pada rentang tanggal itu.
        $this->assertCount(0, $this->listing(['date_range' => '2026-04-10 to 2026-04-10']));
    }

    public function test_listing_filters_by_divisi(): void
    {
        $filters = [
            'date_range' => self::MCU_DATE . ' to ' . self::MCU_DATE,
            'divisi_id' => 23, // Produksi - punya data radiologi pada kontrak 4
        ];

        $expected = $this->expectedRows($filters);
        $withoutDivisi = $this->expectedRows(['date_range' => self::MCU_DATE . ' to ' . self::MCU_DATE]);

        $this->assertGreaterThan(0, $expected, 'Divisi uji tidak punya data radiologi.');
        $this->assertLessThan($withoutDivisi, $expected, 'Filter divisi tidak mempersempit hasil.');

        $rows = $this->listing($filters);

        $this->assertCount($expected, $rows);

        foreach ($rows as $row) {
            $this->assertSame(23, (int) $row['participant']['divisi_id']);
        }
    }

    public function test_listing_returns_nothing_for_a_divisi_without_data(): void
    {
        $filters = [
            'date_range' => self::MCU_DATE . ' to ' . self::MCU_DATE,
            'divisi_id' => 37, // terdaftar pada client ini, tetapi belum punya data radiologi
        ];

        $this->assertSame(0, $this->expectedRows($filters));
        $this->assertCount(0, $this->listing($filters));
    }

    public function test_listing_uses_the_contract_from_the_mcu_in_session(): void
    {
        DB::beginTransaction();

        try {
            // Dua baris pada kontrak lain (37 & 38 = peserta kontrak 3).
            foreach ([37, 38] as $participantId) {
                Radiologi::create([
                    'participant_id' => $participantId,
                    'contract_id' => 3,
                    'date_mcu' => '2026-09-01',
                    'kesan' => 'DATA UJI KONTRAK SESI',
                ]);
            }

            $otherContract = $this->listing([], ['contract_id' => 3]);

            $this->assertCount(2, $otherContract);

            foreach ($otherContract as $row) {
                $this->assertSame(3, (int) $row['contract_id']);
            }

            // Sesi kontrak 4 tetap tidak terpengaruh data kontrak 3.
            $this->assertCount($this->expectedRows(), $this->listing());
        } finally {
            DB::rollBack();
        }
    }

    public function test_listing_ignores_contract_id_sent_from_the_client(): void
    {
        $rows = $this->listing(['contract_id' => 1]);

        $this->assertCount($this->expectedRows(), $rows);

        foreach ($rows as $row) {
            $this->assertSame(self::SESSION_CONTRACT, (int) $row['contract_id']);
        }
    }

    public function test_contract_is_shown_from_session_and_not_as_a_filter(): void
    {
        $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => self::SESSION_CONTRACT])
            ->get('/participant-upload-radiologi')
            ->assertOk()
            ->assertSee(Contract::whereKey(self::SESSION_CONTRACT)->value('name'))
            ->assertDontSee('filter[contract_id]', false)
            ->assertDontSee("contract_id: $('#contract_id').val()", false)
            ->assertSee('loadRadiologi();', false);
    }

    public function test_page_shows_a_placeholder_when_no_contract_is_active(): void
    {
        $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => null])
            ->get('/participant-upload-radiologi')
            ->assertOk()
            ->assertSee('Belum ada kontrak aktif');
    }
}
