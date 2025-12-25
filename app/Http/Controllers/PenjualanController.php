<?php

namespace App\Http\Controllers;

use App\ItemReturPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Produk;
use App\FotoProduk;
use App\Penjualan;
use App\ItemPenjualan;
use App\AngsuranBelanja;
use App\GajiPokok;
use View;
use DB;
use DateTime;

class PenjualanController extends Controller
{
    public function get_penjualan($status,$search){
        $query=Penjualan::select('penjualan.*','status_belanja.status','status_belanja.color','rekening_pembayaran.keterangan as metode_pembayaran','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
            ->leftJoin('anggota','anggota.no_anggota','=','penjualan.fid_anggota')
            ->join('rekening_pembayaran','rekening_pembayaran.id','=','penjualan.fid_metode_pembayaran')
            ->join('status_belanja','status_belanja.id','=','penjualan.fid_status')
            ->where('jenis_belanja','=','toko');
        if(!empty($search)){
            $query=$query->where(function ($i) use ($search) {
                $i->where('penjualan.no_transaksi', 'like', "%{$search}%")
                    ->orWhere('anggota.nama_lengkap', 'like', "%{$search}%");
            });
        }
        if($status =='all'){
            $query=$query->where('penjualan.fid_status','!=',3);
        }
        else{
            $query=$query->where('penjualan.fid_status',$status);
        }

        $result=$query->orderBy('penjualan.created_at')->paginate(10);
        foreach ($result as $key => $value) {
            $petugas=Anggota::where('no_anggota',$value->kasir)->first();
            $result[$key]->nama_petugas=(!empty($petugas) ? $petugas->nama_lengkap : null );
            $result[$key]->avatar_petugas=(!empty($petugas) ? $petugas->avatar : null );
            $result[$key]->jumlah=ItemPenjualan::where('fid_penjualan',$value->id)->sum('jumlah');
            $result[$key]->produk=ItemPenjualan::select('item_penjualan.*','foto_produk.foto','produk.nama_produk','produk.kode','satuan_barang.satuan')
                ->join('produk','produk.id','=','item_penjualan.fid_produk')
                ->leftJoin('foto_produk','foto_produk.fid_produk','=','produk.id')
                ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
                ->where('fid_penjualan',$value->id)
                ->first();
        }
        if(!empty($search)){
            $result->withPath('penjualan?search='.$search);
        }
        return $result;
    }

    public function index(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,26);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $status=(!empty($request->status) ? $request->status : 'all');
            $data['penjualan']=$this->get_penjualan($status,$search);
            $data['status']=DB::table('status_belanja')->get();
            return view('pos.penjualan.index')
                ->with('data',$data)
                ->with('status',$status)
                ->with('search',$search);
        }
    }

    public function form(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,26);
        if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N'){
            return view('404');
        }
        else{
            $penjualan=Penjualan::select('penjualan.*','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar','anggota.id as anggota_id')
                ->leftJoin('anggota','anggota.no_anggota','=','penjualan.fid_anggota')
                ->where('penjualan.id',$request->id)
                ->first();
            if(!empty($penjualan)){
                $action='edit';
                $id=$request->id;
                $penjualan->subtotal=ItemPenjualan::where('fid_penjualan',$id)->sum('total');
                $penjualan->total=$penjualan->subtotal - ($penjualan->subtotal*$penjualan->diskon/100);
            }
            else{
                $action='add';
                $id=0;
            }
            $items=ItemPenjualan::select('item_penjualan.*','produk.nama_produk','produk.kode','satuan_barang.satuan')
                ->join('produk','produk.id','=','item_penjualan.fid_produk')
                ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
                ->where('item_penjualan.fid_penjualan',$id)
                ->get();
            foreach ($items as $key => $value) {
                $jumlah=($penjualan->fid_status == 3 ? $value->jumlah : 0 );
                $foto=FotoProduk::where('fid_produk',$value->fid_produk)->first();
                $items[$key]->foto=(!empty($foto) ? $foto->foto : null );
                $items[$key]->sisa=GlobalHelper::stok_barang($value->fid_produk,$penjualan->id)['sisa'];
            }
            $data['items']=$items;
            $data['penjualan']=$penjualan;
            $data['metode-pembayaran']=DB::table('rekening_pembayaran')->where('jenis_transaksi','like','%belanja%')->get();
            return view('pos.penjualan.form')
                ->with('data',$data)
                ->with('action',$action)
                ->with('id',$id);
        }
    }

    public function check_limit(Request $request)
    {
        return GlobalHelper::limitKaryawan($request->input('fid_anggota'));
    }

    public function detail(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,26);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $belanja=Penjualan::select('penjualan.*','status_belanja.icon','rekening_pembayaran.keterangan as metode_pembayaran','rekening_pembayaran.fid_metode_pembayaran','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
                ->leftJoin('anggota','anggota.no_anggota','=','penjualan.fid_anggota')
                ->join('rekening_pembayaran','rekening_pembayaran.id','=','penjualan.fid_metode_pembayaran')
                ->join('status_belanja','status_belanja.id','=','penjualan.fid_status')
                ->where('penjualan.id',$request->id)
                ->first();
            if(!empty($belanja)){
                $anggota=Anggota::where('no_anggota',$belanja->created_by)->first();
                $belanja->nama_petugas=(!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
                $belanja->nominal_margin=($belanja->margin*$belanja->total_pembayaran/100);
                $data['belanja']=$belanja;
                $items=ItemPenjualan::select('item_penjualan.*','produk.nama_produk','produk.kode','satuan_barang.satuan')
                    ->join('produk','produk.id','=','item_penjualan.fid_produk')
                    ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
                    ->where('item_penjualan.fid_penjualan',$request->id)
                    ->get();
                foreach ($items as $key => $value) {
                    $jumlah=($belanja->fid_status == 3 ? $value->jumlah : 0 );
                    $foto=FotoProduk::where('fid_produk',$value->fid_produk)->first();
                    $items[$key]->foto=(!empty($foto) ? $foto->foto : null );
                    $items[$key]->sisa=GlobalHelper::stok_barang($value->fid_produk)['sisa']+$jumlah;
                }
                $data['items']=$items;
                $data['keterangan']=DB::table('keterangan_status_transaksi')
                    ->where('jenis_transaksi',($belanja->jenis_belanja=='toko' ? 'belanja' : 'kredit belanja'))
                    ->where('fid_status',$belanja->fid_status)
                    ->where('user_page','admin')
                    ->first();
                return view('pos.penjualan.detail')
                    ->with('data',$data)
                    ->with('id',$request->id);
            }
            else{
                return Redirect::back();
            }
        }
    }

    public function cetak_struk(Request $request){
//        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,34);
//        if($data['otoritas']['view']=='N'){
//            return view('404');
//        }
//        else{
            $belanja=Penjualan::select('penjualan.*','rekening_pembayaran.keterangan as metode_pembayaran','rekening_pembayaran.fid_metode_pembayaran','anggota.nama_lengkap','anggota.no_anggota')
                ->leftJoin('anggota','anggota.no_anggota','=','penjualan.fid_anggota')
                ->join('rekening_pembayaran','rekening_pembayaran.id','=','penjualan.fid_metode_pembayaran')
                ->where('penjualan.id',$request->id)
                ->first();
            if(!empty($belanja)){
                $anggota=Anggota::where('no_anggota',$belanja->kasir)->first();
                $belanja->nama_petugas=(!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
                $belanja->subtotal=ItemPenjualan::where('fid_penjualan',$request->id)->sum('total');
                $belanja->diskon=round($belanja->diskon*$belanja->subtotal/100,0);
                $items=ItemPenjualan::select('item_penjualan.*','produk.nama_produk','satuan_barang.satuan')
                    ->join('produk','produk.id','=','item_penjualan.fid_produk')
                    ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
                    ->where('item_penjualan.fid_penjualan',$request->id)
                    ->get();
                foreach ($items as $item) {
                    $item->jumlah_retur = ItemReturPenjualan::whereHas('retur_penjualan', function ($retur) use ($request) {
                        $retur->where('fid_penjualan', $request->id);
                    })->where('fid_produk', $item->fid_produk)->sum('jumlah');
                }
                $total_tanpa_diskon=$total_diskon=0;
                foreach ($items as $key => $value) {
                    $total_harga=$value->harga*$value->jumlah;
                    $items[$key]->total_non_diskon=$total_harga;

                    $nominal_diskon=$total_harga-$value->total;
                    $items[$key]->nominal_diskon=$nominal_diskon;

                    $total_tanpa_diskon=$total_tanpa_diskon+$total_harga;
                    $total_diskon=$total_diskon+$nominal_diskon;
                }
                $data['items']=$items;
                $belanja->total_tanpa_diskon=$total_tanpa_diskon;
                $belanja->total_diskon=$total_diskon;
                $data['belanja']=$belanja;
                // return $data['belanja'];
                return view('pos.penjualan.cetak_struk')
                    ->with('data',$data)
                    ->with('id',$request->id);
            }
            else{
                return Redirect::back();
            }
//        }
    }

    public function add_penjualan($id, $request){
        $cek_penjualan=Penjualan::find($id);
        if(empty($cek_penjualan)){
            $field=new Penjualan;
            $field->tanggal=date('Y-m-d');
            $field->created_at=date('Y-m-d H:i:s');
            $field->created_by=Session::get('useractive')->no_anggota;
            $field->kasir=Session::get('useractive')->no_anggota;
            $field->no_transaksi=GlobalHelper::get_nomor_penjualan($field->created_at);
            $field->fid_status=1;
            $field->fid_anggota=$request->fid_anggota;
            $field->jenis_belanja='toko';
            $field->fid_metode_pembayaran=1;
            $field->save();
            return $field->id;
        }
        else{
            return $id;
        }
    }

    public function proses(Request $request){
        $id = null;

        $request->kode = trim($request->kode);
        if($request->action == 'add_barang' && $request->kode != ''){
            $produk=Produk::where('kode',$request->kode)->first();
            if (empty($produk)) {
                return Redirect::to('pos/penjualan/form')
                    ->with('message','Kode barang tidak ditemukan!')
                    ->with('message_type','error');
            }
            $id=$this->proses_items($request);
            if($id == null){
                return Redirect::back();
            }
        }
        $penjualan=Penjualan::find($request->id);
        $actionZ = $request->action;
        if ($actionZ === 'hold') {
            $penjualan->fid_status = 5;
            $penjualan->save();
        }
        if(!empty($penjualan) && ($actionZ !== 'hold')){
            $this->proses_all_items($penjualan->id,$request);

            $field=$penjualan;
            $field->updated_at=date('Y-m-d H:i:s');
            $field->kasir=Session::get('useractive')->no_anggota;
            $field->no_transaksi=$request->no_transaksi;
            $field->fid_anggota=(!empty($request->fid_anggota) ? $request->fid_anggota : null);
            if($request->metode_pembayaran == 3 && $request->fid_anggota == '') {
                return Redirect::back()
                    ->with('message','Transaksi kredit / angsuran hanya untuk anggota !')
                    ->with('message_type','error');

            } else {
                $field->fid_metode_pembayaran = $request->metode_pembayaran;
            }
            $field->diskon=$request->diskon;
            $field->total_pembayaran=str_replace('.','',$request->total_pembayaran);
            $pembayaran=DB::table('rekening_pembayaran')->find($field->fid_metode_pembayaran);
            if(!empty($pembayaran)){
                if($pembayaran->fid_metode_pembayaran!=3){
                    $field->tipe_voucher=$request->voucher_type;
                    if($field->tipe_voucher=='persen'){
                        $field->voucher_persen=$request->voucher_nominal;
                        $field->voucher_nominal=round($field->voucher_persen*$field->total_pembayaran/100,0);
                    }
                    else{
                        $field->voucher_persen=0;
                        $field->voucher_nominal=str_replace('.','',$request->voucher_nominal);
                    }
                    $field->kode_voucher=$request->kode_voucher;
                }
                else{
                    $field->tipe_voucher=null;
                    $field->voucher_persen=null;
                    $field->voucher_nominal=null;
                    $field->kode_voucher=null;
                }
                $field->tunai=($pembayaran->fid_metode_pembayaran==1 ? str_replace('.','',$request->tunai) : null);
                $field->kembali=($pembayaran->fid_metode_pembayaran==1 ? str_replace('.','',$request->kembali) : null);
                $field->tenor=($pembayaran->fid_metode_pembayaran==3 ? 1 : null);
                $field->angsuran=($pembayaran->fid_metode_pembayaran==3 ? str_replace('.','',$request->total_pembayaran) : null);
                $field->no_debit_card=($pembayaran->fid_metode_pembayaran==5 ? $request->no_debit_card : null );
                $field->account_number=($pembayaran->fid_metode_pembayaran==7 ? $request->account_number : null);
            }
            else{
                $field->tunai=null;
                $field->kembali=null;
                $field->tenor=null;
                $field->angsuran=null;
                $field->no_debit_card=null;
                $field->account_number=null;
            }
            $field->fid_anggota=$request->fid_anggota;
            if ($actionZ === 'hold') {
                $field->fid_status=5;
            } else {
                $field->fid_status=($request->action=='bayar' ? 2 : 1 );
            }
//            dd($field->total_pembayaran);
            $field->save();


//            if($pembayaran->fid_metode_pembayaran==3){
//                $this->update_riwayat_gaji($request);
//            }
            AngsuranBelanja::where('fid_penjualan',$field->id)->delete();
            if($field->fid_status==1){
                return redirect('pos/penjualan/form?id='.$field->id);
            }
            else{
                GlobalHelper::add_verifikasi_transaksi('penjualan',$field->id,'Proses pembayaran dilakukan oleh',null);
                if($pembayaran->fid_metode_pembayaran==3){
                    $this->proses_angsuran($field->id,$request);
                }
                return redirect('pos/penjualan/detail?id='.$field->id);
            }
        }
        else{
            if ($actionZ === 'hold') return redirect('pos/penjualan');
            if($id == null){
                return Redirect::back();
            }
            else{
                return redirect('pos/penjualan/form?id='.$id);
            }
        }
    }

    public function delete_items(Request $request){
        ItemPenjualan::find($request->id)->delete();
        return Redirect::back();
    }

    public function proses_items($request){
        $produk=Produk::where('kode',$request->kode)->first();
        if (empty($produk)) {
            return Redirect::back()
                ->with('message','Kode barang tidak ditemukan!')
                ->with('message_type','error');
        }
        $limit = GlobalHelper::limitKaryawan($request->input('fid_anggota'));
        if (($produk->harga_jual) > $limit)
            return Redirect::back()
                ->with('message','Melebihi Limit Pinjaman Anggota !')
                ->with('message_type','error');

        if(!empty($produk)){
            $barang=GlobalHelper::stok_barang($produk->id);
            $items=ItemPenjualan::where('fid_penjualan',$request->id)->where('fid_produk',$produk->id)->first();
            if(!empty($items)){
                $field=ItemPenjualan::find($items->id);
                $field->jumlah=$field->jumlah+1;
            }
            else{
                $field=new ItemPenjualan;
                $field->fid_penjualan=$this->add_penjualan($request->id, $request);
                $field->fid_produk=$produk->id;
                $field->jumlah=1;
            }
            if($request->action=='delete'){
                $field->delete();
            }
            else{
                $field->diskon=0;
                $field->harga_beli=$produk->harga_beli;
                $field->margin=$produk->margin;
                $field->margin_nominal=$produk->margin_nominal;
                $field->harga=$produk->harga_jual;
                $field->total=$field->harga * $field->jumlah;
                if($field->jumlah <= $barang['sisa']){
                    $field->save();
                }
            }
            return $field->fid_penjualan;
        }
        else{
            return null ;
        }
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
        $field->gaji_pokok=str_replace('.','',$request->gaji_pokok);
        $field->save();
    }

    public function proses_pembatalan(Request $request){
        $field=Penjualan::find($request->id);
        $field->fid_status=$request->status;
        $field->save();
        if($field->fid_status == 1){
            GlobalHelper::add_verifikasi_transaksi('penjualan',$field->id,'Pembatalan Transaksi dibuka kembali oleh',null);
            return redirect('pos/penjualan/form?id='.$field->id)
                ->with('message','Transaksi berhasil dibuka kembali')
                ->with('message_type','success');
        }
        else{
            GlobalHelper::add_verifikasi_transaksi('penjualan',$field->id,'Transaksi dibatalkan oleh',null);
            return redirect('pos/penjualan')
                ->with('message','Transaksi berhasil dibatalkan')
                ->with('message_type','success');
        }
    }

    public function proses_all_items($id,$request){
        $items=ItemPenjualan::select('item_penjualan.*','produk.kode')
            ->where('item_penjualan.fid_penjualan',$id)
            ->join('produk','produk.id','=','item_penjualan.fid_produk')
            ->get();
        foreach ($items as $key => $value){
            $field=ItemPenjualan::find($value->id);

            if(!empty($request->jumlah[$value->id])){
                $diskon_nominal=round($field->diskon*$field->harga/100,0);
                $harga=$field->harga-$diskon_nominal;

                $limit = GlobalHelper::limitKaryawan($request->input('fid_anggota'));
                if (($harga*$field->jumlah) > $limit)
                    return Redirect::back()
                        ->with('message','Melebihi Limit Pinjaman Anggota !')
                        ->with('message_type','error');

                $field->jumlah=($request->kode == $value->kode ? $field->jumlah : $request->jumlah[$value->id]) ;
                $field->diskon=$request->diskon_item[$value->id];


                $field->total=str_replace('.','',$harga*$field->jumlah);
                $field->save();
            }
        }
    }

    public function proses_angsuran($id,$request){
        $field=new AngsuranBelanja;
        $field->fid_penjualan=$id;
        $field->angsuran_ke=1;
        $field->total_angsuran=str_replace('.','',$request->total_pembayaran);
        $field->fid_status=3;
        $field->save();
    }
}
