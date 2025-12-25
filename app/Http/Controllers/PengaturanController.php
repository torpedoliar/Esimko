<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\UserAkses;
use App\OtoritasUser;
use App\RekeningPembayaran;
use App\OtoritasRekeningPembayaran;
use View;
use DB;
use DateTime;
use Redirect;

class PengaturanController extends Controller
{

    //-------------------------------------OTORITAS USER-------------------------------------------//

    public function get_modul($hak_akases,$parent){
      $data=DB::table('modul')->where('parent_id',$parent)->orderBy('order')->get();
      foreach ($data as $key => $value) {
        $data[$key]->otoritas=GlobalHelper::otoritas_modul($hak_akases,$value->id);
        $data[$key]->submodul=$this->get_modul($hak_akases,$value->id);
      }
      return $data;
    }

    public function otoritas_user(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,37);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $hak_akses=(!empty($request->hak_akses) ? $request->hak_akses : '1');
        $data['hak-akses']=DB::table('hak_akses')->where('id','<>',2)->get();
        $data['modul']=$this->get_modul($hak_akses,0);
        return view('pengaturan.otoritas_user.index')
          ->with('hak_akses',$hak_akses)
          ->with('data',$data);
      }
    }

    public function proses_otoritas_user(Request $request){
      foreach ($request->id as $value) {
        $otoritas=OtoritasUser::where('fid_modul','=',$value)
          ->where('fid_hak_akses','=',$request->hak_akses)
          ->delete();
        $field=new OtoritasUser;
        $field->fid_hak_akses=$request->hak_akses;
        $field->fid_modul=$value;
        $field->is_view=((!empty($request->view[$value])) ? 'Y' : 'N');
        $field->is_insert=((!empty($request->insert[$value])) ? 'Y' : 'N');
        $field->is_update=((!empty($request->update[$value])) ? 'Y' : 'N');
        $field->is_delete=((!empty($request->delete[$value])) ? 'Y' : 'N');
        $field->is_print=((!empty($request->print[$value])) ? 'Y' : 'N');
        $field->is_verified=((!empty($request->verified[$value])) ? 'Y' : 'N');
        $field->save();
      }
      return Redirect::back()
        ->with('message','Otoritas User berhasil disimpan')
        ->with('message_type','success');
    }

    //----------------------------Otoritas Rekening Pembayaran---------------------------------//

    public function metode_pembayaran(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,43);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $data['rekening-pembayaran']=RekeningPembayaran::select('rekening_pembayaran.*','metode_pembayaran.metode_pembayaran')
          ->join('metode_pembayaran','metode_pembayaran.id','=','rekening_pembayaran.fid_metode_pembayaran')
          ->get();
        return view('pengaturan.metode_pembayaran.index')
          ->with('data',$data);
      }
    }
}
