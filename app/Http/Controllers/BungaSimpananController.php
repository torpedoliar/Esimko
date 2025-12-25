<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Transaksi;
use App\PayrollSimpanan;
use App\SetoranBerkala;
use App\GajiPokok;
use App\BungaSimpanan;
use View;
use DB;
use DateTime;
use Redirect;

class BungaSimpananController extends Controller
{

    //--------------------------------------------- BUNGA SIMPANAN -------------------------------------------------//
    public function get_bunga_simpanan($search,$tanggal){
        $bunga_simpanan=Transaksi::select('*')
            ->where('transaksi.fid_jenis_transaksi','5')
            ->where('transaksi.tanggal',GlobalHelper::dateFormat($tanggal,'Y-m-d'))
            ->first();
        if(!empty($bunga_simpanan)){
            $query=Transaksi::select('transaksi.*','bunga_simpanan.saldo_awal','bunga_simpanan.saldo_bulan','bunga_simpanan.bunga','anggota.no_anggota','anggota.nama_lengkap','metode_transaksi.metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color')
                ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
                ->join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
                ->join('metode_transaksi','metode_transaksi.id','=','transaksi.fid_metode_transaksi')
                ->join('bunga_simpanan','bunga_simpanan.fid_transaksi','=','transaksi.id')
                ->where('transaksi.fid_jenis_transaksi','5')
                ->where('transaksi.tanggal',GlobalHelper::dateFormat($tanggal,'Y-m-d'));
            if(!empty($search)){
                $query=$query->where(function ($i) use ($search) {
                    $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
                });
            }
            $result=$query->orderBy('transaksi.tanggal')->paginate(10);
            $bunga_simpanan->jumlah_anggota=$query->count();
            $bunga_simpanan->nominal=$query->sum('transaksi.nominal');
            if(!empty($search)){
                $result->withPath('bunga?tanggal='.$tanggal.'&search='.$search);
            }
            else{
                $result->withPath('bunga?tanggal='.$tanggal);
            }
            $bunga_simpanan->data=$result;
            $anggota=Anggota::where('no_anggota',$bunga_simpanan->created_by)->first();
            $bunga_simpanan->nama_lengkap=(!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
        }
        else{
            $bunga_simpanan=null;
        }
        return $bunga_simpanan;
    }

    public function status_bunga($tanggal){
        $tanggal=date('Y-m-d',strtotime($tanggal));
        $tanggal_posting=date('Y-m-d',strtotime(GlobalHelper::tanggal_posting_bunga()['posisi']));
        $awal_posting=date('Y-m-d',strtotime(GlobalHelper::tanggal_posting_bunga()['awal']));
        if($tanggal > date('Y-m-d')){
            $disabled='disabled';
        }
        else{
            if($tanggal_posting < $tanggal ){
                $disabled='disabled';
            }
            else{
                if($awal_posting > $tanggal ){
                    $disabled='disabled';
                }
                else{
                    $disabled='';
                }
            }
        }
        return $disabled;
    }

    public function tanggal_posting(){
        if(GlobalHelper::tanggal_posting_bunga()['posisi'] <= date('Y-m-d') ){
            $tanggal_posting=GlobalHelper::dateFormat(GlobalHelper::tanggal_posting_bunga()['posisi'],'d-m-Y');
        }
        else{
            $tanggal_posting=date('d-m-Y');
        }
        return $tanggal_posting;
    }


    public function index(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,19);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
//      $tanggal_posting=GlobalHelper::tanggal_posting_bunga();
//            $search=(!empty($request->search) ? $request->search : null);
//      $tanggal=(!empty($request->tanggal) ? $request->tanggal : ($tanggal_posting['posisi']==null ? date('d-m-Y') : $this->tanggal_posting() ));
//      $data['bunga-simpanan']=$this->get_bunga_simpanan($search,$tanggal);

            $tanggal = $request->input('tanggal') ?? '';
            $search = $request->input('search') ?? '';
            $bunga_simpanan = Transaksi::select('transaksi.*')->where('transaksi.fid_jenis_transaksi','5');
            if ($tanggal !== '') $bunga_simpanan = $bunga_simpanan->where('transaksi.tanggal', unformat_date($tanggal));
            if ($search !== '') {
                $bunga_simpanan = $bunga_simpanan->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')->where(function ($i) use ($search) {
                    $i->where('anggota.nama_lengkap', 'like', "%{$search}%")->orWhere('anggota.no_anggota', 'like', "%{$search}%");
                });
            }
            $bunga_simpanan = $bunga_simpanan->paginate(10);
            $data['bunga-simpanan'] = $bunga_simpanan;

            $data['status']=$this->status_bunga($tanggal);
            return view('simpanan.bunga.index')
                ->with('data',$data)
                ->with('tanggal',$tanggal)
                ->with('search',$search);
        }
    }

    public function hitung_bunga($anggota,$tanggal,$id){
        $jumlah_hari=GlobalHelper::jumlah_hari(GlobalHelper::dateFormat($tanggal,'m-Y'));

        $saldo_awal=Transaksi::where('fid_anggota',$anggota)
            ->where('fid_status',4)
            ->where('tanggal','<=',$tanggal)
//      ->where('id','<>',$id)
            ->whereIn('fid_jenis_transaksi',array(3,5,6))
            ->sum('nominal');

//    $saldo_tanpa_bunga=Transaksi::where('fid_anggota',$anggota)
//      ->where('fid_status',4)
//      ->where('tanggal','<=',$tanggal)
//      ->where('id','<>',$id)
//      ->whereIn('fid_jenis_transaksi',array(4,6))
//      ->sum('nominal');

//    $bunga_bulan_lalu=Transaksi::where('fid_jenis_transaksi',5)
//      ->where('fid_anggota',$anggota)
//      ->where('tanggal','<',GlobalHelper::dateFormat($tanggal,'Y-m').'-01')
//      ->sum('nominal');

        return (0.3 / 100) * $saldo_awal;

        $saldo_awal_bulan = $saldo_tanpa_bunga+$bunga_bulan_lalu;
        $bunga= 0.3 / 100 / $jumlah_hari;
        $nominal_bunga = round($saldo_awal_bulan * $bunga, 0);
        $bunga = '0.3 / '. $jumlah_hari;
        return array('saldo_awal'=>$saldo_awal,'saldo_awal_bulan'=>$saldo_awal_bulan,'bunga'=>$bunga,'nominal'=>$nominal_bunga);
    }

    public function proses(Request $request){
        $tanggal = $request->input('tanggal');
        $list_anggota = Anggota::get();

        foreach ($list_anggota as $anggota) {
            $bunga = Transaksi::where('fid_anggota',$anggota->no_anggota)
                ->where('fid_status',4)
                ->where('tanggal','<=',unformat_date($tanggal))
                ->whereIn('fid_jenis_transaksi',array(3,5,6))
                ->sum('nominal');
            if ($bunga > 0) {
                $field = new Transaksi;
                $field->created_at = date('Y-m-d H:i:s');
                $field->created_by = Session::get('useractive')->no_anggota;
                $field->fid_status = 4;
                $field->fid_jenis_transaksi = 5;
                $field->fid_anggota = $anggota->no_anggota;
                $field->fid_metode_transaksi = 1;
                $field->nominal = (0.3 / 100) * $bunga;
                $field->tanggal = GlobalHelper::dateFormat($request->tanggal, 'Y-m-d');
                $field->save();
            }
        }
        return redirect('simpanan/bunga?tanggal='.$request->tanggal)
            ->with('message','Bunga Simpanan berhasil berhasil diposting')
            ->with('message_type','success');
    }

    public function update_bunga_simpanan($id,$hitung_bunga){
        $bunga=BungaSimpanan::where('fid_transaksi',$id)->first();
        if(!empty($bunga)){
            $field=BungaSimpanan::find($bunga->id);
        }
        else{
            $field=new BungaSimpanan;
            $field->fid_transaksi=$id;
        }
        $field->saldo_awal=$hitung_bunga['saldo_awal'];
        $field->saldo_bulan=$hitung_bunga['saldo_awal_bulan'];
        $field->bunga=$hitung_bunga['bunga'];
        $field->nominal=$hitung_bunga['nominal'];
        $field->save();
    }
}
