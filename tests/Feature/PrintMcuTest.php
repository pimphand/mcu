<?php

namespace Tests\Feature;

use App\Models\Audiometri;
use App\Models\Client;
use App\Models\Laboratorium;
use App\Models\Participant;
use App\Models\Radiologi;
use App\Models\User;
use App\Models\ValidateDoctor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PrintMcuTest extends TestCase
{
    private const CLIENT_ID = 1;

    // Kontrak yang sedang aktif di sesi MCU IN.
    private const CONTRACT_ID = 1;

    // Kontrak lain milik client yang sama.
    private const OTHER_CONTRACT = 4;

    // Divisi dan department milik client di atas (dipakai agar FK terpenuhi).
    private const DIVISI_ID = 1;

    private const DEPARTMENT_ID = 1;

    // Tanggal register khusus data uji, dipakai agar filter rentang tanggal
    // hanya mengenai peserta buatan test ini.
    private const FIXTURE_DATE = '2020-01-01 08:00:00';

    private const FIXTURE_RANGE = '2020-01-01 to 2020-01-01';

    private int $sequence = 0;

    /** @var array<string, mixed> */
    private array $capturedPdf = [];

    /**
     * Rentang tanggal default halaman print MCU: tanggal 1 bulan berjalan
     * sampai hari ini.
     */
    private function defaultRange(): string
    {
        return now()->startOfMonth()->format('Y-m-d') . ' to ' . now()->format('Y-m-d');
    }

    private function makeParticipant(array $attributes = []): Participant
    {
        $this->sequence++;

        return Participant::create(array_merge([
            'code' => 'TMPPRINT' . $this->sequence . uniqid(),
            'nik' => '9999' . $this->sequence . random_int(1000, 9999),
            'name' => 'PESERTA UJI PRINT ' . $this->sequence,
            'gender' => 'L',
            'birthday' => '1990-01-01',
            'client_id' => self::CLIENT_ID,
            'contract_id' => self::CONTRACT_ID,
            'divisi_id' => self::DIVISI_ID,
            'department_id' => self::DEPARTMENT_ID,
            'register_date' => self::FIXTURE_DATE,
            'no_form' => 9000 + $this->sequence,
        ], $attributes));
    }

    private function page(array $filters = [], ?int $contractId = self::CONTRACT_ID, bool $ajax = false): TestResponse
    {
        $query = $filters ? '?' . http_build_query(['filter' => $filters]) : '';

        return $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => $contractId])
            ->get('/participant-print-mcu' . $query);
    }

    /**
     * Jumlah baris data yang benar-benar tampil di tabel.
     */
    private function renderedRows(string $html): int
    {
        return substr_count($html, 'border-bottom border-top text-right');
    }

    /**
     * Potongan HTML satu baris tabel untuk kode peserta tertentu.
     */
    private function rowFor(string $html, string $code): string
    {
        foreach (preg_split('/<tr>/', $html) as $row) {
            if (str_contains($row, $code)) {
                return $row;
            }
        }

        return '';
    }

    public function test_page_lists_the_active_contract_for_the_default_range(): void
    {
        DB::beginTransaction();

        try {
            // Tanggal 1 bulan berjalan selalu masuk rentang default; sehari
            // sebelum tanggal 1 pasti di luarnya (aman untuk tanggal berapa pun).
            $inside = $this->makeParticipant(['register_date' => now()->startOfMonth()->toDateTimeString()]);
            $outside = $this->makeParticipant(['register_date' => now()->startOfMonth()->subDay()->toDateTimeString()]);
            $otherContract = $this->makeParticipant([
                'contract_id' => self::OTHER_CONTRACT,
                'register_date' => now()->startOfMonth()->toDateTimeString(),
            ]);

            $response = $this->page();

            $response->assertOk();

            $html = $response->getContent();
            $expected = Participant::where('client_id', self::CLIENT_ID)
                ->where('contract_id', self::CONTRACT_ID)
                ->DateRange($this->defaultRange())
                ->count();

            $this->assertGreaterThan(0, $expected, 'Tidak ada peserta pada kontrak sesi untuk rentang default.');
            $this->assertSame($expected, $this->renderedRows($html), 'Jumlah baris tidak sesuai kontrak/rentang default.');

            $this->assertStringContainsString($inside->code, $html);
            $this->assertStringNotContainsString($outside->code, $html, 'Peserta di luar rentang tanggal default ikut tampil.');
            $this->assertStringNotContainsString($otherContract->code, $html, 'Peserta kontrak lain ikut tampil.');

            // Input tanggal harus menampilkan rentang yang benar-benar dipakai.
            $this->assertStringContainsString(
                'value="' . $this->defaultRange() . '"',
                $html,
                'Input tanggal tidak menampilkan rentang yang sedang dipakai.'
            );
        } finally {
            DB::rollBack();
        }
    }

    public function test_page_never_shows_participants_of_other_clients(): void
    {
        DB::beginTransaction();

        try {
            $otherClient = Client::create(['code' => 'TMPPRINTCLIENT', 'name' => 'Client Uji Print']);
            $stranger = $this->makeParticipant([
                'client_id' => $otherClient->id,
                'register_date' => now()->startOfMonth()->toDateTimeString(),
            ]);

            $response = $this->page();

            $response->assertOk();
            $response->assertDontSee($stranger->code);

            $html = $response->getContent();
            $expected = Participant::where('client_id', self::CLIENT_ID)
                ->where('contract_id', self::CONTRACT_ID)
                ->DateRange($this->defaultRange())
                ->count();

            $this->assertSame($expected, $this->renderedRows($html));
        } finally {
            DB::rollBack();
        }
    }

    public function test_page_filters_by_divisi_and_keeps_the_selection(): void
    {
        DB::beginTransaction();

        try {
            $matching = $this->makeParticipant(['divisi_id' => 2, 'register_date' => self::FIXTURE_DATE]);
            $other = $this->makeParticipant(['divisi_id' => 3, 'register_date' => self::FIXTURE_DATE]);

            $response = $this->page(['divisi_id' => 2, 'date_range' => self::FIXTURE_RANGE]);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertStringContainsString($matching->code, $html);
            $this->assertStringNotContainsString($other->code, $html, 'Filter divisi tidak menyaring peserta divisi lain.');
            $this->assertSame(1, $this->renderedRows($html));
            $this->assertStringContainsString('<option value="2" selected>', $html, 'Pilihan divisi tidak tersimpan pada form.');
        } finally {
            DB::rollBack();
        }
    }

    public function test_page_accepts_another_contract_of_the_client_and_falls_back_on_unknown_contract(): void
    {
        DB::beginTransaction();

        try {
            $sessionContract = $this->makeParticipant(['register_date' => self::FIXTURE_DATE]);
            $otherContract = $this->makeParticipant([
                'contract_id' => self::OTHER_CONTRACT,
                'register_date' => self::FIXTURE_DATE,
            ]);

            // Kontrak lain milik client yang sama boleh dilihat lewat filter.
            $response = $this->page(['contract_id' => self::OTHER_CONTRACT, 'date_range' => self::FIXTURE_RANGE]);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertStringContainsString($otherContract->code, $html);
            $this->assertStringNotContainsString($sessionContract->code, $html);
            $this->assertStringContainsString(
                '<option value="' . self::OTHER_CONTRACT . '" selected>',
                $html,
                'Kontrak yang dipilih tidak tersimpan pada form.'
            );

            // Kontrak yang tidak dikenal kembali ke kontrak sesi MCU IN.
            $unknown = $this->page(['contract_id' => 999999, 'date_range' => self::FIXTURE_RANGE]);

            $unknown->assertOk();

            $unknownHtml = $unknown->getContent();

            $this->assertStringContainsString($sessionContract->code, $unknownHtml);
            $this->assertStringNotContainsString($otherContract->code, $unknownHtml);
            $this->assertStringContainsString('<option value="' . self::CONTRACT_ID . '" selected>', $unknownHtml);
        } finally {
            DB::rollBack();
        }
    }

    public function test_page_marks_validated_participants(): void
    {
        DB::beginTransaction();

        try {
            $validated = $this->makeParticipant(['register_date' => self::FIXTURE_DATE]);
            $pending = $this->makeParticipant(['register_date' => self::FIXTURE_DATE]);

            ValidateDoctor::create([
                'client_id' => self::CLIENT_ID,
                'no_mcu' => $validated->code,
                'name' => $validated->name,
                'date_mcu' => self::FIXTURE_DATE,
            ]);

            $response = $this->page(['date_range' => self::FIXTURE_RANGE]);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertStringContainsString('VALID', $this->rowFor($html, $validated->code));
            $this->assertStringContainsString('BELUM', $this->rowFor($html, $pending->code));
        } finally {
            DB::rollBack();
        }
    }

    public function test_page_renders_matching_columns_and_no_dead_markup(): void
    {
        DB::beginTransaction();

        try {
            $this->assertPageRendersMatchingColumnsAndNoDeadMarkup();
        } finally {
            DB::rollBack();
        }
    }

    private function assertPageRendersMatchingColumnsAndNoDeadMarkup(): void
    {
        $participant = $this->makeParticipant();

        $response = $this->page(['date_range' => self::FIXTURE_RANGE]);

        $response->assertOk();

        $html = $response->getContent();

        preg_match('/<thead>(.*?)<\/thead>/s', $html, $head);
        $this->assertNotEmpty($head, 'Tabel tidak punya thead.');
        $this->assertSame(17, substr_count($head[1], '<th'), 'Jumlah judul kolom berubah.');

        // Baris data harus punya jumlah sel yang sama dengan judulnya.
        $row = $this->rowFor($html, $participant->code);
        $this->assertNotSame('', $row, 'Tidak ada baris data untuk diperiksa.');
        $this->assertSame(17, substr_count($row, '<td'), 'Jumlah sel baris data tidak sama dengan judul kolom.');

        $response
            ->assertSee('Print / Cetak MCU', false)
            ->assertSee('Dari No.', false)
            ->assertSee('Sampai No.', false)
            ->assertSee('id="start"', false)
            ->assertSee('id="end"', false)
            ->assertSee('id="reset"', false)
            ->assertSee('id="fp-range"', false)
            ->assertSee('name="filter[contract_id]"', false)
            ->assertSee('name="filter[divisi_id]"', false)
            // Markup lama yang tidak berfungsi tidak boleh tertinggal.
            ->assertDontSee('hilang', false)
            ->assertDontSee('Pusher.logToConsole', false)
            ->assertDontSee('moment(', false)
            // Tombol cetak massal harus punya tujuan dan cakupan filter.
            ->assertSee('data-scope="peserta"', false)
            ->assertSee('data-scope="hasil"', false);

        $this->assertSame(9, substr_count($html, 'data-url='), 'Jumlah tombol cetak massal berubah.');

        foreach ([
            'multiple.identitas', 'multiple.resume', 'multiple.pemFisik', 'multiple.laboratorium',
            'multiple.radiologi', 'multiple.audiometri', 'multiple.ekg', 'multiple.spirometri', 'multiple.rectal',
        ] as $route) {
            $this->assertStringContainsString(
                route($route),
                $html,
                "Tombol cetak massal {$route} tidak punya tujuan."
            );
        }
    }

    public function test_page_shows_empty_state_when_nothing_matches(): void
    {
        DB::beginTransaction();

        try {
            $participant = $this->makeParticipant(['register_date' => self::FIXTURE_DATE]);

            $response = $this->page(['date_range' => '2001-01-01 to 2001-01-02']);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertSame(0, $this->renderedRows($html));
            $this->assertStringNotContainsString($participant->code, $html);
            $this->assertSame(1, substr_count($html, 'colspan="17"'), 'Baris kosong tabel tidak melebar sesuai jumlah kolom.');
            $response->assertSee('Tidak ada peserta pada kontrak, divisi, dan rentang tanggal yang dipilih.');
        } finally {
            DB::rollBack();
        }
    }

    public function test_page_shows_a_placeholder_when_no_contract_is_active(): void
    {
        $response = $this->page([], null, false);

        $response->assertOk()->assertSee('Belum ada kontrak aktif');
    }

    public function test_page_does_not_run_one_query_per_row(): void
    {
        DB::beginTransaction();

        try {
            // Relasi hasil dibuat supaya kolom status ikut memicu query
            // kalau relasinya tidak diambil sekaligus (eager loading).
            foreach (range(1, 8) as $index) {
                $participant = $this->makeParticipant(['register_date' => self::FIXTURE_DATE]);

                Laboratorium::create([
                    'participant_id' => $participant->id,
                    'contract_id' => self::CONTRACT_ID,
                    'client_id' => self::CLIENT_ID,
                    'hemoglobin' => '1' . $index,
                    'selesai' => 1,
                ]);
                Radiologi::create([
                    'participant_id' => $participant->id,
                    'contract_id' => self::CONTRACT_ID,
                    'date_mcu' => '2020-01-01',
                    'selesai' => 1,
                ]);
            }

            $url = '/participant-print-mcu?' . http_build_query(['filter' => ['date_range' => self::FIXTURE_RANGE]]);

            $rows = $this->renderedRows($this->getUrl($url)->getContent());

            $this->assertSame(8, $rows, 'Butuh banyak baris untuk membuktikan tidak ada query per baris.');

            DB::enableQueryLog();

            $this->getUrl($url)->assertOk();

            $queries = count(DB::getQueryLog());
            DB::flushQueryLog();

            // Satu query per relasi (bukan per baris): jumlahnya harus tetap
            // kecil walau barisnya banyak.
            $this->assertLessThan(
                30,
                $queries,
                "Halaman menjalankan {$queries} query untuk {$rows} baris (query per baris)."
            );
        } finally {
            DB::rollBack();
        }
    }

    public function test_result_print_endpoints_follow_the_number_range_and_session_contract(): void
    {
        DB::beginTransaction();

        try {
            $fixtures = [];

            foreach ([1, 2, 3] as $index) {
                $participant = $this->makeParticipant();

                Radiologi::create([
                    'participant_id' => $participant->id,
                    'contract_id' => self::CONTRACT_ID,
                    'date_mcu' => '2020-01-01',
                    'selesai' => 1,
                ]);

                Laboratorium::create([
                    'participant_id' => $participant->id,
                    'contract_id' => self::CONTRACT_ID,
                    'client_id' => self::CLIENT_ID,
                    'hemoglobin' => '10.' . $index,
                    'selesai' => 1,
                ]);

                $fixtures[] = $participant;
            }

            // Peserta kontrak lain dengan tanggal yang sama: harus tetap dikecualikan.
            $stranger = $this->makeParticipant(['contract_id' => self::OTHER_CONTRACT]);
            Radiologi::create([
                'participant_id' => $stranger->id,
                'contract_id' => self::OTHER_CONTRACT,
                'date_mcu' => '2020-01-01',
                'selesai' => 1,
            ]);

            // Rentang satu nomor.
            $this->fakePdf('pages.report.multiple.radiologi');
            $this->getUrl($this->bulkUrl('/multiple-radiologi', 1, 1))->assertOk();

            $this->assertCount(1, $this->capturedPdf['data'], 'Rentang nomor tidak dipakai endpoint radiologi.');
            $this->assertSame($fixtures[0]->id, $this->capturedPdf['data'][0]->participant_id);
            $this->assertSame(self::CONTRACT_ID, (int) $this->capturedPdf['data'][0]->participant->contract_id);

            // Rentang tiga nomor.
            $this->fakePdf('pages.report.multiple.radiologi');
            $this->getUrl($this->bulkUrl('/multiple-radiologi', 1, 3))->assertOk();

            $this->assertCount(3, $this->capturedPdf['data'], 'Rentang nomor tidak dipakai endpoint radiologi.');
            $this->assertSame(
                [$fixtures[0]->id, $fixtures[1]->id, $fixtures[2]->id],
                $this->capturedPdf['data']->pluck('participant_id')->all()
            );

            // Endpoint laboratorium: rentang juga dipakai dan datanya dibatasi kontrak sesi.
            $this->fakePdf('pages.report.multiple.laboratorium');
            $this->getUrl($this->bulkUrl('/multiple-laboratorium', 2, 2))->assertOk();

            $this->assertCount(1, $this->capturedPdf['data'], 'Rentang nomor tidak dipakai endpoint laboratorium.');
            $this->assertSame($fixtures[1]->id, $this->capturedPdf['data'][0]->participant_id);
        } finally {
            DB::rollBack();
        }
    }

    public function test_bulk_print_shows_a_notice_instead_of_a_blank_pdf(): void
    {
        DB::beginTransaction();

        try {
            $participant = $this->makeParticipant();
            Radiologi::create([
                'participant_id' => $participant->id,
                'contract_id' => self::CONTRACT_ID,
                'date_mcu' => '2020-01-01',
                'selesai' => 1,
            ]);

            // Nomor di luar data yang ada: hasilnya keterangan, bukan halaman kosong.
            $this->fakePdf('pages.report.multiple.empty');

            $response = $this->getUrl($this->bulkUrl('/multiple-radiologi', 90, 91));

            $response->assertOk();
            $this->assertSame('pages.report.multiple.empty', $this->capturedPdf['view']);
            $this->assertCount(0, $this->capturedPdf['data']);

            // Stiker juga memakai keterangan yang sama saat tidak ada nomor form yang cocok.
            $this->fakePdf('pages.report.multiple.empty');

            $this->getUrl('/multiple-identitas?start=9501&end=9502')->assertOk();
            $this->assertSame('pages.report.multiple.empty', $this->capturedPdf['view']);
        } finally {
            DB::rollBack();
        }
    }

    public function test_resume_and_audiometri_print_endpoints_render_only_eligible_participants(): void
    {
        DB::beginTransaction();

        try {
            $first = $this->makeParticipant();
            $second = $this->makeParticipant();
            // Peserta ini tidak divalidasi dokter dan tidak punya audiometri.
            $pending = $this->makeParticipant();

            foreach ([$first, $second] as $participant) {
                ValidateDoctor::create([
                    'client_id' => self::CLIENT_ID,
                    'no_mcu' => $participant->code,
                    'name' => $participant->name,
                    'date_mcu' => self::FIXTURE_DATE,
                ]);

                Audiometri::create([
                    'participant_id' => $participant->id,
                    'contract_id' => self::CONTRACT_ID,
                    'selesai' => 1,
                ]);
            }

            // Resume: satu peserta vs dua peserta yang divalidasi dokter.
            $resumeOne = $this->pdfPagesOf('/multiple-resume', 1, 1);
            $resumeTwo = $this->pdfPagesOf('/multiple-resume', 1, 2);
            $resumeAll = $this->pdfPagesOf('/multiple-resume', 1, 50);

            $this->assertGreaterThan($resumeOne, $resumeTwo, 'Rentang nomor tidak dipakai endpoint resume.');
            $this->assertSame(
                $resumeTwo,
                $resumeAll,
                "Peserta yang belum divalidasi dokter ({$pending->code}) ikut tercetak pada resume massal."
            );

            // Audiometri: sama, hanya peserta yang hasilnya sudah selesai.
            $audiometriOne = $this->pdfPagesOf('/multiple-audiometri', 1, 1);
            $audiometriTwo = $this->pdfPagesOf('/multiple-audiometri', 1, 2);
            $audiometriAll = $this->pdfPagesOf('/multiple-audiometri', 1, 50);

            $this->assertGreaterThan($audiometriOne, $audiometriTwo, 'Rentang nomor tidak dipakai endpoint audiometri.');
            $this->assertSame(
                $audiometriTwo,
                $audiometriAll,
                "Peserta tanpa hasil audiometri ({$pending->code}) ikut tercetak pada cetak massal audiometri."
            );
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Jumlah halaman PDF dari cetak massal untuk rentang nomor tertentu.
     */
    private function pdfPagesOf(string $path, int $start, int $end): int
    {
        $response = $this->getUrl($this->bulkUrl($path, $start, $end, ''));

        $response->assertOk();
        $this->assertPdf($response);

        return $this->pdfPageCount($response->getContent());
    }

    public function test_identitas_print_uses_the_form_number_range(): void
    {
        DB::beginTransaction();

        try {
            foreach ([9301, 9302] as $noForm) {
                $this->makeParticipant(['no_form' => $noForm]);
            }

            $response = $this->getUrl('/multiple-identitas?start=9301&end=9302&filter[date_range]=' . urlencode(self::FIXTURE_RANGE));

            $response->assertOk();
            $this->assertPdf($response);
            $this->assertSame(2, $this->pdfPageCount($response->getContent()));

            // Rentang di luar nomor formulir data uji tidak menghasilkan halaman.
            $empty = $this->getUrl('/multiple-identitas?start=9401&end=9402&filter[date_range]=' . urlencode(self::FIXTURE_RANGE));

            $empty->assertOk();
            $this->assertPdf($empty);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * URL cetak massal dengan nama filter yang sama seperti yang dikirim
     * halaman print MCU (hasil pemeriksaan memakai prefix participant.).
     */
    private function bulkUrl(string $path, int $start, int $end, string $scope = 'participant.'): string
    {
        return $path . '?' . http_build_query([
            'start' => $start,
            'end' => $end,
            'filter' => [
                $scope . 'date_range' => self::FIXTURE_RANGE,
                $scope . 'contract_id' => self::CONTRACT_ID,
                $scope . 'client_id' => self::CLIENT_ID,
            ],
        ]);
    }

    private function getUrl(string $url): TestResponse
    {
        return $this->actingAs(User::find(1))
            ->withSession(['client_id' => self::CLIENT_ID, 'contract_id' => self::CONTRACT_ID])
            ->get($url);
    }

    /**
     * Ganti PDF dengan mock supaya data yang dipilih controller bisa diperiksa
     * tanpa membongkar isi berkas PDF.
     */
    private function fakePdf(string $expectedView): void
    {
        $this->capturedPdf = [];

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function ($view, $payload) use ($expectedView) {
                $this->capturedPdf = ['view' => $view, 'data' => $payload['data'] ?? null];

                return true;
            })
            ->andReturnSelf();

        Pdf::shouldReceive('stream')->once()->andReturn(response('pdf', 200));
    }

    private function assertPdf(TestResponse $response): void
    {
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent(), 'Hasil cetak bukan berkas PDF.');
    }

    /**
     * Jumlah halaman PDF. DomPDF menuliskan jumlah halaman pada /Count;
     * nilai terbesar adalah jumlah halaman dokumen.
     */
    private function pdfPageCount(string $pdf): int
    {
        preg_match_all('/\/Count\s+(\d+)/', $pdf, $matches);

        return $matches[1] ? (int) max(array_map('intval', $matches[1])) : 0;
    }
}
