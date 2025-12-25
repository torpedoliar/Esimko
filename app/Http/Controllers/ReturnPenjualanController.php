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
use App\ReturPenjualan;
use App\ItemReturPenjualan;
use View;
use DB;
use DateTime;
use Redirect;

class ReturnPenjualanController extends Controller
{
    public function get_return($search){
        $query=ItemReturPenjualan::select('item_retur_penjualan.*','retur_penjualan.no_retur','retur_penjualan.created_at','retur_penjualan.created_by','retur_penjualan.tanggal','produk.nama_produk','produk.kode','satuan_barang.satuan','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
            ->leftJoin('retur_penjualan','retur_penjualan.id','=','item_retur_penjualan.fid_retur_penjualan')
            ->leftJoin('penjualan','penjualan.id','=','retur_penjualan.fid_penjualan')
            ->join('produk','produk.id','=','item_retur_penjualan.fid_produk')
            ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
            ->leftJoin('anggota','anggota.no_anggota','=','retur_penjualan.fid_anggota');
        if(!empty($search)){
            if ($search === '0000') {
                $query = $query->whereNull('penjualan.fid_anggota');
            } else {
                $query = $query->where(function ($i) use ($search) {
                    $i->where('retur_penjualan.no_retur', 'like', "%{$search}%");
                });
            }
        }
        $result=$query->orderBy('retur_penjualan.tanggal')->paginate(10);
        foreach ($result as $key => $value) {
            $foto=FotoProduk::where('fid_produk',$value->fid_produk)->first();
            $result[$key]->foto=(!empty($foto) ? $foto->foto : null );

            $petugas=DB::table('anggota')->where('no_anggota',$value->created_by)->first();
            $result[$key]->nama_petugas=(!empty($petugas) ? $petugas->nama_lengkap : 'Undefined');
        }
        if(!empty($search)){
            $result->withPath('return?search='.$search);
        }
        return $result;
    }

    public function index(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,27);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $data['retur']=$this->get_return($search);
            return view('pos.return.index')
                ->with('data',$data)
                ->with('search',$search);
        }
    }

    public function form(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,27);
        if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N'){
            return view('404');
        }
        else{
            $id=(!empty($request->id) ? $request->id : 0 );
            $retur=ReturPenjualan::select('retur_penjualan.*','penjualan.no_transaksi','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
                ->Leftjoin('penjualan','penjualan.id','=','retur_penjualan.fid_penjualan')
                ->LeftJoin('anggota','anggota.no_anggota','=','retur_penjualan.fid_anggota')
                ->where('retur_penjualan.id',$id)
                ->first();
            if(!empty($retur)){
                $jenis_pencarian=($retur->fid_penjualan==null ? 'anggota' : 'transaksi' );
                $search=($jenis_pencarian=='anggota' ? $retur->fid_anggota : $retur->no_transaksi );
                $fid_penjualan=($jenis_pencarian=='anggota' ? null : $retur->fid_penjualan );
                $data['retur']=$retur;

            }
            else{
                $search=(!empty($request->search) ? $request->search : null );
                $jenis_pencarian=(!empty($request->jenis_pencarian) ? $request->jenis_pencarian : 'transaksi' );
                $penjualan=Penjualan::select('penjualan.*','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
                    ->leftJoin('anggota','anggota.no_anggota','=','penjualan.fid_anggota')
                    ->where(($jenis_pencarian=='transaksi' ? 'penjualan.no_transaksi' : 'penjualan.fid_anggota' ),$search)
                    ->first();
                $data['retur']=$penjualan;
                $fid_penjualan=($jenis_pencarian=='anggota' ? null : (!empty($penjualan) ? $penjualan->id : 0 ) ) ;
            }

            if($jenis_pencarian=='transaksi'){
                $items=ItemPenjualan::select('item_penjualan.*','produk.nama_produk','produk.kode','satuan_barang.satuan')
                    ->join('produk','produk.id','=','item_penjualan.fid_produk')
                    ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
                    ->where('item_penjualan.fid_penjualan',$fid_penjualan)
                    ->get();
            }
            else{
                $items=ItemPenjualan::select('item_penjualan.*','produk.nama_produk','produk.kode','satuan_barang.satuan')
                    ->selectRaw('sum(jumlah) as jumlah')
                    ->join('produk','produk.id','=','item_penjualan.fid_produk')
                    ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
                    ->join('penjualan','penjualan.id','=','item_penjualan.fid_penjualan')
                    ->where('penjualan.fid_anggota',$search)
                    ->groupBy('produk.id')
                    ->get();
            }
            foreach ($items as $key => $value) {
                $foto=FotoProduk::where('fid_produk',$value->fid_produk)->first();
                $items[$key]->foto=(!empty($foto) ? $foto->foto : null );
                $items_retur=ItemReturPenjualan::where('fid_produk',$value->fid_produk)
                    ->where('fid_retur_penjualan',$id)
                    ->first();
                $items[$key]->jumlah_retur=(!empty($items_retur) ? $items_retur->jumlah : 0 );
                $items[$key]->keterangan=(!empty($items_retur) ? $items_retur->keterangan : '' );
            }
            $data['items']=$items;
            return view('pos.return.form')
                ->with('data',$data)
                ->with('search',$search)
                ->with('jenis_pencarian',$jenis_pencarian)
                ->with('fid_penjualan',$fid_penjualan)
                ->with('id',$id);
        }
    }

    public function proses(Request $request){
        $retur=ReturPenjualan::find($request->id);
        if(!empty($retur)){
            $field=ReturPenjualan::find($retur->id);
            $field->updated_at=date('Y-m-d H:i:s');
            $msg='Retur penjualan barang berhasil disimpan';
        }
        else{
            $field=new ReturPenjualan;
            $field->fid_penjualan=$request->fid_penjualan;
            $field->fid_anggota=$request->fid_anggota;
            $field->created_at=date('Y-m-d H:i:s');
            $field->created_by=Session::get('useractive')->no_anggota;
            $field->no_retur=GlobalHelper::get_nomor_retur_penjualan($field->created_at);
            $msg='Retur penjualan barang berhasil ditambahkan';
        }
        $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
        if($request->action=='delete'){
            $field->delete();
            ItemReturPenjualan::where('fid_retur_penjualan',$request->id)->delete();
            $msg='Retur penjualan barang berhasil dihapus';
        }
        else{
            $field->save();
            $this->proses_items($field->id,$request);
        }
        return redirect('pos/return/form?id='.$field->id)
            ->with('message',$msg)
            ->with('message_type','success');
    }

    public function delete_items(Request $request){
        ItemReturPenjualan::find($request->id)->delete();
        return redirect('pos/return')
            ->with('message','Retur Penjualan berhasil dihapus')
            ->with('message_type','success');
    }

    public function proses_items($id,$request){
        foreach ($request->fid_produk as $key => $produk) {
            $cek_items=ItemReturPenjualan::where('fid_retur_penjualan',$id)
                ->where('fid_produk',$produk)
                ->first();

            $retur_penjualan = ReturPenjualan::find($id);
            $item_penjualan = ItemPenjualan::where('fid_penjualan', $retur_penjualan->fid_penjualan)->where('fid_produk', $produk)->first();
            if (empty($item_penjualan)) {
                return ['error' => 'Item Penjualan Tidak Ditemukan!'];
            }

            if (intval($item_penjualan->jumlah) >= intval($request->jumlah[$produk])) {
                if (!empty($cek_items)) {
                    $field = ItemReturPenjualan::find($cek_items->id);
                    $action = 'edit';
                } else {
                    $field = new ItemReturPenjualan;
                    $field->fid_retur_penjualan = $id;
                    $field->fid_produk = $produk;
                    $action = 'add';
                }
                $field->jumlah = $request->jumlah[$produk];
                $field->keterangan = $request->keterangan[$produk];
                if ($field->jumlah <= 0) {
                    ($action == 'edit' ? $field->delete() : '');
                } else {
                    $field->save();
                }
            }
        }
    }
}
