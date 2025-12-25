<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\FotoProduk;
use App\Penjualan;
use App\ItemPenjualan;
use App\AngsuranBelanja;
use App\GajiPokok;
use View;
use DB;
use DateTime;
use Redirect;

class KonsinyasiController extends Controller
{

    public function get_belanja($jenis,$status,$search){
        $query=Penjualan::select('penjualan.*','status_transaksi.status','status_transaksi.color','metode_pembayaran.metode_pembayaran','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
            ->join('anggota','anggota.no_anggota','=','penjualan.fid_anggota')
            ->join('metode_pembayaran','metode_pembayaran.id','=','penjualan.fid_metode_pembayaran')
            ->join('status_transaksi','status_transaksi.id','=','penjualan.fid_status')
            ->where('jenis_belanja','=',$jenis);
        if(!empty($search)){
            $query=$query->where(function ($i) use ($search) {
                $i->where('penjualan.no_transaksi', 'like', "%{$search}%")
                    ->orWhere('anggota.nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
            });
        }
        if($status !='all'){
            $query=$query->where('penjualan.fid_status',$status);
        }
        $result=$query->orderBy('penjualan.created_at')->paginate(10);
        foreach ($result as $key => $value) {
            $items=ItemPenjualan::where('fid_penjualan',$value->id);
            $result[$key]->jumlah=$items->count();
            $result[$key]->total=$value->total_pembayaran;

            $result[$key]->sisa_angsuran=AngsuranBelanja::where('fid_penjualan',$value->id)->where('fid_status','!=',6)->sum('total_angsuran');
            $result[$key]->sisa_tenor=AngsuranBelanja::where('fid_penjualan',$value->id)->where('fid_status','!=',6)->count();
        }
        if(!empty($search)){
            $result->withPath($jenis.'?search='.$search);
        }
        return $result;
    }

    public function index(Request $request,$jenis){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,($jenis=='konsinyasi' ? 28 : 29 ));
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $status=(!empty($request->status) ? $request->status : 'all');
            $data['penjualan']=$this->get_belanja($jenis,$status,$search);
            $data['status']=DB::table('status_transaksi')->get();
            return view('pos.belanja.index')
                ->with('data',$data)
                ->with('jenis',$jenis)
                ->with('status',$status)
                ->with('search',$search);
        }
    }

