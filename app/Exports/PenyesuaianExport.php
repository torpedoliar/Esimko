<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PenyesuaianExport implements FromView, ShouldAutoSize
{
    protected $penyesuaian;
    public function __construct($penyesuaian)
    {
        $this->penyesuaian = $penyesuaian;
    }

    public function view(): View
    {
        $penyesuaian = $this->penyesuaian;
        return view('pos.laporan.penyesuaian.excel', compact('penyesuaian'));
    }
}
