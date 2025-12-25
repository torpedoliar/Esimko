<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MutasiExport implements FromView, ShouldAutoSize
{
    protected $pembelian, $penjualan, $retur_pembelian, $retur_penjualan, $opname;
    public function __construct($pembelian, $penjualan, $retur_pembelian, $retur_penjualan, $opname)
    {
        $this->pembelian = $pembelian;
        $this->penjualan = $penjualan;
        $this->retur_pembelian = $retur_pembelian;
        $this->retur_penjualan = $retur_penjualan;
        $this->opname = $opname;
    }

    public function view(): View
    {
        $pembelian = $this->pembelian;
        $penjualan = $this->penjualan;
        $retur_pembelian = $this->retur_pembelian;
        $retur_penjualan = $this->retur_penjualan;
        $opname = $this->opname;
        return view('pos.laporan.mutasi.excel', compact('pembelian', 'penjualan', 'retur_pembelian', 'retur_penjualan', 'opname'));
    }
}
