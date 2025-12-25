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

class PembelianController extends Controller
{
    public function get_pembelian($search){
        $pembelian = Pembelian::whereNotNull('id');
        if(!empty($search)) $pembelian = $pembelian->where('no_pembelian', 'like', "%$search%");
        $pembelian = $pembelian->orderBy('tanggal')->paginate(10);
        foreach ($pembelian as $key => $value) {
            $items=ItemPembelian::where('fid_pembelian',$value->id);
            $value->jumlah=$items->count();
            $value->subtotal=$items->sum('total');
        }
        if(!empty($search)) $pembelian->withPath('pembelian?search='.$search);

        return $pembelian;
    }


    public function index(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,22);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $data['pembelian']=$this->get_pembelian($search);
            return view('manajemen_stok.pembelian.index')
                ->with('data',$data)
                ->with('search',$search);
        }
    }

    public function form(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,22);
        if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N'){
            return view('404');
        }
        else{
            $pembelian=Pembelian::find($request->id);
            if(!empty($pembelian)){
                $action='edit';
                $id=$request->id;
                $items=ItemPembelian::select('item_pembelian.*','produk.nama_produk','produk.kode','satuan_barang.satuan')
                    ->join('produk','produk.id','=','item_pembelian.fid_produk')
                    ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
                    ->where('fid_pembelian',$id)
                    ->get();
                $subtotal=0;
                foreach ($items as $key => $value) {
                    $foto=FotoProduk::where('fid_produk',$value->fid_produk)->first();
                    $items[$key]->foto=(!empty($foto) ? $foto->foto : null );
                    $subtotal=$subtotal+$value->total;
                }
                $pembelian->subtotal=$subtotal;
                $data['items']=$items;
                $data['pembelian']=$pembelian;
            }
            else{
                $action='add';
                $id='0';
            }
            $data['supplier']=DB::table('supplier')->get();
            return view('manajemen_stok.pembelian.form')
                ->with('data',$data)
                ->with('action',$action)
                ->with('id',$id);
        }
    }

    public function add_pembelian($request){
        $cek_pembelian=Pembelian::find($request->id);
        if(empty($cek_pembelian)){
            $field=new Pembelian;
            $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
            $field->no_pembelian=GlobalHelper::get_nomor_pembelian($field->created_at);
            $field->fid_supplier=$request->supplier;
            $field->keterangan=$request->keterangan;
            $field->created_at=date('Y-m-d H:i:s');
            $field->created_by=Session::get('useractive')->no_anggota;
            $field->diskon_persen=$request->diskon_persen;
            $field->diskon_nominal=str_replace('.','',$request->diskon_nominal);
            $field->ppn_persen=$request->ppn_persen;
            $field->ppn_nominal=str_replace('.','',$request->ppn_nominal);
            $field->biaya_tambahan=str_replace('.','',$request->biaya_tambahan);
            $field->total=str_replace('.','',$request->total);
            $field->save();
            return $field->id;
        }
        else{
            return $request->id;
        }
    }

    public function proses_items($request){
        $produk=Produk::where('kode',$request->kode)->first();
        if(!empty($produk)){
            $barang=GlobalHelper::stok_barang($produk->id);
            if($request->id == 0 ){
                $field=new ItemPembelian;
                $field->fid_pembelian=$this->add_pembelian($request);
                $field->fid_produk=$produk->id;
                $field->jumlah=1;
            }
            else{
                $items=ItemPembelian::where('fid_pembelian',$request->id)->where('fid_produk',$produk->id)->first();
                if(!empty($items)){
                    $field=ItemPembelian::find($items->id);
                    $field->jumlah=$field->jumlah+1;
                }
                else{
                    $field=new ItemPembelian;
                    $field->fid_pembelian=$this->add_pembelian($request);
                    $field->fid_produk=$produk->id;
                    $field->jumlah=1;
                }
            }
            if($request->action=='delete'){
                $field->delete();
            }
            else{
                $field->harga=$produk->harga_beli;
                $field->margin=$produk->margin;
                $field->margin_nominal=$produk->margin_nominal;
                $field->harga_jual=$produk->harga_jual;
                $field->total=$field->jumlah*$field->harga;
                if($field->jumlah <= $barang['sisa']){
                    $field->save();
                }
            }
            return $field->fid_pembelian;
        }
        else{
            return null ;
        }
    }

    public function update_pembelian($id){
        $pembelian=Pembelian::find($id);
        if(!empty($pembelian)){
            $subtotal=ItemPembelian::select('item_pembelian.*','produk.nama_produk','produk.kode','produk.satuan')
                ->join('produk','produk.id','=','item_pembelian.fid_produk')
                ->where('fid_pembelian',$id)
                ->sum('total');
            $pembelian->diskon_nominal=$subtotal*$pembelian->diskon_persen/100;
            $pembelian->ppn_nominal=($subtotal-$pembelian->diskon_nominal)*$pembelian->ppn_persen/100;
            $pembelian->total=$subtotal-$pembelian->diskon_nominal+$pembelian->ppn_nominal+$pembelian->biaya_tambahan;
            $pembelian->save();
        }
    }

    public function proses_all_items($id,$request){
        $items=ItemPembelian::select('item_pembelian.*','produk.kode')
            ->where('item_pembelian.fid_pembelian',$id)
            ->join('produk','produk.id','=','item_pembelian.fid_produk')
            ->get();
        foreach ($items as $key => $value){
            $field=ItemPembelian::find($value->id);
            if(!empty($request->jumlah[$value->id])){
                $field->jumlah=($request->kode == $value->kode ? $field->jumlah : $request->jumlah[$value->id]) ;
                $field->harga=str_replace('.','',$request->harga[$value->id]);
                $field->margin=$request->margin[$value->id];
                $field->margin_nominal=str_replace('.','',$request->margin_nominal[$value->id]);
                $field->harga_jual=$field->harga+$field->margin_nominal;
                $field->total=$field->harga*$field->jumlah;
                $field->save();
            }
        }
    }


    public function proses(Request $request){
        if($request->action == 'delete'){
            Pembelian::find($request->id)->delete();
            ItemPembelian::where('fid_pembelian',$request->id)->delete();
            return Redirect::back();
        }
        else{
            if($request->action == 'add_barang' && $request->kode != ''){
                $id=$this->proses_items($request);
                if($id == null ){
                    return Redirect::back();
                }
            }
            $pembelian=Pembelian::find($request->id);

            if(!empty($pembelian)){
                $field=$pembelian;
                $field->updated_at=date('Y-m-d H:i:s');
                $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
                $field->fid_supplier=$request->supplier;
                $field->keterangan=$request->keterangan;
                $field->diskon_persen=$request->diskon_persen;
                $field->diskon_nominal=str_replace('.','',$request->diskon_nominal);
                $field->ppn_persen=$request->ppn_persen;
                $field->ppn_nominal=str_replace('.','',$request->ppn_nominal);
                $field->biaya_tambahan=str_replace('.','',$request->biaya_tambahan);
                $field->total=str_replace('.','',$request->total);
                $field->save();
                $this->proses_all_items($field->id,$request);
                return redirect('manajemen_stok/pembelian/form?id='.$field->id);
            }
            else{
                if($id == null ){
                    return Redirect::back();
                }
                else{
                    $this->update_pembelian($id);
                    return redirect('manajemen_stok/pembelian/form?id='.$id);
                }
            }
        }
    }



    // public function proses(Request $request){
    //   if($request->action=='simpan'){
    //     $this->proses_pembelian($request);
    //     $msg='Transaksi Pembelian berhasil disimpan';
    //     $url='manajemen_stok/pembelian';
    //   }
    //   elseif($request->action=='delete'){
    //     $this->proses_pembelian($request);
    //     $msg='Transaksi Pembelian berhasil dihapus';
    //     $url='manajemen_stok/pembelian';
    //   }
    //   else{
    //     $produk=Produk::find($request->produk_id);
    //     if(!empty($produk)){
    //       $produk=ItemPembelian::where('fid_pembelian',$request->id)->where('fid_produk',$request->produk_id)->first();
    //       if(!empty($produk) && $request->id !=0 ){
    //         $field=ItemPembelian::find($produk->id);
    //         $msg='Items Pembelian berhasil disimpan';
    //       }
    //       else{
    //         $field=new ItemPembelian;
    //         $field->fid_produk=$request->produk_id;
    //         $msg='Items Pembelian berhasil ditambahkan';
    //       }
    //       if($request->action=='delete_items'){
    //         $field->delete();
    //         $msg='Items Pembelian berhasil dihapus';
    //       }
    //       else{
    //         $field->fid_pembelian=$this->proses_pembelian($request);
    //         $field->jumlah=$request->jumlah;
    //         $field->harga=str_replace('.','',$request->harga);
    //         $field->margin=$request->margin;
    //         $field->margin_nominal=str_replace('.','',$request->margin_nominal);
    //         $field->harga_jual=$field->harga+$field->margin_nominal;
    //         $field->total=$field->jumlah*$field->harga;
    //         $field->save();
    //         $this->update_produk($field->fid_produk,$request);
    //       }
    //       $this->update_pembelian($field->fid_pembelian);
    //       $url='manajemen_stok/pembelian/form?id='.$field->fid_pembelian;
    //     }
    //     else{
    //       return Redirect::back();
    //     }
    //   }
    //   return redirect($url)
