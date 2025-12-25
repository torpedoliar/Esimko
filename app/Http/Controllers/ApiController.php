<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\KategoriProduk;
use App\BaganAkun;
use View;
use DB;
use DateTime;
use Redirect;


class ApiController extends Controller
{
  public function find_anggota($id,$bulan=null){
    $data=DB::table('anggota')->find($id);
    if(!empty($data)){
      $data->user_akses=DB::table('user_akses')->where('fid_anggota',$id)->get();
      $data->avatar=(!empty($data->avatar) ? asset('storage/'.$data->avatar) : asset('assets/images/user-avatar-placeholder.png'));
      $data->saldo=GlobalHelper::saldo_tabungan($data->no_anggota,'Simpanan Sukarela'); //Simpanan Sukarela

      $data->simpanan_pokok=GlobalHelper::saldo_tabungan($data->no_anggota,1); //Simpanan Pokok
      $data->simpanan_wajib=GlobalHelper::saldo_tabungan($data->no_anggota,2); //Simpanan Wajib
      $data->simpanan_hari_raya=GlobalHelper::saldo_tabungan($data->no_anggota,'Simpanan Hari Raya'); //Simpanan Hari Raya
      $data->total_simpanan=GlobalHelper::saldo_tabungan($data->no_anggota,'Total Simpanan'); //Total Simpanan

      $data->sisa_pinjaman=GlobalHelper::sisa_pinjaman($data->no_anggota,'all'); //Sisa pinjaman
      $data->bunga_pinjaman=$data->sisa_pinjaman*0.01; //Bunga Pinjaman
      $data->total_tagihan=$data->sisa_pinjaman+$data->bunga_pinjaman; //Total Tagihan
      $data->sisa_saldo=$data->total_simpanan-$data->total_tagihan; // Sisa Saldo

      $data->angsuran_jangka_panjang=GlobalHelper::angsuran_pinjaman($data->no_anggota,9);
      $data->angsuran_jangka_pendek=GlobalHelper::angsuran_pinjaman($data->no_anggota,10);
      $data->angsuran_barang=GlobalHelper::angsuran_pinjaman($data->no_anggota,11);

      $data->setoran_berkala=GlobalHelper::setoran_berkala($data->no_anggota);

      $data->angsuran_belanja_toko=GlobalHelper::angsuran_belanja($data->no_anggota,'toko');
      $data->angsuran_belanja_konsinyasi=GlobalHelper::angsuran_belanja($data->no_anggota,'konsinyasi');
      $data->angsuran_belanja_online=GlobalHelper::angsuran_belanja($data->no_anggota,'online');

      $data->sisa_kredit_belanja=GlobalHelper::sisa_kredit_belanja($data->no_anggota,'all');

      $gaji_pokok=GlobalHelper::gaji_pokok($data->no_anggota,$bulan);
      $data->gaji_pokok=$gaji_pokok[1];
      $data->bulan=$gaji_pokok[0];
      $data->bulan_tampil=GlobalHelper::nama_bulan($gaji_pokok[0]);
    }
    return response()->json($data);
  }

  public function nestedArray($source, $parent = '0'){
    $result = array();
    foreach ($source as $value) {
      if ($value['parent_kode'] == $parent) {
        $sub = $this->nestedArray($source, $value['id']);
        if ($sub) {
            $value['children'] = $sub;
        }
        $result[] = $value;
      }
    }
    return $result;
  }


  public function get_tree_kategori(){
    $data = KategoriProduk::select('id','nama_kategori as text','parent_id as parent_kode','kode')->get();
    foreach ($data as $key => $value) {
      $parent=KategoriProduk::find($value->parent_kode);
      if(!empty($parent)){
        $data[$key]->nama_parent=$parent->nama_kategori;
      }
    }
    $result = $this->nestedArray($data,0);
    return response()->json($result);
  }

  public function get_kategori($parent_id,$selected='all'){
    $data = DB::table('kategori_produk')->select('id','nama_kategori')->where('parent_id', '=', $parent_id)
        ->orderby('nama_kategori', 'asc')
        ->get();
    foreach ($data as $key => $value) {
      if($selected==$value->id){
        $data[$key]->selected='selected';
      }
      else{
        $data[$key]->selected='';
      }
    }
    $data->prepend(["id" => 'all',"nama_kategori" => 'Semua Barang',"selected"=>($selected=='all' ? 'selected' : '')]);
    return $data;
  }

