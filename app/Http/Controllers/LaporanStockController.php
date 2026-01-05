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
        if (empty($list_produk_id)) {
            return [
                'pembelian' => [],
                'penjualan' => [],
                'retur_pembelian' => [],
                'retur_penjualan' => [],
                'penyesuaian' => [],
            ];
        }

        // Format dates
        $tgl_awal = ($tanggal_awal !== '') ? unformat_date($tanggal_awal) : null;
        $tgl_akhir = ($tanggal_akhir !== '') ? unformat_date($tanggal_akhir) : null;

        // Pembelian - using JOIN instead of whereHas (much faster)
        $pembelianQuery = ItemPembelian::select('item_pembelian.fid_produk', DB::raw('SUM(item_pembelian.jumlah) as jumlah'))
            ->join('pembelian', 'item_pembelian.fid_pembelian', '=', 'pembelian.id')
            ->where('pembelian.status', 1)
            ->where('item_pembelian.fid_pembelian', '<>', 0)
            ->whereIn('item_pembelian.fid_produk', $list_produk_id);
        if ($tgl_awal && $tgl_akhir) {
            $pembelianQuery->whereBetween('pembelian.tanggal', [$tgl_awal, $tgl_akhir]);
        }
        $pembelian = $pembelianQuery->groupBy('item_pembelian.fid_produk')->get();
        $mapped_pembelian = $pembelian->pluck('jumlah', 'fid_produk')->toArray();

        // Retur Pembelian - using JOIN
        $returPembelianQuery = ItemReturPembelian::select('item_retur_pembelian.fid_produk', DB::raw('SUM(item_retur_pembelian.jumlah) as jumlah'))
            ->whereIn('item_retur_pembelian.fid_produk', $list_produk_id);
        if ($tgl_awal && $tgl_akhir) {
            $returPembelianQuery->join('retur_pembelian', 'item_retur_pembelian.fid_retur_pembelian', '=', 'retur_pembelian.id')
                ->whereBetween('retur_pembelian.tanggal', [$tgl_awal, $tgl_akhir]);
        }
        $return_pembelian = $returPembelianQuery->groupBy('item_retur_pembelian.fid_produk')->get();
        $mapped_retur_pembelian = $return_pembelian->pluck('jumlah', 'fid_produk')->toArray();

        // Penjualan - using JOIN
        $penjualanQuery = ItemPenjualan::select('item_penjualan.fid_produk', DB::raw('SUM(item_penjualan.jumlah) as jumlah'))
            ->join('penjualan', 'item_penjualan.fid_penjualan', '=', 'penjualan.id')
            ->where('penjualan.fid_status', 2)
            ->whereIn('item_penjualan.fid_produk', $list_produk_id);
        if ($tgl_awal && $tgl_akhir) {
            $penjualanQuery->whereBetween('penjualan.tanggal', [$tgl_awal, $tgl_akhir]);
        }
        $penjualan = $penjualanQuery->groupBy('item_penjualan.fid_produk')->get();
        $mapped_penjualan = $penjualan->pluck('jumlah', 'fid_produk')->toArray();

        // Retur Penjualan - using JOIN
        $returPenjualanQuery = ItemReturPenjualan::select('item_retur_penjualan.fid_produk', DB::raw('SUM(item_retur_penjualan.jumlah) as jumlah'))
            ->whereIn('item_retur_penjualan.fid_produk', $list_produk_id);
        if ($tgl_awal && $tgl_akhir) {
            $returPenjualanQuery->join('retur_penjualan', 'item_retur_penjualan.fid_retur_penjualan', '=', 'retur_penjualan.id')
                ->whereBetween('retur_penjualan.tanggal', [$tgl_awal, $tgl_akhir]);
        }
        $retur_penjualan = $returPenjualanQuery->groupBy('item_retur_penjualan.fid_produk')->get();
        $mapped_retur_penjualan = $retur_penjualan->pluck('jumlah', 'fid_produk')->toArray();

        // Penyesuaian (Stok Opname)
        $penyesuaianQuery = StokOpname::select('fid_produk', DB::raw('SUM(jumlah) as jumlah'))
            ->whereIn('fid_produk', $list_produk_id);
        if ($tgl_awal) $penyesuaianQuery->where('tanggal', '>=', $tgl_awal);
        if ($tgl_akhir) $penyesuaianQuery->where('tanggal', '<=', $tgl_akhir);
        $penyesuaian = $penyesuaianQuery->groupBy('fid_produk')->get();
        $mapped_penyesuaian = $penyesuaian->pluck('jumlah', 'fid_produk')->toArray();

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
