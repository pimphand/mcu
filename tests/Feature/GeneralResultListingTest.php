<?php

namespace Tests\Feature;

use App\Models\GeneralResult;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GeneralResultListingTest extends TestCase
{
    private const CLIENT_ID = 1;

    // Kontrak ini dipakai sebagai kontrak sesi MCU IN pada test.
    private const SESSION_CONTRACT = 1;

    /**
     * Panggil endpoint daftar hasil umum tanpa asumsi status, dipakai untuk
     * kasus yang memang harus ditolak.
     *
     * @param  array<string, mixed>  $filters  nilai filter[...] yang dikirim frontend
     * @param  array<string, mixed>  $session
     * @param  array<string, mixed>  $extra  parameter query mentah di luar filter
     */
    private function rawListing(array $filters = [], array $session = [], ?string $sort = null, array $extra = []): TestResponse
    {
        $query = array_merge(['client_id' => self::CLIENT_ID], $extra);

        if ($filters) {
            $query['filter'] = $filters;
        }

        if ($sort) {
            $query['sort'] = $sort;
        }

        $url = '/participant-upload-umum?' . http_build_query($query);

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
     * Buat satu baris hasil umum di dalam transaksi test.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function seedRow(int $contractId = self::SESSION_CONTRACT, int $clientId = self::CLIENT_ID, array $attributes = [], ?int $divisiId = null): GeneralResult
    {
        $participant = Participant::query()
            ->where('client_id', self::CLIENT_ID)
            ->where('contract_id', $contractId)
            ->when($divisiId, fn ($query, $id) => $query->where('divisi_id', $id))
            ->orderBy('id')
            ->first();

        $this->assertNotNull($participant, "Tidak ada peserta untuk kontrak {$contractId}.");

        return GeneralResult::create(array_merge([
            'client_id' => $clientId,
            'contract_id' => $contractId,
            'participant_id' => $participant->id,
            'mcu_id' => $participant->code,
            'name' => $participant->name,
            'result' => 'Normal',
            'mcu_date' => '2026-04-10 08:00:00',
        ], $attributes));
    }

    public function test_listing_only_returns_rows_of_the_active_client_and_contract(): void
    {
        DB::beginTransaction();

        try {
            $first = $this->seedRow(1, 1, ['mcu_id' => 'MCU00001', 'name' => 'PESERTA SATU']);
            $second = $this->seedRow(1, 1, ['mcu_id' => 'MCU00002', 'name' => 'PESERTA DUA']);
            // Kontrak lain pada client yang sama.
            $this->seedRow(4, 1, ['mcu_id' => 'MCU00003', 'name' => 'PESERTA KONTRAK LAIN']);
            // Client lain pada kontrak yang sama.
            $this->seedRow(1, 999, ['mcu_id' => 'MCU00004', 'name' => 'PESERTA CLIENT LAIN']);

            $rows = $this->listing();

            $this->assertCount(2, $rows);
            $this->assertSame(
                [$second->id, $first->id],
                array_column($rows, 'id'),
                'Urutan default harus baris terbaru dulu.'
            );

            foreach ($rows as $row) {
                $this->assertSame(1, (int) $row['contract_id']);
                $this->assertSame(self::CLIENT_ID, (int) $row['client_id']);
            }

            // Kontrak sesi lain hanya melihat barisnya sendiri.
            $this->assertCount(1, $this->listing([], ['contract_id' => 4]));
        } finally {
            DB::rollBack();
        }
    }

    public function test_listing_filters_by_date_range_divisi_and_search(): void
    {
        DB::beginTransaction();

        try {
            $april = $this->seedRow(1, 1, ['mcu_id' => 'MCU00011', 'name' => 'NURJANAH UJI', 'mcu_date' => '2026-04-10 08:00:00']);
            $mei = $this->seedRow(1, 1, ['mcu_id' => 'MCU00012', 'name' => 'PESERTA MEI', 'mcu_date' => '2026-05-10 08:00:00']);

            // Filter tanggal memakai kolom mcu_date milik general_results.
            $aprilRows = $this->listing(['date_range' => '2026-04-01 to 2026-04-30']);
            $this->assertSame([$april->id], array_column($aprilRows, 'id'));
            $this->assertSame([$mei->id], array_column($this->listing(['date_range' => '2026-05-01 to 2026-05-31']), 'id'));
            $this->assertCount(0, $this->listing(['date_range' => '2000-01-01 to 2000-01-02']));

            // Filter divisi lewat peserta: satu baris di divisi 2, satu di divisi 3.
            $divisiDua = $this->seedRow(1, 1, ['mcu_id' => 'MCU00021', 'name' => 'PESERTA DIVISI 2'], 2);
            $divisiTiga = $this->seedRow(1, 1, ['mcu_id' => 'MCU00031', 'name' => 'PESERTA DIVISI 3'], 3);

            $divisiIds = array_column($this->listing(['divisi_id' => 3]), 'id');

            $this->assertContains($divisiTiga->id, $divisiIds);
            $this->assertNotContains($divisiDua->id, $divisiIds, 'Baris peserta di divisi lain ikut terbawa.');
            $this->assertNotContains($april->id, $divisiIds);
            $this->assertNotContains($mei->id, $divisiIds);
            $this->assertCount(0, $this->listing(['divisi_id' => 9999]));

            // Pencarian nama dan kode MCU.
            $this->assertSame([$april->id], array_column($this->listing(['search' => 'NURJANAH UJI']), 'id'));
            $this->assertSame([$mei->id], array_column($this->listing(['search' => 'MCU00012']), 'id'));
            $this->assertCount(0, $this->listing(['search' => 'TIDAK ADA NAMA INI']));
        } finally {
            DB::rollBack();
        }
    }

    public function test_listing_sorts_by_mcu_id_and_created_at(): void
    {
        DB::beginTransaction();

        try {
            $this->seedRow(1, 1, ['mcu_id' => 'MCU00022', 'name' => 'PESERTA B', 'created_at' => '2026-04-11 08:00:00']);
            $this->seedRow(1, 1, ['mcu_id' => 'MCU00013', 'name' => 'PESERTA A', 'created_at' => '2026-04-10 08:00:00']);
            $this->seedRow(1, 1, ['mcu_id' => 'MCU00031', 'name' => 'PESERTA C', 'created_at' => '2026-04-12 08:00:00']);

            $mcuIds = array_column($this->listing([], [], 'mcu_id'), 'mcu_id');
            $this->assertSame(['MCU00013', 'MCU00022', 'MCU00031'], $mcuIds);

            $this->assertSame(
                ['MCU00031', 'MCU00022', 'MCU00013'],
                array_column($this->listing([], [], '-mcu_id'), 'mcu_id')
            );

            // Tanggal upload terbaru di atas.
            $names = array_column($this->listing([], [], '-created_at'), 'name');
            $this->assertSame(['PESERTA C', 'PESERTA B', 'PESERTA A'], $names);

            $this->assertSame(
                ['PESERTA A', 'PESERTA B', 'PESERTA C'],
                array_column($this->listing([], [], 'created_at'), 'name')
            );
        } finally {
            DB::rollBack();
        }
    }

    public function test_page_header_matches_the_rendered_columns(): void
    {
        $response = $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => self::SESSION_CONTRACT])
            ->get('/participant-upload-umum');

        $response->assertOk();

        $html = $response->getContent();

        preg_match('/<thead>(.*?)<\/thead>/s', $html, $head);

        $this->assertNotEmpty($head, 'Tabel tidak punya thead.');
        $this->assertSame(6, substr_count($head[1], '<th'), 'Jumlah judul kolom berubah.');

        // Setiap baris data harus punya jumlah kolom yang sama dengan judulnya.
        $this->assertSame(
            6,
            substr_count($html, '<td class="border-bottom border-top'),
            'Jumlah kolom pada baris data tidak sama dengan judul kolom.'
        );

        $response
            ->assertSee('Upload Hasil Umum', false)
            ->assertSee('HASIL UMUM / DIAGNOSA AKHIR', false)
            ->assertSee('Memuat data...', false)
            ->assertSee('colspan="6"', false)
            ->assertSee('data-sort="mcu_date"', false)
            ->assertSee('data-sort="created_at"', false)
            ->assertSee('data-sort="mcu_id"', false)
            ->assertSee('data-sort="name"', false)
            ->assertSee('id="search"', false)
            ->assertSee('name="filter[search]"', false)
            ->assertSee('id="cari_text"', false)
            ->assertSee('id="reset"', false)
            ->assertSee("$('#cari_text').text(", false)
            ->assertSee("$('#upload_text').text(", false)
            // Tombol tidak boleh memakai .text() langsung, itu menghapus ikonnya.
            ->assertDontSee("$('#cari').attr('disabled'", false)
            ->assertDontSee("$('#upload_btn').attr('disabled'", false)
            // Halaman ini tidak lagi memakai kolom radiologi.
            ->assertDontSee('item.diafragma_sinus', false)
            ->assertDontSee('item.pulmo', false);

        $this->assertStringContainsString(\App\Models\Contract::whereKey(self::SESSION_CONTRACT)->value('name'), $html);
        $this->assertStringContainsString(route('upload.generalResult.template'), $html);
    }

    public function test_page_shows_a_placeholder_when_no_contract_is_active(): void
    {
        $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => null])
            ->get('/participant-upload-umum')
            ->assertOk()
            ->assertSee('Belum ada kontrak aktif');
    }

    public function test_listing_rejects_unknown_filter_and_sort(): void
    {
        $this->rawListing([], [], 'nama_peserta')->assertStatus(400);
        $this->rawListing(['tidak_ada' => 'x'])->assertStatus(400);
    }
}