    public function form(Request $request,$jenis){

        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,($jenis=='konsinyasi' ? 28 : 29 ));
        if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N'){
            return view('404');
        }
        else{
            $data['belanja']=Penjualan::select('penjualan.*','anggota.id as anggota_id','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
                ->leftJoin('anggota','anggota.no_anggota','=','penjualan.fid_anggota')
                ->where('penjualan.id',$request->id)
                ->first();
            $id=(!empty($data['belanja']) ? $request->id : 0);
            $action=(!empty($data['belanja']) ? 'edit' : 'add' );
            $no_anggota=(!empty($data['belanja']) ? $data['belanja']->no_anggota : null );
            $data['gaji-pokok']=GlobalHelper::gaji_pokok($no_anggota);
            $data['items']=ItemPenjualan::where('fid_penjualan',$id)->get();
            return view('pos.belanja.form')
                ->with('data',$data)
                ->with('jenis',$jenis)
                ->with('id',$id)
                ->with('action',$action);
        }
    }

    public function detail(Request $request,$jenis){
        $tab = $request->tab ?? '1';
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,($jenis=='konsinyasi' ? 28 : 29 ));
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $belanja=Penjualan::select('penjualan.*','status_transaksi.icon','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
                ->join('anggota','anggota.no_anggota','=','penjualan.fid_anggota')
                ->join('status_transaksi','status_transaksi.id','=','penjualan.fid_status')
                ->where('penjualan.id',$request->id)
                ->first();
            if(!empty($belanja)){
                $anggota=Anggota::where('no_anggota',$belanja->created_by)->first();
                $belanja->nama_petugas=(!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
                $data['belanja']=$belanja;
                $data['keterangan']=DB::table('keterangan_status_transaksi')
                    ->where('jenis_transaksi','kredit belanja')
                    ->where('fid_status',$belanja->fid_status)
                    ->where('user_page','admin')
                    ->first();
                $data['keterangan']->label=str_replace('Konsinyasi',ucfirst($jenis),$data['keterangan']->label);
                $data['items']=ItemPenjualan::where('fid_penjualan',$belanja->id)->get();
                $angsuran=AngsuranBelanja::select('angsuran_belanja.*','status_angsuran.status_angsuran','status_angsuran.color','payroll_angsuran_belanja.bulan')
                    ->join('status_angsuran','status_angsuran.id','=','angsuran_belanja.fid_status')
                    ->leftJoin('payroll_angsuran_belanja','payroll_angsuran_belanja.id','=','angsuran_belanja.fid_payroll')
                    ->where('fid_penjualan',$request->id)
                    ->get();

                $sisa_angsuran=$belanja->total_pembayaran;
                foreach ($angsuran as $key => $value) {
                    $sisa_angsuran=$sisa_angsuran-$value->total_angsuran;
                    $angsuran[$key]->sisa_angsuran=$sisa_angsuran;
                }
                $data['angsuran']=$angsuran;
                return view('pos.belanja.detail')
                    ->with('data',$data)
                    ->with('jenis',$jenis)
                    ->with('tab',$tab)
                    ->with('id',$request->id);
            }
            else{
                return Redirect::back();
            }
        }
    }

    public function proses_penjualan($request,$jenis){
        $penjualan=Penjualan::find($request->penjualan_id);
        if(!empty($penjualan)){
            $field=$penjualan;
            $field->updated_at=date('Y-m-d H:i:s');
        }
        else{
            $field=new Penjualan;
            $field->created_at=date('Y-m-d H:i:s');
            $field->created_by=Session::get('useractive')->no_anggota;
            $field->fid_status=4;
            $field->jenis_belanja=$jenis;
            $field->fid_metode_pembayaran=3;
            $field->no_transaksi=($jenis=='online' ? $request->no_transaksi : GlobalHelper::get_nomor_penjualan_konsinyasi($field->created_at) );
            $field->tenor=5;
            $field->tanggal=date('Y-m-d');
        }
        $field->save();
        return $field->id;
    }

    public function update_penjualan($id, $fid_anggota = null){
        $field=Penjualan::find($id);
        $field->fid_anggota = $fid_anggota;
        $field->total_pembayaran=ItemPenjualan::where('fid_penjualan',$id)->sum('total');
        $field->angsuran=round($field->total_pembayaran/$field->tenor,0);
        $field->save();
    }

    public function proses_items(Request $request,$jenis){
        $items=ItemPenjualan::find($request->id);
        if(!empty($items)){
            $field=$items;
            $msg='Data Barang berhasil diedit';
        }
        else{
            $field=new ItemPenjualan;
            $msg='Data Barang berhasil ditambahkan';
        }
        $field->fid_penjualan=$this->proses_penjualan($request,$jenis);
        $field->nama_supplier=$request->nama_supplier;
        $field->nama_barang=$request->nama_barang;
        $field->jumlah=$request->jumlah;
        $field->satuan=$request->satuan;
        $field->harga_beli=str_replace('.','',$request->harga_beli);
        $field->margin=$request->margin;
        $field->margin_nominal=str_replace('.','',$request->margin_nominal);
        $field->harga=str_replace('.','',$request->harga_jual);
        $field->total=str_replace('.','',$request->total_harga);
        if($request->action=='delete'){
            $field->delete();
            $msg='Data Barang berhasil dihapus';
        }
        else{
            $field->save();
        }
        $this->update_penjualan($field->fid_penjualan, $request->fid_anggota);
        return redirect('pos/belanja/'.$jenis.'/form?id='.$field->fid_penjualan);
    }

    public function proses(Request $request,$jenis){
        $field=Penjualan::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
        if($request->action=='delete'){
            $field->delete();
            $msg='Belanja Konsinyasi berhasil dihapus';
        }
        else{
            $angsuran = $request->input('angsuran') ?? 0;
            $angsuran = intval(unformat_number($angsuran));
            $limit = GlobalHelper::limitKaryawan($request->fid_anggota);
            if ($angsuran > $limit) {
                $msg = 'Belanja Konsinyasi gagal disimpan karena melebihi limit!';
                return redirect()->back()->with('error', $msg);
            }

            $field->no_transaksi=($jenis=='online' ? $request->no_transaksi : $field->no_transaksi );
            $field->fid_anggota=$request->fid_anggota;
            $field->marketplace=($jenis=='online' ? $request->marketplace : null );
            $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
            $field->tenor=$request->tenor;
            $field->angsuran=str_replace('.','',$request->angsuran);
            $field->save();
            $this->proses_angsuran($field->id,$request);
            $this->update_riwayat_gaji($request);
            $msg='Belanja Konsinyasi berhasil disimpan';
        }
        return redirect('pos/belanja/'.$jenis.'/detail?id='.$field->id);
    }

    public function update_riwayat_gaji($request){
        $riwayat_gaji=GajiPokok::where('fid_anggota',$request->fid_anggota)
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
            $field->fid_anggota=$request->fid_anggota;
        }
        if($request->hasFile('attachment')){
            if(!empty($field->attachment)){
                unlink(storage_path('app/'.$field->attachment));
            }
            $uploadedFile = $request->file('attachment');
            $path = $uploadedFile->store('slip_gaji');
            $field->attachment=$path;
        }
