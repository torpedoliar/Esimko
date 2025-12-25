<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PenjualanExport implements FromView, ShouldAutoSize
{
    public $penjualan, $params;
    public function __construct($penjualan, $params)
    {
        $this->penjualan = $penjualan;
        $this->params = $params;
    }

    public function view(): View
    {
        $penjualan = $this->penjualan;
        $params = $this->params;
        return view('pos.laporan.penjualan.excel', compact('params', 'penjualan'));
    }
}
