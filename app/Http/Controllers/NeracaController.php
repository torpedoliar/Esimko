<?php

namespace App\Http\Controllers;

use App\Akun;
use App\Exports\NeracaExport;
use App\Jurnal;
use App\JurnalDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NeracaController extends Controller
{
    protected $labaRugiController;
    public function __construct(LabaRugiController $labaRugiController)
    {
        $this->labaRugiController = $labaRugiController;
    }

    public function index()
    {
        return view('keuangan.neraca.index');
    }

    public function search(Request $request)
    {
        $tanggal_awal = date('Y-m-01', strtotime('01-' . $request->input('bulan')));
        $tanggal_akhir = date('Y-m-t', strtotime('01-' . $request->input('bulan_akhir')));

        $list_akun = Akun::where('kode_tampil', 'like', '1%')->orderBy('kode_tampil')->get();
        foreach ($list_akun as $value) {
            $value->nominal = JurnalDetail::where('akun_id', $value->id)
                ->whereHas('jurnal', function ($jurnal) use ($tanggal_awal, $tanggal_akhir) {
                    $jurnal->where('tanggal', '>=', $tanggal_awal)->where('tanggal', '<=', $tanggal_akhir);
                })
                ->sum('nominal');
        }

        $list_akun2 = Akun::where('kode_tampil', 'like', '2%')->orderBy('kode_tampil')->get();
        foreach ($list_akun2 as $value) {
            $value->nominal = JurnalDetail::where('akun_id', $value->id)
                ->whereHas('jurnal', function ($jurnal) use ($tanggal_awal, $tanggal_akhir) {
                    $jurnal->where('tanggal', '>=', $tanggal_awal)->where('tanggal', '<=', $tanggal_akhir);
                })
                ->sum('nominal');
        }

        $request = $request->merge(['ajax' => 1]);
        $laba_rugi = $this->labaRugiController->search($request);
        $laba_rugi = $laba_rugi->sum('nominal') * -1;

        $data = [
            'list_akun' => $list_akun,
            'list_akun2' => $list_akun2,
            'laba_rugi' => $laba_rugi,
        ];
        if ($request->has('export')) return $data;
        return view('keuangan.neraca._table', $data);
    }

    public function export(Request $request)
    {
        $tanggal_awal = date('Y-m-01', strtotime('01-' . $request->input('bulan')));
        $tanggal_akhir = date('Y-m-t', strtotime('01-' . $request->input('bulan_akhir')));

        $request->merge(['export' => 1]);
        $data = $this->search($request);
        return Excel::download(new NeracaExport($data), 'neraca_'. $tanggal_awal .'_sd_'. $tanggal_akhir .'.xlsx');
    }
}
