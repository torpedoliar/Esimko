<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Produk;
use App\FotoProduk;
use App\Pembelian;
use App\ItemPembelian;
use App\ReturPembelian;
use App\ItemReturPembelian;
use View;
use DB;
use DateTime;
use Redirect;

class ReturnPembelianController extends Controller
{
    public function jumlah_retur($id,$metode){
        $items=ItemReturPembelian::where('fid_retur_pembelian',$id)->where('metode',$metode);
        $jumlah=$items->sum('jumlah');
        $total=$items->sum('total');
        $data=array('jumlah'=>$jumlah,'total'=>$total);
        return $data;
    }

    public function get_return($search){
        $query=ItemReturPembelian::select('item_retur_pembelian.*','retur_pembelian.no_retur','retur_pembelian.created_at','retur_pembelian.created_by','retur_pembelian.tanggal','produk.nama_produk','produk.kode','satuan_barang.satuan','supplier.nama_supplier','anggota.nama_lengkap')
            ->join('retur_pembelian','retur_pembelian.id','=','item_retur_pembelian.fid_retur_pembelian')
            ->join('produk','produk.id','=','item_retur_pembelian.fid_produk')
            ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
            ->join('supplier','supplier.id','=','retur_pembelian.fid_supplier')
            ->join('anggota','anggota.no_anggota','=','retur_pembelian.created_by');
        if(!empty($search)){
            $query=$query->where(function ($i) use ($search) {
                $i->where('retur_pembelian.no_retur', 'like', "%{$search}%")
                    ->orWhere('supplier.nama_supplier', 'like', "%{$search}%")
                    ->orWhere('produk.nama_produk', 'like', "%{$search}%")
                    ->orWhere('produk.kode', 'like', "%{$search}%");
            });
        }
        $result=$query->orderBy('retur_pembelian.tanggal')->paginate(10);
        foreach ($result as $key => $value) {
            $foto=FotoProduk::where('fid_produk',$value->fid_produk)->first();
            $result[$key]->foto=(!empty($foto) ? $foto->foto : null );
        }
        if(!empty($search)){
            $result->withPath('return?search='.$search);
        }
        return $result;
    }

