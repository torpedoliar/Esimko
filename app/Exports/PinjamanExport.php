<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PinjamanExport implements FromView, ShouldAutoSize
{
    protected $data, $tahun, $bulan;
    public function __construct($data, $tahun, $bulan)
    {
        $this->data = $data;
        $this->tahun = $tahun;
        $this->bulan = $bulan;
    }

    public function view(): View
    {
        $data = $this->data;
        $tahun = $this->tahun;
        $bulan = $this->bulan;
        return view('pinjaman.pengajuan.export', compact('data', 'tahun', 'bulan'));
    }
}
