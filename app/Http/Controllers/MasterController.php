<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\UserAkses;
use App\OtoritasUser;
use App\Karyawan;
use App\Pengurus;
use App\PeriodePengurus;
use App\Anggota;
use App\RekeningPembayaran;
use App\Berita;
use App\AttachmentBerita;
use App\SyaratKetentuan;
use App\KategoriProduk;
use View;
use DB;
use DateTime;
use Redirect;

class MasterController extends Controller
{

    //---------------------------------------KARYAWAN-------------------------------------------//

    public function get_karyawan($search){
      $query=Karyawan::select('karyawan.*','anggota.*','karyawan.id','anggota.id as anggota_id')
        ->join('anggota','anggota.no_anggota','=','karyawan.fid_anggota');
      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
            ->orWhere('anggota.no_anggota', 'like', "%{$search}%")
            ->orWhere('karyawan.jabatan', 'like', "%{$search}%");
         });
      }
      $result=$query->orderBy('anggota.nama_lengkap')->paginate(10);
      foreach ($result as $key => $value) {
        $user_akses=UserAkses::select('hak_akses.hak_akses')
          ->join('hak_akses','hak_akses.id','=','user_akses.fid_hak_akses')
          ->where('fid_anggota',$value->anggota_id)
          ->where('fid_hak_akses','<>',2)->get();
        $result[$key]->user_akses='';
        foreach ($user_akses as $key2 => $value2) {
          $result[$key]->user_akses .='<div style="background:#eaecef;padding:5px 8px;margin-right:5px;white-space:nowrap;margin-top:5px">'.$value2->hak_akses.'</div> ';
        }
      }
      if(!empty($search)){
        $result->withPath('karyawan?search='.$search);
      }
      return $result;
    }

    public function karyawan(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,9);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $serach=(!empty($request->search) ? $request->search : null);
        $data['karyawan']=$this->get_karyawan($serach);
        return view('master.karyawan.index')
          ->with('search',$serach)
          ->with('data',$data);
      }
    }


    public function form_karyawan(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,9);
      if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N' ){
        return view('404');
      }
      else{
        $karyawan=Karyawan::select('karyawan.*','anggota.nama_lengkap','anggota.avatar','anggota.no_anggota','anggota.id as id_pegawai')
          ->join('anggota','anggota.no_anggota','=','karyawan.fid_anggota')
          ->where('karyawan.id',$request->id)
          ->first();
        if(!empty($karyawan)){
          $action='edit';
          $id=$request->id;
          $id_pegawai=$karyawan->id_pegawai;
        }
        else{
          $action='add';
          $id=0;
          $id_pegawai=0;
        }
        $data['karyawan']=$karyawan;
        $hak_akses=DB::table('hak_akses')->where('hak_akses','<>','Anggota')->get();
        foreach ($hak_akses as $key => $value) {
          $cek_data=UserAkses::where('fid_anggota',$id_pegawai)->where('fid_hak_akses',$value->id)->first();
          if(!empty($cek_data)){
            $hak_akses[$key]->selected='selected';
          }
          else{
            $hak_akses[$key]->selected='';
          }
        }
        $data['hak-akses']=$hak_akses;
        $data['anggota']=Anggota::limit(10)->get();
        return view('master.karyawan.form')
          ->with('data',$data)
          ->with('action',$action)
          ->with('id',$id);
      }
    }

    public function proses_karyawan(Request $request){
      if($request->action=='add'){
        $field=new Karyawan;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
      }
      else{
        $field=Karyawan::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
      }
      $field->fid_anggota=$request->no_anggota;
      $field->status=$request->status;
      $field->jabatan=$request->jabatan;
      $field->mulai_bekerja=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
      if($request->action=='delete'){
        $field->delete();
        $msg='Data Karyawan berhasil dihapus';
      }
      else{
        $field->save();
        // $this->proses_akses_anggota($field->fid_anggota,$request->user_akses);
        $msg='Data Karyawan berhasil disimpan';
      }
      return redirect('master/karyawan')
        ->with('message',$msg)
        ->with('message_type','success');

    }

    public function proses_akses_anggota($no_anggota,$hak_akses){
      $anggota=Anggota::where('no_anggota',$no_anggota)->first();
      if(!empty($anggota)){
        UserAkses::where('fid_anggota',$anggota->id)->where('fid_hak_akses','<>',2)->delete();
        if(count($hak_akses)!=0){
          foreach ($hak_akses as $key => $akses_id) {
            $field=new UserAkses;
            $field->fid_anggota=$anggota->id;
            $field->fid_hak_akses=$akses_id;
            $field->save();
          }
        }
      }
    }

    //---------------------------------------PENGURUS DAN PENGAWAS-------------------------------------------//

    public function get_pengurus($periode,$search){
      $query=Pengurus::select('pengurus.*','anggota.*','pengurus.id','anggota.id as anggota_id','jabatan_pengurus.nama_jabatan')
        ->join('anggota','anggota.no_anggota','=','pengurus.fid_anggota')
        ->join('jabatan_pengurus','jabatan_pengurus.id','=','pengurus.fid_jabatan')
        ->where('pengurus.fid_periode',$periode);
      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
            ->orWhere('anggota.no_anggota', 'like', "%{$search}%")
            ->orWhere('jabatan_pengurus.nama_jabatan', 'like', "%{$search}%");
         });
      }
      $result=$query->orderBy('jabatan_pengurus.id')->paginate(10);
      foreach ($result as $key => $value) {
        $user_akses=UserAkses::select('hak_akses.hak_akses')
          ->join('hak_akses','hak_akses.id','=','user_akses.fid_hak_akses')
          ->where('fid_anggota',$value->anggota_id)
          ->where('fid_hak_akses','<>',2)->get();
        $result[$key]->user_akses='';
        foreach ($user_akses as $key2 => $value2) {
          $result[$key]->user_akses .='<span style="background:#eaecef;padding:5px 8px;margin-right:5px">'.$value2->hak_akses.'</span> ';
        }
      }
      if(!empty($search)){
        $result->withPath('pengurus?search='.$search);
      }
      return $result;
    }

    public function pengurus(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,8);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $periode=(!empty($request->periode) ? $request->periode : 'new');
        $serach=(!empty($request->search) ? $request->search : null);
        $data['pengurus']=$this->get_pengurus($request->periode,$serach);
        $data['pilih-periode']=PeriodePengurus::get();
        $data['periode']=PeriodePengurus::find($periode);
        return view('master.pengurus.index')
          ->with('search',$serach)
          ->with('periode',$periode)
          ->with('data',$data);
      }
    }


    public function form_pengurus(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,8);
      if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N' ){
        return view('404');
      }
      else{
        $pengurus=Pengurus::select('pengurus.*','anggota.*','pengurus.id','anggota.id as anggota_id','jabatan_pengurus.nama_jabatan','periode_pengurus.periode_awal','periode_pengurus.periode_akhir')
          ->join('anggota','anggota.no_anggota','=','pengurus.fid_anggota')
          ->join('jabatan_pengurus','jabatan_pengurus.id','=','pengurus.fid_jabatan')
          ->join('periode_pengurus','periode_pengurus.id','=','pengurus.fid_periode')
          ->where('pengurus.id',$request->id)
          ->first();
        if(!empty($pengurus)){
          $action='edit';
          $id=$request->id;
          $id_pegawai=$pengurus->id_pegawai;
        }
        else{
          $action='add';
          $id=0;
          $id_pegawai=0;
        }
        $data['pengurus']=$pengurus;
        $data['pilih-jabatan']=DB::table('jabatan_pengurus')->get();
        $data['pilih-periode']=PeriodePengurus::get();
        $hak_akses=DB::table('hak_akses')->where('hak_akses','<>','Anggota')->get();
        foreach ($hak_akses as $key => $value) {
          $cek_data=UserAkses::where('fid_anggota',$id_pegawai)->where('fid_hak_akses',$value->id)->first();
          if(!empty($cek_data)){
            $hak_akses[$key]->selected='selected';
          }
          else{
            $hak_akses[$key]->selected='';
          }
        }
        $data['hak-akses']=$hak_akses;
        return view('master.pengurus.form')
          ->with('data',$data)
          ->with('action',$action)
          ->with('id',$id);
      }
    }

    public function proses_periode_pengurus($request){
      if($request->periode=='new'){
        $field=new PeriodePengurus;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
      }
      else{
        $field=PeriodePengurus::find($request->periode);
        $field->updated_at=date('Y-m-d H:i:s');
      }

      $field->periode_awal=$request->awal;
      $field->periode_akhir=$request->akhir;
      $field->save();

      return $field->id;
    }

    public function proses_pengurus(Request $request){
      if($request->action=='add'){
        $field=new Pengurus;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
      }
      else{
        $field=Pengurus::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
      }
      $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
      $field->fid_anggota=$request->no_anggota;
      $field->fid_jabatan=$request->jabatan;
      $field->fid_periode=$this->proses_periode_pengurus($request);
      $field->status=$request->status;
      if($request->action=='delete'){
        $field->delete();
        $msg='Data Pengurus berhasil dihapus';
        $url='master/pengurus';
      }
      else{
        $field->save();
        // $this->proses_akses_anggota($field->fid_anggota,$request->user_akses);
        $msg='Data Pengurus berhasil disimpan';
        $url='master/pengurus?periode='.$field->fid_periode;
      }
      return redirect($url)
        ->with('message',$msg)
        ->with('message_type','success');

    }

    //---------------------------------------METODE PEMAYARAN-------------------------------------------//

    public function rekening_pembayaran(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,36);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $search=(!empty($request->search) ? $request->search : null );
        $data['rekening-pembayaran']=RekeningPembayaran::select('rekening_pembayaran.*','metode_pembayaran.metode_pembayaran')
          ->join('metode_pembayaran','metode_pembayaran.id','=','rekening_pembayaran.fid_metode_pembayaran')
          ->get();
        $data['metode-pembayaran']=DB::table('metode_pembayaran')->get();
        return view('master.rekening_pembayaran.index')
          ->with('data',$data)
          ->with('search',$search);
      }
    }

    public function proses_rekening_pembayaran(Request $request){
      $rekening=RekeningPembayaran::find($request->id);
      if(!empty($rekening)){
        $field=RekeningPembayaran::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
        $msg='Data Rekening Pembayaran berhasil disimpan';
      }
      else{
        $field=new RekeningPembayaran;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $msg='Data Rekening Pembayaran berhasil ditambahkan';
      }

      $field->keterangan=$request->keterangan;
      $field->fid_metode_pembayaran=$request->metode;
      $field->no_rekening=$request->no_rekening;
      $field->atas_nama=$request->atas_nama;
      $field->is_active=$request->status_aktif;

      if($request->hasFile('logo')){
        if(!empty($field->logo)){
          unlink(storage_path('app/'.$field->logo));
        }
        $uploadedFile = $request->file('logo');
        $path = $uploadedFile->store('logo');
        $field->logo=$path;
      }

      if($request->action=='delete'){
        $field->delete();
        $msg='Data Rekening Pembayaran berhasil dihapus';
      }
      else{
        $field->save();
      }

      return redirect('master/rekening_pembayaran')
        ->with('message',$msg)
        ->with('message_type','success');
    }

    //------------------------------------------KATEGORI BARANG---------------------------------------------//
    public function kategori_barang(Request $request){
      return view('master/kategori_barang/index');
    }

    public function proses_kategori_barang(Request $request){
      if($request->action == 'add'){
        $field=new KategoriProduk;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $field->jenis=$jenis ?? '';
        $msg='Kategori Barang berhasil ditambahkan';
      }
      else{
        $field=KategoriProduk::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
        $msg='Kategori Barang berhasil disimpan';
      }
      $field->nama_kategori=$request->nama_kategori;
      $field->kode=$request->kode;
      $field->parent_id=($request->have_parent==1 ? $request->parent_id : 0 ) ;
      $field->keterangan=$request->keterangan;
      // return $field;
      if($request->action == 'delete' ){
        $field->delete();
        $msg='Kategori Barang berhasil dihapus';
      }
      else{
        $field->save();
      }
      return redirect('master/kategori_barang')
        ->with('message',$msg)
        ->with('message_type','success');
    }

    //---------------------------------------BERITA DAN INFORMASI-------------------------------------------//

    public function get_berita($search){
      $query=Berita::select('*');
      if(!empty($search)){
        $query=$query->where('judul', 'like', "%{$search}%");
      }
      $result=$query->orderBy('created_at')->paginate(10);
      foreach ($result as $key => $value) {
        $result[$key]->jumlah_attachment=AttachmentBerita::where('fid_berita',$value->id)->count();
      }
      if(!empty($search)){
        $result->withPath('berita?search='.$search);
      }
      return $result;
    }

    public function berita(Request $request){
      $search=(!empty($request->search) ? $request->search : null );
      $data['berita']=$this->get_berita($search);
      return view('master/berita/index')
        ->with('search',$search)
        ->with('data',$data);
    }

    public function form_berita(Request $request){
      $cek_berita=Berita::find($request->id);
      if(!empty($cek_berita)){
        $action='edit';
        $id=$request->id;
      }
      else{
        $action='add';
        $id=null;
      }
      $data['berita']=$cek_berita;
      $data['attachment']=AttachmentBerita::where('fid_berita',$id)->get();
      return view('master.berita.form')
        ->with('action',$action)
        ->with('id',$id)
        ->with('data',$data);
    }

    public function proses_berita(Request $request){
      $berita=Berita::find($request->id);
      if(!empty($berita)){
        $field=$berita;
        $field->updated_at=date('Y-m-d H:i:s');
        $msg='Berita berhasil diedit';
      }
      else{
        $field=new Berita;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->id;
        $msg='Berita berhasil ditambahkan';
      }
      $field->judul=$request->judul;
      $field->content=$request->input('content');
      if($request->hasFile('gambar')){
        if(!empty($field->gambar)){
          unlink(storage_path('app/'.$field->gambar));
        }
        $uploadedFile = $request->file('gambar');
        $path = $uploadedFile->store('berita');
        $field->gambar=$path;
      }
      if($request->action=='delete'){
        if(!empty($field->logo)){
          unlink(storage_path('app/'.$field->gambar));
        }
        $field->delete();
        $msg='Berita berhasil dihapus';
      }
      else{
        $field->save();
      }
      return redirect('master/berita')
          ->with('message',$msg)
          ->with('message_type','success');
    }

    public function proses_attachment_berita(Request $request){
      if($request->action=='add'){
        $field=new AttachmentBerita;
        $field->fid_berita=$request->fid_berita;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $msg='Attachment Berita berhasil ditambahkan';
      }
      else{
        $field=AttachmentBerita::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
        $msg='Attachment Berita berhasil disimpan';
      }
      $field->judul=$request->judul;
      if($request->hasFile('attachment')){
        if(!empty($field->attachment)){
          unlink(storage_path('app/'.$field->attachment));
        }
        $uploadedFile = $request->file('attachment');
        $path = $uploadedFile->store('attachment_berita');
        $field->attachment=$path;
      }
      if($request->action=='delete'){
        if(!empty($field->attachment)){
          unlink(storage_path('app/'.$field->attachment));
        }
        $field->delete();
        $msg='Attachment  Berita berhasil dihapus';
      }
      else{
        $field->save();
      }
      return redirect('master/berita/form?id='.$request->fid_berita)
          ->with('message',$msg)
          ->with('message_type','success');
    }

    //---------------------------------------SYARAT DAN KETENTUAN-------------------------------------------//

    public function syarat_ketentuan(Request $request,$jenis){
      $data['syarat_ketentuan']=SyaratKetentuan::where('jenis',$jenis)->get();
      return view('master.syarat_ketentuan.index')
        ->with('data',$data)
        ->with('jenis',$jenis);
    }

    public function proses_syarat_ketentuan(Request $request,$jenis){
      if($request->action == 'add'){
        $field=new SyaratKetentuan;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $field->jenis=$jenis;
        $msg='Syarat dan Ketentuan berhasil ditambahkan';
      }
      else{
        $field=SyaratKetentuan::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
        $msg='Syarat dan Ketentuan berhasil disimpan';
      }
      $field->uraian=$request->uraian;
      if($request->action == 'delete' ){
        $field->delete();
        $msg='Syarat dan Ketentuan berhasil dihapus';
      }
      else{
        $field->save();
      }
      return redirect('master/syarat_ketentuan/'.$jenis)
          ->with('message',$msg)
          ->with('message_type','success');
    }

}
