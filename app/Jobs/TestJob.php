<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Tambahkan log untuk mengetes
        Log::info('TestJob is being processed.');

        // Simulasikan pekerjaan yang memakan waktu
        sleep(5);

        Log::info('TestJob has been processed successfully.');
    }
}