//      $field->gaji_pokok=str_replace('.','',$request->gaji_pokok);
        $field->save();
    }

    public function proses_angsuran($id,$request){
        AngsuranBelanja::where('fid_penjualan',$id)->delete();
        for($n=1;$n<=$request->tenor;$n++){
            $field=new AngsuranBelanja;
            $field->fid_penjualan=$id;
            $field->angsuran_ke=$n;
            $field->total_angsuran=str_replace('.','',$request->angsuran);
            $field->fid_status=3;
            $field->save();
        }
    }

    public function verifikasi(Request $request,$jenis){
        $field=Penjualan::find($request->id);
        $field->fid_status=$request->status;
        $field->save();
        $status=DB::table('status_transaksi')->find($field->fid_status);
        if($field->fid_status==1){
            GlobalHelper::add_verifikasi_transaksi('penjualan',$field->id,'Verifikasi Transaksi dibatalkan oleh',null);
        }
        else if($field->fid_status == 4){
            $this->update_status_angsuran($field->id);
            GlobalHelper::add_verifikasi_transaksi('penjualan',$field->id,(!empty($status) ? $status->caption : ''),null);
        }
        else{
            GlobalHelper::add_verifikasi_transaksi('penjualan',$field->id,(!empty($status) ? $status->caption : ''),null);
        }
        return Redirect::back()
            ->with('message','Pengajuan kredit belanja '.ucfirst($jenis).' berhasil diverikasi')
            ->with('message_type','success');
    }

    public function update_status_angsuran($id){
        $angsuran=AngsuranBelanja::where('fid_penjualan',$id)->get();
        foreach ($angsuran as $key => $value) {
            $field=AngsuranBelanja::find($value->id);
            $field->fid_status=(in_array($field->fid_status,array(5,6)) ? $field->fid_status : 3 );
            $field->save();
        }
    }

    public function bayar($jenis, $id, $angsuran_id)
    {
        AngsuranBelanja::find($angsuran_id)->update(['fid_status' => 6]);
        return redirect('pos/belanja/' . $jenis . '/detail?id=' . $id . '&tab=3');
    }
}
