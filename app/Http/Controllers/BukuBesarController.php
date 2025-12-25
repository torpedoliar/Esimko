<?php

namespace App\Http\Controllers;

use App\Akun;
use App\Exports\BukuBesarExport;
use App\JurnalDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BukuBesarController extends Controller
{
    public function index()
    {
        $akun = Akun::orderBy('kode_tampil')->get();
        return view('keuangan.buku_besar.index', compact('akun'));
    }

    public function search(Request $request)
    {
        $tanggal_awal = date('Y-m-01', strtotime('01-' . $request->input('bulan')));
        $tanggal_akhir = date('Y-m-t', strtotime('01-' . $request->input('bulan_akhir')));
        $akun_id = $request->input('akun_id');

        $akun = Akun::find($akun_id);
        $jurnals = JurnalDetail::where('akun_id', $akun_id)->whereHas('jurnal', function ($jurnal) use ($tanggal_awal, $tanggal_akhir) {
            $jurnal->where('tanggal', '>=', $tanggal_awal)->where('tanggal', '<=', $tanggal_akhir);
        })->orderBy('id')->get();
        return view('keuangan.buku_besar._table', compact('jurnals', 'akun_id', 'akun'));
    }

    public function export(Request $request)
    {
        $tanggal_awal = date('Y-m-01', strtotime('01-' . $request->input('bulan')));
        $tanggal_akhir = date('Y-m-t', strtotime('01-' . $request->input('bulan_akhir')));
        $akun_id = $request->input('akun_id');

        $akun = Akun::find($akun_id);
        $jurnals = JurnalDetail::where('akun_id', $akun_id)->whereHas('jurnal', function ($jurnal) use ($tanggal_awal, $tanggal_akhir) {
            $jurnal->where('tanggal', '>=', $tanggal_awal)->where('tanggal', '<=', $tanggal_akhir);
        })->orderBy('id')->get();
        return Excel::download(new BukuBesarExport($jurnals, $akun), 'buku_besar_'. $tanggal_awal .'_sd_'. $tanggal_akhir .'.xlsx');
    }
}
