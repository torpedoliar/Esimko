<?php

namespace App\Http\Controllers;

use App\Exports\PinjamanExport;
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

class PinjamanController extends Controller
{
    public function get_pinjaman($jenis,$status,$search, $tahun = '', $bulan = 'all', $is_paginate = true){
        $query=Transaksi::select('transaksi.*','anggota.no_anggota','anggota.nama_lengkap','rekening_pembayaran.keterangan as metode_transaksi','jenis_transaksi.jenis_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color')
            ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
            ->leftJoin('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
            ->join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
            ->join('rekening_pembayaran','rekening_pembayaran.id','=','transaksi.fid_metode_transaksi')
            ->where('jenis_transaksi.group','Pinjaman')
            ->with(['anggota', 'jenis_transaksi', 'angsuran_terakhir', 'angsuran_akan_datang']);
        if(!empty($search)){
            $query=$query->where(function ($i) use ($search) {
                $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
            });
        }
//        dd($jenis);
        if($jenis != 'all'){
            $query=$query->where('transaksi.fid_jenis_transaksi',$jenis);
        }

        if ($tahun !== 'all') {
            $query=$query->whereYear('transaksi.tanggal', $tahun);
        }

        if ($bulan !== 'all') {
            $query=$query->whereMonth('transaksi.tanggal', $bulan+1);
        }

        if($status == 'all'){
            $query=$query->where('transaksi.fid_status','!=',5);
        }
        else{
            $query=$query->where('transaksi.fid_status',$status);
        }
        $result=$query->orderBy('transaksi.tanggal','DESC');
//        dd($query->toSql());
        if ($is_paginate) $result = $result->paginate(10);
        else $result = $result->get();

//        if ($is_paginate == true) {
            foreach ($result as $key => $value) {
                $petugas = DB::table('anggota')->where('no_anggota', $value->created_by)->first();
                $result[$key]->nama_petugas = (!empty($petugas) ? $petugas->nama_lengkap : 'Undefined');

                $angsuran = Angsuran::where('fid_transaksi', $value->id)->first();
                if (!empty($angsuran)) {
                    $result[$key]->total_angsuran = $angsuran->angsuran_pokok + $angsuran->angsuran_bunga;
                    if ($value->fid_status == 6) {
                        $result[$key]->sisa_pinjaman = 0;
                        $result[$key]->sisa_tenor = 0;
                    } else {
                        $sisa_pinjaman = Angsuran::where('fid_transaksi', $value->id)->where('fid_status', '!=', 6)->first();
                        $result[$key]->sisa_pinjaman = (!empty($sisa_pinjaman) ? $sisa_pinjaman->sisa_hutang : 0);
//                        dd($value->id);
                        $result[$key]->sisa_tenor = Angsuran::where('fid_transaksi', $value->id)->where('fid_status', '!=', 6)->count();
                    }
                }
            }
        if ($is_paginate == true) {
            if (!empty($search)) {
                $result->withPath('pengajuan?jenis=' . $jenis . '&status=' . $status . '&search=' . $search);
            } else {
                $result->withPath('pengajuan?jenis=' . $jenis . '&status=' . $status);
            }
        }
//        }
        return $result;
    }

    public function export(Request $request)
    {

        $status = $request->status ?? 'all';

        $search=(!empty($request->search) ? $request->search : null);
        $jenis=(!empty($request->jenis) ? $request->jenis : 9);
        $tahun = $request->tahun ?? 'all';
        $bulan = $request->bulan ?? 'all';
//
//        $data = Transaksi::where('fid_jenis_transaksi', $jenis)->wherehas('jenis_transaksi', function ($q) {
//            $q->where('group', 'Pinjaman');
//        });
//
//        if ($status == 'all') $data = $data->where('fid_status', '!=', 5);
//        else $data = $data->where('fid_status', $status);
//
//        $data = $data->with(['anggota', 'status', 'angsuran_pokok', 'angsuran_bunga'])->get();

        $data = $this->get_pinjaman($jenis,$status,$search, $tahun, $bulan, false);

//        return view('pinjaman.pengajuan.export', compact('data', 'tahun', 'bulan'));
        return Excel::download(new PinjamanExport($data, $tahun, $bulan), 'laporan_pinjaman_'. $tahun .'_'. $bulan .'.xlsx');

//
//        $data = Transaksi::select('transaksi.id', 'transaksi.tanggal', 'jenis_transaksi.jenis_transaksi', 'status_transaksi.status','transaksi.fid_anggota', 'transaksi.tenor', DB::raw('(transaksi.nominal * -1) as nominal'), DB::raw("sum(angsuran.angsuran_pokok) as pokok"), DB::raw("sum(angsuran.angsuran_bunga) as bunga"), DB::raw("max(angsuran.angsuran_ke) as angsuran_ke"))
//            ->join('jenis_transaksi', 'jenis_transaksi.id', '=', 'transaksi.fid_jenis_transaksi')
//            ->join('status_transaksi', 'status_transaksi.id', '=', 'transaksi.fid_status')
//            ->join('angsuran', 'angsuran.fid_transaksi', '=', 'transaksi.id')
//            ->join('payroll_angsuran', 'payroll_angsuran.id', '=', 'angsuran.fid_payroll')
//            ->where('jenis_transaksi.group', 'Pinjaman')
//            ->whereIn('transaksi.fid_status', ['3', '4', '6'])
//            ->whereNotNull('angsuran.fid_payroll')
//            ->groupBy('transaksi.id')
//            ->get();



//        $bulan++;


//        $search=(!empty($request->search) ? $request->search : null);
//        $jenis=(!empty($request->jenis) ? $request->jenis : 9);
//        $status=(!empty($request->status) ? $request->status : 'all');

//        $data = $this->get_pinjaman($jenis, $status, $search, $tahun, $bulan, false);
        return Excel::download(new PinjamanExport($data, $tahun, $bulan), 'laporan_pinjaman_'. $tahun .'_'. $bulan .'.xlsx');
    }

    public function index(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,15);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $jenis=(!empty($request->jenis) ? $request->jenis : 9);
            $tahun = $request->tahun ?? 'all';
            $bulan = $request->bulan ?? 'all';
            $status=(!empty($request->status) ? $request->status : 'all');
            $data['pinjaman']=$this->get_pinjaman($jenis,$status,$search, $tahun, $bulan);
            $data['jenis']=DB::table('jenis_transaksi')->where('group','Pinjaman')->get();
            $data['status']=DB::table('status_transaksi')->get();
            return view('pinjaman.pengajuan.index')
                ->with('data',$data)
                ->with('jenis',$jenis)
                ->with('status',$status)
                ->with('tahun',$tahun)
                ->with('bulan',$bulan)
                ->with('search',$search);
        }
    }

    public function form(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,15);
        if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N'){
            return view('404');
        }
        else{
            $pinjaman=Transaksi::select('transaksi.*','anggota.id as anggota_id','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
                ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
                ->where('transaksi.id',$request->id)
                ->first();
            if(!empty($pinjaman)){
                $action='edit';
                $id=$request->id;
                $type=$pinjaman->fid_jenis_transaksi;
                $data['gaji-pokok']=GlobalHelper::gaji_pokok($pinjaman->no_anggota);
                $data['angsuran']=Angsuran::select('angsuran.*','status_angsuran.status_angsuran','status_angsuran.color')
                    ->join('status_angsuran','status_angsuran.id','=','angsuran.fid_status')
                    ->where('angsuran.fid_transaksi',$id)
                    ->get();
            }
            else{
                $action='add';
                $id=0;
                $type=$request->type;
            }
            $data['pinjaman']=$pinjaman;
            $data['metode-transaksi']=DB::table('rekening_pembayaran')->where('jenis_transaksi','like','%pinjaman%')->get();
            $data['anggota']=Anggota::limit(10)->get();
            $data['jenis-pinjaman']=DB::table('jenis_transaksi')->find($type);
            if(!empty($data['jenis-pinjaman'])){
                return view('pinjaman.pengajuan.form')
                    ->with('data',$data)
                    ->with('action',$action)
                    ->with('id',$id);
            }
            else{
                return redirect('pinjaman/pengajuan');
            }
        }
    }

    public function detail(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,15);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $pinjaman=Transaksi::select('transaksi.*','anggota.no_anggota','jenis_transaksi.jenis_transaksi','anggota.nama_lengkap','rekening_pembayaran.keterangan as metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color','status_transaksi.icon')
                ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
                ->leftJoin('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
                ->join('rekening_pembayaran','rekening_pembayaran.id','=','transaksi.fid_metode_transaksi')
                ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
                ->where('transaksi.id',$request->id)
                ->first();
            if(!empty($pinjaman)){
                $angsuran=Angsuran::where('fid_transaksi',$pinjaman->id)->first();
                if(!empty($angsuran)){
                    $pinjaman->total_angsuran=$angsuran->angsuran_pokok+$angsuran->angsuran_bunga;
                    $pinjaman->angsuran_bunga=$angsuran->angsuran_bunga;
                    $sisa_pinjaman=Angsuran::where('fid_transaksi',$pinjaman->id)->where('fid_status','!=',6)->first();
                    $pinjaman->sisa_pinjaman=(!empty($sisa_pinjaman) ? $sisa_pinjaman->sisa_hutang  : 0 );
                    $pinjaman->sisa_tenor=Angsuran::where('fid_transaksi',$pinjaman->id)->where('fid_status','!=',6)->count();
                    $pinjaman->total_pelunasan=$pinjaman->angsuran_bunga+$pinjaman->sisa_pinjaman;
                }
                $anggota=Anggota::where('no_anggota',$pinjaman->created_by)->first();
                $pinjaman->nama_petugas=(!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
                $data['pinjaman']=$pinjaman;
                $data['keterangan']=DB::table('keterangan_status_transaksi')
                    ->where('jenis_transaksi','pinjaman')
                    ->where('fid_status',$pinjaman->fid_status)
                    ->where('user_page','admin')
                    ->first();
                $data['jenis-transaksi']=DB::table('jenis_transaksi')->whereIn('id',array(9,10,11))->get();
                $data['angsuran']=Angsuran::select('angsuran.*','status_angsuran.status_angsuran','status_angsuran.color')
                    ->join('status_angsuran','status_angsuran.id','=','angsuran.fid_status')
                    ->where('angsuran.fid_transaksi',$request->id)
                    ->get();
                return view('pinjaman.pengajuan.detail')
                    ->with('data',$data)
                    ->with('id',$request->id);
            }
            else{
                return redirect('pinjaman/pengajuan');
            }
        }
    }

    public function proses(Request $request){
        if($request->action=='add'){
            $field=new Transaksi;
            $field->created_at=date('Y-m-d H:i:s');
            $field->created_by=Session::get('useractive')->no_anggota;
            $field->fid_jenis_transaksi=$request->jenis;
        }
        else{
            $field=Transaksi::find($request->id);
            $field->updated_at=date('Y-m-d H:i:s');
        }
        $field->fid_status=1;
        $field->fid_anggota=$request->no_anggota;
        $field->fid_metode_transaksi=$request->metode_transaksi;
        $field->nominal=-str_replace('.','',$request->nominal);
        $field->tenor=$request->tenor;
        $field->keterangan=$request->keterangan;
        $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
        if($request->action=='delete'){
            $field->delete();
            $msg='Pengajuan Pinjaman anggota berhasil dihapus';
        }
        else{
            $field->save();
            $this->proses_angsuran($field->id,$request);
            $this->update_riwayat_gaji($request);
            $this->update_status_angsuran($field->id,1);
            $msg='Pengajuan Pinjaman berhasil disimpan';
        }

        if($request->action=='delete'){
            return redirect()
                ->with('message',$msg)
                ->with('message_type','success');
        }
        else{
            return redirect('pinjaman/pengajuan/detail?id='.$field->id);
        }
    }

    public function update_riwayat_gaji($request){
        $riwayat_gaji=GajiPokok::where('fid_anggota',$request->no_anggota)
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
            $field->fid_anggota=$request->no_anggota;
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

    public function verifikasi(Request $request){
        $field=Transaksi::find($request->id);
        $field->fid_status=($request->status==6 ? 4 : $request->status);
        $field->save();
        if($request->status!=6){
            $this->update_status_angsuran($field->id,$request->status);
            $status=DB::table('status_transaksi')->find($field->fid_status);
            if($field->fid_status==1){
                GlobalHelper::add_verifikasi_transaksi('transaksi',$field->id,'Verifikasi Transaksi dibatalkan oleh',null);
            }
            else{
                GlobalHelper::add_verifikasi_transaksi('transaksi',$field->id,(!empty($status) ? $status->caption : ''),null);
            }
            return Redirect::back()
                ->with('message','Pengajuan pinjaman anggota berhasil diverikasi')
                ->with('message_type','success');
        }
        else{
            GlobalHelper::add_verifikasi_transaksi('transaksi',$field->id,'Pelunasan pinjaman dibatalkan oleh',null);
            return Redirect::back()
                ->with('message','Pelunasan pinjaman anggota berhasil dibatalkan')
                ->with('message_type','success');
        }
    }

    public function update_status_angsuran($id,$status){
        $angsuran=Angsuran::where('fid_transaksi',$id)->get();
        foreach ($angsuran as $key => $value) {
            $field=Angsuran::find($value->id);
            if($status==1){
                $field->fid_status=2;
            }
            elseif($status==2){
                $field->fid_status=4;
            }
            else{
                $field->fid_status=3;
            }
            $field->save();
        }
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

    public function pelunasan(Request $request){
        $pinjaman=Transaksi::find($request->id);
        if(!empty($pinjaman)){
            $jenis_angsuran=array('sisa_pinjaman'=>14,'bunga_pinjaman'=>13);
            foreach ($jenis_angsuran as $key => $jenis){
                $cek_transaksi=Transaksi::where('fid_pinjaman',$pinjaman->id)->where('fid_jenis_transaksi',$jenis)->first();
                if(!empty($cek_transaksi)){
                    $field=Transaksi::find($cek_transaksi->id);
                    $field->updated_at=date('Y-m-d H:i:s');
                }
                else{
                    $field=new Transaksi;
                    $field->created_at=date('Y-m-d H:i:s');
                    $field->created_by=Session::get('useractive')->no_anggota;
                }
                $field->fid_status=4;
                $field->fid_jenis_transaksi=$jenis;
                $field->fid_anggota=$pinjaman->fid_anggota;
                $field->fid_metode_transaksi=1; //Cash & Tunai
                $field->fid_pinjaman=$pinjaman->id;
                $nominal=($jenis==14 ? $request->sisa_pinjaman : $request->bunga_pinjaman);
                $field->nominal=str_replace('.','',$nominal);
                $field->tanggal=date('Y-m-d', strtotime($request->tanggal_pelunasan));
                $field->save();
            }
            $pinjaman->fid_status=6;
            $pinjaman->save();
            GlobalHelper::add_verifikasi_transaksi('transaksi',$pinjaman->id,'Pinjaman sudah dilunasi oleh',null);
        }
        return Redirect::back()
            ->with('message','Pelunasan pinjaman berhasil disimpan')
            ->with('message_type','success');
    }

    public function delete($id)
    {
        $angsuran = Angsuran::find($id);
        $angsuran->fid_payroll = null;
        $angsuran->fid_stastus = 3;
        $angsuran->save();
        return redirect()->back();
    }

}
