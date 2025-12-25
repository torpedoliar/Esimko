<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProdukExport implements FromView, ShouldAutoSize
{
    protected $produk;
    public function __construct($produk)
    {
        $this->produk = $produk;
    }

    public function view(): View
    {
        $produk = $this->produk;
        return view('pos.laporan.produk.excel', compact('produk'));
    }
}
