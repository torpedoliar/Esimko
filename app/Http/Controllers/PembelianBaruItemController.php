<?php

namespace App\Http\Controllers;

use App\Helpers\GlobalHelper;
use App\ItemPembelian;
use App\Produk;
use App\SatuanBarang;
use App\Supplier;
use Illuminate\Http\Request;

class PembelianBaruItemController extends Controller
{
    public function create(Request $request)
    {
        $produk = Produk::where('kode', $request->input('kode'))->first();
        if (!empty($produk)) {
            $request->merge(['fid_produk' => $produk->id]);
            $request->merge(['harga_jual' => $produk->harga_jual]);
            $request->merge(['harga' => $produk->harga_beli]);
            $request->merge(['margin' => $produk->margin]);
            $request->merge(['margin_nominal' => $produk->margin_nominal]);
            $request->merge(['total' => $produk->harga_beli * unformat_number($request->input('jumlah'))]);
            $check = ItemPembelian::where('fid_pembelian', $request->input('fid_pembelian'))
                ->where('fid_produk', $produk->id)
                ->first();
            if (empty($check)) {
                return ItemPembelian::create($request->all());
            } else {
                $jumlah = ($check->jumlah + 1);
                ItemPembelian::find($check->id)->update([
                    'jumlah' => $jumlah,
                    'total' => $check->harga * $jumlah
                ]);
                return $check;
            }
        } else {
            return ['error' => 'Kode barang tidak ditemukan'];
        }
    }

    public function update(Request $request, $id)
    {
        $harga_beli = unformat_number($request->input('harga'));
        $harga_jual = unformat_number($request->input('harga_jual'));
        $margin = unformat_number($request->input('margin'));
        $margin_nominal = unformat_number($request->input('margin_nominal'));

        $item_pembelian = ItemPembelian::find($id);
        $request->merge(['total' => $harga_beli * unformat_number($request->input('jumlah'))]);
        $request->merge(['harga' => $harga_beli]);
        $request->merge(['harga_jual' => $harga_jual]);
        $request->merge(['margin' => $margin]);
        $request->merge(['margin_nominal' => $margin_nominal]);
        $item_pembelian->update($request->all());

        Produk::find($item_pembelian->fid_produk)->update([
            'harga_beli' => $harga_beli,
            'harga_jual' => $harga_jual,
            'margin' => $margin,
            'margin_nominal' => $margin_nominal
        ]);

        return $item_pembelian;
    }

    public function delete($id)
    {
        $item_pembelian = ItemPembelian::find($id);
        $item_pembelian->delete();
        return $item_pembelian;
    }

    public function search($id)
    {
        $items = ItemPembelian::where('fid_pembelian', $id)->orderBy('id', 'desc')->get();
        return view('pos.pembelian_baru._items', compact('items'));
    }

    public function produk_create()
    {
        $supplier = Supplier::get();
        $satuan = SatuanBarang::all();
        return view('pos.pembelian_baru._produk_baru', compact('supplier', 'satuan'));
    }

    public function produk_baru(Request $request)
    {
        $check = Produk::where('kode', $request->kode)->first();
        if (empty($check)) {
            $field = new Produk;
        } else {
            $field = Produk::find($check->id);
        }

        $field->created_at = date('Y-m-d H:i:s');
        $field->created_by = session('useractive')->no_anggota;
        $field->kode=$request->kode;
        $field->nama_produk=$request->nama_produk;
        $field->deskripsi=$request->deskripsi;
        $field->fid_kategori=($request->sub_kategori != 'all' ? $request->sub_kategori : ( $request->kategori != 'all' ? $request->kategori : ( $request->kelompok == 'all' ? null : $request->kelompok ) ) );
        $field->kode_kategori=GlobalHelper::get_kode_kategori($field->fid_kategori);
        $field->stok_awal=$request->stok_awal;
        $field->stok_minimal=$request->stok_minimal;
        $field->fid_satuan=$request->satuan;
        $field->harga_beli=str_replace('.','',$request->harga_beli);
        $field->margin=$request->margin;
        $field->margin_nominal=str_replace('.','',$request->margin_nominal);
        $field->harga_jual=str_replace('.','',$request->harga_jual);
        $field->save();

        return $field;
    }

}
