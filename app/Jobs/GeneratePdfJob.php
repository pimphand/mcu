<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PDF;
use Storage;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $fileName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $fileName)
    {
        $this->data = $data;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Generate PDF and save to storage
        $pdf = PDF::loadView('pdf.dataRegister', ['data' => $this->data]);
        Storage::put('pdf/' . $this->fileName, $pdf->output());
    }
}
