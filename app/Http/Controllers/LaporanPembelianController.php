<?php

namespace App\Http\Controllers;

use App\Exports\PembelianExport;
use App\ItemPembelian;
use App\ItemReturPembelian;
use App\Pembelian;
use App\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class LaporanPembelianController extends Controller
{
    public function index()
    {
        $list_status = DB::table('status_belanja')->get();
        $list_metode = DB::table('metode_pembayaran')->get();
        $supplier = Supplier::all();
        return view('pos.laporan.pembelian.index', compact('list_status', 'list_metode', 'supplier'));
    }

    public function excel(Request $request)
    {
        $request = new Request($request->except('paginate', 'page'));
        $request->merge(['ajax' => 1]);
        $pembelian = $this->search($request);
        return Excel::download(new PembelianExport($pembelian, $request), 'pembelian.xlsx');

//        $request = new Request($request->except('paginate'));
//        $request->merge(['ajax' => 1]);
//        $pembelian = $this->search($request);
//        return view('pos.laporan.pembelian.excel', compact('pembelian', 'request'));
    }

    public function cetak(Request $request)
    {
        $request = new Request($request->except('paginate', 'page'));


        $request->merge(['ajax' => 1]);
        $pembelian = $this->search($request);
        return view('pos.laporan.pembelian.laporan', compact('pembelian', 'request'));
    }

    public function search(Request $request)
    {
        $pembelian = ItemPembelian::select('item_pembelian.*')
            ->join('pembelian', 'pembelian.id', '=', 'item_pembelian.fid_pembelian')
            ->with(['pembelian', 'produk'])
            ->orderBy('fid_supplier')
            ->orderBy('id', 'desc');

        $kode='';
        $kode .= ($request->kelompok=='all' || $request->kelompok=='' ? '' : $request->kelompok);
        $kode .= ($request->kategori=='all' || $request->kategori=='' ? '' : '.'.$request->kategori);
        $kode .= ($request->sub_kategori=='all' || $request->sub_kategori=='' ? '' : '.'.$request->sub_kategori);

        if ($kode !== '') $pembelian->wherehas('produk', function ($produk) use ($kode) {
            $produk->where('produk.kode_kategori','like', $kode . '%');
        });

        $fid_supplier = $request->input('fid_supplier') ?? '';
        if ($fid_supplier !== '') {
            $pembelian = $pembelian->whereHas('pembelian', function ($pembelian) use ($fid_supplier) {
                $pembelian->where('fid_supplier', $fid_supplier);
            });
        }

        $tanggal_awal = $request->input('tanggal_awal') ?? '';
        $tanggal_akhir = $request->input('tanggal_akhir') ?? '';
        if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
            $pembelian = $pembelian->whereHas('pembelian', function ($pembelian) use ($tanggal_awal, $tanggal_akhir) {
                if ($tanggal_awal !== '') $pembelian->where('tanggal', '>=', unformat_date($tanggal_awal));
                if ($tanggal_akhir !== '') $pembelian->where('tanggal', '<=', unformat_date($tanggal_akhir));
            });
        }

        $paginate = $request->input('paginate') ?? '';
        if ($paginate !== '') $pembelian = $pembelian->paginate($paginate);
        else $pembelian = $pembelian->get();

        $returPembelian = ItemReturPembelian::whereIn('fid_produk', $pembelian->pluck('fid_produk')->toArray())
            ->whereHas('retur_pembelian', function ($retur) use ($pembelian) {
                $retur->whereIn('fid_pembelian', $pembelian->pluck('fid_pembelian')->toArray());
            })
            ->with(['retur_pembelian'])
            ->get();
        $mapped_retur = [];
        foreach ($returPembelian as $value) {
            $key = $value->retur_pembelian->fid_pembelian.'_'.$value->fid_produk;
            if (empty($mapped_retur[$key])) $mapped_retur[$key] = 0;
            $mapped_retur[$key] += $value->jumlah;
        }

        foreach ($pembelian as $value) {
            $retur = $mapped_retur[$value->fid_pembelian.'_'.$value->fid_produk] ?? 0;
            $value->jumlah -= $retur;
            $value->total = $value->jumlah * $value->harga;
        }

        if ($request->has('ajax')) return $pembelian;
        return view('pos.laporan.pembelian._table', compact('pembelian'));
    }
}
