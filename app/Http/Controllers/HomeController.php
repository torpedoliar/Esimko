<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\UserAkses;
use App\Transaksi;
use App\Berita;
use View;
use DB;
use DateTime;
use Redirect;

class HomeController extends Controller
{
    public function landing_page(){
      return view('landing_page.index');
    }

    public function get_verifikasi_transaksi($jenis){
      $data=Transaksi::select('transaksi.*','anggota.no_anggota','anggota.nama_lengkap','anggota.avatar')
        ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota');
      if($jenis=='setoran'){
        $data=$data->where('transaksi.fid_jenis_transaksi',4);
      }
      elseif($jenis=='penarikan'){
        $data=$data->whereIn('transaksi.fid_jenis_transaksi',array(6,8));
      }
      else{
        $data=$data->whereIn('transaksi.fid_jenis_transaksi',array(9,10,11));
      }
      $data=$data->where('transaksi.fid_status',1)->limit(10)->get();
      return $data;
    }

    public function bulan_payroll($jenis){
      $bulan_payroll=explode('-',GlobalHelper::bulan_payroll($jenis)['posisi']);
      if($bulan_payroll[0] > date('m') && $bulan_payroll[1] >= date('Y') ){
        $bulan=date('m-Y');
      }
      else{
        $bulan=GlobalHelper::bulan_payroll($jenis)['posisi'];
      }

      $cek_payroll=DB::table('payroll_'.$jenis)->where('bulan',$bulan)->first();
      $status=(!empty($cek_payroll) ? ($cek_payroll->fid_status == 3 ? 'disabled' : 'sudah') : 'belum');
      return array('bulan'=>$bulan,'status'=>$status);
    }

    public function posting_bunga(){
      if(GlobalHelper::tanggal_posting_bunga()['posisi'] <= date('Y-m-d') ){
        $tanggal_posting=GlobalHelper::tanggal_posting_bunga()['posisi'];
      }
      else{
        $tanggal_posting=date('Y-m-d');
      }
      $cek_posting_bunga=DB::table('transaksi')
        ->where('fid_jenis_transaksi','5')
        ->where('tanggal',GlobalHelper::dateFormat($tanggal_posting,'Y-m-d'))
        ->first();

      $status=(!empty($cek_posting_bunga) ? 'sudah' : 'belum');
      return array('tanggal'=>$tanggal_posting,'status'=>$status);
    }

    public function dashboard(){
      $data['total-anggota']=DB::table('anggota')->whereIn('fid_status',array(2,3))->count();
      $data['total-simpanan']=DB::table('transaksi')->whereIn('fid_jenis_transaksi',array(1,2,3,4,5,6,7,8))->where('fid_status',4)->sum('nominal');
      $data['total-pinjaman']=DB::table('transaksi')->whereIn('fid_jenis_transaksi',array(9,10,11))->whereIn('fid_status',array(4,6))->sum('nominal');
      $data['total-penjualan']=DB::table('penjualan')->where('fid_status',3)->sum('total_pembayaran');
      $data['ver_setoran']=$this->get_verifikasi_transaksi('setoran');
      $data['ver_penarikan']=$this->get_verifikasi_transaksi('penarikan');
      $data['ver_pinjaman']=$this->get_verifikasi_transaksi('pinjaman');
      $data['bulan-payrol-simpanan']=$this->bulan_payroll('simpanan');
      $data['bulan-payrol-pinjaman']=$this->bulan_payroll('angsuran');
      $data['bulan-payrol-belanja']=$this->bulan_payroll('angsuran_belanja');
      $data['posting-bunga']=$this->posting_bunga();
      return view('dashboard')
        ->with('data',$data);
    }

    public function get_transaksi_terakhir($anggota,$jenis){
      $query=Transaksi::select('transaksi.*','jenis_transaksi.jenis_transaksi','status_transaksi.status','status_transaksi.color')
        ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
        ->join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
        ->where('fid_anggota',$anggota);
      if($jenis=='simpanan'){
        $query=$query->whereIn('fid_jenis_transaksi',array(1,2,3,4,5,6,7,8));
      }
      else{
        $query=$query->whereIn('fid_jenis_transaksi',array(9,10,11));
      }
      $result=$query->orderBy('tanggal','DESC')
        ->limit(10)
        ->get();
      foreach ($result as $key => $value) {
        $result[$key]->nominal=str_replace('-','',$value->nominal);
      }
      return $result;
    }

    public function main_dashboard(Request $request){
      $anggota=Session::get('useractive')->no_anggota;
      $data['saldo-simpanan']=GlobalHelper::saldo_tabungan($anggota,'Total Simpanan');
      $data['sisa-pinjaman']=GlobalHelper::sisa_pinjaman($anggota,'all');
      $data['total-angsuran']=GlobalHelper::angsuran_pinjaman($anggota,'all');
      $data['total-angsuran-belanja']=GlobalHelper::total_angsuran_belanja($anggota);
      $jenis=array('simpanan'=>'Simpanan','pinjaman'=>'Pinjaman');
      foreach ($jenis as $key => $value) {
        $data['transaksi-terakhir'][$key]=$this->get_transaksi_terakhir($anggota,$key);
      }
      $data['berita']=Berita::select('*')->orderBy('created_at')->limit(10)->get();
      return view('main.dashboard')
        ->with('data',$data);
    }
}
