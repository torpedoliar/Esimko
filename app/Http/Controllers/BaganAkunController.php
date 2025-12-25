<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\BaganAkun;
use View;
use DB;
use DateTime;
use Redirect;

class BaganAkunController extends Controller
{
    public function index(){
      return view('keuangan.bagan_akun.index');
    }

    public function proses(Request $request){
      $akun=BaganAkun::find($request->id);
      if(!empty($akun)){
        $field=BaganAkun::find($request->id);
        $msg='Bagan Akun berhasil disimpan';
      }
      else{
        $field=new BaganAkun;
        $msg='Bagan Akun berhasil ditambahkan';
      }
      $field->nama_akun=$request->nama_akun;
      $field->kode=$request->kode;
      $field->keterangan=$request->deskripsi;
      $field->is_active=(!empty($request->active) ? 1 : 0);
      $field->parent_id=$request->parent_id;
      if($request->action=='delete'){
        $field->delete();
        $msg='Bagan Akun berhasil dihapus';
      }
      else{
        $field->save();
      }
      return redirect('keuangan/bagan_akun')
        ->with('message',$msg)
        ->with('message_type','success');
    }
}
