<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\UserAkses;
use App\OtoritasUser;
use App\RekeningPembayaran;
use App\OtoritasRekeningPembayaran;
use App\Pengaturan;
use App\PengaturanLog;
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

    //----------------------------Bunga Pinjaman---------------------------------//

    public function bunga_pinjaman(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,73);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $data['pengaturan'] = Pengaturan::where('kode', 'bunga_pinjaman')->first();
        $data['logs'] = PengaturanLog::where('fid_pengaturan', $data['pengaturan']->id ?? 0)
            ->orderBy('created_at', 'DESC')
            ->limit(20)
            ->get();
        
        // Get admin names for logs
        foreach($data['logs'] as $key => $log) {
            $admin = DB::table('anggota')->where('no_anggota', $log->created_by)->first();
            $data['logs'][$key]->nama_admin = $admin ? $admin->nama_lengkap : 'Unknown';
        }
        
        return view('pengaturan.bunga_pinjaman.index')
          ->with('data',$data);
      }
    }

    public function proses_bunga_pinjaman(Request $request){
      // 1. Verify admin password
      if (!GlobalHelper::verifyAdminPassword($request->password)) {
          return Redirect::back()
              ->with('message', 'Password tidak valid. Perubahan dibatalkan.')
              ->with('message_type', 'danger');
      }

      // 2. Get or create setting
      $pengaturan = Pengaturan::firstOrCreate(
          ['kode' => 'bunga_pinjaman'],
          ['nama' => 'Bunga Pinjaman (Per Bulan)', 'tipe' => 'persen']
      );

      // 3. Convert percentage input to decimal (e.g., 1 -> 0.01)
      $nilai_baru = floatval(str_replace(',', '.', $request->nilai)) / 100;

      // 4. Log the change (audit trail) if value changed
      if ($pengaturan->nilai != $nilai_baru) {
          PengaturanLog::create([
              'fid_pengaturan' => $pengaturan->id,
              'nilai_lama' => $pengaturan->nilai,
              'nilai_baru' => $nilai_baru,
              'created_by' => Session::get('useractive')->no_anggota,
              'keterangan' => $request->keterangan,
              'created_at' => now()
          ]);

          // 5. Update value
          $pengaturan->nilai = $nilai_baru;
          $pengaturan->updated_at = now();
          $pengaturan->save();

          return Redirect::back()
              ->with('message', 'Pengaturan bunga pinjaman berhasil diubah dari ' . ($pengaturan->getOriginal('nilai') * 100) . '% menjadi ' . ($nilai_baru * 100) . '%')
              ->with('message_type', 'success');
      }

      return Redirect::back()
          ->with('message', 'Tidak ada perubahan nilai')
          ->with('message_type', 'info');
    }
}

