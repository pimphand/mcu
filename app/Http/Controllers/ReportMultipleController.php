<?php

namespace App\Http\Controllers;

use App\Models\Ekg;
use App\Models\Participant;
use App\Models\PemeriksaanFisik;
use App\Models\Radiologi;
use App\Models\Laboratorium;
use App\Models\Rectal;
use App\Models\Spirometri;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReportMultipleController extends Controller
{
    public function spirometri(Request $request)
    {
        $start = intval(request('start', 1)); // Mulai dari item pertama secara default
        $end = intval(request('end', 50));

        $data = QueryBuilder::for(Spirometri::class)
            ->allowedIncludes(['participant'])
            ->allowedFilters([
                AllowedFilter::exact('participant.client_id'),
                AllowedFilter::exact('participant.divisi_id'),
                AllowedFilter::exact('participant.contract_id'),
                AllowedFilter::scope('participant.date_range'),
            ])
            ->whereHas('participant', function ($query) {
                // $query->where('')
            })
            ->where('selesai', 1)
            ->with('participant')
            ->skip($start - 1)  // Lewati data sebelum posisi 'start'
            ->take($end - $start + 1)  // Ambil sejumlah 'end - start + 1' data
            ->get();

        $pdf = Pdf::loadView('pages.report.multiple.spirometri', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "spirometri", $start . '-' . $end));
    }

    public function identitas(Request $request)
    {
        $start = intval(request('start', 1)); // Mulai dari item pertama secara default
        $end = intval(request('end', 50));
       
        $data = QueryBuilder::for(Participant::class)
            ->allowedFilters([
                AllowedFilter::exact('client_id'),
                AllowedFilter::exact('divisi_id'),
                AllowedFilter::exact('contract_id'),
                AllowedFilter::scope('date_range'),
            ])
            ->whereBetween('no_form', [$start, $end])
            ->where('contract_id', $request->session()->get('contract_id'))
            ->get();
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


        return $pdf->stream(sprintf('%s-%s.pdf', "print-stiker", $start . '-' . $end));
    }

    public function pemFisik(Request $request)
    {
        $start = intval(request('start', 1));
        $end = intval(request('end', 50));
        $perPage = $end - $start + 1;

        $data = QueryBuilder::for(PemeriksaanFisik::class)
            ->with(['participant.client', 'participant.divisi', 'participant.contract'])
            ->allowedIncludes(['participant'])
            ->allowedFilters([
                AllowedFilter::exact('participant.client_id'),
                AllowedFilter::exact('participant.divisi_id'),
                AllowedFilter::exact('participant.contract_id'),
                AllowedFilter::scope('participant.date_range'),
            ])
            ->where('selesai', 1)
            ->paginate($perPage, ['*'], 'page', $start);

        $pdf = Pdf::loadView('pages.report.multiple.pemeriksaan-fisik', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "pemeriksaan-fisik", $start . '-' . $end));
    }

    public function radiologi(Request $request)
    {
        $start = intval(request('start', 1)); // Mulai dari item pertama secara default
        $end = intval(request('end', 50));

        $data = QueryBuilder::for(Radiologi::class)
            ->allowedIncludes(['participant'])
            ->allowedFilters([
                AllowedFilter::exact('participant.client_id'),
                AllowedFilter::exact('participant.divisi_id'),
                AllowedFilter::exact('participant.contract_id'),
                AllowedFilter::scope('participant.date_range'),
            ])

            ->where('selesai', 1)
            ->get();

        $pdf = Pdf::loadView('pages.report.multiple.radiologi', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "radiologi", $start . '-' . $end));
    }
    public function ekg(Request $request)
    {
        $start = intval(request('start', 1)); // Mulai dari item pertama secara default
        $end = intval(request('end', 50));

        $data = QueryBuilder::for(Ekg::class)
            ->allowedIncludes(['participant'])
            ->allowedFilters([
                AllowedFilter::exact('participant.client_id'),
                AllowedFilter::exact('participant.divisi_id'),
                AllowedFilter::exact('participant.contract_id'),
                AllowedFilter::scope('participant.date_range'),
            ])
            ->where('selesai', 1)
            ->with('participant')
            ->get();

        $pdf = Pdf::loadView('pages.report.multiple.ekg', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "ekg", $start . '-' . $end));
    }

    public function rectal(Request $request)
    {
        $start = intval(request('start', 1)); // Mulai dari item pertama secara default
        $end = intval(request('end', 50));
        $data = QueryBuilder::for(Rectal::class)
            ->allowedIncludes(['participant'])
            ->allowedFilters([
                AllowedFilter::exact('participant.client_id'),
                AllowedFilter::exact('participant.divisi_id'),
                AllowedFilter::exact('participant.contract_id'),
                AllowedFilter::scope('participant.date_range'),
            ])
            ->where('selesai', 1)
            ->with('participant')
            ->get();

        $pdf = Pdf::loadView('pages.report.multiple.rectal', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "rectal", $start . '-' . $end));
    }

    public function laboratorium(Request $request)
    {
        $start = intval(request('start', 1)); // Mulai dari item pertama secara default
        $end = intval(request('end', 50));

        $data = QueryBuilder::for(Laboratorium::class)
            ->allowedIncludes(['participant'])
            ->allowedFilters([
                AllowedFilter::exact('participant.client_id'),
                AllowedFilter::exact('participant.divisi_id'),
                AllowedFilter::exact('participant.contract_id'),
                AllowedFilter::scope('participant.date_range'),
            ])

            ->where('selesai', 1)
            ->with('participant')
            ->get();

        $pdf = Pdf::loadView('pages.report.multiple.laboratorium', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "laboratorium", $start . '-' . $end));
    }
}
