<?php

namespace App\Http\Controllers;

use App\Produk;
use App\StokOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\GlobalHelper;

class StokOpnameController extends Controller
{
    public function index(Request $request)
    {
        $halaman = $request->input('page') ?? '1';
        return view('manajemen_stok.stok_opname.index', compact('halaman'));
    }

    public function search(Request $request)
    {
        $stokOpname = StokOpname::select('*');

        $fid_produk = $request->input('fid_produk') ?? '';
        if ($fid_produk !== '') $stokOpname = $stokOpname->where('fid_produk', $fid_produk);

        $nama = $request->input('nama') ?? '';
        if ($nama !== '') $stokOpname = $stokOpname->whereHas('produk', function ($produk) use ($nama) {
            $produk->where('nama_produk', 'like', '%'. $nama .'%');
        });

        $tanggal_awal = $request->input('tanggal_awal') ?? '';
        if ($tanggal_awal !== '') $stokOpname = $stokOpname->where('tanggal', '>=', $tanggal_awal);

        $tanggal_akhir = $request->input('tanggal_akhir') ?? '';
        if ($tanggal_akhir !== '') $stokOpname = $stokOpname->where('tanggal', '<=', $tanggal_akhir);

        $paginate = $request->input('paginate') ?? '';
        if ($paginate !== '') $stokOpname = $stokOpname->paginate($paginate);
        else $stokOpname = $stokOpname->get();

        if ($request->has('ajax')) return $stokOpname;
        return view('manajemen_stok.stok_opname._table', compact('stokOpname'));
    }

    public function create()
    {
        $page = 1;
        return view('manajemen_stok.stok_opname.info', compact('page'));
    }

    public function store(Request $request)
    {
        $request->merge(['tanggal' => date('Y-m-d', strtotime($request->input('tanggal')))]);
        $request->merge(['hpp' => unformat_number($request->input('hpp'))]);
        StokOpname::create($request->all());
        return redirect('manajemen_stok/stok_opname');
    }

    public function edit(Request $request, $id)
    {
        $stokOpname = StokOpname::find($id);
        $page = $request->input('page');
        return view('manajemen_stok.stok_opname.info', compact('stokOpname', 'page'));
    }

    public function update(Request $request, $id)
    {
        $request->merge(['tanggal' => date('Y-m-d', strtotime($request->input('tanggal')))]);
        $request->merge(['hpp' => unformat_number($request->input('hpp'))]);
        StokOpname::find($id)->update($request->all());
        return redirect('manajemen_stok/stok_opname?page=' . $request->input('page'));
    }

    public function destroy(Request $request, $id)
    {
        StokOpname::where('id', $id)->delete();
        return redirect('manajemen_stok/stok_opname?page=' . $request->input('page'));
    }

    public function search_produk(Request $request)
    {
        $produk = Produk::where('kode', $request->input('kode'))->first();
        $produk->stok = GlobalHelper::stok_barang($produk->id);
        return $produk;
    }
}
