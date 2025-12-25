<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LabaRugiExport implements FromView, ShouldAutoSize
{
    public $list_akun;
    public function __construct($list_akun)
    {
        $this->list_akun = $list_akun;
    }

    public function view(): View
    {
        $list_akun = $this->list_akun;
        return view('keuangan.laba_rugi.export', compact('list_akun'));
    }
}
