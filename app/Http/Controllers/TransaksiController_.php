<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Transaksi;
use App\GajiPokok;
use App\Angsuran;
use View;
use DB;
use DateTime;
use Redirect;

class TransaksiController_ extends Controller
{

    public function get_transaksi($modul){
      $query=Transaksi::select('transaksi.*','jenis_transaksi.jenis_transaksi','jenis_transaksi.operasi','metode_transaksi.metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color')
        ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
        ->Join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
        ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
        ->join('metode_transaksi','metode_transaksi.id','=','transaksi.fid_metode_transaksi')
        ->where('transaksi.fid_anggota',Session::get('useractive')->no_anggota);

      if($modul=='simpanan'){
        $query=$query->whereIn('transaksi.fid_jenis_transaksi',array(4));
      }
      elseif($modul=='penarikan'){
        $query=$query->whereIn('transaksi.fid_jenis_transaksi',array(6,8));
      }
      elseif($modul=='pinjaman'){
        $query=$query->whereIn('transaksi.fid_jenis_transaksi',array(9,10,11));
      }

      if(!empty(Session::get('filter_transaksi')[$modul])){
        $filters=Session::get('filter_transaksi');
        if($filters[$modul]['jenis']!='all'){
          $query=$query->where('transaksi.fid_jenis_transaksi',$filters[$modul]['jenis']);
        }
        if($filters[$modul]['status']!='all'){
          $query=$query->where('transaksi.fid_status',$filters[$modul]['status']);
        }
        if(!empty($filters[$modul]['from']) && !empty($filters[$modul]['to'])){
          $query=$query->whereBetween('transaksi.tanggal', [GlobalHelper::dateFormat($filters[$modul]['from'],'Y-m-d'), GlobalHelper::dateFormat($filters[$modul]['to'],'Y-m-d')]);
        }
      }
      $result=$query->orderBy('transaksi.tanggal','DESC')->orderBy('transaksi.created_at','DESC')->paginate(10);
      return $result;
    }

    public function validasi_transaksi($request,$jenis){
      if($jenis=='simpanan'){
        $nilai=1;
      }
      elseif($jenis=='penarikan'){
        $saldo=GlobalHelper::saldo_tabungan(Session::get('useractive')->no_anggota,'Simpanan Sukarela');
        $nilai=((str_replace(',','',$request->nominal)>$saldo) ? 'Saldo simpanan tidak mencukupi' : 1 );
      }
      elseif($jenis=='pinjaman'){
        $tenor=array(9=>50,10=>18,11=>18);
        if($request->tenor > $tenor[$request->jenis_transaksi]){
          $nilai='Tenor melebihi maksimal tenor yaitu '.$tenor[$request->jenis_transaksi].' bulan';
        }
        else{
          $nilai=1;
        }
      }
      else{
        $nilai='Failed';
      }
      return $nilai;
    }

    public function proses_transaksi(Request $request){
      $validasi=$this->validasi_transaksi($request,$request->modul);
      if($validasi=='1' ){
        if($request->action=='add'){
          $field=new Transaksi;
          $field->created_at=date('Y-m-d H:i:s');
          $field->created_by=Session::get('useractive')->no_anggota;
          $field->fid_status=($request->modul=='pinjaman' ? 0 : 1 );
        }
        else{
          $field=Transaksi::find($request->id);
          $field->updated_at=date('Y-m-d H:i:s');
        }
        $field->fid_metode_transaksi=($request->modul=='penarikan' ? str_replace(',','',$request->nominal)>1000000 ? 3 : 1 : 3);
        $field->fid_jenis_transaksi=$request->jenis_transaksi;
        $field->fid_anggota=Session::get('useractive')->no_anggota;
        $field->nominal=($request->modul=='simpanan' ? $request->modul=='pinjaman' ? '-' : '' : '-' ).''.str_replace(',','',$request->nominal);
        $field->keterangan=$request->keterangan;
        $field->tanggal=date('Y-m-d');
        $field->tenor=($request->modul=='pinjaman' ? $request->tenor : null );
        if($request->action=='delete'){
          $field->delete();
        }
        else{
          $field->save();
          if($request->modul=='pinjaman'){
            $this->update_riwayat_gaji($request);
            $this->proses_angsuran($field->id,$request);
          }
        }
        return redirect($request->modul.'/detail?id='.$field->id);
      }
      else{
        return Redirect::back()
          ->with('message',$validasi)
          ->with('message_type','warning');
      }
    }

