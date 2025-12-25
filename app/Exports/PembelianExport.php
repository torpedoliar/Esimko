<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PembelianExport implements FromView, ShouldAutoSize
{
    protected $pembelian, $request;
    public function __construct($pembelian, $request)
    {
        $this->pembelian = $pembelian;
        $this->request = $request;
    }

    public function view(): View
    {
        $pembelian = $this->pembelian;
        $request = $this->request;
        return view('pos.laporan.pembelian.excel', compact('pembelian', 'request'));
    }
}
