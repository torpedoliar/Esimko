<?php

namespace App\Http\Controllers;

use App\JenisTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Transaksi;
use App\PayrollSimpanan;
use App\SetoranBerkala;
use App\GajiPokok;
use View;
use DB;
use DateTime;
use Redirect;

class SimpananController extends Controller
{

    //--------------------------------------------- SIMPANAN SUKARELA -------------------------------------------------//

    public function get_simpanan_sukarela($status,$search){
        $query=Transaksi::select('transaksi.*','anggota.no_anggota','anggota.nama_lengkap','rekening_pembayaran.keterangan as metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color')
            ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
            ->join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
            ->join('rekening_pembayaran','rekening_pembayaran.id','=','transaksi.fid_metode_transaksi');
//            ->where('transaksi.fid_jenis_transaksi','4');
        if(!empty($search)){
            $query=$query->where(function ($i) use ($search) {
                $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
            });
        }
        if($status == 'all'){
            $query=$query->where('transaksi.fid_status','!=',5);
        }
        else{
            $query=$query->where('transaksi.fid_status',$status);
        }
        $result=$query->orderBy('transaksi.tanggal','DESC')->paginate(10);

        foreach ($result as $key => $value) {
            $petugas=DB::table('anggota')->where('no_anggota',$value->created_by)->first();
            $result[$key]->nama_petugas=(!empty($petugas) ? $petugas->nama_lengkap : 'Undefined');
        }

        if(!empty($search)){
            $result->withPath('sukarela?status='.$status.'&search='.$search);
        }
        return $result;
    }


