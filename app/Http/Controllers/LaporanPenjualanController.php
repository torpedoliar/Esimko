<?php

namespace App\Http\Controllers;

use App\Exports\PenjualanExport;
use App\ItemPenjualan;
use App\ItemReturPenjualan;
use App\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class LaporanPenjualanController extends Controller
{
    public function index()
    {
        $list_status = DB::table('status_belanja')->get();
        $list_metode = DB::table('metode_pembayaran')->get();
        $metode = DB::table('rekening_pembayaran')->where('jenis_transaksi','like','%belanja%')->get();
        return view('pos.laporan.penjualan.index', compact('list_status', 'list_metode', 'metode'));
    }

    public function excel(Request $request)
    {
        ini_set('memory_limit', '-1');
        $request = new Request($request->except('paginate', 'page'));
        $request->merge(['ajax' => 1]);
        $penjualan = $this->search($request);
        $params = $request->all();
        return Excel::download(new PenjualanExport($penjualan, $params), 'penjualan.xlsx');
    }

    public function cetak(Request $request)
    {
        $request = new Request($request->except('paginate', 'page'));
        $request->merge(['ajax' => 1]);
        $penjualan = $this->search($request);

        return view('pos.laporan.penjualan.laporan', compact('penjualan', 'request'));
    }

    public function search(Request $request)
    {
        $penjualan = ItemPenjualan::select('item_penjualan.*')
            ->join('penjualan', 'penjualan.id', '=', 'item_penjualan.fid_penjualan')
            ->with(['penjualan', 'produk'])
            ->orderBy('fid_anggota')
            ->orderBy('penjualan.no_transaksi')
            ->orderBy('id', 'desc');

        $jenis_penjualan = $request->input('jenis_penjualan') ?? '';
        if ($jenis_penjualan == 'Konsinyasi') $penjualan = $penjualan->whereNotNull('nama_barang');
        if ($jenis_penjualan == 'Belanja Langsung') $penjualan = $penjualan->whereNull('nama_barang');

        $jenis_belanja = $request->input('jenis_belanja') ?? '';
        if ($jenis_belanja != '') {
            $penjualan = $penjualan->where('penjualan.jenis_belanja', $jenis_belanja)->where('penjualan.fid_status', 4);
        }
        else {
            $penjualan = $penjualan->where('penjualan.fid_status', 2);
        }
//        dd($penjualan->toSql());

        $nama_barang = $request->input('nama_barang') ?? '';
        if ($nama_barang !== '') {
            $penjualan = $penjualan->whereHas('produk', function ($produk) use ($nama_barang) {
                return $produk->where('nama_produk', 'like', "%$nama_barang%");
            });
        }

        $no_penjualan = $request->input('no_penjualan') ?? '';
        if ($no_penjualan !== '') {
            $penjualan = $penjualan->wherehas('penjualan', function ($penjualan) use ($no_penjualan) {
                $penjualan->where('no_transaksi', $no_penjualan);
            });
        }

        $kode='';
        $kode .= ($request->kelompok=='all' || $request->kelompok=='' ? '' : $request->kelompok);
        $kode .= ($request->kategori=='all' || $request->kategori=='' ? '' : '.'.$request->kategori);
        $kode .= ($request->sub_kategori=='all' || $request->sub_kategori=='' ? '' : '.'.$request->sub_kategori);

        if ($kode !== '') $penjualan->wherehas('produk', function ($produk) use ($kode) {
            $produk->where('produk.kode_kategori','like', $kode . '%');
        });

        $no_anggota = $request->input('no_anggota') ?? '';
        $no_anggota = str_replace(' ', '', $no_anggota);
        if ($no_anggota !== '') {
            $penjualan->whereHas('penjualan.anggota', function ($anggota) use ($no_anggota) {
                $anggota->whereRaw("REPLACE(no_anggota, ' ', '') = '$no_anggota'");
            });
        }

        $tanggal_awal = $request->input('tanggal_awal') ?? '';
        $tanggal_akhir = $request->input('tanggal_akhir') ?? '';
        if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
            $penjualan = $penjualan->whereHas('penjualan', function ($penjualan) use ($tanggal_awal, $tanggal_akhir) {
                if ($tanggal_awal !== '') $penjualan->where('tanggal', '>=', unformat_date($tanggal_awal));
                if ($tanggal_akhir !== '') $penjualan->where('tanggal', '<=', unformat_date($tanggal_akhir));
            });
        }

        $metode_pembayaran = $request->input('metode_pembayaran') ?? '';
        if ($metode_pembayaran !== '') {
            $penjualan = $penjualan->whereHas('penjualan', function ($penjualan) use ($metode_pembayaran) {
                $penjualan->where('fid_metode_pembayaran', $metode_pembayaran);
            });
        }

        $paginate = $request->input('paginate') ?? '';
        if ($paginate !== '') $penjualan = $penjualan->paginate($paginate);
        else $penjualan = $penjualan->get();

        $returPenjualan = ItemReturPenjualan::whereIn('fid_produk', $penjualan->pluck('fid_produk')->toArray())
            ->whereHas('retur_penjualan', function ($retur) use ($penjualan) {
                $retur->whereIn('fid_penjualan', $penjualan->pluck('fid_penjualan')->toArray());
            })
            ->with(['retur_penjualan'])
            ->get();
        $mapped_retur = [];
        foreach ($returPenjualan as $value) {
            $key = $value->retur_penjualan->fid_penjualan.'_'.$value->fid_produk;
            if (empty($mapped_retur[$key])) $mapped_retur[$key] = 0;
            $mapped_retur[$key] += $value->jumlah;
        }

        foreach ($penjualan as $value) {
            $retur = $mapped_retur[$value->fid_penjualan.'_'.$value->fid_produk] ?? 0;
            $value->jumlah -= $retur;
            $value->total = $value->jumlah * $value->harga;
        }


        if ($request->has('ajax')) return $penjualan;
        return view('pos.laporan.penjualan._table', compact('penjualan'));
    }
}
