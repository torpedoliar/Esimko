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

class ProfilController extends Controller
{
  public function profil_anggota(){
    $anggota=Anggota::select('anggota.*','status_anggota.status_anggota','status_anggota.color')
      ->join('status_anggota','status_anggota.id','=','anggota.fid_status')
      ->where('no_anggota',Session::get('useractive')->no_anggota)
      ->first();
    if(!empty($anggota)){
      $tab=(!empty($request->tab) ? $request->tab : 'profil');
      $anggota->avatar=(!empty($anggota->avatar) ? asset('storage/'.$anggota->avatar) : asset('assets/images/user-avatar-placeholder.png'));

      $anggota->total_saldo_simpanan=GlobalHelper::saldo_tabungan($anggota->no_anggota,'Total Simpanan'); //Total Simpanan
      $anggota->saldo_simpanan_pokok=GlobalHelper::saldo_tabungan($anggota->no_anggota,1); //Simpanan Pokok
      $anggota->saldo_simpanan_wajib=GlobalHelper::saldo_tabungan($anggota->no_anggota,2); //Simpanan Wajib
      $anggota->saldo_simpanan_hari_raya=GlobalHelper::saldo_tabungan($anggota->no_anggota,'Simpanan Hari Raya'); //Simpanan Hari Raya
      $anggota->saldo_simpanan_sukarela=GlobalHelper::saldo_tabungan($anggota->no_anggota,'Simpanan Sukarela'); //Simpanan Sukarela

      $anggota->sisa_pinjaman=GlobalHelper::sisa_pinjaman($anggota->no_anggota,'all'); //Sisa Semua Pinjaman
      $anggota->bunga_pinjaman=$anggota->sisa_pinjaman*GlobalHelper::getBungaPinjaman(); //Bunga Semua Pinjaman

      $anggota->total_angsuran_pinjaman=GlobalHelper::angsuran_pinjaman($anggota->no_anggota,'all'); //Total Angsuran Pinjaman
      $anggota->angsuran_jangka_panjang=GlobalHelper::angsuran_pinjaman($anggota->no_anggota,9); //Angsuran Pinjaman Jangka Panjang
      $anggota->angsuran_jangka_pendek=GlobalHelper::angsuran_pinjaman($anggota->no_anggota,10); //Angsuran Pinjaman Jangka Pendek
      $anggota->angsuran_barang=GlobalHelper::angsuran_pinjaman($anggota->no_anggota,11); //Angsuran Pinjaman Barang

      $anggota->total_angsuran_belanja=GlobalHelper::total_angsuran_belanja($anggota->no_anggota); //Total Angsuran Belanja
      $anggota->angsuran_belanja_toko=GlobalHelper::angsuran_belanja($anggota->no_anggota,'toko'); //Total Angsuran Belanja Toko
      $anggota->angsuran_belanja_konsinyasi=GlobalHelper::angsuran_belanja($anggota->no_anggota,'konsinyasi'); //Total Angsuran Belanja konsinyasi
      $anggota->angsuran_belanja_online=GlobalHelper::angsuran_belanja($anggota->no_anggota,'online'); //Total Angsuran Belanja Online

      $anggota->setoran_berkala=GlobalHelper::setoran_berkala($anggota->no_anggota); //Setoran Berkala
      $anggota->setoran_simpanan_anggota = 350000;

      return $anggota;
    }
    else{
      return array('msg'=>'Anggota tidak Ditemukan');
    }
  }

  public function index(Request $request){
    $tab=(!empty($request->tab) ? $request->tab : 'informasi');
    $data['profil']=$this->profil_anggota();
    $data['gaji-pokok']=DB::table('gaji_pokok')->select('gaji_pokok.*','anggota.nama_lengkap')
      ->leftJoin('anggota','anggota.no_anggota','=','gaji_pokok.created_by')
      ->where('fid_anggota',Session::get('useractive')->no_anggota)->paginate(10);
    return view('profil.index')
      ->with('data',$data)
      ->with('tab',$tab);
  }

  public function edit_profil(Request $request){
    $anggota=Anggota::find(Session::get('useractive')->id);
    if(!empty($anggota)){
      $field=$anggota;
      $field->tempat_lahir=$request->tempat_lahir;
      $field->tanggal_lahir=GlobalHelper::dateFormat($request->tanggal_lahir,'Y-m-d');
      $field->jenis_kelamin=$request->jenis_kelamin;
      $field->nama_panggilan=$request->nama_panggilan;
      $field->level=$request->level_jabatan;
      $field->lokasi=$request->lokasi_kerja;
      $field->bagian=$request->bagian;
      $field->divisi=$request->divisi;
      $field->no_ktp=$request->no_ktp;
      $field->id_karyawan=$request->id_karyawan;
      $field->tanggal_bergabung=GlobalHelper::dateFormat($request->tanggal_bergabung,'Y-m-d');
      $field->tanggal_bekerja=(!empty($request->id_karyawan) ? GlobalHelper::bulan_bekerja($request->id_karyawan) : GlobalHelper::dateFormat($request->tanggal_bekerja,'Y-m-d') );
      $field->save();
      return redirect('main/profil?tab=informasi')
          ->with('message','Informasi Personal berhasil disimpan')
          ->with('message_type','success');
    }
    else{
      return redirect('main/profil?tab=informasi')
          ->with('message','Anggota tidak Ditemukan')
          ->with('message_type','danger');
    }
  }

  public function edit_kontak(Request $request){
    $anggota=Anggota::find(Session::get('useractive')->id);
    if(!empty($anggota)){
      $field=$anggota;
      $field->no_handphone=$request->no_handphone;
      $field->email=$request->email;
      $field->alamat=$request->alamat;
      $field->no_rekening=$request->no_rekening;
      $field->an_rekening=$field->an_rekening;
      $field->nama_bank=$request->nama_bank;
      $field->save();
      return redirect('main/profil?tab=informasi')
          ->with('message','Informasi Kontak dan Rekening berhasil disimpan')
          ->with('message_type','success');
    }
    else{
      return redirect('main/profil?tab=informasi')
          ->with('message','Anggota tidak Ditemukan')
          ->with('message_type','danger');
    }
  }

  public function ubah_password(Request $request){
    $anggota=Anggota::find(Session::get('useractive')->id);
    if(!empty($anggota)){
      if($request->password_lama == decrypt($anggota->password)){
        if($request->password_baru == $request->ulangi_password_baru){
          $field=$anggota;
          $field->password=encrypt($request->password_baru);
          $field->save();
          return redirect('main/profil?tab=ubah_password')
              ->with('message','Password baru berhasil diubah')
              ->with('message_type','success');
        }
        else{
          return redirect('main/profil?tab=ubah_password')
              ->with('message','Password baru tidak sama')
              ->with('message_type','warning');
        }
      }
      else{
        return redirect('main/profil?tab=ubah_password')
            ->with('message','Password lama salah')
            ->with('message_type','warning');
      }
    }
    else{
      return redirect('profile?tab=ubah_password')
          ->with('message','Anggota tidak ditemukan')
          ->with('message_type','warning');
    }
  }

}
