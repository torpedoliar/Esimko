<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class JurnalExport implements FromView, ShouldAutoSize
{
    public $jurnals;
    public function __construct($jurnals)
    {
        $this->jurnals = $jurnals;
    }

    public function view(): View
    {
        $jurnals = $this->jurnals;
        return view('keuangan.jurnal.export', compact('jurnals'));
    }
}
