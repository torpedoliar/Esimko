<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Produk;
use App\ItemPembelian;
use App\Anggota;
use App\UserAkses;
use App\Transaksi;
use App\Angsuran;
use App\PayrollAngsuran;
use App\GajiPokok;
use App\PayrollAngsuranBelanja;
use View;
use DB;
use DateTime;
use Redirect;

class ImportController extends Controller
{


    // public function produk(){
    //   $data=Produk::get();
    //   foreach ($data as $key => $value) {
    //     $cek_kategeori=DB::table('kategori_produk')->where('kode',$value->subdepartemen_id)->first();
    //     $field=Produk::find($value->id);
    //     $field->fid_kategori=(!empty($cek_kategeori) ? $cek_kategeori->id : null);
    //     if($value->harga_beli!=0){
    //       $field->margin_nominal=$value->harga_jual-$value->harga_beli;
    //       $field->margin=round(($field->margin_nominal/$value->harga_beli)*100,0);
    //     }
    //     $field->stok_awal=10;
    //     $field->kode_kategori=GlobalHelper::get_kode_kategori($field->fid_kategori);
    //     $field->created_by='K 0977';
    //     $field->save();
    //     $this->update_item_pembelian($field->id);
    //   }
    // }

    //----------------------------------------------------------------------------------------//
    //----------------------------//Import Data Barang dan Stok//-----------------------------//
    //----------------------------------------------------------------------------------------//


    public function produk(){
      $data=DB::table('import_barang')->get();
      foreach ($data as $key => $value) {
        $cek_kategeori=DB::table('kategori_produk')->where('kode',$value->kode_kategori)->first();
        $cek_satuan=DB::table('satuan_barang')->where('satuan',$value->satuan)->first();
        $field=new Produk;
        $field->kode=$value->kode_barang;
        $field->subdepartemen_id=$value->kode_kategori;
        $field->nama_produk=$value->nama_barang;
        $field->fid_satuan=(!empty($cek_satuan) ? $cek_satuan->id : null);
        $field->fid_kategori=(!empty($cek_kategeori) ? $cek_kategeori->id : null);
        $field->harga_jual=$value->harga_jual;
        $field->harga_beli=$value->harga_beli;
        if($value->harga_beli !=0 ){
          $field->margin_nominal=$value->harga_jual-$value->harga_beli;
          $field->margin=round(($field->margin_nominal/$value->harga_beli)*100,0);
        }
        else{
          $field->margin_nominal=0;
          $field->margin=0;
        }
        $field->stok_awal=$value->stok;
        $field->kode_kategori=GlobalHelper::get_kode_kategori($field->fid_kategori);
        $field->created_by='K 1557';
        $field->is_aktif=1;
        $field->created_at=date('Y-m-d H:i:s');
        $field->save();
        $this->update_item_pembelian($field->id);
      }
    }

    public function update_item_pembelian($id){
      $produk=Produk::find($id);
      if(!empty($produk)){
        $field=new ItemPembelian;
        $field->fid_pembelian=0;
        $field->fid_produk=$id;
        $field->jumlah=$produk->stok_awal;
        $field->harga=$produk->harga_beli;
        $field->margin=$produk->margin;
        $field->margin_nominal=$produk->margin_nominal;
        $field->harga_jual=$produk->harga_jual;
        $field->total=$field->jumlah*$field->harga;
        $field->save();
      }
    }

    //----------------------------------------------------------------------------------------//
    //--------------------------------//Import Data Anggota//---------------------------------//
    //----------------------------------------------------------------------------------------//

    public function anggota(){
      $anggota=Anggota::get();
      foreach ($anggota as $key => $value) {
        $field=Anggota::find($value->id);
        $field->password=encrypt(str_replace(' ','',$value->no_anggota));
        $field->tanggal_bergabung='2018-05-06';
        $field->tanggal_bekerja=(!empty($value->id_karyawan) ? GlobalHelper::bulan_bekerja($value->id_karyawan) : date('Y-m-d') );
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by='K 0977';
        $anggota_lama=DB::table('anggota_copy')->where('no_anggota',str_replace(' ','',$value->no_anggota))->first();
        $field->no_rekening=(!empty($anggota_lama) ? $anggota_lama->no_rekening : null );
        $field->nama_bank=($field->no_rekening==null ? null : 'Bank Mandiri' ) ;
        $field->save();
        $this->update_user_akses($field->id);
      }
    }

    public function generate_password_anggota(){
      $anggota=Anggota::get();
      foreach ($anggota as $key => $value) {
        $password=str_random(6);
        $field=Anggota::find($value->id);
        $field->password=encrypt($password);
        $field->save();
      }
    }