    public function filter_transaksi(Request $request){
      $modul=$request->modul;
      Session::forget('filter_transaksi');
      $data[$modul]=array('jenis'=>$request->jenis,'status'=>$request->status,'from'=>$request->from,'to'=>$request->to);
      Session::put('filter_transaksi',$data);
      return Redirect::back();
    }

    public function update_riwayat_gaji($request){
      $riwayat_gaji=GajiPokok::where('fid_anggota',Session::get('useractive')->no_anggota)
        ->where('bulan',$request->bulan)
        ->first();
      if(!empty($riwayat_gaji)){
        $field=GajiPokok::find($riwayat_gaji->id);
        $field->updated_at=date('Y-m-d H:i:s');
      }
      else{
        $field=new GajiPokok;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $field->bulan=$request->bulan;
        $field->fid_anggota=Session::get('useractive')->no_anggota;
      }
      if($request->hasFile('attachment')){
        if(!empty($field->attachment)){
          unlink(storage_path('app/'.$field->attachment));
        }
        $uploadedFile = $request->file('attachment');
        $path = $uploadedFile->store('slip_gaji');
        $field->attachment=$path;
      }
      $field->gaji_pokok=str_replace(',','',$request->gaji_pokok);
      $field->save();
    }

    public function upload_bukti_transaksi(Request $request){
      $field=Transaksi::find($request->id);
      if(!empty($field)){
        if($request->hasFile('bukti_transaksi')){
          if(!empty($field->bukti_transaksi)){
            unlink(storage_path('app/'.$field->bukti_transaksi));
          }
          $uploadedFile = $request->file('bukti_transaksi');
          $path = $uploadedFile->store('bukti_transaksi');
          $field->bukti_transaksi=$path;
          $field->save();
          return Redirect::back()
            ->with('message','Bukti Transaksi berhasil diupload')
            ->with('message_type','success');
        }
        else{
          return Redirect::back()
            ->with('message','File tida ditemukan')
            ->with('message_type','warning');
        }
      }
      else{
        return Redirect::back()
          ->with('message','Transaksi tidak ditemukan')
          ->with('message_type','warning');
      }
    }

