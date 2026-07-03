<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Produk;
use App\FotoProduk;
use App\Penjualan;
use App\ItemPenjualan;
use App\AngsuranBelanja;
use App\BelanjaKonsinyasi;
use App\PayrollAngsuranBelanja;
use View;
use DB;
use DateTime;
use Redirect;

class AngsuranBelanjaController extends Controller
{
    public function get_angsuran($bulan,$search){
        $payroll=PayrollAngsuranBelanja::select('payroll_angsuran_belanja.*','status_payroll.status','status_payroll.keterangan','status_payroll.color','status_payroll.icon')
            ->join('status_payroll','status_payroll.id','=','payroll_angsuran_belanja.fid_status')
            ->where('bulan',$bulan)
            ->first();
        if(!empty($payroll)){
            $query=AngsuranBelanja::select('angsuran_belanja.*','anggota.*','penjualan.jenis_belanja','penjualan.no_transaksi','penjualan.tenor')
                ->leftjoin('penjualan','penjualan.id','=','angsuran_belanja.fid_penjualan')
                ->leftjoin('anggota','anggota.no_anggota','=','penjualan.fid_anggota')
                ->leftjoin('status_angsuran','status_angsuran.id','=','penjualan.fid_status')
                ->where('angsuran_belanja.fid_payroll',$payroll->id);
            if(!empty($search)){
                $query=$query->where(function ($i) use ($search) {
                    $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('anggota.no_anggota', 'like', "%{$search}%")
                        ->orWhere('penjualan.no_transaksi', 'like', "%{$search}%");
                });
            }
            $result=$query->orderBy('penjualan.tanggal')->paginate(10);
            if(!empty($search)){
                $result->withPath('angsuran?search='.$search);
            }
            $payroll->data=$result;
            $anggota=Anggota::where('no_anggota',$payroll->created_by)->first();
            $payroll->nama_lengkap=(!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
        }
        else{
            $payroll=null;
        }
        return $payroll;
    }

    public function status_payroll($bulan){
        $posisi_bulan=explode('-',$bulan);
        $bulan_payroll=explode('-',GlobalHelper::bulan_payroll('angsuran_belanja')['posisi']);
        $start_payroll=explode('-',GlobalHelper::bulan_payroll('angsuran_belanja')['awal']);
        if($posisi_bulan[0] > date('m') && $posisi_bulan[1] >= date('Y') ){
            $disabled='disabled';
        }
        else{
            if($bulan_payroll[0] < $posisi_bulan[0] && $bulan_payroll[1] <= $posisi_bulan[1] ){
                $disabled='disabled';
            }
            else{
                if($start_payroll[0] > $posisi_bulan[0] && $start_payroll[1] >= $posisi_bulan[1] ){
                    $disabled='disabled';
                }
                else{
                    $disabled='';
                }
            }
        }
        return $disabled;
    }

    public function index(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,30);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $bulan_payroll=GlobalHelper::bulan_payroll('angsuran_belanja');
            $search=(!empty($request->search) ? $request->search : null);
            $bulan=(!empty($request->bulan) ? $request->bulan : (!empty($bulan_payroll['akhir']) ?  $bulan_payroll['akhir'] : date('m-Y')) );
            $data['payroll']=$this->get_angsuran($bulan,$search);
            $data['status']=$this->status_payroll($bulan);
            return view('pos.angsuran.index')
                ->with('data',$data)
                ->with('search',$search)
                ->with('bulan',$bulan)
                ->with('bulan_payroll',$bulan_payroll);
        }
    }

    public function proses(Request $request){
        $payroll=PayrollAngsuranBelanja::where('bulan',$request->bulan)->first();
        if(!empty($payroll)){
            $field=PayrollAngsuranBelanja::find($payroll->id);
            $field->updated_at=date('Y-m-d H:i:s');
        }
        else{
            $field=new PayrollAngsuranBelanja;
            $field->created_at=date('Y-m-d H:i:s');
            $field->created_by=Session::get('useractive')->no_anggota;
            $field->fid_status=1;
            $field->bulan=$request->bulan;
        }
        $field->save();
        $this->proses_angsuran_belanja($field->id,$request);
        return Redirect::back()
            ->with('message','Payroll Angsuran Belanja berhasil diproses')
            ->with('message_type','success');
    }

    public function reload_payroll($id){
        $data=AngsuranBelanja::where('fid_payroll',$id)->get();
        foreach ($data as $key => $value) {
            $angsuran=AngsuranBelanja::find($value->id);
            $angsuran->fid_status=3;
            $angsuran->fid_payroll=null;
            $angsuran->save();
        }
    }

    public function proses_angsuran_belanja($id,$request){
        $this->reload_payroll($id);
        $belanja=Penjualan::select('penjualan.*')
            ->where(function ($a){
                $a->where(function ($i){
                    $i->where('jenis_belanja','toko')
                        ->Where('fid_status',2);
                })->orWhere(function ($i){
                    $i->where('jenis_belanja','!=','toko')
                        ->Where('fid_status',4);
                });
            })
            ->where('fid_metode_pembayaran',3)
            ->where('tanggal', '<=', date('Y-m-d'))
            ->get();

        foreach ($belanja as $key => $value) {
            $angsuran=AngsuranBelanja::where('fid_penjualan',$value->id)
                ->where('fid_status',3)
                ->orderBy('angsuran_ke','ASC')
                ->first();
            if(!empty($angsuran)){
                $field=AngsuranBelanja::find($angsuran->id);
                $field->fid_payroll=$id;
                $field->fid_status=6;
                $field->save();
            }
        }
    }

    public function verifikasi(Request $request){
        $payroll=PayrollAngsuranBelanja::where('bulan',$request->bulan)->first();
        if(!empty($payroll)){
            $field=PayrollAngsuranBelanja::find($payroll->id);
            $field->updated_at=date('Y-m-d H:i:s');
            $field->fid_status=$request->status;
            $field->save();
            if($request->status==3){
                $this->update_status_angsuran($payroll->id,6);
            }
            else{
                $this->update_status_angsuran($payroll->id,5);
            }
        }
        return Redirect::back()
            ->with('message','Proses Verifikasi Angsuran Belanja berhasil')
            ->with('message_type','success');
    }

    public function update_status_angsuran($id,$status){
        $angsuran=AngsuranBelanja::where('fid_payroll',$id)->get();
        foreach ($angsuran as $key => $value) {
            $field=AngsuranBelanja::find($value->id);
            $field->fid_status=$status;
            $field->save();
        }
    }
}
