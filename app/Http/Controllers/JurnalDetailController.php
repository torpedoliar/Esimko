<?php

namespace App\Http\Controllers;

use App\Akun;
use App\Jurnal;
use App\JurnalDetail;
use Illuminate\Http\Request;

class JurnalDetailController extends Controller
{
    public function index(Jurnal $jurnal)
    {
        $akun = Akun::orderBy('kode_tampil')->get();
        return view('keuangan.jurnal.detail._index', compact('jurnal', 'akun'));
    }

    public function store(Request $request, Jurnal $jurnal)
    {
        $request->merge(['jurnal_id' => $jurnal->id]);
        $debit = unformat_number($request->input('debit') ?? '0');
        $kredit = unformat_number($request->input('kredit') ?? '0');
        $request->merge(['nominal' => ($debit - $kredit)]);
        return JurnalDetail::create($request->all());
    }

    public function destroy(Jurnal $jurnal, $jurnal_detail_id)
    {
        $jurnalDetail = JurnalDetail::find($jurnal_detail_id);
        $jurnalDetail->delete();
        return $jurnalDetail;
    }
}
