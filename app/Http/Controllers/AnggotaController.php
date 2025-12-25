<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\UserAkses;
use App\Transaksi;
use App\Angsuran;
use View;
use DB;
use DateTime;
use Redirect;

class AnggotaController extends Controller
{
    public function get_anggota($status,$search){
      $query=Anggota::select('anggota.*','status_anggota.status_anggota','status_anggota.color')
        ->join('status_anggota','status_anggota.id','=','anggota.fid_status');
      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
            ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
         });
      }
      if($status !='all'){
        $query=$query->where('anggota.fid_status',$status);
      }
      else{
//        $query=$query->whereIn('anggota.fid_status',array(1,2,3,5));
      }
      $result=$query->orderBy('anggota.no_anggota')->paginate(10);
      if(!empty($search)){
        $result->withPath('anggota?search='.$search);
      }
      return $result;
    }

    public function cetak(){
      $data=Anggota::select('no_anggota','nama_lengkap','password')->get();
      foreach ($data as $key => $value) {
        $data[$key]->password=decrypt($value->password);
      }
      return view('anggota.cetak')->with('data',$data);
    }

    public function index(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,7);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $search=(!empty($request->search) ? $request->search : null );
        $status=(!empty($request->status) ? $request->status : 'all' );
        $data['anggota']=$this->get_anggota($status,$search);
        $data['status']=DB::table('status_anggota')->get();
        return view('anggota.index')
          ->with('data',$data)
          ->with('status',$status)
          ->with('search',$search);
      }
    }

    public function form(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,7);
      if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N'){
        return view('404');
      }
      else{
        $anggota=Anggota::find($request->id);
        if(!empty($anggota)){
          $action='edit';
          $id=$request->id;
        }
        else{
          $action='add';
          $id=0;
        }
        $data['anggota']=$anggota;
        $data['status-anggota']=DB::table('status_anggota')->get();
        $hak_akses=DB::table('hak_akses')->where('hak_akses','<>','Anggota')->get();
        foreach ($hak_akses as $key => $value) {
          $cek_data=UserAkses::where('fid_anggota',$id)->where('fid_hak_akses',$value->id)->first();
          if(!empty($cek_data)){
            $hak_akses[$key]->selected='selected';
          }
          else{
            $hak_akses[$key]->selected='';
          }
        }
        $data['hak-akses']=$hak_akses;
        $no_anggota = GlobalHelper::get_nomor_anggota2();
        $data['no_anggota'] = $no_anggota;
        return view('anggota.form')
          ->with('data',$data)
          ->with('action',$action)
          ->with('id',$id);
      }
    }

    public function proses(Request $request){
      if($request->action=='add'){
        $no_anggota=$request->input('no_anggota');
        $anggota=Anggota::where('no_anggota',$no_anggota)->first();
        if(empty($anggota)){
          $field=new Anggota;
          $field->created_at=date('Y-m-d H:i:s');
          $field->created_by=Session::get('useractive')->no_anggota;
          $field->no_anggota=$request->input('no_anggota');
          $field->password=encrypt($field->no_anggota);
        }
        else{
          $field=Anggota::find($anggota->id);
          $field->updated_at=date('Y-m-d H:i:s');
        }
      }
      else{
        $field=Anggota::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
      }

      $field->no_ktp=$request->no_ktp;
      $field->nama_lengkap=$request->nama_lengkap;
      $field->password=encrypt($request->password);

      $field->tempat_lahir=$request->tempat_lahir;
      $field->tanggal_lahir=GlobalHelper::dateFormat($request->tanggal_lahir,'Y-m-d');
      $field->jenis_kelamin=$request->jenis_kelamin;
      $field->nama_panggilan=$request->nama_panggilan;

      $field->no_rekening=$request->no_rekening;
      $field->an_rekening=$field->nama_lengkap;
      $field->nama_bank=$request->nama_bank;

      $field->no_hirs=$request->no_hirs;
      $field->id_karyawan=$request->id_karyawan;
      $field->level=$request->level;
      $field->lokasi=$request->lokasi_kerja;
      $field->bagian=$request->bagian;
      $field->divisi=$request->divisi;
      $field->tanggal_bergabung=GlobalHelper::dateFormat($request->tanggal_bergabung,'Y-m-d');
      $field->tanggal_bekerja=(!empty($request->id_karyawan) ? GlobalHelper::bulan_bekerja($request->id_karyawan) : date('Y-m-d') );

      $field->fid_status=$request->status_anggota;
      $field->no_handphone=$request->no_handphone;
      $field->email=$request->email;
      $field->alamat=$request->alamat;

      if($request->hasFile('avatar')){
        if(!empty($field->avatar)){
          unlink(storage_path('app/'.$field->avatar));
        }
        $uploadedFile = $request->file('avatar');
        $path = $uploadedFile->store('avatar');
        $field->avatar=$path;
      }

      if($request->action=='delete'){
        $field->delete();
        $msg='Data anggota berhasil dihapus';
      }
      else{
        $field->save();
        $this->proses_akses_anggota($field->id,$request->hak_akses);
        $msg='Data anggota berhasil disimpan';
      }
      return redirect('anggota')
        ->with('message',$msg)
        ->with('message_type','success');
    }

    public function get_transaksi($jenis,$anggota){
      $query=Transaksi::select('transaksi.*','jenis_transaksi.jenis_transaksi','jenis_transaksi.operasi','metode_transaksi.metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color')
        ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
        ->Join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
        ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
        ->join('metode_transaksi','metode_transaksi.id','=','transaksi.fid_metode_transaksi')
        ->where('transaksi.fid_anggota',$anggota);

      if($jenis=='simpanan'){
        $query=$query->whereIn('transaksi.fid_jenis_transaksi',array(1,2,3,4,5,6,7,8));
      }
      elseif($jenis=='pinjaman'){
        $query=$query->whereIn('transaksi.fid_jenis_transaksi',array(9,10,11));
      }

      if(!empty(Session::get('filter_transaksi')[$jenis])){
        $filters=Session::get('filter_transaksi');
        if($filters[$jenis]['jenis']!='all'){
          $query=$query->where('transaksi.fid_jenis_transaksi',$filters[$jenis]['jenis']);
        }

        if($filters[$jenis]['status']!='all'){
          $query=$query->where('transaksi.fid_status',$filters[$jenis]['status']);
        }
        else{
          $query=$query->where('transaksi.fid_status','!=',5);
        }

        if(!empty($filters[$jenis]['from']) && !empty($filters[$jenis]['to'])){
          $query=$query->whereBetween('transaksi.tanggal', [GlobalHelper::dateFormat($filters[$jenis]['from'],'Y-m-d'), GlobalHelper::dateFormat($filters[$jenis]['to'],'Y-m-d')]);
        }
      }
      else{
        $query=$query->where('transaksi.fid_status','!=',5);
      }
      $result=$query->orderBy('transaksi.tanggal','DESC')->orderBy('transaksi.created_at','DESC')->paginate(10);
      if($jenis == 'pinjaman'){
        foreach ($result as $key => $value) {
          $angsuran=Angsuran::where('fid_transaksi',$value->id)->first();
          if(!empty($angsuran)){
            $result[$key]->total_angsuran=$angsuran->angsuran_pokok+$angsuran->angsuran_bunga;
            $result[$key]->sisa_pinjaman=Angsuran::where('fid_transaksi',$value->id)->where('fid_status','!=',6)->first()->sisa_hutang;
            $result[$key]->sisa_tenor=Angsuran::where('fid_transaksi',$value->id)->where('fid_status','!=',6)->count();
          }
        }
      }
      return $result;
    }

    public function detail_transaksi($id){
      $data=Transaksi::select('transaksi.*','anggota.no_anggota','jenis_transaksi.jenis_transaksi','jenis_transaksi.group as group_transaksi','anggota.nama_lengkap','metode_transaksi.metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color','status_transaksi.icon')
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

    public function detail(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,7);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $anggota=Anggota::select('anggota.*','status_anggota.status_anggota','status_anggota.color')
          ->join('status_anggota','status_anggota.id','=','anggota.fid_status')
          ->where('no_anggota',$request->anggota)
          ->first();
        if(!empty($anggota)){
          $tab=(!empty($request->tab) ? $request->tab : 'profil');
          $anggota->total_simpanan=GlobalHelper::saldo_tabungan($anggota->no_anggota,'Total Simpanan'); //Total Simpanan
          $anggota->sisa_pinjaman=GlobalHelper::sisa_pinjaman($anggota->no_anggota,'all'); //Sisa pinjaman
          $anggota->total_angsuran=GlobalHelper::angsuran_pinjaman($anggota->no_anggota,'all'); //Sisa pinjaman
          $anggota->simpanan_pokok=GlobalHelper::saldo_tabungan($anggota->no_anggota,1);
          $anggota->simpanan_wajib=GlobalHelper::saldo_tabungan($anggota->no_anggota,2);
          $anggota->simpanan_hari_raya=GlobalHelper::saldo_tabungan($anggota->no_anggota,'Simpanan Hari Raya');
          $anggota->simpanan_sukarela=GlobalHelper::saldo_tabungan($anggota->no_anggota,'Simpanan Sukarela');
          $data['anggota']=$anggota;
          $data['gaji-pokok']=DB::table('gaji_pokok')->where('fid_anggota',$anggota->no_anggota)->get();
          if($request->tab=='simpanan' || $request->tab=='pinjaman'){
            $detail_transaksi=$this->detail_transaksi($request->id);
            if(!empty($detail_transaksi)){
              $data['detail-transaksi']=$detail_transaksi;
              $data['keterangan']=DB::table('keterangan_status_transaksi')
                ->where('jenis_transaksi','simpanan')
                ->where('fid_status',$detail_transaksi->fid_status)
                ->where('user_page','admin')
                ->first();
            }
            else{
              $data['transaksi']=$this->get_transaksi($request->tab,$anggota->no_anggota);
              $data['status-transaksi']=DB::table('status_transaksi')->get();
            }

            if($request->tab=='simpanan'){
              $data['jenis-transaksi']=DB::table('jenis_transaksi')->whereIn('id',array(1,2,3,4,5,6,7,8))->get();
            }
            else{
              $jenis_transkasi=DB::table('jenis_transaksi')->whereIn('id',array(9,10,11))->get();
              $total_sisa=0;
              foreach ($jenis_transkasi as $key => $value) {
                $jenis_transkasi[$key]->sisa_pinjaman=GlobalHelper::sisa_pinjaman(Session::get('useractive')->no_anggota,$value->id);
                $total_sisa=$total_sisa+$jenis_transkasi[$key]->sisa_pinjaman;
              }
              $data['jenis-transaksi']=$jenis_transkasi;
              $data['total-sisa']=$total_sisa;
            }
          }
          return view('anggota.detail.index')
            ->with('tab',$tab)
            ->with('id',$request->id)
            ->with('data',$data);
        }
        else{
          return Redirect::back();
        }
      }
    }

    public function proses_akses_anggota($id_anggota,$hak_akses){
      UserAkses::where('fid_anggota',$id_anggota)->delete();
      if(!empty($hak_akses)){
        foreach ($hak_akses as $key => $akses_id) {
          $field=new UserAkses;
          $field->fid_anggota=$id_anggota;
          $field->fid_hak_akses=$akses_id;
          $field->save();
        }
      }
      $field=new UserAkses;
      $field->fid_anggota=$id_anggota;
      $field->fid_hak_akses=2;
      $field->save();
    }
}
