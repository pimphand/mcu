<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Laboratorium;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LaboratoriumListingTest extends TestCase
{
    private const CLIENT_ID = 1;

    // Kontrak ini sudah memiliki data laboratorium sehingga bisa dipakai
    // memastikan daftar memang mengikuti kontrak sesi MCU IN.
    private const SESSION_CONTRACT = 1;

    /**
     * Panggil endpoint daftar laboratorium tanpa asumsi status, dipakai untuk
     * kasus yang memang harus ditolak.
     *
     * @param  array<string, mixed>  $filters  nilai filter[...] yang dikirim frontend
     * @param  array<string, mixed>  $session
     * @param  array<string, mixed>  $extra  parameter query mentah di luar filter (mis. contract_id)
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

        $url = '/get-participant-upload-laboratorium?' . http_build_query($query);

        return $this->actingAs(User::find(1))
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->withSession(array_merge([
                'client_id' => self::CLIENT_ID,
                'contract_id' => self::SESSION_CONTRACT,
            ], $session))
            ->get($url);
    }

    /**
     * @param  array<string, mixed>  $filters  nilai filter[...] yang dikirim frontend
     * @param  array<string, mixed>  $session
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
        return Laboratorium::query()
            ->when($contractId, fn ($query, $id) => $query->where('contract_id', $id))
            ->whereHas('participant', function ($query) use ($filters) {
                $query->where('client_id', self::CLIENT_ID)
                    ->when($filters['divisi_id'] ?? null, fn ($sub, $id) => $sub->where('divisi_id', $id))
                    ->when($filters['date_range'] ?? null, fn ($sub, $range) => $sub->DateRange($range))
                    ->when($filters['search'] ?? null, function ($sub, $term) {
                        $sub->where(function ($inner) use ($term) {
                            $inner->where('name', 'like', "%{$term}%")
                                ->orWhere('code', 'like', "%{$term}%");
                        });
                    });
            })
            ->count();
    }

    /**
     * Satu baris laboratorium (beserta pesertanya) pada kontrak sesi, dipakai
     * sebagai sampel uji filter.
     */
    private function sampleRow(): Laboratorium
    {
        $row = Laboratorium::query()
            ->where('contract_id', self::SESSION_CONTRACT)
            ->whereHas('participant', fn ($query) => $query->where('client_id', self::CLIENT_ID))
            ->with('participant')
            ->first();

        $this->assertNotNull($row, 'Tidak ada data laboratorium pada kontrak sesi untuk diuji.');

        return $row;
    }

    public function test_listing_only_shows_rows_of_the_active_contract(): void
    {
        $rows = $this->listing();

        $this->assertGreaterThan(0, count($rows), 'Tidak ada data laboratorium pada kontrak sesi untuk diuji.');
        $this->assertCount($this->expectedRows(), $rows);

        foreach ($rows as $row) {
            $this->assertSame(self::SESSION_CONTRACT, (int) $row['contract_id']);
        }
    }

    public function test_listing_filters_by_divisi(): void
    {
        $filters = ['divisi_id' => 3]; // KANTIN TKI - punya data laboratorium pada kontrak 1

        $expected = $this->expectedRows($filters);
        $withoutDivisi = $this->expectedRows();

        $this->assertGreaterThan(0, $expected, 'Divisi uji tidak punya data laboratorium.');
        $this->assertLessThan($withoutDivisi, $expected, 'Filter divisi tidak mempersempit hasil.');

        $rows = $this->listing($filters);

        $this->assertCount($expected, $rows);

        foreach ($rows as $row) {
            $this->assertSame(3, (int) $row['participant']['divisi_id']);
        }
    }

    public function test_listing_returns_nothing_for_a_divisi_without_data(): void
    {
        $filters = ['divisi_id' => 5]; // ACCOUNTING - terdaftar pada client ini, tanpa data laboratorium

        $this->assertSame(0, $this->expectedRows($filters));
        $this->assertCount(0, $this->listing($filters));
    }

    public function test_listing_filters_by_participant_name(): void
    {
        $sample = $this->sampleRow();
        $term = mb_substr($sample->participant->name, 0, 5);

        $filters = ['search' => $term];
        $expected = $this->expectedRows($filters);

        $this->assertGreaterThan(0, $expected);

        $rows = $this->listing($filters);

        $this->assertCount($expected, $rows);
        $this->assertContains(
            $sample->participant_id,
            array_column(array_column($rows, 'participant'), 'id'),
            'Peserta yang dicari tidak muncul pada hasil filter.'
        );

        foreach ($rows as $row) {
            $this->assertStringContainsStringIgnoringCase($term, $row['participant']['name']);
        }
    }

    public function test_listing_filters_by_participant_code(): void
    {
        $sample = $this->sampleRow();

        $rows = $this->listing(['search' => $sample->participant->code]);

        $this->assertNotEmpty($rows);

        foreach ($rows as $row) {
            $this->assertSame($sample->participant->code, $row['participant']['code']);
        }
    }

    public function test_listing_filters_by_participant_register_date(): void
    {
        $sample = $this->sampleRow();
        $date = Carbon::parse($sample->participant->register_date)->format('Y-m-d');
        $filters = ['date_range' => "{$date} to {$date}"];

        $expected = $this->expectedRows($filters);

        $this->assertGreaterThan(0, $expected, 'Tanggal registrasi sampel tidak punya data laboratorium.');
        $this->assertCount($expected, $this->listing($filters));

        // Rentang di luar data apa pun harus kosong, bukan mengembalikan semua baris.
        $this->assertCount(0, $this->listing(['date_range' => '2000-01-01 to 2000-01-02']));
    }

    public function test_listing_sorts_by_participant_name_in_both_directions(): void
    {
        $ascending = $this->listing([], [], 'name');
        $descending = $this->listing([], [], '-name');

        $this->assertGreaterThan(1, count($ascending));

        $this->assertSortedBy(
            array_map(fn ($row) => $row['participant']['name'], $ascending),
            'asc',
            'name'
        );

        $this->assertSortedBy(
            array_map(fn ($row) => $row['participant']['name'], $descending),
            'desc',
            'name'
        );

        $this->assertNotSame(
            array_column($ascending, 'id'),
            array_column($descending, 'id'),
            'Arah sortir tidak berpengaruh pada urutan.'
        );
    }

    public function test_listing_sorts_by_participant_code_and_lab_column(): void
    {
        $this->assertSortedBy(
            array_map(fn ($row) => $row['participant']['code'], $this->listing([], [], 'code')),
            'asc',
            'code'
        );

        $rows = $this->listing([], [], 'hemoglobin');
        $this->assertSortedBy(array_map(fn ($row) => $row['hemoglobin'], $rows), 'asc', 'hemoglobin');

        // Kolom hasil tambahan yang ikut disortir di tabel.
        $this->assertSortedBy(
            array_map(fn ($row) => $row['batang'], $this->listing([], [], 'batang')),
            'asc',
            'batang'
        );
        $this->assertSortedBy(
            array_map(fn ($row) => $row['kesimpulan'], $this->listing([], [], 'kesimpulan')),
            'asc',
            'kesimpulan'
        );

        // Kolom yang dipakai tabel (batang/segmen/kesimpulan) harus ikut terkirim,
        // kalau tidak kolom sortir NBatang/NSegmen/kesimpulan_lab selalu "-".
        $this->assertArrayHasKey('batang', $rows[0]);
        $this->assertArrayHasKey('segmen', $rows[0]);
        $this->assertArrayHasKey('kesimpulan', $rows[0]);
    }

    public function test_listing_combines_filter_and_sort(): void
    {
        $sample = $this->sampleRow();
        $term = mb_substr($sample->participant->name, 0, 5);
        $filters = ['search' => $term];

        $rows = $this->listing($filters, [], '-name');

        $this->assertCount($this->expectedRows($filters), $rows);

        $this->assertSortedBy(
            array_map(fn ($row) => $row['participant']['name'], $rows),
            'desc',
            'name'
        );
    }

    public function test_listing_rejects_unknown_sort_and_filter(): void
    {
        $this->rawListing([], [], 'nama_peserta')->assertStatus(400);
        $this->rawListing(['tidak_ada' => 'x'])->assertStatus(400);
    }

    public function test_listing_uses_the_contract_from_the_mcu_in_session(): void
    {
        DB::beginTransaction();

        try {
            // Dua baris pada kontrak lain (peserta kontrak 4).
            $participantIds = Participant::where('contract_id', 4)
                ->where('client_id', self::CLIENT_ID)
                ->limit(2)
                ->pluck('id');

            foreach ($participantIds as $participantId) {
                Laboratorium::create([
                    'participant_id' => $participantId,
                    'contract_id' => 4,
                    'client_id' => self::CLIENT_ID,
                    'hemoglobin' => '9.9',
                ]);
            }

            $otherContract = $this->listing([], ['contract_id' => 4]);

            $this->assertGreaterThanOrEqual(count($participantIds), count($otherContract));
            $this->assertCount($this->expectedRows([], 4), $otherContract);

            foreach ($otherContract as $row) {
                $this->assertSame(4, (int) $row['contract_id']);
            }

            // Baris yang baru dibuat pada kontrak 4 memang ikut tampil.
            $otherContractParticipants = array_column($otherContract, 'participant_id');

            foreach ($participantIds as $participantId) {
                $this->assertContains($participantId, $otherContractParticipants);
            }

            // Sesi kontrak 1 tetap tidak terpengaruh data kontrak 4.
            $this->assertCount($this->expectedRows(), $this->listing());
        } finally {
            DB::rollBack();
        }
    }

    public function test_listing_ignores_contract_id_sent_from_the_client(): void
    {
        // Kontrak tetap diambil dari sesi MCU IN, bukan dari query string.
        $rows = $this->listing([], [], null, ['contract_id' => 4]);

        $this->assertCount($this->expectedRows(), $rows);

        foreach ($rows as $row) {
            $this->assertSame(self::SESSION_CONTRACT, (int) $row['contract_id']);
        }
    }

    public function test_page_shows_filters_and_sortable_headers(): void
    {
        $response = $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => self::SESSION_CONTRACT])
            ->get('/participant-upload-laboratorium');

        $response->assertOk();

        $html = $response->getContent();

        // Setiap kolom pada judul tabel harus punya pasangan sel di baris data,
        // dan urutannya harus sama, kalau tidak nilai hasil lab akan bergeser.
        preg_match('/<thead>(.*?)<\/thead>/s', $html, $head);
        $this->assertNotEmpty($head, 'Tabel tidak punya thead.');

        preg_match_all('/data-sort="([^"]+)"/', $head[1], $sortable);
        $sortableColumns = $sortable[1];

        preg_match('/let kolomLaboratorium = \[(.*?)\];/s', $html, $arrayMatch);
        $this->assertNotEmpty($arrayMatch, 'Daftar kolom hasil lab tidak ditemukan pada script halaman.');

        $columns = array_values(array_filter(array_map(
            fn ($value) => trim($value, " \n\r\t'"),
            explode(',', $arrayMatch[1])
        ), fn ($value) => $value !== ''));

        $withoutColumnInTable = ['user_lab', 'lab_date', 'pemeriksa_lab'];

        $this->assertSame(3 + count($columns), substr_count($head[1], '<th'), 'Jumlah judul kolom tidak sama dengan jumlah sel baris data.');
        $this->assertSame(40, substr_count($head[1], '<th'));
        $this->assertGreaterThanOrEqual(1, substr_count($html, 'colspan="40"'), 'Baris kosong tabel tidak melebar sesuai jumlah kolom.');
        $this->assertSame(
            array_merge(['code', 'name'], array_values(array_diff($columns, $withoutColumnInTable))),
            $sortableColumns,
            'Judul kolom yang bisa disortir tidak sinkron dengan sel baris data.'
        );
        $this->assertSame(
            $withoutColumnInTable,
            array_values(array_diff($columns, $sortableColumns)),
            'Ada kolom yang tidak punya judul kolom yang cocok.'
        );

        $response
            ->assertSee('Upload Laboratorium', false)
            ->assertSee(Contract::whereKey(self::SESSION_CONTRACT)->value('name'))
            ->assertSee('Memuat data...', false)
            ->assertDontSee('filter[contract_id]', false)
            ->assertDontSee("contract_id: $('#contract_id').val()", false)
            ->assertSee('id="search"', false)
            ->assertSee('name="filter[search]"', false)
            // Tombol aksi: label dan ikon di elemennya sendiri supaya status
            // "Diproses..." tidak menghapus isi tombol, dan reset selalu tersedia.
            ->assertSee('id="cari"', false)
            ->assertSee('id="cari_text"', false)
            ->assertSee('id="reset"', false)
            ->assertSee("$('#cari_text').text(", false)
            ->assertSee("$('#cari').prop('disabled'", false)
            ->assertDontSee("$('#cari').attr('disabled'", false)
            // Tombol Upload: teks dan ikon dipisah, status mengunggah dipasang
            // lewat prop() supaya ikonnya tidak ikut terhapus.
            ->assertSee('id="fileExcel"', false)
            ->assertSee('id="upload_btn"', false)
            ->assertSee('id="upload_text"', false)
            ->assertSee('id="upload_icon"', false)
            ->assertSee("$('#upload_text').text(", false)
            ->assertDontSee("$('#upload_btn').attr('disabled'", false)
            // Markup lama yang tidak lagi dipakai tidak boleh tertinggal.
            ->assertDontSee('id="progress"', false)
            ->assertDontSee('id="contract_name"', false)
            ->assertSee('data-sort="name"', false)
            ->assertSee('data-sort="hemoglobin"', false)
            ->assertSee('data-sort="kesimpulan"', false)
            ->assertSee('data-sort="reduksi"', false)
            // Nama parameter yang dikirim frontend harus sama dengan yang
            // diizinkan endpoint, kalau tidak filter akan ditolak/\ diabaikan.
            ->assertSee("params.set('filter[date_range]'", false)
            ->assertSee("params.set('filter[divisi_id]'", false)
            ->assertSee("params.set('filter[search]'", false)
            ->assertSee("params.set('sort'", false)
            ->assertSee('loadLaboratorium();', false);
    }

    public function test_page_shows_a_placeholder_when_no_contract_is_active(): void
    {
        $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => null])
            ->get('/participant-upload-laboratorium')
            ->assertOk()
            ->assertSee('Belum ada kontrak aktif');
    }

    /**
     * Pastikan nilai benar-benar terurut pada arah yang diminta. Nilai kosong
     * (kolom nullable) dilewati karena posisinya ditentukan collation database.
     *
     * @param  array<int, mixed>  $values
     */
    private function assertSortedBy(array $values, string $direction, string $label): void
    {
        $values = array_values(array_filter($values, fn ($value) => $value !== null && $value !== ''));

        $this->assertGreaterThan(1, count($values), "Data kolom {$label} tidak cukup untuk diuji.");

        for ($i = 1; $i < count($values); $i++) {
            $comparison = strcasecmp((string) $values[$i - 1], (string) $values[$i]);

            $message = sprintf(
                'Kolom %s tidak terurut %s: "%s" muncul sebelum "%s".',
                $label,
                $direction,
                $values[$i - 1],
                $values[$i]
            );

            $direction === 'asc'
                ? $this->assertLessThanOrEqual(0, $comparison, $message)
                : $this->assertGreaterThanOrEqual(0, $comparison, $message);
        }
    }
}
