<?php

namespace App\Http\Controllers;

use App\Exports\PayrollExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Transaksi;
use App\Angsuran;
use App\PayrollAngsuran;
use App\GajiPokok;
use Maatwebsite\Excel\Facades\Excel;
use View;
use DateTime;
use Redirect;

class PayrollPinjamanController extends Controller
{

    public function get_payroll($search,$bulan){
        $payroll=PayrollAngsuran::select('payroll_angsuran.*','status_payroll.status','status_payroll.keterangan','status_payroll.color','status_payroll.icon')
            ->join('status_payroll','status_payroll.id','=','payroll_angsuran.fid_status')
            ->where('payroll_angsuran.bulan',$bulan)
            ->first();
        if(!empty($payroll)){
            $query=Transaksi::select('anggota.*','transaksi.fid_angsuran')
                ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
                ->join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
                ->whereIn('transaksi.fid_jenis_transaksi',array(12,13))
                ->where('fid_payroll',$payroll->id);
            if(!empty($search)){
                $query=$query->where(function ($i) use ($search) {
                    $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
                });
            }
            $result=$query->orderBy('anggota.no_anggota')->groupBy('transaksi.fid_angsuran')->paginate(10);
            foreach ($result as $key => $value){
                $angsuran=Angsuran::select('angsuran.*','transaksi.tenor','jenis_transaksi.jenis_transaksi')
                    ->join('transaksi','transaksi.id','angsuran.fid_transaksi')
                    ->join('jenis_transaksi','jenis_transaksi.id','transaksi.fid_jenis_transaksi')
                    ->where('angsuran.id',$value->fid_angsuran)
                    ->first();
                $result[$key]->angsuran_ke=$angsuran->angsuran_ke;
                $result[$key]->jenis_transaksi=$angsuran->jenis_transaksi;
                $result[$key]->tenor=$angsuran->tenor;
                $result[$key]->angsuran_pokok=$angsuran->angsuran_pokok;
                $result[$key]->bunga=$angsuran->angsuran_bunga;
            }
            if(!empty($search)){
                $result->withPath('payroll?bulan='.$bulan.'&search='.$search);
            }
            else{
                $result->withPath('payroll?bulan='.$bulan);
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
        $bulan_payroll=explode('-',GlobalHelper::bulan_payroll('angsuran')['posisi']);
        $start_payroll=explode('-',GlobalHelper::bulan_payroll('angsuran')['awal']);
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
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,17);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $bulan_payroll=GlobalHelper::bulan_payroll('angsuran');
            $search=(!empty($request->search) ? $request->search : null);

            $bulan=(!empty($request->bulan) ? $request->bulan : $bulan_payroll['akhir'] );
            if (date('Y-m-d', strtotime('01-' . $bulan)) > date('Y-m-d', strtotime('01-' . $bulan_payroll['posisi']))) {
                return redirect('pinjaman/payroll?bulan=' . $bulan_payroll['posisi']);
            }

            $data['payroll']=$this->get_payroll($search,$bulan);
            $data['status']=$this->status_payroll($bulan);
            return view('pinjaman.payroll.index')
                ->with('data',$data)
                ->with('search',$search)
                ->with('bulan',$bulan)
                ->with('bulan_payroll',$bulan_payroll);
        }
    }

    public function export(Request $request)
    {
        $bulan = $request->bulan ?? '-';

        $payroll = PayrollAngsuran::where('bulan', date('m-Y', strtotime('01-' . $bulan)))->first();
        if (empty($payroll)) dd('Payroll belum diproses!');
        $data = Angsuran::where('angsuran.fid_payroll', $payroll->id)
            ->with(['transaksi'])
            ->get();

        return Excel::download(new PayrollExport($data, $bulan), 'laporan_payroll_'. $bulan .'.xlsx');
    }

    public function proses(Request $request){
        $payroll=PayrollAngsuran::where('bulan',$request->bulan)->first();
        if(!empty($payroll)){
            $field=PayrollAngsuran::find($payroll->id);
            $field->updated_at=date('Y-m-d H:i:s');
        }
        else{
            $field=new PayrollAngsuran;
            $field->created_at=date('Y-m-d H:i:s');
            $field->created_by=Session::get('useractive')->no_anggota;
            $field->fid_status=1;
            $field->bulan=$request->bulan;
        }
        $field->save();
        $angsuran=$this->proses_payroll($field->id,$request);
        if($angsuran==0){
            PayrollAngsuran::find($field->id)->delete();
            $msg='Belum ada Angsuran Pinjaman yang diproses';
            $type='warning';
        }
        else{
            $msg='Payroll Angsuran Pinjaman berhasil diproses';
            $type='success';
        }
        return redirect('pinjaman/payroll?bulan='.$field->bulan)
            ->with('message',$msg)
            ->with('message_type',$type);
    }

