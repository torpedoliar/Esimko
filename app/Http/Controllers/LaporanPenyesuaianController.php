<?php

namespace App\Http\Controllers;

use App\Exports\PenyesuaianExport;
use App\StokOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanPenyesuaianController extends Controller
{
    public function index()
    {
        $list_status = DB::table('status_belanja')->get();
        $list_metode = DB::table('metode_pembayaran')->get();
        return view('pos.laporan.penyesuaian.index', compact('list_status', 'list_metode'));
    }

    public function excel(Request $request)
    {
        $request = new Request($request->except('paginate', 'page'));
        $request->merge(['ajax' => 1]);
        $penyesuaian = $this->search($request);
        return Excel::download(new PenyesuaianExport($penyesuaian), 'penyesuaian.xlsx');
    }

    public function cetak(Request $request)
    {
        $request = new Request($request->except('paginate', 'page'));
        $request->merge(['ajax' => 1]);
        $penyesuaian = $this->search($request);
        return view('pos.laporan.penyesuaian.laporan', compact('penyesuaian'));
    }

    public function search(Request $request)
    {
        $penyesuaian = StokOpname::select('*');

        $fid_produk = $request->input('fid_produk') ?? '';
        if ($fid_produk !== '') $penyesuaian = $penyesuaian->where('fid_produk', $fid_produk);

        $tanggal_awal = $request->input('tanggal_awal') ?? '';
        if ($tanggal_awal !== '') $penyesuaian = $penyesuaian->where('tanggal', '>=', unformat_date($tanggal_awal));

        $tanggal_akhir = $request->input('tanggal_akhir') ?? '';
        if ($tanggal_akhir !== '') $penyesuaian = $penyesuaian->where('tanggal', '<=', unformat_date($tanggal_akhir));

        $paginate = $request->input('paginate') ?? '';
        if ($paginate !== '') $penyesuaian = $penyesuaian->paginate($paginate);
        else $penyesuaian = $penyesuaian->get();

        if ($request->has('ajax')) return $penyesuaian;
        return view('pos.laporan.penyesuaian._table', compact('penyesuaian'));
    }
}
