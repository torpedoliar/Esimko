<?php

namespace App\Http\Controllers;

use App\Exports\ReturPenjualanExport;
use App\ItemReturPenjualan;
use App\ReturPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class LaporanReturPenjualanController extends Controller
{
    public function index()
    {
        $list_status = DB::table('status_belanja')->get();
        $list_metode = DB::table('metode_pembayaran')->get();
        $metode = DB::table('rekening_pembayaran')->where('jenis_transaksi','like','%belanja%')->get();
        return view('pos.laporan.retur_penjualan.index', compact('list_status', 'list_metode', 'metode'));
    }

    public function excel(Request $request)
    {
        $request = new Request($request->except('paginate', 'page'));
        $request->merge(['ajax' => 1]);
        $retur_penjualan = $this->search($request);
        return Excel::download(new ReturPenjualanExport($retur_penjualan, $request), 'retur_penjualan.xlsx');
    }

    public function cetak(Request $request)
    {
        $request = new Request($request->except('paginate', 'page'));
        $request->merge(['ajax' => 1]);
        $retur_penjualan = $this->search($request);
        return view('pos.laporan.retur_penjualan.laporan', compact('retur_penjualan', 'request'));
    }

    public function search(Request $request)
    {
        $retur_penjualan = ItemReturPenjualan::select('*')->with(['retur_penjualan', 'produk'])  ->orderBy('id', 'desc');

        $kode='';
        $kode .= ($request->kelompok=='all' || $request->kelompok=='' ? '' : $request->kelompok);
        $kode .= ($request->kategori=='all' || $request->kategori=='' ? '' : '.'.$request->kategori);
        $kode .= ($request->sub_kategori=='all' || $request->sub_kategori=='' ? '' : '.'.$request->sub_kategori);

        if ($kode !== '') $retur_penjualan->wherehas('produk', function ($produk) use ($kode) {
            $produk->where('produk.kode_kategori','like', $kode . '%');
        });

        $no_anggota = $request->input('no_anggota') ?? '';
        $no_anggota = str_replace(' ', '', $no_anggota);
        if ($no_anggota !== '') {
            $retur_penjualan = $retur_penjualan->whereHas('retur_penjualan.anggota', function ($anggota) use ($no_anggota) {
                $anggota->whereRaw("REPLACE(no_anggota, ' ', '') = '$no_anggota'");
            });
        }

        $tanggal_awal = $request->input('tanggal_awal') ?? '';
        $tanggal_akhir = $request->input('tanggal_akhir') ?? '';
        if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
            $retur_penjualan = $retur_penjualan->whereHas('retur_penjualan', function ($retur_penjualan) use ($tanggal_awal, $tanggal_akhir) {
                if ($tanggal_awal !== '') $retur_penjualan->where('tanggal', '>=', unformat_date($tanggal_awal));
                if ($tanggal_akhir !== '') $retur_penjualan->where('tanggal', '<=', unformat_date($tanggal_akhir));
            });
        }

        $metode_pembayaran = $request->input('metode_pembayaran') ?? '';
        if ($metode_pembayaran !== '') {
            $retur_penjualan = $retur_penjualan->whereHas('retur_penjualan.penjualan', function ($penjualan) use ($metode_pembayaran) {
                $penjualan->where('fid_metode_pembayaran', $metode_pembayaran);
            });
        }

        $paginate = $request->input('paginate') ?? '';
        if ($paginate !== '') $retur_penjualan = $retur_penjualan->paginate($paginate);
        else $retur_penjualan = $retur_penjualan->get();

        if ($request->has('ajax')) return $retur_penjualan;
        return view('pos.laporan.retur_penjualan._table', compact('retur_penjualan'));
    }
}
