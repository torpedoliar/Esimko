<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Supplier;
use App\Produk;
use View;
use DB;
use DateTime;
use Redirect;

class SupplierController extends Controller
{
    public function get_supplier($search){
      $query=Supplier::select('*');
      if(!empty($search)){
        $query=$query->where(function ($i) use ($search) {
          $i->where('supplier.nama_supplier', 'like', "%{$search}%")
            ->orWhere('supplier.contact_person', 'like', "%{$search}%");
        });
      }
      $result=$query->orderBy('supplier.created_at')->paginate(10);
      if(!empty($search)){
        $result->withPath('supplier?search='.$search);
      }
      return $result;
    }

    public function index(Request $request){
      $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,20);
      if($data['otoritas']['view']=='N'){
        return view('404');
      }
      else{
        $search=(!empty($request->search) ? $request->search : null);
        $data['supplier']=$this->get_supplier($search);
        return view('manajemen_stok.supplier.index')
          ->with('data',$data)
          ->with('search',$search);
      }
    }

    public function proses(Request $request){
      $supplier=Supplier::find($request->id);
      if(!empty($supplier)){
        $field=Supplier::find($request->id);
        $field->updated_at=date('Y-m-d H:i:s');
        $msg='Data Supplier berhasil disimpan';
      }
      else{
        $field=new Supplier;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $msg='Data Supplier berhasil ditambahkan';
      }
      $field->nama_supplier=$request->nama_supplier;
      $field->website=$request->website;
      $field->contact_person=$request->contact_person;
      $field->no_handphone=$request->no_handphone;
      $field->email=$request->email;
      $field->alamat=$request->alamat;
      $field->no_rekening=$request->no_rekening;
      $field->nama_bank=$request->nama_bank;
      $field->atas_nama=$request->atas_nama;
      if($request->action=='delete'){
        $field->delete();
        $msg='Data Supplier berhasil dihapus';
      }
      else{
        $field->save();
      }

      return redirect('manajemen_stok/supplier')
        ->with('message',$msg)
        ->with('message_type','success');
    }
}
