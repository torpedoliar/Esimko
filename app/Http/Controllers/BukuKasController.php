<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\BaganAkun;
use App\Jurnal;
use App\JurnalDetail;
use App\BukuKas;
use App\BukuKasDetail;
use View;
use DB;
use DateTime;
use Redirect;

class BukuKasController extends Controller
{
    public function get_buku_kas($search,$jenis){
      $query=BukuKas::select('buku_kas.*','bagan_akun.nama_akun','bagan_akun.kode','anggota.nama_lengkap','anggota.no_anggota')
        ->join('bagan_akun','bagan_akun.kode','=','buku_kas.akun_kas')
        ->join('anggota','anggota.no_anggota','=','buku_kas.created_by');

      if($jenis != 'all'){
        $query=$query->where('jenis',$jenis);
      }

      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('buku_kas.no_transaksi', 'like', "%{$search}%")
            ->orWhere('buku_kas.akun_kas', 'like', "%{$search}%")
            ->orWhere('buku_kas.catatan', 'like', "%{$search}%");
         });
      }

      $result=$query->orderBy('buku_kas.tanggal')->paginate(10);
      foreach ($result as $key => $value) {
        $result[$key]->nominal=BukuKasDetail::where('fid_buku_kas',$value->id)->sum('nominal');
      }
      if(!empty($search)){
        $result->withPath('buku_kas?search='.$search);
      }
      return $result;
    }

    public function index(Request $request){
      $search=(!empty($request->search) ? $request->search : null);
      $jenis=(!empty($request->jenis) ? $request->jenis : 'all');
      $data['buku_kas']=$this->get_buku_kas($search,$jenis);
      $data['akun_kas']=BaganAkun::whereIn('kode',array('1-1-0001','1-1-0002'))->get();
      return view('keuangan.buku_kas.index')
        ->with('data',$data)
        ->with('jenis',$jenis)
        ->with('search',$search);
    }

    public function form(Request $request){
      $buku_kas=BukuKas::find($request->id);
      if(!empty($buku_kas)){
        $data['items']=BukuKasDetail::select('buku_kas_detail.*','bagan_akun.nama_akun')
          ->join('bagan_akun','bagan_akun.kode','=','buku_kas_detail.kode_akun')
          ->where('fid_buku_kas',$request->id)
          ->get();
        $buku_kas->nominal=BukuKasDetail::where('fid_buku_kas',$request->id)->sum('nominal');
        $data['akun_kas']=BaganAkun::whereIn('kode',array('1-1-0001','1-1-0002'))->get();
        $data['buku_kas']=$buku_kas;
        return view('keuangan.buku_kas.form')
          ->with('data',$data)
          ->with('id',$request->id);
      }
      else{
        return redirect('keuangan/buku_kas')
          ->with('message','Buku Kas tidak Ditemukan')
          ->with('message_type','warning');
      }
    }

    public function proses(Request $request){
      if($request->action=='add'){
        $field=new BukuKas;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $field->jenis=$request->jenis;
        $field->catatan=(!empty($request->catatan) ? $request->catatan : $request->deskripsi);
        $msg='Transaksi Buku Kas berhasil ditambahkan';
      }
      else{
        $field=BukuKas::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
        $msg='Transaksi Buku Kas berhasil disimpan';
      }
      if($request->action=='delete'){
        $field->delete();
        $this->delete_journal($request->id);
        $msg='Transaksi Buku Kas berhasil dihapus';
        $url='keuangan/buku_kas';
      }
      else{
        $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
        $field->no_transaksi=$request->no_transaksi;
        $field->akun_kas=$request->akun_kas;
        $field->save();
        $id_jurnal=$this->proses_jurnal($field->id);
        if($request->action=='add'){
          $this->proses_detail($request,$field->id,$id_jurnal);
        }
        $url='keuangan/buku_kas/form?id='.$field->id;
      }
      return redirect($url)
        ->with('message',$msg)
        ->with('message_type','success');
    }

    public function proses_detail($request,$id,$id_jurnal){
      if($request->action=='add'){
        $field=new BukuKasDetail;
        $field->fid_buku_kas=$id;
      }
      else{
        $field=BukuKasDetail::find($request->id);
      }
      if($request->action=='delete'){
        $field->delete();
      }
      else{
        $field->deskripsi=$request->deskripsi;
        $field->kode_akun=$request->kode_akun;
        $field->nominal=str_replace(',','',$request->nominal);
        $field->save();
        $this->proses_jurnal_detail($id_jurnal,'buku_kas',$id);
        $this->proses_jurnal_detail($id_jurnal,'buku_kas_detail',$field->id);
      }
    }

    public function proses_jurnal($id){
      $buku_kas=BukuKas::find($id);
      if(!empty($buku_kas)){
        $jurnal=Jurnal::where('data_from','buku_kas')->where('data_id',$id)->first();
        if(!empty($jurnal)){
          $field=Jurnal::find($jurnal->id);
          $field->updated_at=date('Y-m-d H:i:s');
          $action='edit';
        }
        else{
          $field=new Jurnal;
          $field->created_at=date('Y-m-d H:i:s');
          $field->created_by=Session::get('useractive')->no_anggota;
          $field->data_from='buku_kas';
          $field->data_id=$id;
          $field->st_balance=1;
          $field->nomor_jurnal=GlobalHelper::get_nomor_jurnal(($buku_kas->jenis=='masuk' ? 'CI' : 'CO'),GlobalHelper::dateFormat($buku_kas->tanggal,'Y-m-d'));
          $action='add';
        }
        $field->tanggal=GlobalHelper::dateFormat($buku_kas->tanggal,'Y-m-d');
        $field->deskripsi='Buku Kas '.($buku_kas->jenis=='masuk' ? 'Masuk' : 'Keluar').' No. '.$buku_kas->no_transaksi;
        $field->save();
        return $field->id;
      }
    }

    public function proses_jurnal_detail($id,$data_from,$data_id){
      $jurnal=Jurnal::select('jurnal.*','buku_kas.jenis','buku_kas.akun_kas')
        ->join('buku_kas','buku_kas.id','=','jurnal.data_id')
        ->where('jurnal.id',$id)
        ->first();
      if(!empty($jurnal)){
        $jurnal_detail=JurnalDetail::where('fid_jurnal',$id)->where('data_from',$data_from)->where('data_id',$data_id)->first();
        if(!empty($jurnal_detail)){
          $field=JurnalDetail::find($jurnal_detail->id);
          $field->updated_at=date('Y-m-d H:i:s');
        }
        else{
          $field=new JurnalDetail;
          $field->fid_jurnal=$id;
          $field->data_from=$data_from;
          $field->data_id=$data_id;
          $field->created_at=date('Y-m-d H:i:s');
          $field->created_by=Session::get('useractive')->no_anggota;
        }
        if($data_from=='buku_kas'){
          $field->kode_akun=$jurnal->akun_kas;
          $nominal=BukuKasDetail::where('fid_buku_kas',$jurnal->data_id)->sum('nominal');
          if($jurnal->jenis=='masuk'){
            $field->debit=$nominal;
            $field->kredit=0;
          }
          else{
            $field->debit=0;
            $field->kredit=$nominal;
          }
        }
        else{
          $buku_kas_detail=BukuKasDetail::find($data_id);
          $field->kode_akun=$buku_kas_detail->kode_akun;
          $nominal=BukuKasDetail::where('fid_buku_kas',$jurnal->data_id)->where('kode_akun',$buku_kas_detail->kode_akun)->sum('nominal');
          if($jurnal->jenis=='masuk'){
            $field->debit=0;
            $field->kredit=$nominal;
          }
          else{
            $field->debit=$nominal;
            $field->kredit=0;
          }
        }
        $field->save();
      }
    }
}
