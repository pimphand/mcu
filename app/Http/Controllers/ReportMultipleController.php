<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Spirometri;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReportMultipleController extends Controller
{
    public function spirometri(Request $request)
    {
        $start = request('start', 1); // Mulai dari item pertama secara default
        $end = request('end', 50);    // Hingga item ke-50 secara default

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
            ->with('participant')
            ->skip($start - 1)  // Lewati data sebelum posisi 'start'
            ->take($end - $start)  // Ambil sejumlah 'end - start + 1' data
            ->get();

        $pdf = Pdf::loadView('pages.report.multiple.spirometri', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "spirometri", $start . '-' . $end));
    }

    public function identitas(Request $request)
    {
        $start = request('start', 1); // Mulai dari item pertama secara default
        $end = request('end', 50);    // Hingga item ke-50 secara default

        $data = QueryBuilder::for(Participant::class)

            ->allowedFilters([
                AllowedFilter::exact('client_id'),
                AllowedFilter::exact('divisi_id'),
                AllowedFilter::exact('contract_id'),
                AllowedFilter::scope('date_range'),
            ])
            ->skip($start - 1)  // Lewati data sebelum posisi 'start'
            ->take($end - $start)  // Ambil sejumlah 'end - start + 1' data
            ->get();

        $pdf = Pdf::loadView('pages.report.multiple.identitas', compact('data'));

        return $pdf->stream(sprintf('%s-%s.pdf', "spirometri", $start . '-' . $end));
    }
}
