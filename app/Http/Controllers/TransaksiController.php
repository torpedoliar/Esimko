<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Transaksi;
use App\GajiPokok;
use App\Angsuran;
use App\Berita;
use App\AttachmentBerita;
use View;
use DB;
use DateTime;
use Redirect;

class TransaksiController extends Controller
{

    public function get_transaksi($modul){
        $query=Transaksi::select('transaksi.*','jenis_transaksi.jenis_transaksi','jenis_transaksi.operasi','metode_transaksi.metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color')
            ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
            ->Join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
            ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
            ->join('metode_transaksi','metode_transaksi.id','=','transaksi.fid_metode_transaksi')
            ->where('transaksi.fid_anggota',Session::get('useractive')->no_anggota);

        if($modul=='simpanan'){
            $query=$query->whereIn('transaksi.fid_jenis_transaksi',array(1,2,3,4,5,6,7,8));
        }
        elseif($modul=='pinjaman'){
            $query=$query->whereIn('transaksi.fid_jenis_transaksi',array(9,10,11));
        }

        if(!empty(Session::get('filter_transaksi')[$modul])){
            $filters=Session::get('filter_transaksi');
            if($filters[$modul]['jenis']!='all'){
                $query=$query->where('transaksi.fid_jenis_transaksi',$filters[$modul]['jenis']);
            }

            if($filters[$modul]['status']!='all'){
                $query=$query->where('transaksi.fid_status',$filters[$modul]['status']);
            }
            else{
                $query=$query->where('transaksi.fid_status','!=',5);
            }

            if(!empty($filters[$modul]['from']) && !empty($filters[$modul]['to'])){
                $query=$query->whereBetween('transaksi.tanggal', [GlobalHelper::dateFormat($filters[$modul]['from'],'Y-m-d'), GlobalHelper::dateFormat($filters[$modul]['to'],'Y-m-d')]);
            }
        }
        else{
            $query=$query->where('transaksi.fid_status','!=',5);
        }
        $result=$query->orderBy('transaksi.tanggal','DESC')->orderBy('transaksi.created_at','DESC')->paginate(10);
        if($modul == 'pinjaman'){
            foreach ($result as $key => $value) {
                $angsuran=Angsuran::where('fid_transaksi',$value->id)->first();
                if(!empty($angsuran)){
                    $result[$key]->total_angsuran=$angsuran->angsuran_pokok+$angsuran->angsuran_bunga;
                    $sisa_pinjaman=Angsuran::where('fid_transaksi',$value->id)->where('fid_status','!=',6)->first();
                    $result[$key]->sisa_pinjaman=(!empty($sisa_pinjaman) ? $sisa_pinjaman->sisa_hutang  : 0 );
                    $result[$key]->sisa_tenor=Angsuran::where('fid_transaksi',$value->id)->where('fid_status','!=',6)->count();
                }
            }
        }
        return $result;
    }

