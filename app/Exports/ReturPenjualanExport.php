<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReturPenjualanExport implements FromView, ShouldAutoSize
{
    protected $retur_penjualan, $request;
    public function __construct($retur_penjualan, $request)
    {
        $this->retur_penjualan = $retur_penjualan;
        $this->request = $request;
    }

    public function view(): View
    {
        $retur_penjualan = $this->retur_penjualan;
        $request = $this->request;
        return view('pos.laporan.retur_penjualan.excel', compact('retur_penjualan', 'request'));
    }
}
