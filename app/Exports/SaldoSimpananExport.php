<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SaldoSimpananExport implements FromView, ShouldAutoSize
{
    protected $data, $request;
    public function __construct($data, $request)
    {
        $this->data = $data;
        $this->request = $request;
    }

    public function view(): View
    {
        $data = $this->data;
        $request = $this->request;
        return view('monitoring.saldo_simpanan_excel', compact('data', 'request'));
    }
}
