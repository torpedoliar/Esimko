<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PayrollExport implements FromView, ShouldAutoSize
{
    protected $data, $tahun, $bulan;
    public function __construct($data, $bulan)
    {
        $this->data = $data;
        $this->bulan = $bulan;
    }

    public function view(): View
    {
        $data = $this->data;
        $bulan = $this->bulan;
        return view('pinjaman.payroll.export', compact('data',  'bulan'));
    }
}
