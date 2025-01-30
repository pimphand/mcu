<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class ResultMcu implements FromView , ShouldQueue
{
        use Exportable;
    private $participants;

    /**
     * Constructor to pass participants data
     *
     * @param $participants
     */
    public function __construct($participants)
    {
        $this->participants = $participants;
    }

    /**
     * Export data using view
     *
     * @return View
     */
    public function view(): View
    {
        return view('exports.resultMcu', [
            'data' => $this->participants
        ]);
    }
}
