<?php

namespace App\Jobs;

use App\Models\PemeriksaanFisik;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Pusher\Pusher;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use ZipArchive;

class GenerateReportPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $start;
    protected $end;
    protected $data;

    public function __construct($start, $end, $data)
    {
        $this->start = $start;
        $this->end = $end;
        $this->data = $data;
    }

    public function handle()
    {
        $pdfFiles = []; // Initialize an array to hold the filenames

        foreach ($this->data as $index => $data) {
            Queue::push(function () use ($data, $index, &$pdfFiles) {
                // Generate PDF for the chunk
                $pdf = Pdf::loadView('pages.report.multiple.pemeriksaan-fisik', compact('data'));

                // Create filename based on current chunk index
                $filename = sprintf('pemeriksaan-fisik-%s.pdf', $index + 1); // Adjust as needed for unique names

                // Store PDF in public storage
                Storage::put('public/reports/' . $filename, $pdf->output());

                // Add filename to the array
                $pdfFiles[] = $filename;

                // Optionally, send notifications for each chunk processed
                // $this->notifyFrontend($filename);
            });
        }

        // Create a ZIP file containing all generated PDFs
        $this->createZipFile($pdfFiles);
    }

    protected function createZipFile($pdfFiles)
    {
        $zipFilename = 'pemeriksaan-fisik-reports.zip';
        $zipFilePath = storage_path('app/public/reports/' . $zipFilename);

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($pdfFiles as $file) {
                $zip->addFile(storage_path('app/public/reports/' . $file), $file);
            }
            $zip->close();

            // Notify frontend about the zip file creation
            // $this->notifyFrontend($zipFilename);
        } else {
            // Handle error when creating the zip
            \Log::error("Failed to create ZIP file: $zipFilename");
        }
    }

    protected function notifyFrontend($filename)
    {
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ]
        );

        // Trigger an event to notify frontend
        $data = [
            'message' => 'PDF Report Generated',
            'filename' => $filename,
        ];
        $pusher->trigger('report-channel', 'pdf-generated', $data);
    }
}
