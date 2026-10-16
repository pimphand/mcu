<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Radiologi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RadiologiListingTest extends TestCase
{
    private const CLIENT_ID = 1;
    private const SESSION_CONTRACT = 4;
    private const MCU_DATE = '2026-10-12';

    /**
     * Panggil endpoint daftar radiologi tanpa asumsi status, dipakai untuk
     * kasus yang memang harus ditolak.
     *
     * @param  array<string, mixed>  $filters  nilai filter[...] yang dikirim frontend
     * @param  array<string, mixed>  $session
     * @param  array<string, mixed>  $extra  parameter query mentah di luar filter
     */
    private function rawListing(array $filters = [], array $session = [], ?string $sort = null, array $extra = []): TestResponse
    {
        // http_build_query memakai encoding yang sama dengan URLSearchParams di
        // frontend (spasi menjadi '+', kurung menjadi %5B/%5D), sehingga format
        // wire-nya ikut teruji.
        $query = array_merge(['client_id' => self::CLIENT_ID], $extra);

        if ($filters) {
            $query['filter'] = $filters;
        }

        if ($sort) {
            $query['sort'] = $sort;
        }

        $url = '/participant-upload-radiologi?' . http_build_query($query);

        return $this->actingAs(User::find(1))
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->withSession(array_merge([
                'client_id' => self::CLIENT_ID,
                'contract_id' => self::SESSION_CONTRACT,
            ], $session))
            ->get($url);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, mixed>
     */
    private function listing(array $filters = [], array $session = [], ?string $sort = null, array $extra = []): array
    {
        $response = $this->rawListing($filters, $session, $sort, $extra);

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
                    ->when($filters['divisi_id'] ?? null, fn ($sub, $id) => $sub->where('divisi_id', $id))
                    ->when($filters['search'] ?? null, function ($sub, $term) {
                        $sub->where(function ($inner) use ($term) {
                            $inner->where('name', 'like', "%{$term}%")
                                ->orWhere('code', 'like', "%{$term}%");
                        });
                    });
            })
            ->when($filters['date_range'] ?? null, function ($query, $range) {
                [$from, $to] = array_pad(explode(' to ', $range), 2, null);

                return $query->whereBetween('date_mcu', [$from, $to ?? $from]);
            })
            ->count();
    }

    /**
     * Satu baris radiologi (beserta pesertanya) pada kontrak sesi.
     */
    private function sampleRow(): Radiologi
    {
        $row = Radiologi::query()
            ->where('contract_id', self::SESSION_CONTRACT)
            ->whereHas('participant', fn ($query) => $query->where('client_id', self::CLIENT_ID))
            ->with('participant')
            ->first();

        $this->assertNotNull($row, 'Tidak ada data radiologi pada kontrak sesi untuk diuji.');

        return $row;
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

    public function test_listing_filters_by_participant_name_and_code(): void
    {
        $sample = $this->sampleRow();
        $term = mb_substr($sample->participant->name, 0, 5);

        $byName = ['search' => $term];
        $expected = $this->expectedRows($byName);

        $this->assertGreaterThan(0, $expected);

        $rows = $this->listing($byName);

        $this->assertCount($expected, $rows);
        $this->assertContains(
            $sample->participant_id,
            array_column($rows, 'participant_id'),
            'Peserta yang dicari tidak muncul pada hasil filter.'
        );

        foreach ($rows as $row) {
            $this->assertStringContainsStringIgnoringCase($term, $row['participant']['name']);
        }

        $byCode = $this->listing(['search' => $sample->participant->code]);

        $this->assertNotEmpty($byCode);

        foreach ($byCode as $row) {
            $this->assertSame($sample->participant->code, $row['participant']['code']);
        }

        $this->assertCount(0, $this->listing(['search' => 'TIDAK ADA NAMA INI']));
    }

    public function test_listing_sorts_by_mcu_id_in_both_directions(): void
    {
        $ascending = array_column($this->listing([], [], 'code'), 'id');
        $descending = array_column($this->listing([], [], '-code'), 'id');

        $this->assertGreaterThan(1, count($ascending));
        $this->assertSame(
            array_reverse($descending),
            $ascending,
            'Sortir MCU ID naik dan turun tidak saling berkebalikan.'
        );
        $this->assertNotSame($ascending, $descending, 'Arah sortir tidak berpengaruh pada urutan.');

        // Kolom hasil radiologi juga bisa disortir lewat whitelist yang sama.
        $this->assertCount($this->expectedRows(), $this->listing([], [], 'kesan'));
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
        // Kontrak tetap diambil dari sesi MCU IN, bukan dari query string.
        $rows = $this->listing([], [], null, ['contract_id' => 1]);

        $this->assertCount($this->expectedRows(), $rows);

        foreach ($rows as $row) {
            $this->assertSame(self::SESSION_CONTRACT, (int) $row['contract_id']);
        }
    }

    public function test_listing_rejects_unknown_filter_and_sort(): void
    {
        $this->rawListing([], [], 'tanggal_mcu')->assertStatus(400);
        $this->rawListing(['tidak_ada' => 'x'])->assertStatus(400);
    }

    public function test_page_renders_filters_sortable_headers_and_matching_columns(): void
    {
        $response = $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => self::SESSION_CONTRACT])
            ->get('/participant-upload-radiologi');

        $response->assertOk();

        $html = $response->getContent();

        preg_match('/<thead>(.*?)<\/thead>/s', $html, $head);

        $this->assertNotEmpty($head, 'Tabel tidak punya thead.');
        $this->assertSame(11, substr_count($head[1], '<th'), 'Jumlah judul kolom berubah.');

        // Setiap baris data harus punya jumlah kolom yang sama dengan judulnya.
        $this->assertSame(
            11,
            substr_count($html, '<td class="border-bottom border-top'),
            'Jumlah kolom pada baris data tidak sama dengan judul kolom.'
        );

        $response
            ->assertSee('Upload Radiologi', false)
            ->assertSee(Contract::whereKey(self::SESSION_CONTRACT)->value('name'))
            ->assertSee('Memuat data...', false)
            ->assertSee('colspan="11"', false)
            ->assertDontSee('filter[contract_id]', false)
            ->assertDontSee("contract_id: $('#contract_id').val()", false)
            ->assertSee('id="search"', false)
            ->assertSee('name="filter[search]"', false)
            ->assertSee('id="cari_text"', false)
            ->assertSee('id="reset"', false)
            ->assertSee('id="upload_text"', false)
            ->assertSee('id="upload_icon"', false)
            ->assertSee("$('#cari_text').text(", false)
            ->assertSee("$('#upload_text').text(", false)
            // Tombol tidak boleh memakai .text() langsung, itu menghapus ikonnya.
            ->assertDontSee("$('#cari').attr('disabled'", false)
            ->assertDontSee("$('#upload_btn').attr('disabled'", false)
            // Nama parameter harus sama dengan yang diizinkan endpoint.
            ->assertSee("params.set('filter[date_range]'", false)
            ->assertSee("params.set('filter[divisi_id]'", false)
            ->assertSee("params.set('filter[search]'", false)
            ->assertSee("params.set('sort'", false)
            ->assertSee('loadRadiologi();', false);

        foreach (['date_mcu', 'created_at', 'code', 'name', 'gender', 'cor', 'diafragma_sinus', 'pulmo', 'kesan', 'diperiksa'] as $column) {
            $this->assertStringContainsString(
                'data-sort="' . $column . '"',
                $html,
                "Judul kolom {$column} tidak bisa disortir."
            );
        }
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
