<?php

namespace App\Http\Controllers;

use App\Models\Ekg;
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
                AllowedFilter::scope('date_range'),
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
            ->skip($start - 1)  // Lewati data sebelum posisi 'start'
            ->take($end - $start + 1)  // Ambil sejumlah 'end - start + 1' data
            ->get();

        $pdf = Pdf::loadView('pages.report.multiple.identitas', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "spirometri", $start . '-' . $end));
    }

    public function pemFisik(Request $request)
    {
        $start = intval(request('start', 1)); // Mulai dari item pertama secara default
        $end = intval(request('end', 50));

        $data = QueryBuilder::for(PemeriksaanFisik::class)
            ->allowedIncludes(['participant'])
            ->allowedFilters([
                AllowedFilter::exact('participant.client_id'),
                AllowedFilter::exact('participant.divisi_id'),
                AllowedFilter::exact('participant.contract_id'),
                AllowedFilter::scope('date_range'),
            ])
            ->whereHas('participant', function ($query) {
                // $query->where('')
            })
            ->where('selesai', 1)
            ->with('participant')
            ->skip($start - 1)  // Lewati data sebelum posisi 'start'
            ->take($end - $start + 1)  // Ambil sejumlah 'end - start + 1' data
            ->get();

        $pdf = Pdf::loadView('pages.report.multiple.pemeriksaan-fisik', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "spirometri", $start . '-' . $end));
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
                AllowedFilter::scope('date_range'),
            ])
            ->whereHas('participant', function ($query) {
                // $query->where('')
            })
            ->where('selesai', 1)
            ->with('participant')
            ->skip($start - 1)  // Lewati data sebelum posisi 'start'
            ->take($end - $start + 1)  // Ambil sejumlah 'end - start + 1' data
            ->get();

        $pdf = Pdf::loadView('pages.report.multiple.radiologi', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "spirometri", $start . '-' . $end));
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
                AllowedFilter::scope('date_range'),
            ])
            ->whereHas('participant', function ($query) {
                // $query->where('')
            })
            ->where('selesai', 1)
            ->with('participant')
            ->skip($start - 1)  // Lewati data sebelum posisi 'start'
            ->take($end - $start + 1)  // Ambil sejumlah 'end - start + 1' data
            ->get();

        $pdf = Pdf::loadView('pages.report.multiple.ekg', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "spirometri", $start . '-' . $end));
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
                AllowedFilter::scope('date_range'),
            ])
            ->whereHas('participant', function ($query) {
                // $query->where('')
            })
            ->where('selesai', 1)
            ->with('participant')
            ->skip($start - 1)  // Lewati data sebelum posisi 'start'
            ->take($end - $start + 1)  // Ambil sejumlah 'end - start + 1' data
            ->get();

        $pdf = Pdf::loadView('pages.report.multiple.rectal', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "spirometri", $start . '-' . $end));
    }
}
