<?php

namespace App\Http\Controllers;

use App\Exports\BigProdukExport;
use App\Exports\PenjualanExport;
use App\Exports\ProdukExport;
use App\Helpers\GlobalHelper;
use App\ItemPembelian;
use App\ItemPenjualan;
use App\ItemReturPembelian;
use App\ItemReturPenjualan;
use App\KategoriProduk;
use App\Penjualan;
use App\Produk;
use App\StokOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanStockController extends Controller
{
    public function index()
    {
        $list_kategori = KategoriProduk::where('parent_id', 0)->get();
        return view('pos.laporan.produk.index', compact('list_kategori'));
    }

    public function excel(Request $request)
    {
        $request = new Request($request->except('paginate', 'page'));
        $request->merge(['ajax' => 1]);
        $produk = $this->search($request);
        return Excel::download(new BigProdukExport($produk), 'produk.xlsx');
    }

    public function cetak(Request $request)
    {
        $request = new Request($request->except('paginate', 'page'));
        $request->merge(['ajax' => 1]);
        $produk = $this->search($request);
        return view('pos.laporan.produk.laporan', compact('produk'));
    }

    public function search(Request $request)
    {
        // produk
        $produk = Produk::select('*')->with(['kategori_produk', 'satuan_barang']);

        $search = $request->input('search') ?? '';
        if ($search !== '') {
            $produk = $produk->where(function ($i) use ($search) {
                $i->where('produk.nama_produk', 'like', "%{$search}%")
                    ->orWhere('produk.kode', 'like', "%{$search}%");
            });
        }

        $kode='';
        $kode .= ($request->kelompok=='all' || $request->kelompok=='' ? '' : $request->kelompok);
        $kode .= ($request->kategori=='all' || $request->kategori=='' ? '' : '.'.$request->kategori);
        $kode .= ($request->sub_kategori=='all' || $request->sub_kategori=='' ? '' : '.'.$request->sub_kategori);
        if ($kode !== '') $produk = $produk->where('kode_kategori','like', $kode . '%');
        $paginate = $request->input('paginate') ?? '';
        if ($paginate !== '') $produk = $produk->paginate($paginate);
        else $produk = $produk->get();

        $tanggal_awal = $request->input('tanggal_awal') ?? '';
        $tanggal_akhir = $request->input('tanggal_akhir') ?? '';
        $list_produk_id = $produk->pluck('id')->toArray();

        $data_stock = $this->get_data_stock($list_produk_id, $tanggal_awal, $tanggal_akhir);
        $data_stock_awal = ($tanggal_akhir !== '') ? $this->get_data_stock($list_produk_id, '1990-01-01', date('Y-m-d', strtotime('-1 days' . $tanggal_awal))) : [];

        foreach ($produk as $value) {
            $stok_awal_tambahan = (($data_stock_awal['pembelian'][$value->id] ?? 0) - ($data_stock_awal['retur_pembelian'][$value->id] ?? 0)) - (($data_stock_awal['penjualan'][$value->id] ?? 0) - ($data_stock_awal['retur_penjualan'][$value->id] ?? 0)) + ($data_stock_awal['penyesuaian'][$value->id] ?? 0);
            $sisa = $value->stok_awal + $stok_awal_tambahan + (($data_stock['pembelian'][$value->id] ?? 0) - ($data_stock['retur_pembelian'][$value->id] ?? 0)) - (($data_stock['penjualan'][$value->id] ?? 0) - ($data_stock['retur_penjualan'][$value->id] ?? 0)) + ($data_stock['penyesuaian'][$value->id] ?? 0);
            $stok = [
                'stok_awal' => $value->stok_awal + $stok_awal_tambahan,
                'pembelian' => ($data_stock['pembelian'][$value->id] ?? 0),
                'retur' => ($data_stock['retur_pembelian'][$value->id] ?? 0),
                'terjual' => (($data_stock['penjualan'][$value->id] ?? 0) - ($data_stock['retur_penjualan'][$value->id] ?? 0)),
                'penyesuaian' => ($data_stock['penyesuaian'][$value->id] ?? 0),
                'sisa' => $sisa,
            ];
            $value->stok = $stok;
        }

        if ($request->has('ajax')) return $produk;
        return view('pos.laporan.produk._table', compact('produk'));
    }

    public function get_data_stock($list_produk_id, $tanggal_awal, $tanggal_akhir)
    {
        // pembelian
        $pembelian = ItemPembelian::select('fid_produk', DB::raw('sum(jumlah) as jumlah'))
            ->whereHas('pembelian', function ($q) {
                $q->where('status', 1);
            })
            ->whereIn('fid_produk', $list_produk_id)->where('fid_pembelian', '<>', 0);
        if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
            $pembelian = $pembelian->whereHas('pembelian', function ($pembelian) use ($tanggal_awal, $tanggal_akhir) {
                if ($tanggal_awal !== '') $pembelian->where('tanggal', '>=', unformat_date($tanggal_awal));
                if ($tanggal_akhir !== '') $pembelian->where('tanggal', '<=', unformat_date($tanggal_akhir));
            });
        }
        $pembelian = $pembelian->groupBy('fid_produk')->get();
        $mapped_pembelian = [];
        foreach ($pembelian as $value) $mapped_pembelian[$value->fid_produk] = $value->jumlah;

        // retur pembelian
        $return_pembelian = ItemReturPembelian::select('fid_produk', DB::raw('sum(jumlah) as jumlah'))
            ->whereIn('fid_produk', $list_produk_id);
        if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
            $return_pembelian = $return_pembelian->whereHas('retur_pembelian', function ($pembelian) use ($tanggal_awal, $tanggal_akhir) {
                if ($tanggal_awal !== '') $pembelian->where('tanggal', '>=', unformat_date($tanggal_awal));
                if ($tanggal_akhir !== '') $pembelian->where('tanggal', '<=', unformat_date($tanggal_akhir));
            });
        }
        $return_pembelian = $return_pembelian->groupBy('fid_produk')->get();
        $mapped_retur_pembelian = [];
        foreach ($return_pembelian as $value) $mapped_retur_pembelian[$value->fid_produk] = $value->jumlah;

        // penjualan
        $penjualan = ItemPenjualan::select('fid_produk', DB::raw('sum(jumlah) as jumlah'))
            ->whereIn('item_penjualan.fid_produk', $list_produk_id)
            ->whereHas('penjualan', function ($penjualan) use ($tanggal_awal, $tanggal_akhir) {
                $penjualan->where('fid_status', 2);
                if ($tanggal_awal !== '') $penjualan->where('tanggal', '>=', unformat_date($tanggal_awal));
                if ($tanggal_akhir !== '') $penjualan->where('tanggal', '<=', unformat_date($tanggal_akhir));
            });
        $penjualan = $penjualan->groupBy('fid_produk')->get();
        $mapped_penjualan = [];
        foreach ($penjualan as $value) $mapped_penjualan[$value->fid_produk] = $value->jumlah;

        // retur penjualan
        $retur_penjualan = ItemReturPenjualan::select('fid_produk', DB::raw('sum(jumlah) as jumlah'))
            ->whereIn('fid_produk', $list_produk_id);
        if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
            $retur_penjualan = $retur_penjualan->whereHas('retur_penjualan', function ($penjualan) use ($tanggal_awal, $tanggal_akhir) {
                if ($tanggal_awal !== '') $penjualan->where('tanggal', '>=', unformat_date($tanggal_awal));
                if ($tanggal_akhir !== '') $penjualan->where('tanggal', '<=', unformat_date($tanggal_akhir));
            });
        }
        $retur_penjualan = $retur_penjualan->groupBy('fid_produk')->get();
        $mapped_retur_penjualan = [];
        foreach ($retur_penjualan as $value) $mapped_retur_penjualan[$value->fid_produk] = $value->jumlah;

        // penyesuaian
        $penyesuaian = StokOpname::select('fid_produk', DB::raw('sum(jumlah) as jumlah'));
        if ($tanggal_awal !== '') $penyesuaian->where('tanggal', '>=', unformat_date($tanggal_awal));
        if ($tanggal_akhir !== '') $penyesuaian->where('tanggal', '<=', unformat_date($tanggal_akhir));
        $penyesuaian = $penyesuaian->whereIn('fid_produk', $list_produk_id)->groupBy('fid_produk')->get();
        $mapped_penyesuaian = [];
        foreach ($penyesuaian as $value) $mapped_penyesuaian[$value->fid_produk] = $value->jumlah;

        return [
            'pembelian' => $mapped_pembelian,
            'penjualan' => $mapped_penjualan,
            'retur_pembelian' => $mapped_retur_pembelian,
            'retur_penjualan' => $mapped_retur_penjualan,
            'penyesuaian' => $mapped_penyesuaian,
        ];
    }

    public function stok_barang($id, $tanggal_awal = '', $tanggal_akhir = ''){
        $produk = Produk::find($id);
        if (empty($produk)) {
            return [
                'stok_awal' => 0,
                'pembelian' => 0,
                'retur' => 0,
                'terjual' => 0,
                'penyesuaian' => 0,
                'sisa' => 0,
            ];
        }


        $pembelian = ItemPembelian::where('fid_produk', $id)->where('fid_pembelian', '<>', 0);
        if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
            $pembelian = $pembelian->whereHas('pembelian', function ($pembelian) use ($tanggal_awal, $tanggal_akhir) {
                if ($tanggal_awal !== '') $pembelian->where('tanggal', '>=', $tanggal_awal);
                if ($tanggal_akhir !== '') $pembelian->where('tanggal', '<=', $tanggal_akhir);
            });
        }
        $pembelian = $pembelian->sum('jumlah');

        $return_pembelian = ItemReturPembelian::where('fid_produk', $id)->where('metode', 'Kembali Uang');
        if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
            $return_pembelian = $return_pembelian->whereHas('retur_pembelian', function ($pembelian) use ($tanggal_awal, $tanggal_akhir) {
                if ($tanggal_awal !== '') $pembelian->where('tanggal', '>=', $tanggal_awal);
                if ($tanggal_akhir !== '') $pembelian->where('tanggal', '<=', $tanggal_akhir);
            });
        }
        $return_pembelian = $return_pembelian->sum('jumlah');

        $terjual = ItemPenjualan::where('item_penjualan.fid_produk',$id)
            ->whereHas('penjualan', function ($penjualan) use ($tanggal_awal, $tanggal_akhir) {
                $penjualan->where('fid_status', 2);
                if ($tanggal_awal !== '') $penjualan->where('tanggal', '>=', $tanggal_awal);
                if ($tanggal_akhir !== '') $penjualan->where('tanggal', '<=', $tanggal_akhir);
            });
        $terjual = $terjual->sum('jumlah');

        $penyesuaian = StokOpname::where('fid_produk', $id)->sum('jumlah');

        $retur_penjualan = ItemReturPenjualan::where('fid_produk', $id);
        if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
            $retur_penjualan = $retur_penjualan->whereHas('retur_penjualan', function ($penjualan) use ($tanggal_awal, $tanggal_akhir) {
                if ($tanggal_awal !== '') $penjualan->where('tanggal', '>=', $tanggal_awal);
                if ($tanggal_akhir !== '') $penjualan->where('tanggal', '<=', $tanggal_akhir);
            });
        }
        $retur_penjualan = $retur_penjualan->sum('jumlah');

        $stok_awal_tambahan = 0;
        if ($tanggal_awal !== '') {
            $stok_awal = $this->stok_barang($id, '', date('Y-m-d', strtotime('-1 days' . $tanggal_awal)));
            $stok_awal_tambahan = $stok_awal['sisa'];
        }

        $sisa = $produk->stok_awal + $stok_awal_tambahan + ($pembelian - $return_pembelian) - ($terjual - $retur_penjualan) + $penyesuaian;

        return [
            'stok_awal' => $produk->stok_awal + $stok_awal_tambahan,
            'pembelian' => $pembelian,
            'retur' => $return_pembelian,
            'terjual' => ($terjual - $retur_penjualan),
            'penyesuaian' => $penyesuaian,
            'sisa' => $sisa,
        ];
    }
}