    public function update_user_akses($id){
      $cek_user_akses=UserAkses::where('fid_anggota',$id)->where('fid_hak_akses',2)->first();
      $field=(!empty($cek_user_akses) ? UserAkses::find($cek_user_akses->id) : new UserAkses );
      $field->fid_anggota=$id;
      $field->fid_hak_akses=2;
      $field->save();
    }

    //----------------------------------------------------------------------------------------//
    //--------------------------------//Import Data Pinjaman//--------------------------------//
    //----------------------------------------------------------------------------------------//


    //Import Data Pinjaman berdasarkan jenis pinjaman (9,10,11)
    public function pinjaman($jenis){
      $import_pinjaman=DB::table('import_pinjaman')->where('fid_pinjaman',$jenis)->get();
      foreach ($import_pinjaman as $key => $value) {
        $field=new Transaksi;
        $field->created_at=($value->tanggal=='0000-00-00' ? date('Y-m-d H:i:s') : $value->tanggal.' '.date('H:i:s') );
        $field->created_by=Session::get('useractive')->no_anggota;
        $field->fid_jenis_transaksi=$value->fid_pinjaman;
        $field->fid_status=4; //Transaksi Selesai (Sukses)
        $field->fid_anggota=$value->no_anggota;
        $field->fid_metode_transaksi=1; //metode Cash/Tunai
        $field->nominal=-str_replace('.','',$value->nominal); //Jumlah Pinjaman
        $field->tenor=$value->tenor;
        $field->tanggal=($value->tanggal=='0000-00-00' ? date('Y-m-d') : $value->tanggal);
        $field->save();
        $this->proses_angsuran($field->id,$value);
      }
    }

    public function proses_angsuran($id,$request){
      Angsuran::where('fid_transaksi',$id)->delete();
      $angsuran_ke=$request->tenor-$request->sisa_angsuran;
      for($n=1;$n<=$request->tenor;$n++){
        $field=new Angsuran;
        $field->angsuran_ke=$n;
        $field->fid_transaksi=$id;
        $field->bunga=0.01;
        $field->sisa_hutang=$this->sisa_hutang($id,$n);
        $field->angsuran_pokok=$request->angsuran_pokok;
        $field->angsuran_bunga=$request->angsuran_bunga;
        if($n <= $angsuran_ke){
          $field->fid_payroll=$this->payroll_angsuran($request->tanggal,$field->angsuran_ke);
          $field->fid_status=6;
          $field->save();
          $this->proses_pembayaran_angsuran($id,$field->id,$field->fid_payroll,$request); //create data transaksi pembayaran angsuran
        }
        else{
          $field->fid_status=3;
          $field->save();
        }
      }
    }

    public function sisa_hutang($id,$n){
      $angsuran=Angsuran::where('angsuran_ke',$n-1)->where('fid_transaksi',$id)->first();
      if(!empty($angsuran)){
        $sisa_hutang=$angsuran->sisa_hutang-$angsuran->angsuran_pokok;
        return $sisa_hutang;
      }
      else{
        $pinjaman=Transaksi::find($id);
        return (!empty($pinjaman) ? str_replace('-','',$pinjaman->nominal) : 0 );
      }
    }

    public function payroll_angsuran($tanggal,$angsuran_ke){
      $bulan='12-2021';
      $payroll=PayrollAngsuran::where('bulan',$bulan)->first();
      if(!empty($payroll)){
        $field=PayrollAngsuran::find($payroll->id);
        $field->updated_at=date('Y-m-d H:i:s');
      }
      else{
        $field=new PayrollAngsuran;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $field->fid_status=3;
        $field->bulan=$bulan;
      }
      $field->save();
      return $field->id;
    }



    public function proses_pembayaran_angsuran($id,$angsuran,$payroll,$request){
      $jenis_angsuran=array('pokok'=>12,'bunga'=>13);
      foreach ($jenis_angsuran as $key => $jenis){
        $field=new Transaksi;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $field->fid_status=4;
        $field->fid_jenis_transaksi=$jenis;
        $field->fid_anggota=$request->no_anggota;
        $field->fid_metode_transaksi=2;
        $field->fid_payroll=$payroll;
        $field->nominal=($jenis==12 ? $request->angsuran_pokok : $request->angsuran_bunga);
        $field->tanggal=date('Y-m-d');
        $field->fid_angsuran=$angsuran;
        $field->save();
      }
    }

    public function proses_status_pinjaman(){
      $data=Transaksi::whereIn('fid_jenis_transaksi',array(9,10,11))->get();
      foreach ($data as $key => $value) {
        $field=Transaksi::find($value->id);
        $sisa_tenor=Angsuran::where('fid_transaksi',$value->id)->where('fid_status','!=',6)->count();
        if($sisa_tenor==0){
          $field->fid_status=6;
        }
        else{
          $field->fid_status=4;
        }
        $field->save();
      }
    }

