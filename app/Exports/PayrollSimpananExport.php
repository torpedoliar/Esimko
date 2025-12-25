<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PayrollSimpananExport implements FromView, ShouldAutoSize
{
    protected $anggota, $list_jenis_simpanan;
    public function __construct($anggota, $list_jenis_simpanan)
    {
        $this->anggota = $anggota;
        $this->list_jenis_simpanan = $list_jenis_simpanan;
    }

    public function view(): View
    {
        $anggota = $this->anggota;
        $list_jenis_simpanan = $this->list_jenis_simpanan;
        return view('simpanan.payroll.excel', compact('anggota', 'list_jenis_simpanan'));
    }
}
