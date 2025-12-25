<?php

namespace App\Http\Controllers;

use App\Exports\JurnalExport;
use App\Jurnal;
use App\JurnalDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class JurnalController extends Controller
{
    public function index()
    {
        return view('keuangan.jurnal.index');
    }

    public function search(Request $request)
    {
        $tanggal_awal = date('Y-m-01', strtotime('01-' . $request->input('bulan')));
        $tanggal_akhir = date('Y-m-t', strtotime('01-' . $request->input('bulan_akhir')));

        $jurnal = Jurnal::where('tanggal', '>=', $tanggal_awal)->where('tanggal', '<=', $tanggal_akhir)
            ->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->paginate(10);
        return view('keuangan.jurnal._table', compact('jurnal'));
    }

    public function create()
    {
        return view('keuangan.jurnal._info');
    }

    public function store(Request $request, Jurnal $jurnal)
    {
        $request->merge(['tanggal' => unformat_date($request->input('tanggal'))]);
        $request->merge(['jurnal_id' => $jurnal->id]);
        return Jurnal::create($request->all());
    }

    public function edit(Jurnal $jurnal, JurnalDetail $jurnalDetail)
    {
        return view('keuangan.jurnal._info', compact('jurnal', 'jurnalDetail'));
    }

    public function update(Request $request, Jurnal $jurnal)
    {
        $request->merge(['tanggal' => unformat_date($request->input('tanggal'))]);
        $jurnal->update($request->all());
        return $jurnal;
    }

    public function destroy(Jurnal $jurnal)
    {
        $jurnal->delete();
        return $jurnal;
    }

    public function export(Request $request)
    {
        $tanggal_awal = date('Y-m-01', strtotime('01-' . $request->input('bulan')));
        $tanggal_akhir = date('Y-m-t', strtotime('01-' . $request->input('bulan_akhir')));

        $jurnals = Jurnal::where('tanggal', '>=', $tanggal_awal)->where('tanggal', '<=', $tanggal_akhir)->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();
        return Excel::download(new JurnalExport($jurnals), 'jurnal_'. $tanggal_awal .'_sd_'. $tanggal_akhir .'.xlsx');
    }
}