    public function validasi_transaksi($request,$jenis){
        $anggota=Session::get('useractive')->no_anggota;
        if($jenis=='simpanan'){
            $msg='success';
        }
        elseif($jenis=='penarikan'){
            $saldo=GlobalHelper::saldo_tabungan($anggota,'Simpanan Sukarela');
            $msg=((str_replace(',','',$request->nominal)>$saldo) ? 'Saldo simpanan tidak mencukupi' : 'success' );
        }
        elseif($jenis=='pinjaman'){
            $jenis_pinjaman=DB::table('jenis_transaksi')->find($jenis);
            if(!empty($jenis_pinjaman)){
                $tenor=array(9=>50,10=>18,11=>18);
                if($request->tenor > $tenor[$request->jenis_transaksi]){
                    $msg='Tenor melebihi maksimal tenor yaitu '.$tenor[$request->jenis_transaksi].' bulan';
                }
                else{
                    $total_angsuran=GlobalHelper::angsuran_pinjaman($anggota,'all')+$request->total_angsuran_pinjaman+GlobalHelper::total_angsuran_belanja($anggota)+GlobalHelper::setoran_berkala($anggota)+350000;
                    $total_angsuran_pinjaman=GlobalHelper::angsuran_pinjaman($anggota,'all')+350000+str_replace('.','',$request->total_angsuran_pinjaman);

                    $sisa_tenor=GlobalHelper::sisa_tenor_pinjaman($anggota,$request->jenis_transaksi)['sisa'];
                    $sisa_pinjaman=GlobalHelper::sisa_pinjaman($anggota,$request->jenis_transaksi);

                    if($sisa_tenor==0){
                        if($total_angsuran <= $request->gaji_pokok ){
                            if($total_angsuran_pinjaman > $request->gaji_pokok/2 ){
                                $msg='Maaf anda belum bisa mengajukan pinjaman dengan total angsuran perbulan <b>Rp '.$request->total_angsuran_pinjaman.'</b> karena melebihi 50% Gaji Pokok. Silahkan masukkan jumlah pinjaman dan tenor yang sesuai';
                            }
                            else{
                                $msg='success';
                            }
                        }
                        else{
                            $msg='Maaf anda belum bisa mengajukan pinjaman dengan total angsuran perbulan Rp '.$request->total_angsuran_pinjaman.' karena total angsuran melebihi Gaji Pokok. Silahkan masukkan jumlah pinjaman dan tenor yang sesuai atau ubah kembali nominal setoran berkala';
                        }
                    }
                    else{
                        $msg='Maaf anda belum bisa mengajukan '.$jenis_pinjaman->jenis_transaksi.', karena anda masih mempunyai sisa angsuran senilai <b>Rp '.number_format($sisa_pinjaman,0,',','.').'</b> dan sisa tenor <b>'.$sisa_tenor.'x </b>. Silahkan melunasi pinjaman anda atau melakukan pengajuan pinjaman yang lain.';
                    }
                }
            }
        }
        else{
            $msg='failed';
        }
        return $msg;
    }

    public function proses_transaksi(Request $request){
        $validasi=$this->validasi_transaksi($request,$request->modul);
        if($validasi=='success' ){
            if($request->action=='add'){
                $field=new Transaksi;
                $field->created_at=date('Y-m-d H:i:s');
                $field->created_by=Session::get('useractive')->no_anggota;
                $field->fid_status=1;
            }
            else{
                $field=Transaksi::find($request->id);
                $field->updated_at=date('Y-m-d H:i:s');
                $field->fid_status=1;
                $field->bukti_transaksi=($request->action=='proses_ulang' ? null : $field->bukti_transaksi );
            }
            $field->fid_metode_transaksi=($request->modul=='penarikan' ? str_replace('.','',$request->nominal)>1000000 ? 3 : 1 : 3);
            $field->fid_jenis_transaksi=$request->jenis_transaksi;
            $field->fid_anggota=Session::get('useractive')->no_anggota;
            $field->nominal=($request->modul=='simpanan' ? $request->modul=='pinjaman' ? '-' : '' : '-' ).''.str_replace('.','',$request->nominal);
            $field->keterangan=$request->keterangan;
            $field->tanggal=date('Y-m-d');
            $field->tenor=($request->modul=='pinjaman' ? $request->tenor : null );
            if($request->action=='delete'){
                $field->delete();
            }
            else{
                $field->save();
                if($request->modul=='pinjaman'){
                    $this->update_riwayat_gaji($request);
                    $this->proses_angsuran($field->id,$request);
                }
                if($request->action=='proses_ulang'){
                    GlobalHelper::add_verifikasi_transaksi('transaksi',$field->id,'Transaksi diajukan ulang oleh',null);
                }
            }
            if($request->modul == 'pinjaman'){
                return redirect('main/pinjaman/detail?id='.$field->id);
            }
            else{
                return redirect('main/simpanan/detail?id='.$field->id);
            }

        }
        else{
            return Redirect::back()
                ->with('message',$validasi)
                ->with('message_type','warning');
        }
    }

    public function proses_pembatalan(Request $request){
        $field=Transaksi::find($request->id);
        $field->fid_status=5;
        $field->save();
        GlobalHelper::add_verifikasi_transaksi('transaksi',$field->id,'Transaksi dibatalkan oleh',null);
        if($request->modul == 'pinjaman'){
            return redirect('main/pinjaman');
        }
        else{
            return redirect('main/simpanan');
        }
    }

