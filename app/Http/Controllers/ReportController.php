<?php

namespace App\Http\Controllers;

use App\Services\ParticipantService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private ParticipantService $participantService;
    public function __construct(ParticipantService $participantService)
    {
        $this->participantService = $participantService;
    }
    public function identitas($participantId)
    {
        $participant = $this->participantService->find($participantId);
        $pdf = Pdf::loadView('pages.report.identitas', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }

    public function tandaVital($participantId)
    {
        $participant = $this->participantService->find($participantId);

        $pdf = Pdf::loadView('pages.report.tanda-vital', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }

    public function pemeriksaanFisik($participantId)
    {
        $participant = $this->participantService->find($participantId);
        // dd($participant);
        $pdf = Pdf::loadView('pages.report.pemeriksaan-fisik', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }

    public function laboratorium($participantId)
    {
        $participant = $this->participantService->find($participantId);
        $pdf = Pdf::loadView('pages.report.laboratorium', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }

    public function laboratoriumLabDriver($participantId)
    {
        $participant = $this->participantService->find($participantId);
        $pdf = Pdf::loadView('pages.report.laboratorium-lab-driver', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }

    public function laboratoriumSgptUreum($participantId)
    {
        $participant = $this->participantService->find($participantId);
        $pdf = Pdf::loadView('pages.report.laboratorium-sgpt-ureum', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }

    public function radiologi($participantId)
    {
        $participant = $this->participantService->find($participantId);
        $pdf = Pdf::loadView('pages.report.radiologi', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }
    public function audiometri($participantId)
    {
        $participant = $this->participantService->find($participantId);
        $pdf = Pdf::loadView('pages.report.audiometri', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }
    public function spirometri($participantId)
    {
        $participant = $this->participantService->find($participantId);
        $pdf = Pdf::loadView('pages.report.spirometri', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }
    public function rectal($participantId)
    {
        $participant = $this->participantService->find($participantId);
        $pdf = Pdf::loadView('pages.report.rectal', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }
    public function ekg($participantId)
    {
        $participant = $this->participantService->find($participantId);
        $pdf = Pdf::loadView('pages.report.ekg', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }

    public function stickerLab($participantId)
    {
        $paper = [];
        $participant = $this->participantService->find($participantId);
        $pdf = Pdf::loadView('pages.report.sticker-lab', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }
    public function sticker5pcs($participantId)
    {
        $paper = [];
        $participant = $this->participantService->find($participantId);
        $pdf = Pdf::loadView('pages.report.sticker-5pcs', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }

    public function register($participantId)
    {
        $participant = $this->participantService->find($participantId);
        // dd($participant);
        $pdf = Pdf::loadView('pages.report.register', compact('participant'))

            ->setOption('margin-top', 0)  // Atur margin atas menjadi 0
            ->setOption('margin-bottom', 0)  // Atur margin bawah menjadi 0
            ->setOption('margin-left', 0)  // Atur margin kiri menjadi 0
            ->setOption('margin-right', 0)
            ->setOption('header-html', '')
            ->setOption('footer-html', '');

        return $pdf->stream(sprintf('%s.pdf', $participant->code));

        // return view('pages.report.register', compact('participant'));
    }

    public function audiometriBulk(array $participantIds)
    {
        $pdfs = [];

        foreach ($participantIds as $participantId) {
            $participant = $this->participantService->find($participantId);
            $pdf = Pdf::loadView('pages.report.audiometri', compact('participant'));

            // Menyimpan setiap PDF ke array
            $pdfs[] = [
                'filename' => sprintf('%s.pdf', $participant->code),
                'content' => $pdf->output()
            ];
        }

        // Menggabungkan semua PDF ke dalam satu file
        $mergedPdf = new \setasign\Fpdi\Fpdi();
        foreach ($pdfs as $pdfData) {
            $tmpPdf = \TCPDI::createFromString($pdfData['content']);
            for ($i = 1; $i <= $tmpPdf->getNumberOfPages(); $i++) {
                $mergedPdf->addPage();
                $mergedPdf->useTemplate($mergedPdf->importPage($i));
            }
        }

        // Menampilkan PDF hasil gabungan
        return $mergedPdf->Output('I', 'merged_audiometri.pdf');
    }

    public function resume($participantId)
    {
        $participant = $this->participantService->find($participantId);
        $pdf = Pdf::loadView('pages.report.resume', compact('participant'));

        return $pdf->stream(sprintf('%s.pdf', $participant->code));
    }
}