  public function get_bagan_akun($active='all'){
    $query = BaganAkun::select('*','parent_id as parent_kode');
    $query = ($active == 'all' ? $query : $query->where('is_active',$active));
    $data = $query->orderBy('kode')->get();
    foreach ($data as $key => $value) {
      $data[$key]->text='('.$value->kode.') '.$value->nama_akun;
      $parent=BaganAkun::find($value->parent_id);
      if(!empty($parent)){
        $data[$key]->nama_akun_parent='('.$parent->kode.') '.$parent->nama_akun;
      }
    }
    $result = $this->nestedArray($data);
    return response()->json($result);
  }

  public function find_produk($id){
    $data=DB::table('produk')->select('produk.*','satuan_barang.satuan')
      ->join('satuan_barang','satuan_barang.id','produk.fid_satuan')
      ->where('produk.id',$id)
      ->first();
    if(!empty($data)){
      $foto=DB::table('foto_produk')->where('fid_produk',$data->id)->first();
      $data->foto=(!empty($foto) && !empty($foto->foto) ? asset('storage/'.$foto->foto) : asset('assets/images/produk-default.jpg'));
    }
    return response()->json($data);
  }

  public function get_items_penjualan($id){
    $data=DB::table('item_penjualan')->select('item_penjualan.*','produk.harga_jual')
      ->join('produk','produk.id','=','item_penjualan.fid_produk')
      ->join('satuan_barang','satuan_barang.id','produk.fid_satuan')
      ->where('fid_penjualan',$id)
      ->get();

    return response()->json($data);
  }

  public function get_items_pembelian($id){
    $data=DB::table('item_pembelian')->select('item_pembelian.*','produk.harga_jual')
      ->join('produk','produk.id','=','item_pembelian.fid_produk')
      ->join('satuan_barang','satuan_barang.id','produk.fid_satuan')
      ->where('fid_pembelian',$id)
      ->get();

    return response()->json($data);
  }

  public function find_jurnal_detail($id){
    $data=DB::table('jurnal_detail')->select('jurnal_detail.*','bagan_akun.kode','bagan_akun.nama_akun')
      ->join('bagan_akun','bagan_akun.kode','=','jurnal_detail.kode_akun')
      ->where('jurnal_detail.id',$id)
      ->first();
    return response()->json($data);
  }


  public function find_periode_pengurus($id){
    $data=DB::table('periode_pengurus')->find($id);
    return response()->json($data);
  }

  public function find_supplier($id){
    $data=DB::table('supplier')->find($id);
    return response()->json($data);
  }

  public function find_status($jenis,$id){
    $data=DB::table('status_'.$jenis)->find($id);
    return response()->json($data);
  }

  public function find_items_penjualan($id){
    $data=DB::table('item_penjualan')->find($id);
    return response()->json($data);
  }

  public function find_metode_pembayaran($id){
    $data=DB::table('rekening_pembayaran')->find($id);
    return response()->json($data);
  }

  public function find_attachment_berita($id){
    $data=DB::table('attachment_berita')->find($id);
    return response()->json($data);
  }

  public function find_keranjang($id){
    $data=DB::table('keranjang_belanja')->find($id);
    return response()->json($data);
  }

  public function find_syarat_ketentuan($id){
    $data=DB::table('syarat_ketentuan')->find($id);
    return response()->json($data);
  }

  public function find_buku_kas_detail($id){
    $data=DB::table('buku_kas_detail')->select('buku_kas_detail.*','bagan_akun.kode','bagan_akun.nama_akun')
      ->join('bagan_akun','bagan_akun.kode','=','buku_kas_detail.kode_akun')
      ->where('buku_kas_detail.id',$id)
      ->first();
    return response()->json($data);
  }


  public function find_items_pembelian($id){
    $data=DB::table('item_pembelian')->select('item_pembelian.*','produk.kode','produk.nama_produk','satuan_barang.satuan')
      ->join('produk','produk.id','=','item_pembelian.fid_produk')
      ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
      ->where('item_pembelian.id',$id)
      ->first();

    if(!empty($data)){
      $foto=DB::table('foto_produk')->where('fid_produk',$data->fid_produk)->first();
      $data->foto=(!empty($foto) && !empty($foto->foto) ? asset('storage/'.$foto->foto) : asset('assets/images/produk-default.jpg'));
    }

    return response()->json($data);
  }

  public function find_items_return_pembelian($id){
    $data=DB::table('item_retur_pembelian')->select('item_retur_pembelian.*','produk.kode','produk.nama_produk','satuan_barang.satuan')
      ->join('produk','produk.id','=','item_retur_pembelian.fid_produk')
      ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
      ->where('item_retur_pembelian.id',$id)
      ->first();

    if(!empty($data)){
      $foto=DB::table('foto_produk')->where('fid_produk',$data->fid_produk)->first();
      $data->foto=(!empty($foto) && !empty($foto->foto) ? asset('storage/'.$foto->foto) : asset('assets/images/produk-default.jpg'));
    }
    return response()->json($data);
  }