    public function index(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,34);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $status=(!empty($request->status) ? $request->status : 'all');
            $data['simpanan-sukarela']=$this->get_simpanan_sukarela($status,$search);
            $data['status']=DB::table('status_transaksi')->where('id','!=',6)->get();
            return view('simpanan.sukarela.index')
                ->with('data',$data)
                ->with('status',$status)
                ->with('search',$search);
        }
    }

    public function form(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,34);
        $data['jenis_simpanan']=JenisTransaksi::where('id', '<=', 4)->get();
        if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N'){
            return view('404');
        }
        else{
            $simpanan=Transaksi::select('transaksi.*','anggota.nama_lengkap','no_anggota','anggota.avatar')
                ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
                ->where('transaksi.id',$request->id)
                ->first();
            if(!empty($simpanan)){
                $action='edit';
                $id=$request->id;
            }
            else{
                $action='add';
                $id=0;
            }
            $data['simpanan']=$simpanan;
            $data['metode-transaksi']=DB::table('rekening_pembayaran')->where('jenis_transaksi','like','%simpanan%')->get();
            $data['anggota']=Anggota::limit(10)->get();
            return view('simpanan.sukarela.form')
                ->with('data',$data)
                ->with('action',$action)
                ->with('id',$id);
        }
    }

    public function detail(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,34);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $simpanan=Transaksi::select('transaksi.*','anggota.no_anggota','anggota.nama_lengkap','jenis_transaksi.jenis_transaksi','status_transaksi.icon','rekening_pembayaran.keterangan as metode_transaksi','anggota.avatar','status_transaksi.status','status_transaksi.color')
                ->join('anggota','anggota.no_anggota','=','transaksi.fid_anggota')
                ->join('status_transaksi','status_transaksi.id','=','transaksi.fid_status')
                ->join('rekening_pembayaran','rekening_pembayaran.id','=','transaksi.fid_metode_transaksi')
                ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.fid_jenis_transaksi')
                ->where('transaksi.fid_jenis_transaksi','4')
                ->where('transaksi.id',$request->id)
                ->first();
            if(!empty($simpanan)){
                $anggota=Anggota::where('no_anggota',$simpanan->created_by)->first();
                $simpanan->nama_petugas=(!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
                $data['simpanan']=$simpanan;
                $data['keterangan']=DB::table('keterangan_status_transaksi')
                    ->where('jenis_transaksi','penarikan')
                    ->where('fid_status',$simpanan->fid_status)
                    ->where('user_page','admin')
                    ->first();
                $data['metode-transaksi']=DB::table('metode_transaksi')->get();
                return view('simpanan.sukarela.detail')
                    ->with('data',$data)
                    ->with('id',$request->id);
            }
            else{
                return Redirect::back();
            }
        }
    }

    public function delete(Request  $request)
    {
        Transaksi::where('id', $request->input('id'))->delete();
        return redirect()->back();
    }

    public function proses(Request $request){
        $anggota=Anggota::where('no_anggota',$request->no_anggota)->first();
        if(!empty($anggota) && $request->no_anggota!=null){
            if($request->action=='add'){
                $field=new Transaksi;
                $field->created_at=date('Y-m-d H:i:s');
                $field->created_by=Session::get('useractive')->no_anggota;
                $field->fid_jenis_transaksi= $request->input('jenis_simpanan');
            }
            else{
                $field=Transaksi::find($request->id);
                $field->updated_at=date('Y-m-d H:i:s');
            }
            $field->fid_status=4;
            $field->fid_anggota=$request->no_anggota;
            $field->fid_metode_transaksi=$request->metode_transaksi;
            $field->nominal=str_replace(',','',$request->nominal);
            $field->keterangan=$request->keterangan;
            $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
            if($request->action=='delete'){
                $field->delete();
                $msg='Simpanan Sukarela anggota berhasil dihapus';
                $url='simpanan/sukarela';
            }
            else{
                $field->save();
                $msg='Simpanan Sukarela berhasil disimpan';
                $url='simpanan/sukarela/detail?id='.$field->id;
                if($request->action=='proses_ulang'){
                    GlobalHelper::add_verifikasi_transaksi('transaksi',$field->id,'Transaksi diajukan ulang oleh',null);
                }
            }
            return redirect($url)
                ->with('message',$msg)
                ->with('message_type','success');
        }
        else{
            return Redirect::back()
                ->with('message','Anggota Koperasi belum dipilih / tidak ditemukan')
                ->with('message_type','warning');
        }
    }

    public function verifikasi(Request $request){
        $field=Transaksi::find($request->id);
        $field->fid_status=$request->status;
        $field->save();
        $status=DB::table('status_transaksi')->find($field->fid_status);
        if($field->fid_status==1){
            GlobalHelper::add_verifikasi_transaksi('transaksi',$field->id,'Verifikasi Transaksi dibatalkan oleh',null);
        }
        else{
            GlobalHelper::add_verifikasi_transaksi('transaksi',$field->id,(!empty($status) ? $status->caption : ''),null);
        }
        return Redirect::back()
            ->with('message','Simpanan sukarela berhasil diverikasi')
            ->with('message_type','success');
    }

    //----------------------------------------------SETORAN BERKALA-------------------------------------------------//

    public function get_setoran_berkala($status,$search){
        $query=SetoranBerkala::select('setoran_berkala.*','anggota.no_anggota','anggota.nama_lengkap','anggota.avatar')
            ->join('anggota','anggota.no_anggota','=','setoran_berkala.fid_anggota');

        if(!empty($search)){
            $query=$query->where(function ($i) use ($search) {
                $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
            });
        }
        if($status != 'all'){
            $query=$query->where('setoran_berkala.fid_status',$status);
        }
        $result=$query->orderBy('setoran_berkala.tanggal','DESC')->paginate(10);

        foreach ($result as $key => $value) {
            $petugas=DB::table('anggota')->where('no_anggota',$value->created_by)->first();
            $result[$key]->nama_petugas=(!empty($petugas) ? $petugas->nama_lengkap : 'Undefined');
        }

        if(!empty($search)){
            $result->withPath('berkala?status='.$status.'&search='.$search);
        }
        return $result;
    }

    public function setoran_berkala(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,35);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $status=(!empty($request->status) ? $request->status : 'all');
            $data['setoran-berkala']=$this->get_setoran_berkala($status,$search);
            return view('simpanan.berkala.index')
                ->with('data',$data)
                ->with('status',$status)
                ->with('search',$search);
        }
    }

    public function form_setoran_berkala(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,35);
        if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N'){
            return view('404');
        }
        else{
            $setoran=SetoranBerkala::select('setoran_berkala.*','anggota.id as anggota_id','anggota.nama_lengkap','no_anggota','anggota.avatar')
                ->join('anggota','anggota.no_anggota','=','setoran_berkala.fid_anggota')
                ->where('setoran_berkala.id',$request->id)
                ->first();
            if(!empty($setoran)){
                $action='edit';
                $id=$request->id;
            }
            else{
                $action='add';
                $id=0;
            }
            $data['setoran-berkala']=$setoran;
            return view('simpanan.berkala.form')
                ->with('data',$data)
                ->with('action',$action)
                ->with('id',$id);
        }
    }

    public function proses_setoran_berkala(Request $request){
        $anggota=Anggota::where('no_anggota',$request->no_anggota)->first();
        if(!empty($anggota) && $request->no_anggota!=null){
            if($request->action=='add'){
                $field=new SetoranBerkala;
                $field->created_at=date('Y-m-d H:i:s');
                $field->created_by=Session::get('useractive')->no_anggota;
            }
            else{
                $field=SetoranBerkala::find($request->id);
                $field->updated_at=date('Y-m-d H:i:s');
            }

            if($request->action=='delete'){
                $field->delete();
                $msg='Pengajuan setoran simpanan berkala berhasil dihapus';
                $url='simpanan/sukarela/berkala';
            }
            elseif($request->action=='aktifasi'){
                $field->fid_status=$request->status;
                $field->save();
                if($field->fid_status==1){
                    GlobalHelper::add_verifikasi_transaksi('setoran berkala',$field->id,'Setoran berkala diaktifkan kembali oleh',null);
                    $msg='Pengajuan setoran simpanan berkala berhasil diaktifkan';
                }
                else{
                    GlobalHelper::add_verifikasi_transaksi('setoran berkala',$field->id,'Setoran berkala dinonaktifkan oleh',null);
                    $msg='Pengajuan setoran simpanan berkala berhasil dinonaktifkan';
                }
                $url='simpanan/sukarela/berkala/detail?id='.$field->id;
            }
            else{
                $field->fid_status=1;
                $field->fid_anggota=$request->no_anggota;
                $field->nominal=str_replace('.','',$request->nominal);
                $field->keterangan=$request->keterangan;
                $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
                if($request->tipe_jadwal=='range_bulan'){
                    $field->bulan_awal=$request->bulan_awal;
                    $field->bulan_akhir=$request->bulan_akhir;
                }
                else{
                    $field->bulan_awal=$request->mulai_bulan;
                    $field->bulan_akhir='Belum Ditentukan';
                }
                $field->save();
                $this->update_riwayat_gaji($request);
                if($request->action=='edit'){
                    GlobalHelper::add_verifikasi_transaksi('setoran berkala',$field->id,'Setoran berkala diedit oleh',null);
                    $msg='Pengajuan setoran simpanan berkala berhasil diedit';
                }
                else{
                    $msg='Pengajuan setoran simpanan berkala berhasil disimpan';
                }
                $url='simpanan/sukarela/berkala/detail?id='.$field->id;
            }
            return redirect($url)
                ->with('message',$msg)
                ->with('message_type','success');
        }
        else{
            return Redirect::back()
                ->with('message','Anggota Koperasi belum dipilih / tidak ditemukan')
                ->with('message_type','warning');
        }
    }

    public function detail_setoran_berkala(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,35);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $setoran=SetoranBerkala::select('setoran_berkala.*','anggota.no_anggota','anggota.nama_lengkap','anggota.avatar')
                ->join('anggota','anggota.no_anggota','=','setoran_berkala.fid_anggota')
                ->where('setoran_berkala.id',$request->id)
                ->first();
            if(!empty($setoran)){
                $anggota=Anggota::where('no_anggota',$setoran->created_by)->first();
                $setoran->nama_petugas=(!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
                $data['setoran-berkala']=$setoran;
                $data['keterangan']=DB::table('keterangan_status_transaksi')
                    ->where('jenis_transaksi','setoran berkala')
                    ->where('fid_status',$setoran->fid_status)
                    ->where('user_page','admin')
                    ->first();
                return view('simpanan.berkala.detail')
                    ->with('data',$data)
                    ->with('id',$request->id);
            }
            else{
                return Redirect::back();
            }
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

    //--------------------------------------------Cetak Buku Simpanan------------------------------------------------------------//

    public function get_buku_simpanan($anggota, $jenis, $page, $tanggal_awal, $tanggal_akhir, $nomor_awal){
        $jenis_simpanan = [intval($jenis)];
        if ($jenis == 2) array_push($jenis_simpanan, 8);
        if ($jenis == 3) {
            array_push($jenis_simpanan, 5);
            array_push($jenis_simpanan, 6);
        }

        $query = Transaksi::where('fid_status', 4)
            ->where('fid_anggota', $anggota)->with(['anggota', 'jenis_transaksi'])
            ->whereIn('fid_jenis_transaksi', $jenis_simpanan);

        if ($tanggal_awal != '') $query = $query->where('tanggal', '>=', unformat_date($tanggal_awal));
        if ($tanggal_akhir != '') $query = $query->where('tanggal', '<=', unformat_date($tanggal_akhir));

        $perpage = 25 - ($nomor_awal % 25);

        $data['datatotal'] = $query->count();
        $data['pagetotal'] = ceil($data['datatotal'] / $perpage);
        $data['perpage'] = $perpage;
        $data['pageposition'] = $page;

        $result = $query->skip(($page - 1) * $perpage)->limit($perpage)->get();

        $saldo_awal = 0;
        if ($page > 1) {
            $saldo_awal = Transaksi::where('fid_status', 4)
                ->where('fid_anggota', $anggota)
                ->where('tanggal', '<', $result[0]->tanggal)
                ->where('id', '<', $result[0]->id)
                ->whereIn('fid_jenis_transaksi', $jenis_simpanan)
                ->sum('nominal');
        }
        $data['saldo_awal'] = $saldo_awal;

        if ($page > 1) $nomor_awal = (($page - 1) + 24);
        foreach ($result as $key => $value) {
            if (in_array($value->fid_jenis_transaksi, [1,2,3,4])) $result[$key]->sandi='STR';
            else if (in_array($value->fid_jenis_transaksi, [6,7,8])) $result[$key]->sandi='PNR';
            else if (in_array($value->fid_jenis_transaksi, [5])) $result[$key]->sandi='BUN';

            $operator = $value->operator->nama_lengkap ?? '';
            $operator = explode(' ', $operator);
            if (count($operator) === 1) $operator = substr($operator, 0, 3);
            else {
                $operator = array_map(function ($item) {
                    return substr($item, 0,1);
                }, $operator);
                $operator = join('', $operator);
            }

            $result[$key]->operator = $operator;
            $result[$key]->kredit = ($value->jenis_transaksi->operasi == 'kredit' ? $value->nominal : 0 );
            $result[$key]->debit = ($value->jenis_transaksi->operasi == 'debit' ? $value->nominal : 0 );

            $result[$key]->nomor = $nomor_awal;
            $result[$key]->saldo = $saldo_awal += ($result[$key]->kredit + $result[$key]->debit);
            $nomor_awal++;
        }
        $data['data'] = $result;
        return $data;
    }

    public function buku_simpanan(Request $request){
        $nomor_awal = $request->nomor_awal ?? 1;
        $page = $request->page ?? 1;
        $data_cetak = [];

        $jenis_simpanan[2] = 'Simpanan Wajib';
        $jenis_simpanan[3] = 'Simpanan Sukarela';
        $jenis_selected = $request->input('jenis_simpanan');

        $data['otoritas'] = GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,41);
        if ($data['otoritas']['view'] == 'N') return view('404');
        else{
            $anggota=Anggota::where('no_anggota',$request->anggota)->first();
            $data=$this->get_buku_simpanan(
                $request->anggota,
                $jenis_selected,
                $page,
                $request->tanggal_awal,
                $request->tanggal_akhir,
                $nomor_awal
            );
            $saldo_awal = $data['saldo_awal'] ?? 0;
            $index = 0;
            for($i = 0; $i < 27; $i++) {
                if ($i < ($nomor_awal % 27)) $data_cetak[$i] = [];
                else {
                    $data_cetak[$i] = $data['data'][$index] ?? [];
                    $index++;
                }
            }
            $path = ($request->action=='cetak') ? 'simpanan.buku_simpanan.cetak' : 'simpanan.buku_simpanan.index';
            return view($path)
                ->with('saldo_awal',$saldo_awal)
                ->with('anggota',$anggota)
                ->with('request',$request)
                ->with('nomor_awal', $nomor_awal)
                ->with('page', $page)
                ->with('data_cetak', $data_cetak)
                ->with('jenis_simpanan', $jenis_simpanan)
                ->with('jenis_selected', $jenis_selected)
                ->with('data', $data);
        }
    }

    public function cetak_cover(Request $request)
    {
        $no_anggota = $request->input('anggota');
        $anggota = Anggota::where('no_anggota', $no_anggota)->first();
        return view('simpanan.buku_simpanan.cover', compact('anggota'));
    }
}