    public function reload_payroll($id){
        $data=Transaksi::where('fid_payroll',$id)
            ->whereIn('fid_jenis_transaksi',array(12,13))
            ->get();
        foreach ($data as $key => $value) {
            $angsuran=Angsuran::find($value->fid_angsuran);
            $angsuran->fid_status=3;
            $angsuran->save();
            Transaksi::find($value->id)->delete();
        }
    }

    public function proses_payroll($id,$request){
        $this->reload_payroll($id);
        $transaksi=Transaksi::select('transaksi.*')
            ->whereIn('transaksi.fid_jenis_transaksi',array(9,10,11))
            ->where('transaksi.fid_status', 4)
            ->where('transaksi.tanggal', '<', date('Y-m-d'))
            ->get();

//        $list_angsuran = Angsuran::whereIn('fid_transaksi', $transaksi->pluck('id')->toArray())
//            ->where('fid_status', 3)
//            ->get();
//        $mapped_angsuran = [];
//        foreach ($list_angsuran as $value) $mapped_angsuran[$value->fid_transaksi] = $value;

        if (count($transaksi)==0) {
            return 0;
        }
        else{
            foreach ($transaksi as $key => $value) {
                $angsuran=Angsuran::where('fid_transaksi',$value->id)
                    ->where('fid_status',3)
                    ->orderBy('angsuran_ke','ASC')
                    ->first();
//                $angsuran = $mapped_angsuran[$value->id] ?? [];
                if(!empty($angsuran)){
                    $jenis_angsuran=array('pokok'=>12,'bunga'=>13);
                    foreach ($jenis_angsuran as $key => $jenis){
                        $field=new Transaksi;
                        $field->created_at=date('Y-m-d H:i:s');
                        $field->created_by=Session::get('useractive')->no_anggota;
                        $field->fid_status=1;
                        $field->fid_jenis_transaksi=$jenis;
                        $field->fid_anggota=$value->fid_anggota;
                        $field->fid_metode_transaksi=2;
                        $field->fid_payroll=$id;
                        $field->nominal=($jenis==12 ? $angsuran->angsuran_pokok : $angsuran->angsuran_bunga);
                        $field->tanggal=date('Y-m-d');
                        $field->fid_angsuran=$angsuran->id;
                        $field->fid_pinjaman = $angsuran->fid_transaksi;
                        $field->save();
                    }
                    $angsuran->fid_status=5;
                    $angsuran->fid_payroll=$id;
                    $angsuran->save();
                }
            }
            return 1;
        }
    }

    public function verifikasi(Request $request){
        $payroll=PayrollAngsuran::where('bulan',$request->bulan)->first();
        if(!empty($payroll)){
            $field=PayrollAngsuran::find($payroll->id);
            $field->updated_at=date('Y-m-d H:i:s');
            $field->fid_status=$request->status;
            $field->save();
            $status=DB::table('status_payroll')->find($field->fid_status);
            GlobalHelper::add_verifikasi_transaksi('payroll_pinjaman',$field->id,(!empty($status) ? $status->caption : ''),null);
            if($request->status==3){
                $this->update_status_transaksi($payroll->id,4);
            }
            else{
                $this->update_status_transaksi($payroll->id,1);
            }
        }
        return Redirect::back()
            ->with('message','Proses Verifikasi Angsuran Anggota berhasil')
            ->with('message_type','success');
    }

    public function update_status_transaksi($id,$status){
        $pinjaman=Transaksi::where('fid_payroll',$id)->whereIn('fid_jenis_transaksi',array(12,13))->get();
        foreach ($pinjaman as $key => $value) {
            $field=Transaksi::find($value->id);
            $field->fid_status=$status;
            $field->save();
            $angsuran=Angsuran::find($value->fid_angsuran);
            $angsuran->fid_status=($status==4 ? 6 : 5 );
            $angsuran->save();
            $sisa_tenor=Angsuran::where('fid_transaksi',$angsuran->fid_transaksi)->where('fid_status','!=',6)->count();
            if($sisa_tenor==0){
                $pinjaman=Transaksi::find($angsuran->fid_transaksi);
                $pinjaman->fid_status=6;
                $pinjaman->save();
            }
        }
    }

    public function hapus(Request $request)
    {
        $bulan = $request->input('bulan') ?? '';
        $payroll = PayrollAngsuran::where('bulan', $bulan)->first();

        DB::statement("update angsuran set fid_payroll = null, fid_status = 3 where fid_payroll = " . $payroll->id . ";");
//        DB::statement("update angsuran join transaksi on transaksi.id = angsuran.fid_transaksi set transaksi.fid_payroll = null where transaksi.fid_payroll = ". $payroll->id ." and fid_jenis_transaksi in (12, 13);");
        DB::statement("delete from transaksi where fid_payroll = ". $payroll->id .";");
        DB::statement("delete from payroll_angsuran where id = ". $payroll->id .";");
        DB::statement("update angsuran set fid_status = 3 where fid_payroll is null;");
        return redirect()->back();
    }
}
