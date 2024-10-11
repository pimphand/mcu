<?php

namespace App\Exports;

use App\Models\Participant;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class ResultMcu implements FromView
{
    private $participant;
    public function __construct($participant){
        $this->participant = $participant;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $data = $this->participant;
        return view('exports.result_mcu', [
            "data" => $data
        ]);
    }
}
