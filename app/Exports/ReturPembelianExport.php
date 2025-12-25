<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReturPembelianExport implements FromView, ShouldAutoSize
{
    protected $retur_pembelian, $request;
    public function __construct($retur_pembelian, $request)
    {
        $this->retur_pembelian = $retur_pembelian;
        $this->request = $request;
    }

    public function view(): View
    {
        $retur_pembelian = $this->retur_pembelian;
        $request = $this->request;
        return view('pos.laporan.retur_pembelian.excel', compact('retur_pembelian', 'request'));
    }
}
