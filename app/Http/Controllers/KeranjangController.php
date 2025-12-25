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
use App\AngsuranBelanja;
use App\KeranjangBelanja;
use View;
use DB;
use DateTime;
use Redirect;

class KeranjangController extends Controller
{
    public function get_keranjang($search){
      $query=KeranjangBelanja::select('keranjang_belanja.*','produk.nama_produk','produk.kode')
        ->join('produk','produk.id','keranjang_belanja.fid_produk')
        ->where('fid_anggota',Session::get('useractive')->no_anggota);
      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('produk.nama_produk', 'like', "%{$search}%")
            ->orWhere('produk.kode', 'like', "%{$search}%");
        });
      }
      $result=$query->orderBy('produk.nama_produk')->get();
      foreach ($result as $key => $value){
        $foto=FotoProduk::where('fid_produk',$value->fid_produk)->first();
        $result[$key]->foto=(!empty($foto) ? $foto->foto : null );

        $barang=GlobalHelper::stok_barang($value->fid_produk);
        $result[$key]->terjual=$barang['terjual'];
        $result[$key]->sisa=$barang['sisa'];
        $result[$key]->jumlah=($value->jumlah > $barang['sisa'] ? $barang['sisa'] : $value->jumlah );
      }
      return $result;
    }

    public function index(Request $request){
      $search=(!empty($request->search) ? $request->search : null);
      $data['keranjang']=$this->get_keranjang($search);
      return view('main.belanja.keranjang.index')
        ->with('data',$data)
        ->with('search',$search);
    }

    public function add_penjualan(){
      $field=new Penjualan;
      $field->tanggal=date('Y-m-d');
      $field->created_at=date('Y-m-d H:i:s');
      $field->created_by=Session::get('useractive')->no_anggota;
      $field->fid_anggota=Session::get('useractive')->no_anggota;
      $field->no_transaksi=GlobalHelper::get_nomor_penjualan($field->created_at);
      $field->fid_status=1;
      $field->jenis_belanja='toko';
      $field->fid_metode_pembayaran=1;
      $field->save();
      return $field->id;
    }

    public function add_produk(Request $request){
      $produk=Produk::find($request->id);
      if(!empty($produk)){
        if($request->action == 'add_cart'){
          $cek_keranjang=KeranjangBelanja::where('fid_produk',$request->id)->where('fid_anggota',Session::get('useractive')->no_anggota)->first();
          if(!empty($cek_keranjang)){
            $field=KeranjangBelanja::find($cek_keranjang->id);
            $field->jumlah=$cek_keranjang->jumlah+$request->jumlah;
          }
          else{
            $field=new KeranjangBelanja;
            $field->fid_anggota=Session::get('useractive')->no_anggota;
            $field->fid_produk=$request->id;
            $field->jumlah=$request->jumlah;
          }
          $field->harga=str_replace('.','',$request->harga);
          $field->total=$field->jumlah*$field->harga;
          $field->save();
          $url='main/belanja/keranjang';
        }
        else{
          $barang=GlobalHelper::stok_barang($request->id);
          $field=new ItemPenjualan;
          $field->fid_penjualan=$this->add_penjualan();
          $field->fid_produk=$request->id;
          $field->harga_beli=$produk->harga_beli;
          $field->margin=$produk->margin;
          $field->margin_nominal=$produk->margin_nominal;
          $field->harga=$produk->harga_jual;
          $field->jumlah=$request->jumlah;
          $field->total=$field->harga*$field->jumlah;
          if($field->jumlah <= $barang['sisa']){
            $field->save();
            $this->update_total_pembayaran($field->fid_penjualan,$field->total);
            $url='main/belanja/riwayat/toko/detail?id='.$field->fid_penjualan;
          }
          else{
            $url='main/belanja/produk/detail?id='.$produk->kode;
          }
        }
      }
      else{
        $url='main/belanja';
      }
      return redirect($url);
    }

    public function delete(Request $request){
      $field=KeranjangBelanja::find($request->id);
      $field->delete();
      return redirect('main/belanja/keranjang')
        ->with('message','Barang dikeranjang berhasil dihapus')
        ->with('message_type','success');
    }

    public function update_total_pembayaran($id,$total){
      $field=Penjualan::find($id);
      $field->total_pembayaran=$total;
      $field->save();
    }

    public function hapus_keranjang($id){
      $penjualan=Penjualan::find($id);
      if(!empty($penjualan)){
        $items=ItemPenjualan::where('fid_penjualan',$id)->get();
        foreach ($items as $key => $value) {
          KeranjangBelanja::where('fid_produk',$value->fid_produk)->where('fid_anggota',$penjualan->fid_anggota)->delete();
        }
      }
    }

    public function proses(Request $request){
      if($request->action=='checkout'){
        $id=$this->add_penjualan();
        $total=0;
        foreach ($request->pilih as $key => $item) {
          $keranjang=KeranjangBelanja::select('keranjang_belanja.*','produk.*')
            ->join('produk','produk.id','keranjang_belanja.fid_produk')
            ->where('keranjang_belanja.id',$item)
            ->first();
          if(!empty($keranjang)){
            $stok=GlobalHelper::stok_barang($keranjang->fid_produk);
            $field=new ItemPenjualan;
            $field->fid_penjualan=$id;
            $field->fid_produk=$keranjang->fid_produk;
            $field->harga_beli=$keranjang->harga_beli;
            $field->margin=$keranjang->margin;
            $field->margin_nominal=$keranjang->margin_nominal;
            $field->harga=$keranjang->harga_jual;
            $field->jumlah=$request->jumlah[$item];
            $field->total=$field->harga*$field->jumlah;
            if($field->jumlah <= $stok['sisa']){
              $field->save();
            }
          }
          $total=$total+$field->total;
        }
        $this->update_total_pembayaran($id,$total);
        $this->hapus_keranjang($id);
        return redirect('main/belanja/riwayat/toko/detail?id='.$field->fid_penjualan);
      }
      else{
        KeranjangBelanja::whereIn('id',$request->pilih)->delete();
        return redirect('main/belanja/keranjang')
          ->with('message','Barang dikeranjang berhasil dihapus')
          ->with('message_type','success');
      }
    }

    //---------------------------------------------Pilih Produk------------------------------------------------//

    public function get_produk($kategori,$search){
      $query=Produk::select('produk.*','satuan_barang.satuan')
        ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan');

      if(!empty(Session::get('filter_produk'))){
        $filters=Session::get('filter_produk');
        if($filters['kode']!='all'){
          $query=$query->where('produk.kode_kategori','like', "{$filters['kode']}%");
        }
      }

      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('produk.nama_produk', 'like', "%{$search}%")
            ->orWhere('produk.kode', 'like', "%{$search}%");
        });
      }

      $result=$query->orderBy('produk.fid_kategori')->paginate(24);
      foreach ($result as $key => $value){
        $petugas=DB::table('anggota')->where('no_anggota',$value->created_by)->first();
        $result[$key]->nama_petugas=(!empty($petugas) ? $petugas->nama_lengkap : 'Undefined');

        $foto=FotoProduk::where('fid_produk',$value->id)->first();
        $result[$key]->foto=(!empty($foto) ? $foto->foto : null );

        $stok=GlobalHelper::stok_barang($value->id);
        $result[$key]->stok_masuk=$stok['stok_awal']+$stok['pembelian'];
        $result[$key]->stok_keluar=$stok['retur']+$stok['terjual'];
        $result[$key]->stok_awal=$stok['stok_awal'];
        $result[$key]->pembelian=$stok['pembelian'];
        $result[$key]->retur=$stok['retur'];
        $result[$key]->terjual=$stok['terjual'];
        $result[$key]->sisa=$stok['sisa'];

        $kategori=explode('.',$value->kode_kategori);
        $result[$key]->kelompok=GlobalHelper::detail_kategori_produk($kategori[0]);
        $result[$key]->kategori=GlobalHelper::detail_kategori_produk($kategori[1]);
        $result[$key]->sub_kategori=GlobalHelper::detail_kategori_produk($kategori[2]);
      }

      if(!empty($search)){
        $result->withPath('belanja?search='.$search);
      }
      return $result;
    }

    public function pilih_produk(Request $request){
      $search=(!empty($request->search) ? $request->search : null);
      $kategori=(!empty($request->kategori) ? $request->kategori : 'all');
      $data['produk']=$this->get_produk($kategori,$search);
      return view('main.belanja.pilih_produk.index')
        ->with('data',$data)
        ->with('kategori',$kategori)
        ->with('search',$search);
    }

    public function filter_produk(Request $request){
      Session::forget('filter_produk');
      $kode='';
      $kode .=($request->kelompok=='all' || $request->kelompok=='' ? 'all' : $request->kelompok);
      $kode .=($request->kategori=='all' || $request->kategori=='' ? '' : '.'.$request->kategori);
      $kode .=($request->sub_kategori=='all' || $request->sub_kategori=='' ? '' : '.'.$request->sub_kategori);
      $filter=array('kode'=>$kode,
        'kelompok'=>($request->kelompok=='' ? 'all' : $request->kelompok ),
        'kategori'=>($request->kategori=='' ? 'all' : $request->kategori ),
        'sub_kategori'=>($request->sub_kategori=='' ? 'all' : $request->sub_kategori ));
        if ($request->has('is_aktif')) {
            $filter['is_aktif'] = $request->is_aktif;
        }
      Session::put('filter_produk',$filter);
      return Redirect::back();
    }


    public function detail_produk(Request $request){
      $produk=Produk::select('produk.*','satuan_barang.satuan')
        ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
        ->where('produk.kode',$request->id)
        ->first();
      if(!empty($produk)){
        $foto=FotoProduk::where('fid_produk',$produk->id)->first();
        $produk->foto=(!empty($foto) ? $foto->foto : null );

        $stok=GlobalHelper::stok_barang($produk->id);
        $produk->terjual=$stok['terjual'];
        $produk->sisa=$stok['sisa'];

        $kategori=explode('.',$produk->kode_kategori);
        $produk->kelompok=GlobalHelper::detail_kategori_produk($kategori[0]);
        $produk->kategori=GlobalHelper::detail_kategori_produk($kategori[1]);
        $produk->sub_kategori=GlobalHelper::detail_kategori_produk($kategori[2]);

        $data['produk']=$produk;
        $data['produk-terkait']=$this->get_produk('all',null);
        return view('main.belanja.pilih_produk.detail')
          ->with('data',$data)
          ->with('id',$produk->id);
      }
      else{
        return redirect('main/belanja');
      }
    }
}
