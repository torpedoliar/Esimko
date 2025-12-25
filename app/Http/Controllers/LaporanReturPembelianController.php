<?php

namespace App\Http\Controllers;

use App\Exports\ReturPembelianExport;
use App\ItemReturPembelian;
use App\ReturPembelian;
use App\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class LaporanReturPembelianController extends Controller
{
    public function index()
    {
        $list_status = DB::table('status_belanja')->get();
        $list_metode = DB::table('metode_pembayaran')->get();
        $supplier = Supplier::all();
        return view('pos.laporan.retur_pembelian.index', compact('list_status', 'list_metode', 'supplier'));
    }

    public function excel(Request $request)
    {
        $request = new Request($request->except('paginate', 'page'));
        $request->merge(['ajax' => 1]);
        $retur_pembelian = $this->search($request);
        return Excel::download(new ReturPembelianExport($retur_pembelian, $request), 'retur_pembelian.xlsx');
    }

    public function cetak(Request $request)
    {
        $request = new Request($request->except('paginate', 'page'));
        $request->merge(['ajax' => 1]);
        $retur_pembelian = $this->search($request);
        return view('pos.laporan.retur_pembelian.laporan', compact('retur_pembelian', 'request'));
    }

    public function search(Request $request)
    {
        $retur_pembelian = ItemReturPembelian::select('item_retur_pembelian.*')->with(['retur_pembelian', 'produk'])
            ->join('retur_pembelian', 'retur_pembelian.id', '=', 'item_retur_pembelian.fid_retur_pembelian')
            ->orderBy('fid_supplier')
            ->orderBy('id', 'desc');


        $kode='';
        $kode .= ($request->kelompok=='all' || $request->kelompok=='' ? '' : $request->kelompok);
        $kode .= ($request->kategori=='all' || $request->kategori=='' ? '' : '.'.$request->kategori);
        $kode .= ($request->sub_kategori=='all' || $request->sub_kategori=='' ? '' : '.'.$request->sub_kategori);

        if ($kode !== '') $retur_pembelian->wherehas('produk', function ($produk) use ($kode) {
            $produk->where('produk.kode_kategori','like', $kode . '%');
        });

        $fid_supplier = $request->input('fid_supplier') ?? '';
        if ($fid_supplier !== '') {
            $retur_pembelian = $retur_pembelian->whereHas('retur_pembelian', function ($pembelian) use ($fid_supplier) {
                $pembelian->where('fid_supplier', $fid_supplier);
            });
        }

        $tanggal_awal = $request->input('tanggal_awal') ?? '';
        $tanggal_akhir = $request->input('tanggal_akhir') ?? '';
        if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
            $retur_pembelian = $retur_pembelian->whereHas('retur_pembelian', function ($retur_pembelian) use ($tanggal_awal, $tanggal_akhir) {
                if ($tanggal_awal !== '') $retur_pembelian->where('tanggal', '>=', unformat_date($tanggal_awal));
                if ($tanggal_akhir !== '') $retur_pembelian->where('tanggal', '<=', unformat_date($tanggal_akhir));
            });
        }

        $paginate = $request->input('paginate') ?? '';
        if ($paginate !== '') $retur_pembelian = $retur_pembelian->paginate($paginate);
        else $retur_pembelian = $retur_pembelian->get();

        if ($request->has('ajax')) return $retur_pembelian;
        return view('pos.laporan.retur_pembelian._table', compact('retur_pembelian'));
    }
}
