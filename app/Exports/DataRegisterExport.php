<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class DataRegisterExport implements FromView
{
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
        return view('exports.dataRegister', [
            'data' => $this->participants
        ]);
    }
}