    public function filter_transaksi(Request $request){
        $modul=$request->modul;
        Session::forget('filter_transaksi');
        $data[$modul]=array('jenis'=>$request->jenis,'status'=>$request->status,'from'=>$request->from,'to'=>$request->to);
        Session::put('filter_transaksi',$data);
        return Redirect::back();
    }

    public function update_riwayat_gaji($request){
        $riwayat_gaji=GajiPokok::where('fid_anggota',Session::get('useractive')->no_anggota)
            ->where('bulan',$request->bulan)
            ->first();
        if(!empty($riwayat_gaji)){
            $field=GajiPokok::find($riwayat_gaji->id);
            $field->updated_at=date('Y-m-d H:i:s');
        }
        else{
            $field=new GajiPokok;
            $field->created_at=date('Y-m-d H:i:s');
            $field->created_by=Session::get('useractive')->no_anggota;
            $field->bulan=$request->bulan;
            $field->fid_anggota=Session::get('useractive')->no_anggota;
        }
        if($request->hasFile('attachment')){
            if(!empty($field->attachment)){
                unlink(storage_path('app/'.$field->attachment));
            }
            $uploadedFile = $request->file('attachment');
            $path = $uploadedFile->store('slip_gaji');
            $field->attachment=$path;
        }
        $field->gaji_pokok=str_replace('.','',$request->gaji_pokok);
        $field->save();
    }

    public function upload_bukti_transaksi(Request $request){
        $field=Transaksi::find($request->id);
        if(!empty($field)){
            if($request->hasFile('bukti_transaksi')){
                if(!empty($field->bukti_transaksi)){
                    unlink(storage_path('app/'.$field->bukti_transaksi));
                }
                $uploadedFile = $request->file('bukti_transaksi');
                $path = $uploadedFile->store('bukti_transaksi');
                $field->bukti_transaksi=$path;
                $field->save();
                return Redirect::back()
                    ->with('message','Bukti Transaksi berhasil diupload')
                    ->with('message_type','success');
            }
            else{
                return Redirect::back()
                    ->with('message','File tida ditemukan')
                    ->with('message_type','warning');
            }
        }
        else{
            return Redirect::back()
                ->with('message','Transaksi tidak ditemukan')
                ->with('message_type','warning');
        }
    }

