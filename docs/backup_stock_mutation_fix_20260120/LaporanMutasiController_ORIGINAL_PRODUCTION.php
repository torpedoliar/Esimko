<?php

namespace App\Http\Controllers;

use App\Exports\MutasiExport;
use App\ItemPembelian;
use App\ItemPenjualan;
use App\ItemReturPembelian;
use App\ItemReturPenjualan;
use App\StokOpname;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanMutasiController extends Controller
{
    public function index()
    {
        return view('pos.laporan.mutasi.index');
    }

    public function excel(Request $request)
    {
        $request->merge(['ajax' => 1]);
        $data = $this->search($request);
        return Excel::download(new MutasiExport($data['pembelian'], $data['penjualan'], $data['retur_pembelian'], $data['retur_penjualan'], $data['opname']), 'mutasi_stok.xlsx');
    }

    public function cetak(Request $request)
    {
        $request->merge(['ajax' => 1]);
        $data = $this->search($request);
        return view('pos.laporan.mutasi.laporan', $data);
    }

    public function search(Request $request)
    {
        if ($request->input('tanggal_awal') == '' || $request->input('tanggal_akhir') == '' || $request->input('fid_produk') == '') {
            return "<h1 class='text-center'>Pilih Tanggal Awal, Akhir dan Produk</h1>";
        }
        $fid_produk = $request->input('fid_produk');

        $pembelian = ItemPembelian::where('fid_produk', $fid_produk);
        $pembelian = $this->filterMutasi2($request, $pembelian, 'pembelian')->get();

        $retur_pembelian = ItemReturPembelian::where('fid_produk', $fid_produk);
        $retur_pembelian = $this->filterMutasi2($request, $retur_pembelian, 'retur_pembelian')->get();

        $penjualan = ItemPenjualan::where('fid_produk', $fid_produk);
        $penjualan = $this->filterMutasi2($request, $penjualan, 'penjualan')->get();

        $retur_penjualan = ItemReturPenjualan::where('fid_produk', $fid_produk);
        $retur_penjualan = $this->filterMutasi2($request, $retur_penjualan, 'retur_penjualan')->get();

        $opname = StokOpname::where('fid_produk', $fid_produk);
        $opname = $this->filterMutasi($request, $opname)->get();

        if ($request->has('ajax')) {
            return [
                'penjualan' => $penjualan,
                'pembelian' => $pembelian,
                'retur_pembelian' => $retur_pembelian,
                'retur_penjualan' => $retur_penjualan,
                'opname' => $opname,
            ];
        }
        return view('pos.laporan.mutasi._table', compact('pembelian', 'penjualan', 'opname', 'retur_pembelian', 'retur_penjualan'));
    }

    public function filterMutasi(Request $request, $model)
    {
        $tanggal_awal = $request->input('tanggal_awal') ?? '';
        if ($tanggal_awal !== '') $model = $model->where('tanggal', '>=', unformat_date($tanggal_awal));

        $tanggal_akhir = $request->input('tanggal_akhir') ?? '';
        if ($tanggal_akhir !== '') $model = $model->where('tanggal', '<=', unformat_date($tanggal_akhir));

        return $model;
    }

    public function filterMutasi2(Request $request, $model, $relation)
    {
        $tanggal_awal = $request->input('tanggal_awal') ?? '';
        $tanggal_akhir = $request->input('tanggal_akhir') ?? '';
        if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
            $model = $model->whereHas($relation, function ($q) use ($tanggal_awal, $tanggal_akhir) {
                $q->where('tanggal', '>=', unformat_date($tanggal_awal))->where('tanggal', '<=', unformat_date($tanggal_akhir));
            });
        }
        return $model;
    }
}
