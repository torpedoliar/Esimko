<?php

namespace App\Http\Controllers;

use App\Exports\PayrollSimpananExport;
use App\JenisTransaksi;
use App\PayrollAngsuran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Transaksi;
use App\PayrollSimpanan;
use App\SetoranBerkala;
use App\GajiPokok;
use Maatwebsite\Excel\Facades\Excel;
use View;
use DateTime;
use Redirect;

class PayrollSimpananController extends Controller
{
    public function get_payroll_simpanan($search,$bulan,$mode){
        $payroll=PayrollSimpanan::select('payroll_simpanan.*','status_payroll.status','status_payroll.keterangan','status_payroll.color','status_payroll.icon')
            ->join('status_payroll','status_payroll.id','=','payroll_simpanan.fid_status')
            ->where('payroll_simpanan.bulan',$bulan)
            ->first();
        if(!empty($payroll)){
            $query=Anggota::select('anggota.*')
                ->join('transaksi', function ($join) use($payroll) {
                    $join->on('anggota.no_anggota', '=', 'transaksi.fid_anggota')
                        ->whereIn('fid_jenis_transaksi',array(1,2,3,4))
                        ->where('fid_payroll',$payroll->id);
                });

            if(!empty($search)){
                $query=$query->where(function ($i) use ($search) {
                    $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
                });
            }
            if ($mode === 'cetak') {
                $result=$query->groupBy('anggota.no_anggota')
                    ->orderBy('anggota.nama_lengkap')
                    ->get();
            } else {
                $result=$query->groupBy('anggota.no_anggota')
                    ->orderBy('anggota.nama_lengkap')
                    ->paginate(10);
            }


            foreach ($result as $key => $value){
                $jenis_simpanan=DB::table('jenis_transaksi')->whereIn('id',array(1,2,3,4))->get();
                foreach ($jenis_simpanan as $key2 => $value2) {
                    $simpanan=Transaksi::where('fid_anggota',$value->no_anggota)
                        ->where('fid_jenis_transaksi',$value2->id)
                        ->where('fid_payroll',$payroll->id)
                        ->first();
                    $label=str_replace(' ','_',strtolower($value2->jenis_transaksi));
                    $result[$key]->$label=(!empty($simpanan) ? $simpanan->nominal : 0);
                }
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
            $result=null;
        }
        return $payroll;
    }

    public function status_payroll($bulan){
        $posisi_bulan=explode('-',$bulan);
        $bulan_payroll=explode('-',GlobalHelper::bulan_payroll('simpanan')['posisi']);
        $start_payroll=explode('-',GlobalHelper::bulan_payroll('simpanan')['awal']);
        if(GlobalHelper::bulan_payroll('simpanan')['posisi'] == GlobalHelper::bulan_payroll('simpanan')['awal'] ){
            if($posisi_bulan[0] > date('m') && $posisi_bulan[1] >= date('Y') ){
                $disabled='disabled';
            }
            else{
                $disabled='';
            }
        }
        else{
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
        }
        return $disabled;
    }

    public function posisi_bulan(){
        $bulan_payroll=explode('-',GlobalHelper::bulan_payroll('simpanan')['posisi']);
        if($bulan_payroll[0] > date('m') && $bulan_payroll[1] >= date('Y') ){
            return date('m-Y');
        }
        else{
            return GlobalHelper::bulan_payroll('simpanan')['posisi'];
        }
    }

    public function get_data_export($bulan)
    {
        $payroll = PayrollSimpanan::where('bulan', $bulan)->first();
        $list_id_jenis_simpanan = [1, 2, 3, 4];
        $transaksi = Transaksi::select('fid_anggota', 'fid_jenis_transaksi', 'nominal')->whereIn('fid_jenis_transaksi', $list_id_jenis_simpanan)->where('fid_payroll', $payroll->id)->get();
        $mapped_transaksi = [];
        foreach ($transaksi as $value) {
            if (empty($mapped_transaksi[$value->fid_anggota . '_' . $value->fid_jenis_transaksi])) $mapped_transaksi[$value->fid_anggota . '_' . $value->fid_jenis_transaksi] = 0;
            $mapped_transaksi[$value->fid_anggota . '_' . $value->fid_jenis_transaksi] += $value->nominal;
        }
        $list_anggota_id = array_values(array_unique(array_column($transaksi->toArray(), 'fid_anggota')));

        $anggota = Anggota::whereIn('no_anggota', $list_anggota_id)->get();

        foreach ($anggota as $value) {
            $data_simpanan = [];
            foreach ($list_id_jenis_simpanan as $id_jenis_simpanan) $data_simpanan[$id_jenis_simpanan] = $mapped_transaksi[$value->no_anggota . '_' . $id_jenis_simpanan] ?? 0;
            $value->data_simpanan = $data_simpanan;
        }

        return $anggota;
    }

    public function index(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,16);
        $mode = ($request->input('mode') ?? '');

        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $bulan_payroll=GlobalHelper::bulan_payroll('simpanan');
            $search=(!empty($request->search) ? $request->search : null);
            $bulan=(!empty($request->bulan) ? $request->bulan : $this->posisi_bulan() );

            if ($mode === 'cetak') {
                $anggota = $this->get_data_export($bulan);
                $list_jenis_simpanan = JenisTransaksi::whereIn('id', [1, 2, 3, 4])->get();
                return Excel::download(new PayrollSimpananExport($anggota, $list_jenis_simpanan), 'payroll_saldo_simpanan_'. $bulan .'.xlsx');
            }

            $data['payroll']=$this->get_payroll_simpanan($search,$bulan, $mode);
            $data['status']=$this->status_payroll($bulan);
            $data['jenis-simpanan']=DB::table('jenis_transaksi')->whereIn('id',array(1,2,3,4))->get();
            return view('simpanan.payroll.index')
                ->with('data',$data)
                ->with('search',$search)
                ->with('bulan_payroll',$bulan_payroll)
                ->with('bulan',$bulan);
        }
    }

    public function proses(Request $request){
        $payroll=PayrollSimpanan::where('bulan',$request->bulan)->first();
        if(!empty($payroll)){
            $field=PayrollSimpanan::find($payroll->id);
            $field->updated_at=date('Y-m-d H:i:s');
        }
        else{
            $field=new PayrollSimpanan;
            $field->created_at=date('Y-m-d H:i:s');
            $field->created_by=Session::get('useractive')->no_anggota;
            $field->fid_status=1;
            $field->bulan=$request->bulan;
        }
        $field->save();
        $this->proses_payroll($field->id,$request, $field);
        return redirect('simpanan/payroll?bulan='.$field->bulan)
            ->with('message','Payroll Simpanan Anggota berhasil diproses')
            ->with('message_type','success');
    }

    public function nominal_setoran_berkala($anggota,$payroll){
        $payroll=PayrollSimpanan::find($payroll);
        if(!empty($payroll)){
            $setoran=SetoranBerkala::where('fid_anggota',$anggota)->where('fid_status',1)->first();
            if(!empty($setoran)){
                if($setoran->mulai_bulan <= $payroll->bulan){
                    if($setoran->bulan_akhir == 'Belum Ditentukan'){
                        $nominal=$setoran->nominal;
                    }
                    elseif($setoran->bulan_akhir >= $payroll->bulan){
                        $nominal=$setoran->nominal;
                    }
                    else{
                        $nominal=0;
                    }
                }
                else{
                    $nominal=0;
                }
            }
            else{
                $nominal=0;
            }
        }
        else{
            $nominal=0;
        }
        return $nominal;
    }

    public function jumlah_simpanan($anggota,$jenis,$payroll){
        $anggota=Anggota::where('no_anggota',$anggota)->first();
        if(!empty($anggota)){
            if($jenis=='1'){
                $nominal=($anggota->fid_status==2 ? 75000 : 0);
            }
            elseif($jenis=='2'){
                $nominal=150000;
            }
            elseif($jenis=='4'){
                $nominal=200000;
            }
            else{
                $nominal=$this->nominal_setoran_berkala($anggota->no_anggota,$payroll);
            }
        }
        else{
            $nominal=0;
        }
        return $nominal;
    }

    public function proses_payroll($id,$request, $payroll){
        Transaksi::where('fid_payroll',$id)->delete();
        $anggota=Anggota::whereIn('fid_status',array(2,3))->where('no_anggota','<>',null)->get();
        foreach ($anggota as $key => $value) {
            $jenis_simpanan=DB::table('jenis_transaksi')->whereIn('id',array(2,4))->get();
            foreach ($jenis_simpanan as $key2 => $value2){
                $field=new Transaksi;
                $field->created_at=date('Y-m-d H:i:s');
                $field->created_by=Session::get('useractive')->no_anggota;
                $field->fid_status=0;
                $field->fid_jenis_transaksi=$value2->id;
                $field->fid_anggota=$value->no_anggota;
                $field->fid_metode_transaksi=2;
                $field->fid_payroll=$id;
                if ($value2->id == 2) {
                    $field->nominal = 150000;
                }
                if ($value2->id == 4) {
                    $field->nominal = 200000;
                }
                $field->tanggal = date('Y-m-t', strtotime('01-' . $payroll->bulan));
                if($field->nominal!=0){
                    $field->save();
                }
            }

            $setoran = SetoranBerkala::where('fid_anggota',$value->no_anggota)
                ->where('tanggal', '<=', date('Y-m-t', strtotime('01-' . $payroll->bulan)))
                ->where('fid_status',1)
                ->first();
            if (!empty($setoran)) {
                $field=new Transaksi;
                $field->created_at=date('Y-m-d H:i:s');
                $field->created_by=Session::get('useractive')->no_anggota;
                $field->fid_status=0;
                $field->fid_jenis_transaksi=3;
                $field->fid_anggota=$value->no_anggota;
                $field->fid_metode_transaksi=2;
                $field->fid_payroll=$id;
                $field->nominal = $setoran->nominal;
                $field->tanggal = date('Y-m-t', strtotime('01-' . $payroll->bulan));
                $field->save();
            }
        }
    }

    public function verifikasi(Request $request){
        $payroll=PayrollSimpanan::where('bulan',$request->bulan)->first();
        if(!empty($payroll)){
            $field=PayrollSimpanan::find($payroll->id);
            $field->updated_at=date('Y-m-d H:i:s');
            $field->fid_status=$request->status;
            $field->save();
            $status=DB::table('status_payroll')->find($field->fid_status);
            GlobalHelper::add_verifikasi_transaksi('payroll_simpanan',$field->id,(!empty($status) ? $status->caption : ''),null);
            if($request->status==3){
                $this->update_status_simpanan($payroll->id,4);
            }
            else{
                $this->update_status_simpanan($payroll->id,1);
            }
        }
        return Redirect::back()
            ->with('message','Proses Verifikasi Simpanan Anggota berhasil')
            ->with('message_type','success');
    }

    public function update_status_simpanan($id,$status){
        $simpanan=Transaksi::where('fid_payroll',$id)->get();
        foreach ($simpanan as $key => $value) {
            $field=Transaksi::find($value->id);
            $field->fid_status=($status==4 ? 4 : 0);
            $field->save();
            if($status==4){
                $anggota=Anggota::where('no_anggota',$value->fid_anggota)->where('fid_status',2)->first();
                if(!empty($anggota)){
                    $anggota=Anggota::find($anggota->id);
                    $anggota->fid_status=3;
                    $anggota->save();
                }
            }
        }
    }

    public function hapus(Request $request)
    {
        $bulan = $request->input('bulan') ?? '';
        $payroll = PayrollSimpanan::where('bulan', $bulan)->first();

        DB::statement("delete from transaksi where fid_payroll = ". $payroll->id ." and fid_jenis_transaksi in (2, 3, 4);");
        DB::statement("delete from payroll_simpanan where id = ". $payroll->id .";");
        return redirect()->back();
    }

}
