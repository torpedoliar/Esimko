<?php

namespace App\Http\Controllers;

use App\Helpers\GlobalHelper;
use App\ItemPenjualan;
use App\Produk;
use Illuminate\Http\Request;

class PenjualanBaruItemController extends Controller
{
    public function check_stock($kode, $jumlah)
    {
        $product = Produk::where('kode', $kode)->first();
        $stok = GlobalHelper::stok_barang($product->id);
        return $jumlah <= $stok['sisa'];
    }

    public function create(Request $request)
    {
        $produk = Produk::where('kode', $request->input('kode'))->first();
        $stok = GlobalHelper::stok_barang($produk->id ?? '');
        $jumlah = unformat_number($request->input('jumlah'));
        $check_stock = $this->check_stock($request->input('kode'), $jumlah);
        if ($check_stock === false) return ['error' => 'Stok barang tidak mencukupi! Sisa stok : ' . ($stok['sisa'] ?? 0)];

        if (!empty($produk)) {
            $diskon = unformat_number($request->input('diskon'));
            $request->merge(['fid_produk' => $produk->id]);
            $request->merge(['harga_beli' => $produk->harga_beli]);
            $request->merge(['harga' => $produk->harga_jual]);
            $request->merge(['margin' => $produk->margin]);
            $request->merge(['margin_nominal' => $produk->margin_nominal]);
            $request->merge(['total' => $produk->harga_jual * $jumlah - $diskon]);
            $request->merge(['diskon' => $diskon]);
            $check = ItemPenjualan::where('fid_penjualan', $request->input('fid_penjualan'))
                ->where('fid_produk', $produk->id)
                ->first();
            if (empty($check)) {
                return ItemPenjualan::create($request->all());
            } else {
                $jumlah = ($check->jumlah + 1);
                $diskon = $check->diskon;
                ItemPenjualan::find($check->id)->update([
                    'jumlah' => $jumlah,
                    'total' => $check->harga * $jumlah - $diskon
                ]);
                return $check;
            }
        } else {
            return ['error' => 'Kode barang tidak ditemukan'];
        }
    }

    public function update(Request $request, $id)
    {
        $item_penjualan = ItemPenjualan::find($id);
        $jumlah = unformat_number($request->input('jumlah'));

        $stok = GlobalHelper::stok_barang($item_penjualan->fid_produk);
        if (($stok['sisa'] - $jumlah) < 0) return ['error' => 'Stok barang tidak mencukupi!'];

        $diskon = unformat_number($request->input('diskon'));
        $request->merge(['total' => $item_penjualan->harga * $jumlah - $diskon]);
        $request->merge(['diskon' => $diskon]);
        $item_penjualan->update($request->all());
        return $item_penjualan;
    }

    public function delete($id)
    {
        $item_penjualan = ItemPenjualan::find($id);
        $item_penjualan->delete();
        return $item_penjualan;
    }

    public function search($id)
    {
        $items = ItemPenjualan::where('fid_penjualan', $id)->orderBy('id', 'desc')->get();
        return view('pos.penjualan_baru._items', compact('items'));
    }


}
