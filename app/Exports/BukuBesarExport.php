<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BukuBesarExport implements FromView, ShouldAutoSize
{
    public $jurnals, $akun;
    public function __construct($jurnals, $akun)
    {
        $this->jurnals = $jurnals;
        $this->akun = $akun;
    }

    public function view(): View
    {
        $jurnals = $this->jurnals;
        $akun = $this->akun;
        return view('keuangan.buku_besar.export', compact('jurnals', 'akun'));
    }
}