  public function get_anggota($status,$search='all'){
    $query=DB::table('anggota')->select('anggota.*','status_anggota.status_anggota','status_anggota.color')
      ->join('status_anggota','status_anggota.id','=','anggota.fid_status');
    if($status!='all'){
      if($status=='aktif'){
        $query=$query->whereIn('anggota.fid_status',array(2,3));
      }
      elseif($status=='nonaktif'){
        $query=$query->whereIn('anggota.fid_status',array(1,4));
      }
      else{
        $query=$query->where('anggota.fid_status',$status);
      }
    }
    if($search=='all'){
      $query=$query->limit(10);
    }
    else{
      $query=$query->where(function ($i) use ($search) {
        $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
          ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
       });
    }
    $result=$query->orderBy('anggota.no_anggota')->get();
    foreach ($result as $key => $value) {
      $result[$key]->avatar=(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png'));
    }
    return $result;
  }

  public function get_produk($supplier = 'all',$search='all'){
    if($supplier !=='all' ){
      $query=DB::table('produk')->select('produk.*','satuan_barang.satuan','item_pembelian.harga as harga_beli')
        ->join('satuan_barang','satuan_barang.id','produk.fid_satuan')
        ->leftjoin('item_pembelian','item_pembelian.fid_produk','produk.id')
        ->leftjoin('pembelian','pembelian.id','item_pembelian.fid_pembelian');
      if ($supplier != 'all') {
          $query = $query->where('pembelian.fid_supplier',$supplier);
      }
      $query = $query->groupBy('produk.id');
    }
    else{
      $query=DB::table('produk')->select('produk.*','satuan_barang.satuan')
        ->join('satuan_barang','satuan_barang.id','produk.fid_satuan');
    }
    if($search=='all'){
      $query=$query->limit(10);
    }
    else{
      $query=$query->where(function ($i) use ($search) {
        $i->where('produk.kode', 'like', "%{$search}%")
          ->orWhere('produk.nama_produk', 'like', "%{$search}%");
      });
    }
    $result=$query->orderBy('produk.kode')->get();
    foreach ($result as $key => $value) {
      $foto=DB::table('foto_produk')->where('fid_produk',$value->id)->first();
      $result[$key]->foto=(!empty($foto) && !empty($foto->foto) ? asset('storage/'.$foto->foto) : asset('assets/images/produk-default.jpg'));
    }
    return $result;
  }

    public function get_produk2($search='all'){
        $query=DB::table('produk')->select('produk.*','satuan_barang.satuan')
            ->join('satuan_barang','satuan_barang.id','produk.fid_satuan');
        if($search=='all'){
            $query=$query->limit(10);
        }
        else{
            $query=$query->where(function ($i) use ($search) {
                $i->where('produk.kode', 'like', "%{$search}%")
                    ->orWhere('produk.nama_produk', 'like', "%{$search}%");
            });
        }
        $result=$query->orderBy('produk.kode')->get();
        foreach ($result as $key => $value) {
            $foto=DB::table('foto_produk')->where('fid_produk',$value->id)->first();
            $result[$key]->foto=(!empty($foto) && !empty($foto->foto) ? asset('storage/'.$foto->foto) : asset('assets/images/produk-default.jpg'));
        }
        return $result;
    }

  public function check_sisa_pinjaman($anggota,$jenis){
    $jenis_pinjaman=DB::table('jenis_transaksi')->find($jenis);
    $pinjaman=DB::table('transaksi')->where('fid_anggota',$anggota)
      ->where('fid_jenis_transaksi',$jenis)
      ->where('fid_status',4)
      ->first();
    if(!empty($pinjaman)){
      $sisa_tenor=DB::table('angsuran')->where('fid_transaksi',$pinjaman->id)->where('fid_status','!=',6)->count();
      $sisa_angsuran=DB::table('angsuran')->where('fid_transaksi',$pinjaman->id)->where('fid_status','!=',6)->sum('angsuran_pokok');
      $data=array('jenis_pinjaman'=>$jenis_pinjaman->jenis_transaksi,'sisa_tenor'=>$sisa_tenor,'sisa_angsuran'=>$sisa_angsuran);
    }
    else{
      $data=array('jenis_pinjaman'=>$jenis_pinjaman->jenis_transaksi,'sisa_tenor'=>0,'sisa_angsuran'=>0);
    }
    return $data;
  }

}
