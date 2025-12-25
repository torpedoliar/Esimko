<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Transaksi;
use App\Angsuran;
use App\AngsuranBelanja;
use App\PayrollSimpanan;
use App\PayrollAngsuran;
use App\PayrollAngsuranBelanja;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollExport;
use View;
use DB;
use DateTime;
use Redirect;


class CetakPayrollController extends Controller
{
    public function get_payroll($search,$bulan,$limit='10'){
      $query=Anggota::select('*')->whereIn('anggota.fid_status',array(2,3,5));
      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('nama_lengkap', 'like', "%{$search}%")
            ->orWhere('no_anggota', 'like', "%{$search}%");
         });
      }
      if($limit == 'all'){
        $result=$query->orderBy('anggota.no_hirs')->get();
      }
      else{
        $result=$query->orderBy('anggota.no_hirs')->paginate($limit);
        if(!empty($search)){
          $result->withPath('payroll?search='.$search);
        }
      }
      foreach ($result as $key => $value) {

        //Angsuran Simpanan
        $payrol_simpanan=PayrollSimpanan::where('bulan',$bulan)->first();
        if(!empty($payroll_simpanan)){
          $result[$key]->simpanan=Transaksi::where('no_anggota',$value->no_anggota)
            ->whereIn('fid_jenis_transaksi',array(1,2,3,4))
            ->where('fid_payroll',$payroll_simpanan->id)
            ->sum('nominal');
        }
        else{
          $result[$key]->simpanan=0;
        }

        //Angsuran Pinjaman
        $payroll_pinjaman=PayrollAngsuran::where('bulan',$bulan)->first();
        if(!empty($payroll_pinjaman)){
          $angsuran_pokok_uang=Angsuran::join('transaksi','transaksi.id','=','angsuran.fid_transaksi')
            ->where('angsuran.fid_payroll',$payroll_pinjaman->id)
            ->where('transaksi.fid_anggota',$value->no_anggota)
            ->whereIn('fid_jenis_transaksi',array(9,10))
            ->sum('angsuran.angsuran_pokok');

          $angsuran_bunga_uang=Angsuran::join('transaksi','transaksi.id','=','angsuran.fid_transaksi')
            ->where('angsuran.fid_payroll',$payroll_pinjaman->id)
            ->where('transaksi.fid_anggota',$value->no_anggota)
            ->whereIn('fid_jenis_transaksi',array(9,10))
            ->sum('angsuran.angsuran_bunga');

          $result[$key]->angsuran_uang=$angsuran_pokok_uang+$angsuran_bunga_uang;

          $angsuran_pokok_barang=Angsuran::join('transaksi','transaksi.id','=','angsuran.fid_transaksi')
            ->where('angsuran.fid_payroll',$payroll_pinjaman->id)
            ->where('transaksi.fid_anggota',$value->no_anggota)
            ->where('fid_jenis_transaksi',11)
            ->sum('angsuran.angsuran_pokok');

          $angsuran_bunga_barang=Angsuran::join('transaksi','transaksi.id','=','angsuran.fid_transaksi')
            ->where('angsuran.fid_payroll',$payroll_pinjaman->id)
            ->where('transaksi.fid_anggota',$value->no_anggota)
            ->where('fid_jenis_transaksi',11)
            ->sum('angsuran.angsuran_bunga');

          $result[$key]->angsuran_barang=$angsuran_pokok_barang+$angsuran_bunga_barang;
        }
        else{
          $result[$key]->angsuran_uang=0;
          $result[$key]->angsuran_barang=0;
        }

        //Angsuran Kredit Toko
        $payroll_angsuran_belanja=PayrollAngsuranBelanja::where('bulan',$bulan)->first();
        if(!empty($payroll_pinjaman)){
          $result[$key]->pinjaman_toko=AngsuranBelanja::join('penjualan','penjualan.id','=','angsuran_belanja.fid_penjualan')
            ->where('angsuran_belanja.fid_payroll',$payroll_pinjaman->id)
            ->where('penjualan.fid_anggota',$value->no_anggota)
            ->sum('angsuran_belanja.total_angsuran');
        }
        else{
          $result[$key]->pinjaman_toko=0;
        }
      }
      return $result;
    }

    public function index(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,59);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $search=(!empty($request->search) ? $request->search : null);
        $bulan=(!empty($request->bulan) ? $request->bulan : date('m-Y'));
        if($request->mode=='cetak'){
          $data['payroll']=$this->get_payroll($search,$bulan,'all');
          return Excel::download(new PayrollExport($data), 'invoices.xlsx');
        }
        else{
          $data['payroll']=$this->get_payroll($search,$bulan);
          return view('payroll.index')
            ->with('data',$data)
            ->with('search',$search)
            ->with('bulan',$bulan);
        }
      }
    }
}
