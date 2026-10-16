<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use App\Models\ValidateDoctor;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ValidateDoctorListingTest extends TestCase
{
    private const CLIENT_ID = 1;

    private const CONTRACT_ID = 1;

    private const OTHER_CONTRACT = 4;

    private int $sequence = 0;

    private function makeParticipant(array $attributes = []): Participant
    {
        $this->sequence++;

        return Participant::create(array_merge([
            'code' => 'TMPLIST' . $this->sequence . random_int(1000, 9999),
            'nik' => '7777' . $this->sequence . random_int(1000, 9999),
            'name' => 'PESERTA DAFTAR VALIDASI ' . $this->sequence,
            'gender' => 'L',
            'birthday' => '1990-01-01',
            'client_id' => self::CLIENT_ID,
            'contract_id' => self::CONTRACT_ID,
            'divisi_id' => 1,
            'department_id' => 1,
            'register_date' => '2020-01-01 08:00:00',
            'no_form' => 9700 + $this->sequence,
        ], $attributes));
    }

    private function makeValidation(Participant $participant, array $attributes = []): ValidateDoctor
    {
        return ValidateDoctor::create(array_merge([
            'client_id' => $participant->client_id,
            'contract_id' => $participant->contract_id,
            'no_mcu' => $participant->code,
            'date_mcu' => '2020-02-02 08:00:00',
            'name' => $participant->name,
            'gender' => $participant->gender,
            'result_mcu' => 'Kesimpulan ' . $participant->code,
            'doctor' => 'dr. Uji',
            'notes' => 'Catatan ' . $participant->code,
        ], $attributes));
    }

    private function rawListing(array $filters = [], ?string $sort = null, array $session = []): TestResponse
    {
        $query = [];
        $query['client_id'] = self::CLIENT_ID;

        if ($filters) {
            $query['filter'] = $filters;
        }

        if ($sort) {
            $query['sort'] = $sort;
        }

        return $this->actingAs(User::find(1))
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->withSession(array_merge([
                'client_id' => self::CLIENT_ID,
                'contract_id' => self::CONTRACT_ID,
            ], $session))
            ->get('/get-participant-upload-validasi-dokter?' . http_build_query($query));
    }

    /**
     * @return array<int, mixed>
     */
    private function listing(array $filters = [], ?string $sort = null, array $session = []): array
    {
        $response = $this->rawListing($filters, $sort, $session);

        $response->assertOk();

        return $response->json('data');
    }

    public function test_listing_only_shows_rows_of_the_active_contract_and_client(): void
    {
        DB::beginTransaction();

        try {
            $participant = $this->makeParticipant();
            $validation = $this->makeValidation($participant);

            $otherContractParticipant = $this->makeParticipant(['contract_id' => self::OTHER_CONTRACT]);
            $otherContract = $this->makeValidation($otherContractParticipant);

            $otherClient = $this->makeValidation($participant, [
                'client_id' => 999999,
                'no_mcu' => $participant->code . 'X',
            ]);

            $rows = $this->listing();
            $ids = array_column($rows, 'id');

            $this->assertContains($validation->id, $ids);
            $this->assertNotContains($otherContract->id, $ids, 'Baris kontrak lain ikut tampil.');
            $this->assertNotContains($otherClient->id, $ids, 'Baris client lain ikut tampil.');
        } finally {
            DB::rollBack();
        }
    }

    public function test_listing_filters_by_mcu_date(): void
    {
        DB::beginTransaction();

        try {
            $inside = $this->makeValidation($this->makeParticipant(), ['date_mcu' => '2020-02-02 08:00:00']);
            $outside = $this->makeValidation($this->makeParticipant(), ['date_mcu' => '2019-01-01 08:00:00']);

            $rows = $this->listing(['date_range' => '2020-02-02 to 2020-02-02']);
            $ids = array_column($rows, 'id');

            $this->assertContains($inside->id, $ids);
            $this->assertNotContains($outside->id, $ids, 'Filter tanggal MCU tidak menyaring.');
        } finally {
            DB::rollBack();
        }
    }

    public function test_listing_filters_by_divisi_and_search(): void
    {
        DB::beginTransaction();

        try {
            $matching = $this->makeValidation($this->makeParticipant(['divisi_id' => 2]));
            $otherDivisi = $this->makeValidation($this->makeParticipant(['divisi_id' => 3]));

            $byDivisi = $this->listing(['divisi_id' => 2]);
            $ids = array_column($byDivisi, 'id');

            $this->assertContains($matching->id, $ids);
            $this->assertNotContains($otherDivisi->id, $ids, 'Filter divisi tidak menyaring.');

            $byCode = $this->listing(['search' => $matching->no_mcu]);
            $this->assertSame([$matching->id], array_column($byCode, 'id'));

            $byName = $this->listing(['search' => mb_substr($matching->name, 0, 12)]);
            $this->assertContains($matching->id, array_column($byName, 'id'));
        } finally {
            DB::rollBack();
        }
    }

    public function test_listing_sorts_by_name_and_date(): void
    {
        DB::beginTransaction();

        try {
            $this->makeValidation($this->makeParticipant(), ['name' => 'AAA PESERTA', 'date_mcu' => '2020-01-01 08:00:00']);
            $this->makeValidation($this->makeParticipant(), ['name' => 'ZZZ PESERTA', 'date_mcu' => '2020-03-03 08:00:00']);

            $ascending = array_column($this->listing(['search' => 'PESERTA'], 'name'), 'name');
            $descending = array_column($this->listing(['search' => 'PESERTA'], '-name'), 'name');

            $this->assertSame(['AAA PESERTA', 'ZZZ PESERTA'], $ascending);
            $this->assertSame(['ZZZ PESERTA', 'AAA PESERTA'], $descending);

            $dates = array_column($this->listing(['search' => 'PESERTA'], 'date_mcu'), 'date_mcu');
            $this->assertSame(['2020-01-01 08:00:00', '2020-03-03 08:00:00'], $dates);
        } finally {
            DB::rollBack();
        }
    }

    public function test_listing_rejects_unknown_filter_and_sort(): void
    {
        $this->rawListing(['tidak_ada' => 'x'])->assertStatus(400);
        $this->rawListing([], 'tanggal_mcu')->assertStatus(400);
    }

    public function test_page_renders_filters_table_and_import_controls(): void
    {
        $response = $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => self::CONTRACT_ID])
            ->get('/participant-upload-validasi-dokter');

        $response->assertOk();

        $html = $response->getContent();

        preg_match('/<thead>(.*?)<\/thead>/s', $html, $head);
        $this->assertNotEmpty($head, 'Tabel tidak punya thead.');
        $this->assertSame(9, substr_count($head[1], '<th'), 'Jumlah judul kolom berubah.');
        // Satu untuk baris "Memuat data...", satu lagi untuk empty state di script.
        $this->assertSame(2, substr_count($html, 'colspan="9"'), 'Baris kosong tabel tidak melebar sesuai jumlah kolom.');

        $response
            ->assertSee('Upload Validasi Dokter', false)
            ->assertSee('Memuat data...', false)
            ->assertSee('id="search"', false)
            ->assertSee('name="filter[search]"', false)
            ->assertSee('name="filter[divisi_id]"', false)
            ->assertSee('id="fp-range"', false)
            ->assertSee('id="cari"', false)
            ->assertSee('id="cari_text"', false)
            ->assertSee('id="reset"', false)
            ->assertSee('id="fileExcel"', false)
            ->assertSee('id="upload_text"', false)
            ->assertSee('id="upload_icon"', false)
            // Markup lama yang tidak berfungsi tidak boleh tertinggal.
            ->assertDontSee('id="progress"', false)
            ->assertDontSee('Hasil MCUç', false)
            ->assertDontSee('border="1"', false)
            ->assertDontSee("$('#cari').attr('disabled'", false)
            ->assertDontSee("$('#upload_btn').attr('disabled'", false)
            ->assertSee("$('#cari_text').text(", false)
            ->assertSee("$('#upload_text').text(", false)
            ->assertSee('loadValidateDoctor();', false)
            // Nama parameter harus sama dengan yang diizinkan endpoint.
            ->assertSee("params.set('filter[date_range]'", false)
            ->assertSee("params.set('filter[divisi_id]'", false)
            ->assertSee("params.set('filter[search]'", false)
            ->assertSee("params.set('sort'", false);

        foreach (['created_at', 'date_mcu', 'no_mcu', 'name', 'gender', 'result_mcu', 'doctor'] as $column) {
            $this->assertStringContainsString(
                'data-sort="' . $column . '"',
                $html,
                "Judul kolom {$column} tidak bisa disortir."
            );
        }

        $this->assertStringContainsString(route('upload.validasi.dokter.template'), $html);
        $this->assertStringContainsString(route('get.validasi.dokter'), $html);

        // Nilai yang ditampilkan harus nama kolom yang benar.
        foreach (['item.no_mcu', 'item.result_mcu', 'item.notes', 'item.doctor'] as $field) {
            $this->assertStringContainsString($field, $html, "Kolom {$field} tidak dipakai pada tabel.");
        }
    }

    public function test_page_shows_a_placeholder_when_no_contract_is_active(): void
    {
        $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => null])
            ->get('/participant-upload-validasi-dokter')
            ->assertOk()
            ->assertSee('Belum ada kontrak aktif');
    }
}
