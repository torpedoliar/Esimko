<?php

namespace App\Http\Controllers;

use App\Anggota;
use App\AngsuranBelanja;
use App\Helpers\GlobalHelper;
use App\ItemPembelian;
use App\Pembelian;
use App\Produk;
use App\RekeningPembayaran;
use App\SatuanBarang;
use App\Supplier;
use Illuminate\Http\Request;

class PembelianBaruController extends Controller
{
    public function index(Request $request)
    {
        $no_pembelian = $request->input('no_pembelian') ?? '';
        $id = $request->input('id') ?? '';
        $pembelian = Pembelian::where('id', $id)->first();
        $supplier = Supplier::get();
        $satuan = SatuanBarang::all();
        return view('pos.pembelian_baru.index', compact(
            'supplier', 'no_pembelian', 'pembelian', 'satuan'
        ));
    }

    public function create(Request $request)
    {
        $request->merge(['tanggal' => date('Y-m-d')]);
        $request->merge(['no_pembelian' => GlobalHelper::get_nomor_pembelian(date('Y-m-d H:i:s'))]);
        $request->merge(['created_at' => (date('Y-m-d H:i:s'))]);
        $request->merge(['created_by' => session('useractive')->no_anggota]);

        $filename = $this->save_file($request, 'file_faktur', 'faktur_pembelian');
        if ($filename !== '') $request->merge(['file' => $filename]);

        return Pembelian::create($request->all());
    }

    public function filterNumber($params, $columns = [])
    {
        foreach ($columns as $column) {
            if (!empty($params[$column])) $params[$column] = unformat_number($params[$column]);
        }
        return $params;
    }

    public function update(Request $request, $id)
    {
        $filename = $this->save_file($request, 'file_faktur', 'faktur_pembelian');
        if ($filename !== '') $request->merge(['file' => $filename]);

        $pembelian = Pembelian::find($id);
        $params = $this->filterNumber($request->all(), ['diskon_persen', 'diskon_nominal', 'ppn_persen', 'ppn_nominal', 'biaya_tambahan']);
        $pembelian->update($params);
    }

    public function delete($id)
    {
        ItemPembelian::where('fid_pembelian', $id)->delete();
        Pembelian::where('id', $id)->delete();
    }

    public function cari_produk(Request $request)
    {
        $produk = Produk::where('nama_produk', 'like', '%'. $request->input('nama') .'%')->limit(8)->get();
        return view('pos.pembelian_baru._list_produk', compact('produk'));
    }

    public function selesai(Request $request)
    {
        $pembelian = Pembelian::find($request->id);
        $pembelian->status = 1;
        $pembelian->save();

        return redirect('manajemen_stok/pembelian')
            ->with('message', 'Pembelian berhasil disimpan')
            ->with('message_type','success');
    }
}
