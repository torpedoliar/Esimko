<?php

namespace App\Http\Controllers;

use App\Anggota;
use App\AngsuranBelanja;
use App\Helpers\GlobalHelper;
use App\ItemPenjualan;
use App\Penjualan;
use App\Produk;
use App\RekeningPembayaran;
use Illuminate\Http\Request;

class PenjualanBaruController extends Controller
{
    public function index(Request $request)
    {
        $no_transaksi = $request->input('no_transaksi') ?? '';
        $penjualan = Penjualan::where('no_transaksi', $no_transaksi)->first();
        if (!empty($penjualan) && $penjualan->fid_status == 2) {
            return redirect('pos/penjualan_baru')
                ->with('message','Penjualan sudah selesai')
                ->with('message_type','error');
        }
        $metode_pembayaran = RekeningPembayaran::where('jenis_transaksi','like','%belanja%')->get();
        return view('pos.penjualan_baru.index', compact(
            'metode_pembayaran', 'no_transaksi', 'penjualan'
        ));
    }

    public function create(Request $request)
    {
        $request->merge(['kasir' => session('useractive')->no_anggota]);
        $request->merge(['created_by' => session('useractive')->no_anggota]);
        $request->merge(['fid_status' => 5]);
        $request->merge(['jenis_belanja' => 'toko']);
        $request->merge(['tanggal' => date('Y-m-d')]);
        $request->merge(['no_transaksi' => GlobalHelper::get_nomor_penjualan(date('Y-m-d H:i:s'))]);
        $no_anggota = $request->input('no_anggota') ?? '';
        $no_anggota = strtoupper($no_anggota);
        $request->merge(['fid_anggota' => $no_anggota === '' ? '0000' : $no_anggota]);
        return Penjualan::create($request->all());
    }

    public function update(Request $request, $id)
    {
        if ($request->has('no_anggota')) {
            $no_anggota = $request->input('no_anggota') ?? '';
            $no_anggota = strtoupper($no_anggota);
            $request->merge(['fid_anggota' => $no_anggota === '' ? '0000' : $no_anggota]);
        }
        if ($request->has('fid_metode_pembayaran')) {
            if ($request->input('fid_metode_pembayaran') == 3) {
                AngsuranBelanja::updateOrCreate([
                    'fid_penjualan' => $id,
                    'angsuran_ke' => 1,
                    'fid_status' => 3
                ], [
                    'total_angsuran' => unformat_number($request->input('total_pembayaran'))
                ]);
            }
        }
        $penjualan = Penjualan::find($id);
        $penjualan->update($request->all());
    }

    public function delete($id)
    {
        AngsuranBelanja::where('fid_penjualan', $id)->delete();
        ItemPenjualan::where('fid_penjualan', $id)->delete();
        Penjualan::where('id', $id)->delete();
    }

    public function list_tunda()
    {
        $list_penjualan = Penjualan::where('fid_status', 5)->get();
        return view('pos.penjualan_baru._list_tunda', compact('list_penjualan'));
    }

    public function cari_anggota(Request $request)
    {
        $no_anggota = str_replace(' ', '', $request->input('no_anggota'));
        $anggota = Anggota::whereRaw("REPLACE(no_anggota, ' ', '') = '$no_anggota'")->first();
        if (!empty($anggota)) return $anggota;
        return ['error' => 'No. Anggota tidak ditemukan !'];
    }

    public function cetak_struk($id)
    {
        $penjualan = Penjualan::find($id);
        if (empty($penjualan)) abort(404);
        return view('pos.penjualan_baru.cetak_struk', compact('penjualan'));
    }

    public function cari_produk(Request $request)
    {
        $produk = Produk::where('nama_produk', 'like', '%'. $request->input('nama') .'%')->limit(8)->get();
        foreach ($produk as $item) {
            $item->stok = GlobalHelper::stok_barang($item->id);
        }
        return view('pos.penjualan_baru._list_produk', compact('produk'));
    }
}
