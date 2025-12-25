<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BigProdukExport implements WithMultipleSheets
{
    protected $produk;
    public function __construct($produk)
    {
        $this->produk = $produk;
    }

    public function sheets(): array
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '3000');
        $chunks = array_chunk($this->produk->toArray(), 5000);
        $result = [];
        foreach ($chunks as $key => $chunk) $result['sheet' . ($key + 1)] = new ProdukExport($chunk);
        return $result;
    }
}
