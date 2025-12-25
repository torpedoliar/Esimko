<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\BaganAkun;
use App\Jurnal;
use App\JurnalDetail;
use View;
use DB;
use DateTime;
use Redirect;

class JurnalUmumController extends Controller
{
    public function get_jurnal($search){
      $query=Jurnal::select('jurnal.*','anggota.nama_lengkap','anggota.no_anggota')
        ->join('anggota','anggota.no_anggota','=','jurnal.created_by');

      if(!empty($search)){
        $query=$query->where('jurnal.nomor_jurnal', 'like', "%{$search}%")
                     ->orwhere('jurnal.deskripsi', 'like', "%{$search}%");;
      }
      $result=$query->orderBy('jurnal.tanggal')->paginate(10);
      foreach ($result as $key => $value) {
        $jurnal_detail=JurnalDetail::where('fid_jurnal',$value->id);
        $result[$key]->debit=$jurnal_detail->sum('debit');
        $result[$key]->kredit=$jurnal_detail->sum('kredit');
      }
      if(!empty($search)){
        $result->withPath('jurnal_umum?search='.$search);
      }
      return $result;
    }

    public function index(Request $request){
      $search=(!empty($request->search) ? $request->search : null);
      $data['jurnal']=$this->get_jurnal($search);
      return view('keuangan.jurnal_umum.index')
        ->with('data',$data)
        ->with('search',$search);
    }

    public function form(Request $request){
      $jurnal=Jurnal::find($request->id);
      if(!empty($jurnal)){
        $data['jurnal-detail']=JurnalDetail::select('jurnal_detail.*','bagan_akun.kode','bagan_akun.nama_akun')
          ->leftJoin('bagan_akun','bagan_akun.kode','jurnal_detail.kode_akun')
          ->where('jurnal_detail.fid_jurnal',$request->id)
          ->get();
        $jurnal_detail=JurnalDetail::where('fid_jurnal',$request->id);
        $jurnal->total_debit=$jurnal_detail->sum('debit');
        $jurnal->total_kredit=$jurnal_detail->sum('kredit');
        $data['jurnal']=$jurnal;
        return view('keuangan.jurnal_umum.form')
          ->with('data',$data)
          ->with('id',$request->id);
      }
      else{
        return redirect('keuangan/jurnal_umum')
          ->with('message','Jurnal tidak Ditemukan')
          ->with('message_type','warning');
      }
    }

    public function proses(Request $request){
      if($request->action=='add'){
        $field=new Jurnal;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $field->st_balance=0;
        $field->nomor_jurnal=GlobalHelper::get_nomor_jurnal('GL',GlobalHelper::dateFormat($request->date,'Y-m-d'));
        $msg='Data Jurnal berhasil ditambahkan';
        $url='keuangan/jurnal_umum/form?id='.$field->id;
      }
      else{
        $field=Jurnal::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
        $msg='Data Jurnal berhasil disimpan';
        $url='keuangan/jurnal_umum';
      }
      if($request->action=='delete'){
        $field->delete();
        JurnalDetail::where('fid_jurnal',$request->id)->delete();
        $msg='Data Jurnal berhasil dihapus';
        $url='keuangan/jurnal_umum';
      }
      else{
        $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
        $field->deskripsi=$request->deskripsi;
        $field->save();
      }
      return redirect($url)
        ->with('message',$msg)
        ->with('message_type','success');
    }

    public function update_status_balance($id){
      $journal_detail=JurnalDetail::where('fid_jurnal',$id);
      $debit=$journal_detail->sum('debit');
      $kredit=$journal_detail->sum('kredit');
      $field=Jurnal::find($id);
      if($debit==$kredit){
        $field->st_balance=1;
      }
      else{
        $field->st_balance=0;
      }
      $field->save();
    }

    public function proses_detail(Request $request){
      $jurnal=Jurnal::find($request->jurnal_id);
      if(!empty($jurnal)){
        if($request->action=='add'){
          $field=new JurnalDetail;
          $field->created_at=date('Y-m-d H:i:s');
          $field->created_by=Session::get('useractive')->no_anggota;
          $msg='Akun Jurnal berhasil ditambahkan';
        }
        else{
          $field=JurnalDetail::find($request->id);
          $field->updated_at=date('Y-m-d H:i:s');
          $msg='Akun Jurnal berhasil disimpan';
        }
        if($request->action=='delete'){
          $field->delete();
          $msg='Akun Jurnal berhasil dihapus';
        }
        else{
          $field->fid_jurnal=$request->jurnal_id;
          $field->kode_akun=$request->kode_akun;
          $field->debit=(!empty($request->debit) ? str_replace(',','',$request->debit) : 0 );
          $field->kredit=(!empty($request->kredit) ? str_replace(',','',$request->kredit) : 0 );
          $field->save();
        }
        $this->update_status_balance($request->jurnal_id);
        return redirect('keuangan/jurnal_umum/form?id='.$request->jurnal_id)
          ->with('message',$msg)
          ->with('message_type','success');
      }
      else{
        return redirect('keuangan/jurnal_umum')
          ->with('message','Jurnal tidak Ditemukan')
          ->with('message_type','warning');
      }
    }
}
