<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CetakBukuController extends Controller
{
    public function cetak_buku(Request $request)
    {
        return view('simpanan.buku_simpanan.cetak');
    }
}
