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
use App\BelanjaKonsinyasi;
use App\KeranjangBelanja;
use App\ItemReturPenjualan;
use View;
use DB;
use DateTime;
use Redirect;

class BelanjaController extends Controller
{

    //---------------------------------------------------RIWAYAT BELANJA ----------------------------------------------//

    public function get_penjualan($jenis,$status,$search){
      if($jenis=='toko'){
        $query=Penjualan::select('penjualan.*','status_belanja.status','status_belanja.color','metode_pembayaran.metode_pembayaran')
          ->join('status_belanja','status_belanja.id','=','penjualan.fid_status');

      }
      else{
        $query=Penjualan::select('penjualan.*','status_transaksi.status','status_transaksi.color','metode_pembayaran.metode_pembayaran')
          ->join('status_transaksi','status_transaksi.id','=','penjualan.fid_status');
      }

      $query=$query->join('metode_pembayaran','metode_pembayaran.id','=','penjualan.fid_metode_pembayaran')
          ->where('jenis_belanja',$jenis)
          ->where('fid_anggota','=',Session::get('useractive')->no_anggota);

      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('penjualan.no_penjualan', 'like', "%{$search}%")
            ->orWhere('anggota.nama_lengkap', 'like', "%{$search}%");
         });
      }

      if($status=='all'){
        $query=$query->where('penjualan.fid_status','!=',3);
      }
      else{
        $query=$query->where('penjualan.fid_status',$status);
      }

      $result=$query->orderBy('penjualan.created_at')->paginate(10);
      foreach ($result as $key => $value) {
        $items=ItemPenjualan::select('item_penjualan.*','produk.nama_produk','produk.kode','satuan_barang.satuan')
          ->join('produk','produk.id','=','item_penjualan.fid_produk')
          ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
          ->where('fid_penjualan',$value->id)
          ->first();
        if(!empty($items)){
          $foto=FotoProduk::where('fid_produk',$items->fid_produk)->first();
          $items->foto=(!empty($foto) ? asset('storage/'.$foto->foto) : asset('assets/images/produk-default.jpg') );
        }
        $result[$key]->produk=$items;
        $result[$key]->jumlah=ItemPenjualan::where('fid_penjualan',$value->id)->sum('item_penjualan.jumlah');
        $result[$key]->sisa_angsuran=AngsuranBelanja::where('fid_penjualan',$value->id)->where('fid_status','!=',6)->sum('total_angsuran');
        $result[$key]->sisa_tenor=AngsuranBelanja::where('fid_penjualan',$value->id)->where('fid_status','!=',6)->count();
      }
      if(!empty($search)){
        $result->withPath($jenis.'?search='.$search);
      }
      return $result;
    }

    public function index(Request $request,$jenis){
      $search=(!empty($request->search) ? $request->search : null);
      $status=(!empty($request->status) ? $request->status : 'all');
      $data['penjualan']=$this->get_penjualan($jenis,$status,$search);
      $data['status']=DB::table('status_belanja')->get();
      return view('main.belanja.riwayat.index')
        ->with('data',$data)
        ->with('jenis',$jenis)
        ->with('status',$status)
        ->with('search',$search);
    }

    public function detail(Request $request,$jenis){
      $penjualan=Penjualan::select('penjualan.*','rekening_pembayaran.keterangan as metode_pembayaran','rekening_pembayaran.fid_metode_pembayaran','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
        ->leftJoin('anggota','anggota.no_anggota','=','penjualan.fid_anggota')
        ->join('rekening_pembayaran','rekening_pembayaran.id','=','penjualan.fid_metode_pembayaran')
        ->where('penjualan.id',$request->id)
        ->first();
      if(!empty($penjualan)){
        $id=$request->id;
        $penjualan->jumlah=ItemPenjualan::where('fid_penjualan',$id)->sum('jumlah');
        $penjualan->total=ItemPenjualan::where('fid_penjualan',$id)->sum('total');

        if($jenis=='toko'){
          $status=DB::table('status_belanja')->find($penjualan->fid_status);
          $penjualan->icon=(!empty($status) ? $status->icon : '');
          $items=ItemPenjualan::select('item_penjualan.*','produk.nama_produk','produk.kode','satuan_barang.satuan')
            ->join('produk','produk.id','=','item_penjualan.fid_produk')
            ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
            ->where('item_penjualan.fid_penjualan',$request->id)
            ->get();
        }
        else{
          $status=DB::table('status_transaksi')->find($penjualan->fid_status);
          $penjualan->icon=(!empty($status) ? $status->icon : '');
          $items=ItemPenjualan::select('item_penjualan.*')->where('item_penjualan.fid_penjualan',$request->id)->get();
        }

        foreach ($items as $key => $value) {
          $jumlah=($penjualan->fid_status == 3 ? $value->jumlah : 0 );
          $foto=FotoProduk::where('fid_produk',$value->fid_produk)->first();
          $items[$key]->foto=(!empty($foto) ? $foto->foto : null );
        }
        $data['items']=$items;
        $anggota=Anggota::where('no_anggota',$penjualan->created_by)->first();
        $penjualan->nama_petugas=(!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
        $data['penjualan']=$penjualan;

        $angsuran=AngsuranBelanja::select('angsuran_belanja.*','status_angsuran.status_angsuran','status_angsuran.color','payroll_angsuran_belanja.*')
          ->join('status_angsuran','status_angsuran.id','=','angsuran_belanja.fid_status')
          ->leftJoin('payroll_angsuran_belanja','payroll_angsuran_belanja.id','=','angsuran_belanja.fid_payroll')
          ->where('fid_penjualan',$request->id)
          ->get();

        $sisa_angsuran=$penjualan->total;
        foreach ($angsuran as $key => $value) {
          $sisa_angsuran=$sisa_angsuran-$value->total_angsuran;
          $angsuran[$key]->sisa_angsuran=$sisa_angsuran;
        }

        $data['angsuran']=$angsuran;
        $data['keterangan']=DB::table('keterangan_status_transaksi')
          ->where('jenis_transaksi',($jenis=='toko' ? 'belanja' : 'kredit belanja'))
          ->where('fid_status',$penjualan->fid_status)
          ->where('user_page','main')
          ->first();
        $data['keterangan']->label=str_replace('Konsinyasi',ucfirst($jenis),$data['keterangan']->label);
        return view('main.belanja.riwayat.detail')
          ->with('jenis',$jenis)
          ->with('data',$data)
          ->with('id',$id);
      }
      else{
        return redirect('main/belanja');
      }
    }

    public function proses_pembatalan(Request $request,$jenis){
      $field=Penjualan::find($request->id);
      $field->fid_status=($jenis=='toko' ? 3 : 5);
      $field->save();
      GlobalHelper::add_verifikasi_transaksi('penjualan',$field->id,'Transaksi dibatalkan oleh',null);
      return redirect('main/belanja/riwayat/'.$jenis)
        ->with('message','Transaksi Belanja berhasil dibatalkan')
        ->with('message_type','success');
    }

    //------------------------------------------------ Retur Belanja ---------------------------------------------//

    public function get_retur($search){
      $query=ItemReturPenjualan::select('item_retur_penjualan.*','retur_penjualan.no_retur','retur_penjualan.created_at','retur_penjualan.created_by','retur_penjualan.tanggal','produk.nama_produk','produk.kode','satuan_barang.satuan')
        ->join('retur_penjualan','retur_penjualan.id','=','item_retur_penjualan.fid_retur_penjualan')
        ->join('produk','produk.id','=','item_retur_penjualan.fid_produk')
        ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
        ->where('fid_anggota',Session::get('useractive')->no_anggota);

      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('retur_penjualan.no_retur', 'like', "%{$search}%")
            ->orWhere('produk.nama_produk', 'like', "%{$search}%")
            ->orWhere('produk.kode', 'like', "%{$search}%");
         });
      }

      $result=$query->orderBy('retur_penjualan.tanggal')->paginate(10);
      foreach ($result as $key => $value) {
        $foto=FotoProduk::where('fid_produk',$value->fid_produk)->first();
        $result[$key]->foto=(!empty($foto) ? asset('storage/'.$foto->foto) : asset('assets/images/produk-default.jpg') );
      }
      return $result;
    }

    public function retur(Request $request){
      $search=(!empty($request->search) ? $request->search : 'all');
      $data=$this->get_retur($search);
      return view('main.belanja.retur.index')
        ->with('search',$search)
        ->with('data',$data);
    }

    //------------------------------------------------ Angsuran Belanja ---------------------------------------------//

    public function get_angsuran($search){
      $query=AngsuranBelanja::select('penjualan.id','penjualan.no_transaksi','penjualan.jenis_belanja','penjualan.total_pembayaran','payroll_angsuran_belanja.bulan','angsuran_belanja.total_angsuran','angsuran_belanja.angsuran_ke','status_angsuran.status_angsuran','status_angsuran.color')
        ->join('penjualan','penjualan.id','=','angsuran_belanja.fid_penjualan')
        ->join('status_angsuran','status_angsuran.id','=','angsuran_belanja.fid_status')
        ->leftJoin('payroll_angsuran_belanja','payroll_angsuran_belanja.id','=','angsuran_belanja.fid_payroll')
        ->whereIn('angsuran_belanja.fid_status',array(5,6))
        ->where('penjualan.fid_anggota',Session::get('useractive')->no_anggota);

      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('penjualan.no_transaksi', 'like', "%{$search}%");
            // ->orWhere('produk.nama_produk', 'like', "%{$search}%")
            // ->orWhere('produk.kode', 'like', "%{$search}%");
         });
      }
      $result=$query->paginate(10);
      foreach ($result as $key => $value) {
        $result[$key]->nama_bulan=GlobalHelper::nama_bulan($value->bulan);
      }
      return $result;
    }

    public function angsuran(Request $request){
      $search=(!empty($request->search) ? $request->search : 'all');
      $data=$this->get_angsuran($search);
      return view('main.belanja.angsuran.index')
        ->with('search',$search)
        ->with('data',$data);
    }
}
