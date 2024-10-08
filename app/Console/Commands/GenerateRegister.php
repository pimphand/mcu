<?php

namespace App\Console\Commands;

use App\Models\Participant;
use App\Services\ParticipantService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRegister extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $par = Participant::all();

        foreach ($par as $participant) {
            $usia = self::getAgeInYearsMonthsDays($participant->birthday);
            $pdf = Pdf::loadView('pages.report.register', compact('participant', 'usia'))
                ->setOption('margin-top', 0)
                ->setOption('margin-bottom', 0)
                ->setOption('margin-left', 0)
                ->setOption('margin-right', 0)
                ->setOption('header-html', '')
                ->setOption('footer-html', '');

            // Tentukan lokasi penyimpanan file PDF
            $filePath = storage_path("app/public/reports/{$participant->divisi->name}/{$participant->code}.pdf");

            // Simpan file PDF
            $pdf->save($filePath);
        }
    }

    function getAgeInYearsMonthsDays($birthDate)
    {
        // Mengubah string tanggal lahir ke objek Carbon
        $birthDate = Carbon::parse($birthDate);
        $currentDate = Carbon::now();

        // Hitung selisih dalam tahun, bulan, dan hari
        $diffYears = $birthDate->diffInYears($currentDate);
        $diffMonths = $birthDate->copy()->addYears($diffYears)->diffInMonths($currentDate);
        $diffDays = $birthDate->copy()->addYears($diffYears)->addMonths($diffMonths)->diffInDays($currentDate);

        // Bulatkan hasil
        $roundedYears = floor($diffYears);
        $roundedMonths = floor($diffMonths);
        $roundedDays = floor($diffDays);

        // Format hasilnya
        return "{$roundedYears} Tahun {$roundedMonths} Bulan {$roundedDays} Hari";
    }
}
