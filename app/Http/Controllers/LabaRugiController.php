<?php

namespace App\Http\Controllers;

use App\Akun;
use App\Exports\LabaRugiExport;
use App\Jurnal;
use App\JurnalDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LabaRugiController extends Controller
{
    public function index()
    {
        return view('keuangan.laba_rugi.index');
    }

    public function search(Request $request)
    {
        $tanggal_awal = date('Y-m-01', strtotime('01-' . $request->input('bulan')));
        $tanggal_akhir = date('Y-m-t', strtotime('01-' . $request->input('bulan_akhir')));

        $list_akun = Akun::where('tipe', 'Laba/Rugi')->orderBy('kode_tampil')->get();
        foreach ($list_akun as $value) {
            $value->nominal = JurnalDetail::where('akun_id', $value->id)
                ->whereHas('jurnal', function ($jurnal) use ($tanggal_awal, $tanggal_akhir) {
                    $jurnal->where('tanggal', '>=', $tanggal_awal)->where('tanggal', '<=', $tanggal_akhir);
                })
                ->sum('nominal');
        }

        if ($request->has('ajax')) return $list_akun;
        return view('keuangan.laba_rugi._table', compact('list_akun'));
    }

    public function export(Request $request)
    {
        $tanggal_awal = date('Y-m-01', strtotime('01-' . $request->input('bulan')));
        $tanggal_akhir = date('Y-m-t', strtotime('01-' . $request->input('bulan_akhir')));

        $request->merge(['ajax' => 1]);
        $data = $this->search($request);
        return Excel::download(new LabaRugiExport($data), 'laba_rugi_'. $tanggal_awal .'_sd_'. $tanggal_akhir .'.xlsx');
    }
}