    public function detail_transaksi($id){
      $data=Transaksi::select('transaksi.*','anggota.no_anggota','jenis_transaksi.jenis_transaksi','anggota.nama_lengkap','metode_transaksi.metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color','status_transaksi.icon')
        ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
        ->leftJoin('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
        ->join('metode_transaksi','metode_transaksi.id','=','transaksi.fid_metode_transaksi')
        ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
        ->where('transaksi.id',$id)
        ->first();
      if(!empty($data)){
        $anggota=Anggota::where('no_anggota',$data->created_by)->first();
        $data->nama_petugas=(!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
      }
      return $data;
    }


    //----------------------------------------------------SIMPANAN-----------------------------------------//

    public function simpanan(Request $request){
      $data['simpanan']=$this->get_transaksi('simpanan');
      $data['status-transaksi']=DB::table('status_transaksi')->get();
      return view('main.transaksi.simpanan.index')
        ->with('data',$data);
    }

    public function simpanan_detail(Request $request){
      $simpanan=$this->detail_transaksi($request->id);
      if(!empty($simpanan)){
        $data['simpanan']=$simpanan;
        $data['keterangan']=DB::table('keterangan_status_transaksi')
          ->where('jenis_transaksi','simpanan')
          ->where('fid_status',$simpanan->fid_status)
          ->where('user_page','main')
          ->first();
        $data['metode-transaksi']=DB::table('metode_transaksi')->where('id','<>',1)->get();
        return view('main.transaksi.simpanan.detail')
          ->with('data',$data)
          ->with('id',$request->id);
      }
      else{
        return redirect('simpanan');
      }
    }

    //----------------------------------------------------PENARIKAN-----------------------------------------//

    public function penarikan(Request $request){
      $data['penarikan']=$this->get_transaksi('penarikan');
      $data['jenis-transaksi']=DB::table('jenis_transaksi')->whereIn('id',array(6,8))->get();
      $data['status-transaksi']=DB::table('status_transaksi')->get();
      return view('main.transaksi.penarikan.index')
        ->with('data',$data);
    }

    public function penarikan_detail(Request $request){
      $penarikan=$this->detail_transaksi($request->id);
      if(!empty($penarikan)){
        $data['penarikan']=$penarikan;
        $data['keterangan']=DB::table('keterangan_status_transaksi')
          ->where('jenis_transaksi','penarikan')
          ->where('fid_status',$penarikan->fid_status)
          ->where('user_page','main')
          ->first();
        return view('main.transaksi.penarikan.detail')
          ->with('data',$data)
          ->with('id',$request->id);
      }
      else{
        return redirect('penarikan');
      }
    }

    //----------------------------------------------------PINJAMAN-----------------------------------------//

    public function pinjaman(Request $request){
      $data['pinjaman']=$this->get_transaksi('pinjaman');
      $data['jenis-transaksi']=DB::table('jenis_transaksi')->whereIn('id',array(9,10,11))->get();
      $data['status-transaksi']=DB::table('status_transaksi')->get();
      $data['gaji-pokok']=GlobalHelper::gaji_pokok(Session::get('useractive')->no_anggota);
      return view('main.transaksi.pinjaman.index')
        ->with('data',$data);
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

    public function proses_angsuran($id,$request){
      Angsuran::where('fid_transaksi',$id)->delete();
      for($n=1;$n<=$request->tenor;$n++){
        $field=new Angsuran;
        $field->angsuran_ke=$n;
        $field->fid_transaksi=$id;
        $field->bunga=0.01;
        $field->sisa_hutang=$this->sisa_hutang($id,$n);
        $field->angsuran_pokok=ROUND(str_replace(',','',$request->nominal)/$request->tenor,0);
        $field->angsuran_bunga=ROUND(0.01*str_replace(',','',$request->nominal));
        $field->fid_status=($request->action=='ajukan' ? 2 : 1);
        $field->save();
      }
    }

    public function konfirmasi_angsuran(Request $request){
      $field=Transaksi::find($request->id);
      if(!empty($field)){
        $field->fid_status=1;
        $field->save();
        return redirect('pinjaman/detail?id='.$request->id);
      }
      else{
        return redirect('pinjaman');
      }
    }

    public function pinjaman_detail(Request $request){
      $pinjaman=$this->detail_transaksi($request->id);
      if(!empty($pinjaman)){
        $data['pinjaman']=$pinjaman;
        $data['keterangan']=DB::table('keterangan_status_transaksi')
          ->where('jenis_transaksi','pinjaman')
          ->where('fid_status',$pinjaman->fid_status)
          ->where('user_page','main')
          ->first();
        $data['jenis-transaksi']=DB::table('jenis_transaksi')->whereIn('id',array(9,10,11))->get();
        $data['gaji-pokok']=GlobalHelper::gaji_pokok(Session::get('useractive')->no_anggota);
        $data['angsuran']=Angsuran::select('angsuran.*','status_angsuran.status_angsuran','status_angsuran.color')
          ->join('status_angsuran','status_angsuran.id','=','angsuran.fid_status')
          ->where('angsuran.fid_transaksi',$request->id)
          ->orderBy('angsuran.angsuran_ke','ASC')
          ->get();
        if($data['pinjaman']->fid_status == 0){
          $data['status']=GlobalHelper::validasi_pinjaman($request->id);
          return view('main.transaksi.pinjaman.angsuran')
            ->with('data',$data)
            ->with('id',$request->id);
        }
        else{
          return view('main.transaksi.pinjaman.detail')
            ->with('data',$data)
            ->with('id',$request->id);
        }
      }
      else{
        return redirect('pinjaman');
      }
    }

    //-------------------------------------------RIWAYAT--------------------------------------//

    public function riwayat(Request $request){
      $data['simpanan']=$this->get_transaksi('riwayat','simpanan','all');
      $data['pinjaman']=$this->get_transaksi('riwayat','pinjaman','all');
      $data['jenis-transaksi-simpanan']=DB::table('jenis_transaksi')->whereIn('id',array(1,2,3,4))->get();
      $data['jenis-transaksi-pinjaman']=DB::table('jenis_transaksi')->whereIn('id',array(9,10,11))->get();
      return view('main.transaksi.riwayat.index')
        ->with('data',$data);
    }


}
