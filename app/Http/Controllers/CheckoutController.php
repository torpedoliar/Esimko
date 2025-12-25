<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Produk;
use App\FotoProduk;
use App\Penjualan;
use App\ItemPenjualan;
use App\KeranjangBelanja;
use View;
use DB;
use DateTime;
use Redirect;

class CheckoutController extends Controller
{
    public function index(Request $request){
      $penjualan=Penjualan::select('penjualan.*','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
        ->leftJoin('anggota','anggota.no_anggota','=','penjualan.fid_anggota')
        ->where('penjualan.id',$request->id)
        ->first();
      if(!empty($penjualan)){
        $action='edit';
        $id=$request->id;
        $penjualan->jumlah=ItemPenjualan::where('fid_penjualan',$id)->sum('jumlah');
        $penjualan->total=ItemPenjualan::where('fid_penjualan',$id)->sum('total');
        $items=ItemPenjualan::select('item_penjualan.*','produk.nama_produk','produk.kode','satuan_barang.satuan')
          ->join('produk','produk.id','=','item_penjualan.fid_produk')
          ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
          ->where('item_penjualan.fid_penjualan',$id)
          ->get();
        foreach ($items as $key => $value) {
          $jumlah=($penjualan->fid_status == 3 ? $value->jumlah : 0 );
          $foto=FotoProduk::where('fid_produk',$value->fid_produk)->first();
          $items[$key]->foto=(!empty($foto) ? $foto->foto : null );
        }
        $data['items']=$items;
        $data['penjualan']=$penjualan;
        $data['metode-pembayaran']=DB::table('metode_pembayaran')->get();
        return view('main.belanja.checkout.index')
          ->with('data',$data)
          ->with('action',$action)
          ->with('id',$id);
      }
      else{
        return redirect('main/belanja');
      }
    }

    // public function hapus_keranjang($id){
    //   $items=ItemPenjualan::where('fid_penjualan',$id)->get();
    //   foreach ($items as $key => $value) {
    //     KeranjangBelanja::where('fid_produk',$value->fid_produk)->delete();
    //   }
    // }
    //
    // public function proses(Request $request){
    //   $field=Penjualan::find($request->id);
    //   $field->fid_status=2;
    //   $field->total_pembayaran=$request->total_pembayaran;
    //   $field->save();
    //   $this->hapus_keranjang($field->id);
    //   return redirect('main/belanja/riwayat/toko/detail?id='.$field->id);
    // }


    
}