    public function index(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,23);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $data['retur']=$this->get_return($search);
            return view('manajemen_stok.return.index')
                ->with('data',$data)
                ->with('search',$search);
        }
    }

    public function form(Request $request){
        $id = '';
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,23);
        if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N'){
            return view('404');
        }
        else{
            $retur=ReturPembelian::find($request->id);
            $action=(!empty($retur) ? 'edit' : 'add');
            $id=(!empty($retur) ? $request->id : 0 );
            $data['retur']=$retur;
            $items=ItemReturPembelian::select('item_retur_pembelian.*','produk.nama_produk','produk.kode','satuan_barang.satuan')
                ->join('produk','produk.id','=','item_retur_pembelian.fid_produk')
                ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
                ->where('fid_retur_pembelian',$id)
                ->get();
            foreach ($items as $key => $value) {
                $foto=FotoProduk::where('fid_produk',$value->fid_produk)->first();
                $items[$key]->foto=(!empty($foto) ? $foto->foto : null );
            }
            $data['items']=$items;
            $data['supplier']=DB::table('supplier')->get();

            $exclude_product = $items->pluck('fid_produk')->toArray();
            $no_pembelian = $request->input('no_pembelian') ?? '';
            $pembelian = Pembelian::where('no_pembelian', $no_pembelian)->first();
            $list_produk_id = ItemPembelian::select('fid_produk')->whereHas('pembelian', function ($pembelian) use ($no_pembelian) {
                $pembelian->where('no_pembelian', $no_pembelian);
            })->whereNotIn('fid_produk', $exclude_product)->get()->pluck('fid_produk')->toArray();
            $list_produk = Produk::whereIn('id', $list_produk_id)->get();
            $fid_pembelian = Pembelian::where('no_pembelian', $no_pembelian)->first()->id ?? '';
            $fid_supplier = $request->input('fid_supplier') ?? '';

            return view('manajemen_stok.return.form')
                ->with('data',$data)
                ->with('action',$action)
                ->with('pembelian',$pembelian)
                ->with('no_pembelian',$no_pembelian)
                ->with('list_produk',$list_produk)
                ->with('fid_pembelian',$fid_pembelian)
                ->with('fid_supplier',$fid_supplier)
                ->with('id',$id);
        }
    }

    public function proses(Request $request){

        $message_type = 'success';
        if($request->action=='add'){
            $field=new ReturPembelian;
            $field->fid_pembelian = $request->input('fid_pembelian');
            $field->created_at=date('Y-m-d H:i:s');
            $field->created_by=Session::get('useractive')->no_anggota;
            $field->no_retur=GlobalHelper::get_nomor_retur_pembelian($field->created_at);
            $msg='Retur pembelian barang berhasil ditambahkan';
        }
        else{
            $field=ReturPembelian::find($request->id);
            $field->fid_pembelian = $request->input('fid_pembelian');
            $field->updated_at=date('Y-m-d H:i:s');
            $msg='Retur pembelian barang berhasil disimpan';
        }
        $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
        $field->fid_supplier=$request->supplier;
        $field->keterangan=$request->keterangan;
        if($request->action=='delete'){
            $field->delete();
            ItemReturPembelian::where('fid_retur_pembelian',$request->id)->delete();
            $msg='Retur pembelian barang berhasil dihapus';
        }
        else{
            if($request->action=='delete_items'){
                $this->proses_items($field->id,$request);
            }
            else{
                $field->save();
                if($request->action != 'edit_transaksi'){
                    $msg = $this->proses_items($field->id,$request);
                    if ($msg === 'Retur tidak dapat melebihi sisa stok') $message_type = 'error';
                }
            }
        }
        return redirect('manajemen_stok/return/form?id='.$field->id.'&no_pembelian=' . $request->no_pembelian . '&fid_supplier=' . $field->fid_supplier)
            ->with('message',$msg)
            ->with('message_type',$message_type);
    }

    public function proses_items($id,$request){
        $items=ItemReturPembelian::find($request->items_id);
        if(!empty($items)){
            $field=ItemReturPembelian::find($items->id);
            $msg='Items Pembelian berhasil disimpan';
        }
        else{
            $field=new ItemReturPembelian;
            $msg='Items Pembelian berhasil ditambahkan';
        }
        if($request->action=='delete_items'){
            $field->delete();
            $msg='Items Pembelian berhasil dihapus';
        }
        else{

            $produk=Produk::find($request->produk_id);
            if (!empty($produk)) {
                $stok=GlobalHelper::stok_barang($produk->id);
                if (intval($request->jumlah) <= $stok['sisa']) {
                    if (!empty($produk)) {
                        $field->fid_retur_pembelian = $id;
                        $field->fid_produk = $produk->id;
                        $field->metode = $request->metode;
                        $field->jumlah = $request->jumlah;
                        $field->harga = str_replace('.', '', $request->harga);
                        $field->total = $field->harga * $field->jumlah;
                        $field->margin = $produk->margin;
                        $field->margin_nominal = $produk->margin_nominal;
                        $field->harga_jual = $produk->harga_jual;
                        $field->save();
                    }
                } else {
                    $msg='Retur tidak dapat melebihi sisa stok';
                }
            }
            if ($request->has('produk_id_lainnya')) {
                $list_produk_id = $request->input('produk_id_lainnya');
                foreach ($list_produk_id as $produk_id) {
                    $produk=Produk::find($produk_id);
                    $stok=GlobalHelper::stok_barang($produk_id);
                    if (intval($request->jumlah) <= $stok['sisa']) {
                        $harga = $request->input('harga_' . $produk_id) ?? '';
                        $jumlah = $request->input('jumlah_' . $produk_id);
                        if (!empty($produk) && $jumlah != '' && $jumlah != '0' && $harga != '') {
                            $harga = str_replace('.', '', $harga);
                            $field->fid_retur_pembelian = $id;
                            $field->fid_produk = $produk_id;
                            $field->metode = $request->metode;
                            $field->jumlah = $jumlah;
                            $field->harga = $harga;
                            $field->total = $harga * $jumlah;
                            $field->margin = $produk->margin;
                            $field->margin_nominal = $produk->margin_nominal;
                            $field->harga_jual = $produk->harga_jual;
                            $field->save();
                        }
                    } else {
                        $msg='Retur tidak dapat melebihi sisa stok';
                    }
                }
            }
        }
        return $msg;
    }

}
