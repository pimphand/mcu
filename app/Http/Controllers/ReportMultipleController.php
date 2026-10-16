<?php

namespace App\Http\Controllers;

use App\Models\Ekg;
use App\Models\Laboratorium;
use App\Models\Participant;
use App\Models\PemeriksaanFisik;
use App\Models\Radiologi;
use App\Models\Rectal;
use App\Models\Spirometri;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReportMultipleController extends Controller
{
    /**
     * Rentang nomor yang diminta dari halaman print MCU.
     *
     * @return array{start: int, end: int, perPage: int}
     */
    private function range(Request $request): array
    {
        $start = max(1, intval($request->query('start', 1)));
        $end = max($start, intval($request->query('end', 50)));

        return [
            'start' => $start,
            'end' => $end,
            'perPage' => $end - $start + 1,
        ];
    }

    /**
     * Kontrak untuk cetak massal: kontrak sesi MCU IN, boleh diganti kontrak
     * lain lewat filter halaman. Nilainya tetap dijaga oleh filter client,
     * jadi kontrak milik client lain tidak akan pernah terambil.
     */
    private function contractId(Request $request, string $scope): ?int
    {
        $requested = data_get($request->query('filter'), $scope . 'contract_id');

        return (int) ($requested ?: $request->session()->get('contract_id')) ?: null;
    }

    /**
     * Batasi hasil cetak ke client dan kontrak yang sedang aktif. Client selalu
     * dari sesi MCU IN; nilai dari URL tidak dipercaya.
     */
    private function scopeToSession($query, Request $request, bool $onParticipant)
    {
        $clientId = $request->session()->get('client_id');
        $contractId = $this->contractId($request, $onParticipant ? '' : 'participant.');

        return $query
            ->when($clientId, fn ($sub) => $this->scopeWhere($sub, $onParticipant, 'client_id', $clientId))
            ->when($contractId, fn ($sub) => $this->scopeWhere($sub, $onParticipant, 'contract_id', $contractId));
    }

    private function scopeWhere($query, bool $onParticipant, string $column, $value)
    {
        if ($onParticipant) {
            return $query->where($column, $value);
        }

        return $query->whereHas('participant', fn ($sub) => $sub->where($column, $value));
    }

    /**
     * Filter client/kontrak boleh dikirim halaman, tetapi nilainya diabaikan
     * karena yang berlaku adalah sesi MCU IN. Tanpa ini filter dari URL akan
     * ditolak sebagai filter yang tidak dikenal.
     *
     * @param  array<int, string>  $names
     * @return array<int, AllowedFilter>
     */
    private function ignoredFilters(string $scope, array $names): array
    {
        return array_map(
            fn (string $name) => AllowedFilter::callback($scope . $name, fn ($query, $value) => $query),
            $names
        );
    }

    /**
     * Filter yang dipakai halaman print MCU untuk data hasil pemeriksaan
     * (divisi dan tanggal register peserta).
     *
     * @return array<int, AllowedFilter>
     */
    private function participantResultFilters(): array
    {
        return array_merge($this->ignoredFilters('participant.', ['client_id', 'contract_id']), [
            AllowedFilter::exact('participant.divisi_id'),
            AllowedFilter::scope('participant.date_range'),
        ]);
    }

    /**
     * Filter yang dipakai halaman print MCU untuk data peserta langsung
     * (dipakai stiker, resume, dan audiometri).
     *
     * @return array<int, AllowedFilter>
     */
    private function participantFilters(): array
    {
        return array_merge($this->ignoredFilters('', ['client_id', 'contract_id']), [
            AllowedFilter::exact('divisi_id'),
            AllowedFilter::scope('date_range'),
        ]);
    }

    public function spirometri(Request $request)
    {
        $range = $this->range($request);

        $data = $this->scopeToSession(QueryBuilder::for(Spirometri::class), $request, false)
            ->allowedIncludes(['participant'])
            ->allowedFilters($this->participantResultFilters())
            ->where('selesai', 1)
            ->with('participant')
            ->orderBy('id')
            ->skip($range['start'] - 1)  // Lewati data sebelum posisi 'start'
            ->take($range['perPage'])  // Ambil sejumlah 'end - start + 1' data
            ->get();

        return $this->stream('pages.report.multiple.spirometri', $data, 'spirometri', $range);
    }

    public function identitas(Request $request)
    {
        $range = $this->range($request);

        // Nomor formulir peserta yang dipakai untuk stiker mengikuti input
        // "Dari No / Sampai No" pada halaman print MCU.
        $data = $this->scopeToSession(QueryBuilder::for(Participant::class), $request, true)
            ->allowedFilters($this->participantFilters())
            ->whereBetween('no_form', [$range['start'], $range['end']])
            ->orderBy('no_form')
            ->get();

        if ($data->isEmpty()) {
            return $this->stream('pages.report.multiple.empty', $data, 'print-stiker', $range);
        }

        $pdf = Pdf::loadView('pages.report.multiple.identitas', compact('data'));

        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->getCanvas();
        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
            $text = "No Form $pageNumber";
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $size = 4;
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $canvas->text($canvas->get_width() - $width - 20, $canvas->get_height() - 20, $text, $font, $size);
        });

        return $pdf->stream(sprintf('%s-%s.pdf', "print-stiker", $range['start'] . '-' . $range['end']));
    }

    public function pemFisik(Request $request)
    {
        $range = $this->range($request);

        $data = $this->scopeToSession(QueryBuilder::for(PemeriksaanFisik::class), $request, false)
            ->with(['participant.client', 'participant.divisi', 'participant.contract'])
            ->allowedIncludes(['participant'])
            ->allowedFilters($this->participantResultFilters())
            ->where('selesai', 1)
            ->orderBy('id')
            ->skip($range['start'] - 1)
            ->take($range['perPage'])
            ->get();

        return $this->stream('pages.report.multiple.pemeriksaan-fisik', $data, 'pemeriksaan-fisik', $range);
    }

    public function radiologi(Request $request)
    {
        $range = $this->range($request);

        $data = $this->scopeToSession(QueryBuilder::for(Radiologi::class), $request, false)
            ->allowedIncludes(['participant'])
            ->allowedFilters($this->participantResultFilters())
            ->where('selesai', 1)
            ->with('participant')
            ->orderBy('id')
            ->skip($range['start'] - 1)
            ->take($range['perPage'])
            ->get();

        return $this->stream('pages.report.multiple.radiologi', $data, 'radiologi', $range);
    }

    public function ekg(Request $request)
    {
        $range = $this->range($request);

        $data = $this->scopeToSession(QueryBuilder::for(Ekg::class), $request, false)
            ->allowedIncludes(['participant'])
            ->allowedFilters($this->participantResultFilters())
            ->where('selesai', 1)
            ->with('participant')
            ->orderBy('id')
            ->skip($range['start'] - 1)
            ->take($range['perPage'])
            ->get();

        return $this->stream('pages.report.multiple.ekg', $data, 'ekg', $range);
    }

    public function rectal(Request $request)
    {
        $range = $this->range($request);

        $data = $this->scopeToSession(QueryBuilder::for(Rectal::class), $request, false)
            ->allowedIncludes(['participant'])
            ->allowedFilters($this->participantResultFilters())
            ->where('selesai', 1)
            ->with('participant')
            ->orderBy('id')
            ->skip($range['start'] - 1)
            ->take($range['perPage'])
            ->get();

        return $this->stream('pages.report.multiple.rectal', $data, 'rectal', $range);
    }

    public function laboratorium(Request $request)
    {
        $range = $this->range($request);

        $data = $this->scopeToSession(QueryBuilder::for(Laboratorium::class), $request, false)
            ->allowedIncludes(['participant'])
            ->allowedFilters($this->participantResultFilters())
            ->where('selesai', 1)
            ->with('participant')
            ->orderBy('id')
            ->skip($range['start'] - 1)
            ->take($range['perPage'])
            ->get();

        return $this->stream('pages.report.multiple.laboratorium', $data, 'laboratorium', $range);
    }

    public function resume(Request $request)
    {
        $range = $this->range($request);

        // Resume hanya dicetak untuk peserta yang sudah divalidasi dokternya,
        // sama seperti tombol Resume per peserta pada halaman print MCU.
        $data = $this->scopeToSession(QueryBuilder::for(Participant::class), $request, true)
            ->allowedFilters($this->participantFilters())
            ->whereHas('validateDoctor')
            ->with(['divisi', 'department', 'client', 'contract', 'pemeriksaanFisik', 'validateDoctor'])
            ->orderBy('code')
            ->skip($range['start'] - 1)
            ->take($range['perPage'])
            ->get();

        return $this->stream('pages.report.multiple.resume', $data, 'resume', $range);
    }

    public function audiometri(Request $request)
    {
        $range = $this->range($request);

        $data = $this->scopeToSession(QueryBuilder::for(Participant::class), $request, true)
            ->allowedFilters($this->participantFilters())
            ->whereHas('audiometri', fn ($query) => $query->where('selesai', 1))
            ->with(['divisi', 'department', 'client', 'contract', 'audiometri'])
            ->orderBy('code')
            ->skip($range['start'] - 1)
            ->take($range['perPage'])
            ->get();

        return $this->stream('pages.report.multiple.audiometri', $data, 'audiometri', $range);
    }

    /**
     * Cetak satu PDF untuk semua data yang terpilih.
     *
     * @param  \Illuminate\Support\Collection<int, mixed>  $data
     * @param  array{start: int, end: int, perPage: int}  $range
     */
    private function stream(string $view, $data, string $name, array $range)
    {
        // Tanpa data, halaman PDF berisi keterangan saja supaya pengguna tidak
        // mencetak berkas kosong tanpa tahu sebabnya.
        if ($data->isEmpty()) {
            $view = 'pages.report.multiple.empty';
        }

        $pdf = Pdf::loadView($view, compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', $name, $range['start'] . '-' . $range['end']));
    }
}
