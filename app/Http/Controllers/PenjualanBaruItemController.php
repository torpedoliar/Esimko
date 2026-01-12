<?php

namespace App\Http\Controllers;

use App\Helpers\GlobalHelper;
use App\ItemPenjualan;
use App\Produk;
use Illuminate\Http\Request;
use DB;

class PenjualanBaruItemController extends Controller
{
    /**
     * Optimized stock check - returns stok data for reuse
     */
    private function get_stok_cepat($produk_id)
    {
        // Single optimized query instead of multiple queries
        $produk = Produk::select('id', 'stok_awal')->find($produk_id);
        if (empty($produk)) return ['sisa' => 0];
        
        // Batch query for stock calculation
        $pembelian = DB::table('item_pembelian')
            ->join('pembelian', 'pembelian.id', 'item_pembelian.fid_pembelian')
            ->where('item_pembelian.fid_produk', $produk_id)
            ->where('pembelian.status', 1)
            ->where('item_pembelian.fid_pembelian', '<>', 0)
            ->sum('item_pembelian.jumlah');
            
        $retur_pembelian = DB::table('item_retur_pembelian')
            ->where('fid_produk', $produk_id)
            ->sum('jumlah');
            
        $terjual = DB::table('item_penjualan')
            ->join('penjualan', 'penjualan.id', 'item_penjualan.fid_penjualan')
            ->where('item_penjualan.fid_produk', $produk_id)
            ->where('penjualan.fid_status', 2)
            ->sum('item_penjualan.jumlah');
            
        $retur_penjualan = DB::table('item_retur_penjualan')
            ->where('fid_produk', $produk_id)
            ->sum('jumlah');
            
        $penyesuaian = DB::table('stok_opname')
            ->where('fid_produk', $produk_id)
            ->sum('jumlah');
        
        $sisa = $produk->stok_awal + ($pembelian - $retur_pembelian) - ($terjual - $retur_penjualan) + $penyesuaian;
        
        return ['sisa' => $sisa];
    }

    public function create(Request $request)
    {
        // Single query with necessary fields only
        $produk = Produk::select('id', 'kode', 'nama_produk', 'harga_beli', 'harga_jual', 'margin', 'margin_nominal', 'stok_awal')
            ->where('kode', $request->input('kode'))
            ->first();
        
        if (empty($produk)) {
            return ['error' => 'Kode barang tidak ditemukan'];
        }
        
        $jumlah = unformat_number($request->input('jumlah'));
        
        // Single stock check (not 3 times like before)
        $stok = $this->get_stok_cepat($produk->id);
        if ($jumlah > $stok['sisa']) {
            return ['error' => 'Stok barang tidak mencukupi! Sisa stok : ' . $stok['sisa']];
        }

        $diskon = unformat_number($request->input('diskon'));
        
        // Check if product already in cart
        $check = ItemPenjualan::where('fid_penjualan', $request->input('fid_penjualan'))
            ->where('fid_produk', $produk->id)
            ->first();
            
        if (empty($check)) {
            return ItemPenjualan::create([
                'fid_penjualan' => $request->input('fid_penjualan'),
                'fid_produk' => $produk->id,
                'harga_beli' => $produk->harga_beli,
                'harga' => $produk->harga_jual,
                'margin' => $produk->margin,
                'margin_nominal' => $produk->margin_nominal,
                'jumlah' => $jumlah,
                'diskon' => $diskon,
                'total' => $produk->harga_jual * $jumlah - $diskon
            ]);
        } else {
            // Update existing item
            $new_jumlah = $check->jumlah + 1;
            if ($new_jumlah > $stok['sisa']) {
                return ['error' => 'Stok barang tidak mencukupi! Sisa stok : ' . $stok['sisa']];
            }
            $check->update([
                'jumlah' => $new_jumlah,
                'total' => $check->harga * $new_jumlah - $check->diskon
            ]);
            return $check;
        }
    }

    public function update(Request $request, $id)
    {
        $item_penjualan = ItemPenjualan::find($id);
        if (empty($item_penjualan)) {
            return ['error' => 'Item tidak ditemukan'];
        }
        
        $jumlah = unformat_number($request->input('jumlah'));
        
        // Quick stock check
        $stok = $this->get_stok_cepat($item_penjualan->fid_produk);
        if ($jumlah > $stok['sisa']) {
            return ['error' => 'Stok barang tidak mencukupi! Sisa: ' . $stok['sisa']];
        }

        $diskon = unformat_number($request->input('diskon'));
        $item_penjualan->update([
            'jumlah' => $jumlah,
            'diskon' => $diskon,
            'total' => $item_penjualan->harga * $jumlah - $diskon
        ]);
        return $item_penjualan;
    }

    public function delete($id)
    {
        $item_penjualan = ItemPenjualan::find($id);
        if ($item_penjualan) {
            $item_penjualan->delete();
        }
        return $item_penjualan;
    }

    public function search($id)
    {
        // Eager load produk with foto to avoid N+1 queries
        $items = ItemPenjualan::with(['produk' => function($q) {
                $q->select('id', 'kode', 'nama_produk')
                  ->with(['foto' => function($f) {
                      $f->select('id', 'fid_produk', 'foto')->limit(1);
                  }]);
            }])
            ->where('fid_penjualan', $id)
            ->orderBy('id', 'desc')
            ->get();
            
        return view('pos.penjualan_baru._items', compact('items'));
    }
}