    public function detail_transaksi($id){
        $data=Transaksi::select('transaksi.*','anggota.no_anggota','jenis_transaksi.jenis_transaksi','jenis_transaksi.group as group_transaksi','anggota.nama_lengkap','metode_transaksi.metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color','status_transaksi.icon')
            ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
            ->leftJoin('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
            ->join('metode_transaksi','metode_transaksi.id','=','transaksi.fid_metode_transaksi')
            ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
            ->where('transaksi.id',$id)
            ->first();
        if(!empty($data)){
            $anggota=Anggota::where('no_anggota',$data->created_by)->first();
            $data->nama_petugas=(!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
        }
        return $data;
    }

    //----------------------------------------------------SIMPANAN-----------------------------------------//

    public function simpanan(Request $request){
        $data['simpanan']=$this->get_transaksi('simpanan');
        $data['status-transaksi']=DB::table('status_transaksi')->where('id','<>',6)->get();
        $data['jenis-transaksi']=DB::table('jenis_transaksi')->whereIn('id',array(1,2,3,4,5,6,7,8))->get();
        $data['saldo']=Session::get('useractive');
        $data['saldo']->simpanan_pokok=GlobalHelper::saldo_tabungan(Session::get('useractive')->no_anggota,1);
        $data['saldo']->simpanan_wajib=GlobalHelper::saldo_tabungan(Session::get('useractive')->no_anggota,2);
        $data['saldo']->simpanan_hari_raya=GlobalHelper::saldo_tabungan(Session::get('useractive')->no_anggota,'Simpanan Hari Raya');
        $data['saldo']->simpanan_sukarela=GlobalHelper::saldo_tabungan(Session::get('useractive')->no_anggota,'Simpanan Sukarela');
        $data['saldo']->total_simpanan=GlobalHelper::saldo_tabungan(Session::get('useractive')->no_anggota,'Total Simpanan');
        return view('main.transaksi.simpanan.index')
            ->with('data',$data);
    }

    public function simpanan_detail(Request $request){
        $simpanan=$this->detail_transaksi($request->id);
        if(!empty($simpanan)){
            $simpanan->modul=($simpanan->group_transaksi=='Penarikan Simpanan' ? 'penarikan' : 'simpanan');
            $data['simpanan']=$simpanan;
            $data['keterangan']=DB::table('keterangan_status_transaksi')
                ->where('jenis_transaksi','simpanan')
                ->where('fid_status',$simpanan->fid_status)
                ->where('user_page','main')
                ->first();
            $data['metode-transaksi']=DB::table('metode_transaksi')->where('id','<>',1)->get();
            return view('main.transaksi.simpanan.detail')
                ->with('data',$data)
                ->with('id',$request->id);
        }
        else{
            return redirect('main/simpanan');
        }
    }

    //----------------------------------------------------PINJAMAN-----------------------------------------//

    public function pinjaman(Request $request){
        $data['pinjaman']=$this->get_transaksi('pinjaman');
        $data['status-transaksi']=DB::table('status_transaksi')->get();
        $data['gaji-pokok']=GlobalHelper::gaji_pokok(Session::get('useractive')->no_anggota);
        $jenis_transkasi=DB::table('jenis_transaksi')->whereIn('id',array(9,10,11))->get();
        $total_sisa=0;
        foreach ($jenis_transkasi as $key => $value) {
            $jenis_transkasi[$key]->sisa_pinjaman=GlobalHelper::sisa_pinjaman(Session::get('useractive')->no_anggota,$value->id);
            $total_sisa=$total_sisa+$jenis_transkasi[$key]->sisa_pinjaman;
        }
        $data['jenis-transaksi']=$jenis_transkasi;
        $data['total-sisa']=$total_sisa;
        return view('main.transaksi.pinjaman.index')
            ->with('data',$data);
    }

    public function pinjaman_form(Request $request){
        $data['gaji-pokok']=GlobalHelper::gaji_pokok(Session::get('useractive')->no_anggota);
        $jenis_pinjaman=DB::table('jenis_transaksi')->whereIn('id',array(9,10,11))->get();
        foreach ($jenis_pinjaman as $key => $value) {
            $jenis_pinjaman[$key]->angsuran=GlobalHelper::angsuran_pinjaman(Session::get('useractive')->no_anggota,$value->id);
        }
        $data['jenis-transaksi']=$jenis_pinjaman;
        $data['angsuran-pinjaman']=GlobalHelper::angsuran_pinjaman(Session::get('useractive')->no_anggota,'all');
        $data['angsuran-belanja']=GlobalHelper::total_angsuran_belanja(Session::get('useractive')->no_anggota);
        $data['angsuran-simpanan']=350000;
        $data['setoran-berkala']=GlobalHelper::setoran_berkala(Session::get('useractive')->no_anggota);
        $data['total-angsuran']=$data['angsuran-pinjaman']+$data['angsuran-belanja']+$data['angsuran-simpanan']+$data['setoran-berkala'];
        return view('main.transaksi.pinjaman.form')
            ->with('data',$data);
    }

    public function sisa_hutang($id,$n){
        $angsuran=Angsuran::where('angsuran_ke',$n-1)->where('fid_transaksi',$id)->first();
        if(!empty($angsuran)){
            $sisa_hutang=$angsuran->sisa_hutang-$angsuran->angsuran_pokok;
            return $sisa_hutang;
        }
        else{
            $pinjaman=Transaksi::find($id);
            return (!empty($pinjaman) ? str_replace('-','',$pinjaman->nominal) : 0 );
        }
    }

    public function proses_angsuran($id,$request){
        Angsuran::where('fid_transaksi',$id)->delete();
        for($n=1;$n<=$request->tenor;$n++){
            $field=new Angsuran;
            $field->angsuran_ke=$n;
            $field->fid_transaksi=$id;
            $field->bunga=0.01;
            $field->sisa_hutang=$this->sisa_hutang($id,$n);
            $field->angsuran_pokok=ROUND(str_replace('.','',$request->nominal)/$request->tenor,0);
            $field->angsuran_bunga=ROUND(0.01*str_replace('.','',$request->nominal));
            $field->fid_status=2;
            $field->save();
        }
    }

    public function konfirmasi_angsuran(Request $request){
        $field=Transaksi::find($request->id);
        if(!empty($field)){
            $field->fid_status=1;
            $field->save();
            return redirect('main/pinjaman/detail?id='.$request->id);
        }
        else{
            return redirect('main/pinjaman');
        }
    }

    public function pinjaman_detail(Request $request){
        $pinjaman=$this->detail_transaksi($request->id);
        if(!empty($pinjaman)){
            $angsuran=Angsuran::where('fid_transaksi',$pinjaman->id)->first();
            if(!empty($angsuran)){
                $pinjaman->total_angsuran=$angsuran->angsuran_pokok+$angsuran->angsuran_bunga;
                $sisa_pinjaman=Angsuran::where('fid_transaksi',$pinjaman->id)->where('fid_status','!=',6)->first();
                $pinjaman->sisa_pinjaman=(!empty($sisa_pinjaman) ? $sisa_pinjaman->sisa_hutang  : 0 );
                $pinjaman->sisa_tenor=Angsuran::where('fid_transaksi',$pinjaman->id)->where('fid_status','!=',6)->count();
            }
            $data['pinjaman']=$pinjaman;
            $data['keterangan']=DB::table('keterangan_status_transaksi')
                ->where('jenis_transaksi','pinjaman')
                ->where('fid_status',$pinjaman->fid_status)
                ->where('user_page','main')
                ->first();
            $jenis_pinjaman=DB::table('jenis_transaksi')->whereIn('id',array(9,10,11))->get();
            $total_angsuran=0;
            foreach ($jenis_pinjaman as $key => $value) {
                $jenis_pinjaman[$key]->angsuran=GlobalHelper::angsuran_pinjaman(Session::get('useractive')->no_anggota,$value->id);
                $total_angsuran=$total_angsuran+$jenis_pinjaman[$key]->angsuran;
            }

            $data['jenis-transaksi']=$jenis_pinjaman;
            $data['total-angsuran']=$total_angsuran+350000;
            $data['gaji-pokok']=GlobalHelper::gaji_pokok(Session::get('useractive')->no_anggota);
            $data['angsuran']=Angsuran::select('angsuran.*','status_angsuran.status_angsuran','status_angsuran.color')
                ->join('status_angsuran','status_angsuran.id','=','angsuran.fid_status')
                ->where('angsuran.fid_transaksi',$request->id)
                ->orderBy('angsuran.angsuran_ke','ASC')
                ->get();

            if($data['pinjaman']->fid_status == 0){
                $data['status']=GlobalHelper::validasi_pinjaman($request->id);
                return view('main.transaksi.pinjaman.angsuran')
                    ->with('data',$data)
                    ->with('id',$request->id);
            }
            else{
                return view('main.transaksi.pinjaman.detail')
                    ->with('data',$data)
                    ->with('id',$request->id);
            }
        }
        else{
            return redirect('main/pinjaman');
        }
    }

    //-----------------------------------------ANGSURAN---------------------------------------//
    public function get_angsuran($anggota,$modul='angsuran'){
        $query=Angsuran::select('angsuran.id','angsuran.angsuran_ke','angsuran.angsuran_pokok','angsuran.angsuran_bunga','payroll_angsuran.created_at','jenis_transaksi.jenis_transaksi','payroll_angsuran.bulan','status_angsuran.status_angsuran','status_angsuran.color')
            ->join('transaksi','transaksi.id','=','angsuran.fid_transaksi')
            ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
            ->join('status_angsuran','status_angsuran.id','=','angsuran.fid_status')
            ->leftJoin('payroll_angsuran','payroll_angsuran.id','=','angsuran.fid_payroll')
            ->where('transaksi.fid_anggota',$anggota);
        if(!empty(Session::get('filter_transaksi')[$modul])){
            $filters=Session::get('filter_transaksi');
            if($filters[$modul]['jenis']!='all'){
                $query=$query->where('transaksi.fid_jenis_transaksi',$filters[$modul]['jenis']);
            }

            if($filters[$modul]['status']!='all'){
                $query=$query->where('transaksi.fid_status',$filters[$modul]['status']);
            }
            else{
                $query=$query->whereIn('angsuran.fid_status',array(5,6));
            }

            if(!empty($filters[$modul]['from']) && !empty($filters[$modul]['to'])){
                $query=$query->whereBetween('payroll_angsuran.created_at', [GlobalHelper::dateFormat($filters[$modul]['from'],'Y-m-d'), GlobalHelper::dateFormat($filters[$modul]['to'],'Y-m-d')]);
            }
        }
        else{
            $query=$query->whereIn('angsuran.fid_status',array(5,6));
        }
        $result=$query->orderBy('payroll_angsuran.created_at','DESC')->orderBy('angsuran.angsuran_ke','DESC')->paginate(10);
        foreach ($result as $key => $value) {
            $result[$key]->total_angsuran=$value->angsuran_pokok+$value->angsuran_bunga;
        }
        return $result;
    }

    public function angsuran(Request $request){
        $data['angsuran']=$this->get_angsuran(Session::get('useractive')->no_anggota);
        $data['status-transaksi']=DB::table('status_angsuran')->whereIn('id',array(5,6))->get();
        $jenis_transkasi=DB::table('jenis_transaksi')->whereIn('id',array(9,10,11))->get();
        $total_sisa=0;
        foreach ($jenis_transkasi as $key => $value) {
            $jenis_transkasi[$key]->angsuran_pinjaman=GlobalHelper::angsuran_pinjaman(Session::get('useractive')->no_anggota,$value->id);
        }
        $data['jenis-transaksi']=$jenis_transkasi;
        $data['total-angsuran']=GlobalHelper::angsuran_pinjaman(Session::get('useractive')->no_anggota,'all'); //Total Angsuran Pinjaman
        return view('main.transaksi.angsuran.index')
            ->with('data',$data);
    }

    public function angsuran_detail(Request $request){
        $angsuran=Angsuran::select('angsuran.id','angsuran.angsuran_ke','angsuran.angsuran_pokok','angsuran.angsuran_bunga','payroll_angsuran.created_at','jenis_transaksi.jenis_transaksi','payroll_angsuran.bulan','status_angsuran.status_angsuran','status_angsuran.color')
            ->join('transaksi','transaksi.id','=','angsuran.fid_transaksi')
            ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
            ->join('status_angsuran','status_angsuran.id','=','angsuran.fid_status')
            ->leftJoin('payroll_angsuran','payroll_angsuran.id','=','angsuran.fid_payroll')
            ->where('angsuran.id',$request->id)
            ->where('transaksi.fid_anggota',Session::get('useractive')->no_anggota)
            ->first();
        if(!empty($angsuran)){
            $angsuran->no_anggota=Session::get('useractive')->no_anggota;
            $angsuran->nama_lengkap=Session::get('useractive')->nama_lengkap;
            $angsuran->total_angsuran=$angsuran->angsuran_pokok+$angsuran->angsuran_bunga;
            $data['angsuran']=$angsuran;
            return view('main.transaksi.angsuran.detail')
                ->with('data',$data);
        }
        else{
            return redirect('main/angsuran');
        }
    }

    //-----------------------------------BERITA------------------------------------//

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
        return view('main.berita.index')
            ->with('search',$search)
            ->with('data',$data);
    }

    public function detail_berita(Request $request){
        $data['berita']=Berita::find($request->id);
        $data['attachment']=AttachmentBerita::where('fid_berita',$request->id)->get();
        return view('main.berita.detail')
            ->with('data',$data);
    }



}