    public function change_format_nomor($no_anggota){
      if(strlen($no_anggota)==5){
        $no_anggota=str_replace('K','K ',$no_anggota);
      }
      else{
        $no_anggota=str_replace('AK','AK ',$no_anggota);
      }
      return $no_anggota;
    }

    public function update_transaksi(){
      $data=Transaksi::get();
      foreach ($data as $key => $value) {
        $field=Transaksi::find($value->id);
        $field->fid_anggota=$this->change_format_nomor($value->fid_anggota);
        $field->created_by=$this->change_format_nomor($value->created_by);
        $field->save();
      }
    }

    public function update_payroll($jenis){
      $data=DB::table('payroll_'.$jenis)->get();
      foreach ($data as $key => $value) {
        if($jenis=='simpanan'){
          $field=PayrollSimpanan::find($value->id);
        }
        else{
          $field=PayrollAngsuran::find($value->id);
        }
        $field->created_by=$this->change_format_nomor($value->created_by);
        $field->save();
      }
    }

    public function update_status_pinjaman(){
      $pinjaman=Transaksi::whereIn('fid_jenis_transaksi',array(9,10,11))->where('fid_status',4)->get();
      foreach ($pinjaman as $key => $value) {
        $sisa_tenor=Angsuran::where('fid_transaksi',$value->id)->where('fid_status','!=',6)->count();
        if($sisa_tenor == 0 ){
          $field=Transaksi::find($value->id);
          $field->fid_status=6;
          $field->save();
        }
      }
    }


    public function import_belanja_konsinyasi(){
      $data=DB::table('import_belanja_konsinyasi')->get();
      foreach ($data as $key => $value) {
        $field=new Penjualan;
        $field->tanggal=$value->tanggal;
        $field->jenis_belanja='konsinyasi';
        $field->no_transaksi=GlobalHelper::get_nomor_penjualan_konsinyasi($value->tanggal);
        $field->fid_anggota=$value->fid_anggota;
        $field->fid_metode_pembayaran=3;
        $field->total_pembayaran=$value->harga_jual;
        $field->diskon=0;
        $field->tenor=$value->tenor;
        $field->angsuran=$value->angsuran;
        $field->created_by='K 1557';
        $field->created_at=$value->tanggal.' '.date('H:i:s');
        $field->save();
        $this->proses_item_penjualan($value->id,$field->id);
        $this->proses_angsuran($value->id,$field->id);
      }
    }

    public function proses_item_penjualan($id,$penjualan_id){
      $import=DB::table('import_belanja_konsinyasi')->find($id);
      if(!empty($import)){
        $field=new ItemPenjualan;
        $field->fid_penjualan=$penjualan_id;
        $field->nama_supplier=null;
        $field->nama_barang=$import->nama_barang;
        $field->jumlah=1;
        $field->satuan='Pieces';
        $field->harga_beli=$import->harga_beli;
        $field->margin_nominal=$import->margin;
        $field->margin=round(($field->margin_nominal/$field->harga_beli)*100,0);
        $field->harga=str_replace('.','',$request->harga_jual);
        $field->total=str_replace('.','',$request->harga_jual);
        $field->save();
      }
    }

    public function proses_angsuran_belanja($id,$penjualan_id){
      $import=DB::table('import_belanja_konsinyasi')->find($id);
      if(!empty($import)){
        AngsuranBelanja::where('fid_penjualan',$penjualan_id)->delete();
        $angsuran_ke=$import->tenor-$import->sisa_angsuran;
        for($n=1;$n<=$import->tenor;$n++){
          $field=new AngsuranBelanja;
          $field->angsuran_ke=$n;
          $field->fid_penjualan=$penjualan_id;
          $field->total_angsuran=$import->angsuran;
          if($n <= $angsuran_ke){
            $field->fid_payroll=$this->payroll_angsuran_belanja();
            $field->fid_status=6;
            $field->save();
          }
          else{
            $field->fid_status=3;
            $field->save();
          }
        }
      }
    }

    public function payroll_angsuran_belanja(){
      $bulan='12-2021';
      $payroll=PayrollAngsuranBelanja::where('bulan',$bulan)->first();
      if(!empty($payroll)){
        $field=PayrollAngsuranBelanja::find($payroll->id);
        $field->updated_at=date('Y-m-d H:i:s');
      }
      else{
        $field=new PayrollAngsuran;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by='K 1557';
        $field->fid_status=3;
        $field->bulan=$bulan;
      }
      $field->save();
      return $field->id;
    }
}