//         ->with('message',$msg)
//         ->with('message_type','success');
    // }
    //
    // public function update_pembelian($id){
    //   $pembelian=Pembelian::find($id);
    //   if(!empty($pembelian)){
    //     $subtotal=ItemPembelian::select('item_pembelian.*','produk.nama_produk','produk.kode','produk.satuan')
    //       ->join('produk','produk.id','=','item_pembelian.fid_produk')
    //       ->where('fid_pembelian',$id)
    //       ->sum('total');
    //     $pembelian->diskon_nominal=$subtotal*$pembelian->diskon_persen/100;
    //     $pembelian->ppn_nominal=($subtotal-$pembelian->diskon_nominal)*$pembelian->ppn_persen/100;
    //     $pembelian->total=$subtotal-$pembelian->diskon_nominal+$pembelian->ppn_nominal+$pembelian->biaya_tambahan;
    //     $pembelian->save();
    //   }
    // }
    //
    // public function proses_pembelian($request){
    //   $pembelian=Pembelian::find($request->id);
    //   if(!empty($pembelian)){
    //     $field=$pembelian;
    //     $field->updated_at=date('Y-m-d H:i:s');
    //   }
    //   else{
    //     $field=new Pembelian;
    //     $field->no_pembelian=GlobalHelper::get_nomor_pembelian($field->created_at);
    //     $field->created_at=date('Y-m-d H:i:s');
    //     $field->created_by=Session::get('useractive')->no_anggota;
    //   }
    //   if($request->action=='delete'){
    //     $field->delete();
    //     ItemPembelian::where('fid_pembelian',$request->id)->delete();
    //   }
    //   else{
    //     $field->tanggal=GlobalHelper::dateFormat($request->tanggal,'Y-m-d');
    //     $field->fid_supplier=$request->supplier;
    //     $field->keterangan=$request->keterangan;
    //     $field->diskon_persen=$request->diskon_persen;
    //     $field->diskon_nominal=str_replace('.','',$request->diskon_nominal);
    //     $field->ppn_persen=$request->ppn_persen;
    //     $field->ppn_nominal=str_replace('.','',$request->ppn_nominal);
    //     $field->biaya_tambahan=str_replace('.','',$request->biaya_tambahan);
    //     $field->total=str_replace('.','',$request->total);
    //     $field->save();
    //   }
    //   return $field->id;
    // }
    //
    // public function update_produk($id,$request){
    //   $produk=Produk::find($id);
    //   if(!empty($produk)){
    //     $field=$produk;
    //     $field->harga_beli=str_replace('.','',$request->harga);
    //     $field->margin=$request->margin;
    //     $field->margin_nominal=str_replace('.','',$request->margin_nominal);
    //     $field->harga_jual=$field->harga_beli+$field->margin_nominal;
    //     $field->save();
    //   }
    // }

}
