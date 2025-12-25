<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Transaksi;
use App\PeriodeHariRaya;
use View;
use DB;
use DateTime;
use Redirect;

class PenarikanController extends Controller
{
    //----------------------------------------------------ANGGOTA-----------------------------------------//

    public function get_penarikan($jenis,$search){
      $query=Transaksi::select('transaksi.*','jenis_transaksi.jenis_transaksi','rekening_pembayaran.keterangan as metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color')
      ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
      ->join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
      ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
      ->join('rekening_pembayaran','rekening_pembayaran.id','=','transaksi.fid_metode_transaksi')
      ->whereIn('transaksi.fid_jenis_transaksi',array(6,7,8));
      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
            ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
         });
      }
      $result=$query->orderBy('transaksi.tanggal','DESC')->paginate(10);
      if(!empty($search)){
        $result->withPath('simpanan?search='.$search);
      }
      return $result;
    }


    // public function index(Request $request){
    //   $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,19);
    //   if($data['otoritas']['view']=='N'){
    //     return view('404');
    //   }
    //   else{
    //     $search=(!empty($request->search) ? $request->search : null);
    //     $jenis=(!empty($request->jenis) ? $request->jenis : null);
    //     $data['simpanan']=$this->get_penarikan($jenis,$search);
    //     $penarikan=Transaksi::select('transaksi.*','anggota.nama_lengkap','no_anggota')
    //       ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
    //       ->where('transaksi.id',$request->id)
    //       ->first();
    //     if(!empty($penarikan)){
    //       $action='edit';
    //       $id=$request->id;
    //     }
    //     else{
    //       $action='add';
    //       $id=0;
    //     }
    //     $data['form']=$penarikan;
    //     $data['metode-transaksi']=DB::table('rekening_pembayaran')->where('jenis_transaksi','like','%penarikan%')->get();
    //     $data['jenis-transaksi']=DB::table('jenis_transaksi')->whereIn('id',array(6,7,8))->get();
    //     $data['status-transaksi']=DB::table('status_transaksi')->get();
    //     return view('main.penarikan.index')
    //       ->with('data',$data)
    //       ->with('search',$search);
    //   }
    // }

    //------------------------------------------PENARIKAN SIMPANAN SUKARELA-----------------------------------------------------//

    public function get_penarikan_sukarela($status,$search){
      $query=Transaksi::select('transaksi.*','anggota.no_anggota','anggota.nama_lengkap','metode_transaksi.metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color')
      ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
      ->join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
      ->join('metode_transaksi','metode_transaksi.id','=','transaksi.fid_metode_transaksi')
      ->where('transaksi.fid_jenis_transaksi','6');
      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
            ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
         });
      }
      if($status !='all'){
        $query=$query->where('transaksi.fid_status',$status);
      }
      $result=$query->orderBy('transaksi.tanggal')->paginate(10);
      foreach ($result as $key => $value) {
        $petugas=DB::table('anggota')->where('no_anggota',$value->created_by)->first();
        $result[$key]->nama_petugas=(!empty($petugas) ? $petugas->nama_lengkap : 'Undefined');
      }

      if(!empty($search)){
        $result->withPath('sukarela?search='.$search);
      }
      return $result;
    }

    public function sukarela(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,31);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $search=(!empty($request->search) ? $request->search : null);
        $status=(!empty($request->status) ? $request->status : 'all');
        $data['penarikan']=$this->get_penarikan_sukarela($status,$search);
        $data['status']=DB::table('status_transaksi')->get();
        return view('penarikan.sukarela.index')
          ->with('data',$data)
          ->with('status',$status)
          ->with('search',$search);
      }
    }

    public function form_penarikan_sukarela(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,31);
      if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N'){
        return view('404');
      }
      else{
        $penarikan=Transaksi::select('transaksi.*','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
          ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
          ->where('transaksi.id',$request->id)
          ->first();
        if(!empty($penarikan)){
          $action='edit';
          $id=$request->id;
          $penarikan->saldo=number_format(GlobalHelper::saldo_tabungan($penarikan->no_anggota,4),0,',','.'); //Simpanan Sukarela
        }
        else{
          $action='add';
          $id=0;
        }
        $data['penarikan']=$penarikan;
        $data['metode-transaksi']=DB::table('rekening_pembayaran')->where('jenis_transaksi','like','%penarikan%')->get();
        $data['anggota']=Anggota::limit(10)->get();
        return view('penarikan.sukarela.form')
          ->with('data',$data)
          ->with('action',$action)
          ->with('id',$id);
      }
    }

    public function detail_penarikan(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,31);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $simpanan=Transaksi::select('transaksi.*','anggota.no_anggota','anggota.nama_lengkap','jenis_transaksi.jenis_transaksi','status_transaksi.icon','rekening_pembayaran.keterangan as metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color')
          ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
          ->join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
          ->join('rekening_pembayaran','rekening_pembayaran.id','=','transaksi.fid_metode_transaksi')
          ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
          ->where('transaksi.fid_jenis_transaksi','6')
          ->where('transaksi.id',$request->id)
          ->first();
        if(!empty($simpanan)){
          $anggota=Anggota::where('no_anggota',$simpanan->created_by)->first();
          $simpanan->nama_petugas=(!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
          $data['simpanan']=$simpanan;
          $data['keterangan']=DB::table('keterangan_status_transaksi')
            ->where('jenis_transaksi','penarikan')
            ->where('fid_status',$simpanan->fid_status)
            ->where('user_page','admin')
            ->first();
          return view('penarikan.sukarela.detail')
            ->with('data',$data)
            ->with('id',$request->id);
        }
        else{
          return Redirect::back();
        }
      }
    }

    public function validasi_penarikan($request,$jenis){
      $anggota=Anggota::where('no_anggota',$request->no_anggota)->first();
      if(!empty($anggota)){
        $saldo=GlobalHelper::saldo_tabungan($request->no_anggota,$jenis);
        if(str_replace(',','',$request->jumlah)>$saldo){
          $nilai='Saldo simpanan tidak mencukupi';
        }
        else{
          $nilai=1;
        }
      }
      else{
        $nilai='Anggota tidak ditemukan / belum dipilih';
      }
      return $nilai;
    }

    public function proses_penarikan_sukarela(Request $request){
      $validasi=$this->validasi_penarikan($request,'Simpanan Sukarela');
      if($validasi==1){
        if($request->action=='add'){
          $field=new Transaksi;
          $field->created_at=date('Y-m-d H:i:s');
          $field->created_by=Session::get('useractive')->no_anggota;
          $field->fid_status=1; //New Request
          $field->fid_jenis_transaksi=6; //Penarikan Simpanan Sukarela
        }
        else{
          $field=Transaksi::find($request->id);
          $field->updated_at=date('Y-m-d H:i:s');
        }
        $field->fid_anggota=$request->no_anggota;
        $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
        $field->fid_metode_transaksi=$request->metode_transaksi;
        $field->nominal=-str_replace(',','',$request->jumlah);
        $field->keterangan=$request->keterangan;
        if($request->action=='delete'){
          $field->delete();
          $msg='Transaksi penarikan simpanan sukarela berhasil dihapus';
          $url='penarikan/sukarela';
        }
        else{
          $field->save();
          $msg='Transaksi penarikan simpanan sukarela berhasil disimpan';
          $url='penarikan/sukarela/detail?id='.$field->id;
        }
        return redirect($url)
          ->with('message',$msg)
          ->with('message_type','success');
      }
      else{
        return Redirect::back()
          ->with('message',$validasi)
          ->with('message_type','warning');
      }
    }

    public function verifikasi_penarikan_sukarela(Request $request){
      $field=Transaksi::find($request->id);
      $field->fid_status=$request->status;
      $field->save();
      $status=DB::table('status_transaksi')->find($field->fid_status);
      GlobalHelper::add_verifikasi_transaksi('transaksi',$field->id,(!empty($status) ? $status->caption : ''),null);
      return Redirect::back()
        ->with('message','Penarikan simpanan sukarela berhasil diverikasi')
        ->with('message_type','success');
    }


    //-------------------------------------------PENARIKAN SIMPANAN HARI RAYA------------------------------------------------------------//

    public function get_penarikan_hari_raya($id,$search){
      $query=Transaksi::select('transaksi.*','anggota.*','status_transaksi.status','status_transaksi.color')
        ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
        ->join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
        ->where('transaksi.fid_jenis_transaksi','7')
        ->where('transaksi.fid_payroll',$id);
      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
            ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
         });
      }
      $data=$query->orderBy('transaksi.tanggal')->paginate(10);
      $jumlah_anggota=$query->count();
      $total=$query->sum('transaksi.nominal');
      $result=array('data'=>$data,'jumlah'=>$jumlah_anggota,'total'=>$total);
      return $result;
    }


    function hari_raya(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,32);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $periode=PeriodeHariRaya::select('periode_hari_raya.*','status_transaksi.icon')
          ->join('status_transaksi','status_transaksi.id','=','periode_hari_raya.fid_status')
          ->where('periode_hari_raya.id',$request->id)
          ->first();
        if(!empty($periode)){
          $action='edit';
          $id=$request->id;
          $data['keterangan']=DB::table('keterangan_status_transaksi')
            ->where('jenis_transaksi','penarikan')
            ->where('fid_status',$periode->fid_status)
            ->where('user_page','admin')
            ->first();
          $periode->jumlah=$this->get_penarikan_hari_raya($id,null)['jumlah'];
          $periode->total=$this->get_penarikan_hari_raya($id,null)['total'];
          $data['detail-periode']=$periode;
        }
        else{
          $periode=new PeriodeHariRaya;
          $action='add';
          $id=0;
        }
        $search=(!empty($request->search) ? $request->search : null);
        $data['penarikan']=$this->get_penarikan_hari_raya($id,$search)['data'];
        $data['periode']=PeriodeHariRaya::get();
        return view('penarikan.hari_raya.index')
          ->with('data',$data)
          ->with('action',$action)
          ->with('id',$id)
          ->with('search',$search);
      }
    }

    function proses_hari_raya(Request $request){
      if($request->action=='add'){
        $field=new PeriodeHariRaya;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
      }
      else{
        $field=PeriodeHariRaya::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
      }
      $field->fid_status=1;
      $field->periode=$request->periode;
      $field->keterangan=$request->keterangan;
      $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
      if($request->action=='delete'){
        $field->delete();
        $msg='Periode Penarikan Simpanan Hari Raya berhasil dihapus';
        $page='penarikan/hari_raya';
      }
      else{
        $field->save();
        $msg='Penarikan Simpanan Hari Raya '.$field->periode.' H berhasil diproses';
        $page='penarikan/hari_raya?id='.$field->id;
        $this->proses_payroll_hari_raya($field->id,$request);
      }
      return redirect($page)
        ->with('message',$msg)
        ->with('message_type','success');
    }

    function proses_payroll_hari_raya($id,$request){
      Transaksi::where('fid_payroll',$id)->where('fid_jenis_transaksi',7)->delete();
      $anggota=Anggota::whereIn('fid_status',array(2,3))->where('no_anggota','<>',null)->get(); //Anggota Baru dan Aktif
      foreach ($anggota as $key => $value) {
        $field=new Transaksi;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $field->fid_status=4;
        $field->fid_jenis_transaksi=7;
        $field->fid_anggota=$value->no_anggota;
        $field->fid_metode_transaksi=2;
        $field->fid_payroll=$id;
        $field->nominal=-str_replace(',','',GlobalHelper::saldo_tabungan($field->fid_anggota, 4));
        $field->tanggal=date('Y-m-d');
        if($field->nominal!=0){
          $field->save();
        }
      }
    }

    public function verifikasi_hari_raya(Request $request){
      $field=PeriodeHariRaya::find($request->id);
      $field->updated_at=date('Y-m-d H:i:s');
      $field->fid_status=$request->status;
      $field->save();
      $status=DB::table('status_transaksi')->find($field->fid_status);
      GlobalHelper::add_verifikasi_transaksi('penarikan_hari_raya',$field->id,(!empty($status) ? $status->caption : ''),null);
      $this->update_status_transaksi($request->id,$field->fid_status);
      return Redirect::back()
        ->with('message','Proses Verifikasi Penarikan Simpanan Hari Raya berhasil')
        ->with('message_type','success');
    }

    public function update_status_transaksi($id,$status){
      $simpanan=Transaksi::where('fid_payroll',$id)->where('fid_jenis_transaksi',7)->get();
      foreach ($simpanan as $key => $value) {
        $field=Transaksi::find($value->id);
        $field->fid_status=$status;
        $field->save();
      }
    }


    //-------------------------------------------PENARIKAN PENUTUPAN SIMPANAN------------------------------------------------------------//

    public function get_penutupan_simpanan($status,$search){
      $query=Transaksi::select('transaksi.*','anggota.no_anggota','anggota.nama_lengkap','rekening_pembayaran.keterangan as metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color')
      ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
      ->join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
      ->join('rekening_pembayaran','rekening_pembayaran.id','=','transaksi.fid_metode_transaksi')
      ->where('transaksi.fid_jenis_transaksi','8');
      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
            ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
         });
      }
      if($status!='all'){
        $query=$query->where('transaksi.fid_status',$status);
      }
      $result=$query->orderBy('transaksi.tanggal')->paginate(10);
      if(!empty($search)){
        $result->withPath('pentupan?search='.$search);
      }
      return $result;
    }

    function penutupan(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,33);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $search=(!empty($request->search) ? $request->search : null);
        $status=(!empty($request->status) ? $request->status : 'all');
        $data['penarikan']=$this->get_penutupan_simpanan($status,$search);
        $data['status']=DB::table('status_transaksi')->get();
        return view('penarikan.penutupan.index')
          ->with('data',$data)
          ->with('status',$status)
          ->with('search',$search);
      }
    }

    public function form_penutupan(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,33);
      if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N'){
        return view('404');
      }
      else{
        $penarikan=Transaksi::select('transaksi.*','anggota.nama_lengkap','anggota.no_anggota')
          ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
          ->where('transaksi.id',$request->id)
          ->first();
        if(!empty($penarikan)){
          $action='edit';
          $id=$request->id;
        }
        else{
          $action='add';
          $id=0;
        }
        $data['penarikan']=$penarikan;
        $data['metode-transaksi']=DB::table('rekening_pembayaran')->where('jenis_transaksi','like','%penarikan%')->get();
        $data['anggota']=Anggota::limit(10)->get();
        return view('penarikan.penutupan.form')
          ->with('data',$data)
          ->with('action',$action)
          ->with('id',$id);
      }
    }

    public function proses_penutupan(Request $request){
      if($request->action=='add'){
        $field=new Transaksi;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $field->fid_status=1; //New Request
        $field->fid_jenis_transaksi=8; //Penutupan Simpanan
      }
      else{
        $field=Transaksi::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
      }
      $field->fid_anggota=$request->no_anggota;
      $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
      $field->fid_metode_transaksi=$request->metode_transaksi;
      $field->nominal=-str_replace('.','',$request->jumlah);
      $field->keterangan=$request->keterangan;
      if($request->action=='delete'){
        $field->delete();
        $msg='Transaksi penarikan simpanan sukarela berhasil dihapus';
      }
      else{
        $field->save();
        $msg='Transaksi penarikan simpanan sukarela berhasil disimpan';
      }
      return redirect('penarikan/penutupan')
        ->with('message',$msg)
        ->with('message_type','success');
    }

    public function detail_penutupan(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,33);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $simpanan=Transaksi::select('transaksi.*','anggota.no_anggota','anggota.nama_lengkap','jenis_transaksi.jenis_transaksi','status_transaksi.icon','rekening_pembayaran.keterangan as metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color')
          ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
          ->join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
          ->join('rekening_pembayaran','rekening_pembayaran.id','=','transaksi.fid_metode_transaksi')
          ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
          ->where('transaksi.fid_jenis_transaksi','8')
          ->where('transaksi.id',$request->id)
          ->first();
        if(!empty($simpanan)){
          $data['simpanan']=$simpanan;
          $data['keterangan']=DB::table('keterangan_status_transaksi')
            ->where('jenis_transaksi','penarikan')
            ->where('fid_status',$simpanan->fid_status)
            ->where('user_page','admin')
            ->first();
          return view('penarikan.penutupan.detail')
            ->with('data',$data)
            ->with('id',$request->id);
        }
        else{
          return Redirect::back();
        }
      }
    }

    public function verifikasi_penutupan_simpanan(Request $request){
      $field=Transaksi::find($request->id);
      $field->fid_status=$request->status;
      $field->save();

      $anggota=Anggota::where('no_anggota',$field->fid_anggota)->first();
      $anggota->fid_status=($field->fid_status == 4 ? 4 : 3 );
      $anggota->save();

      return Redirect::back()
        ->with('message','Penarikan semua simpanan berhasil diverikasi')
        ->with('message_type','success');
    }

}
