<?php

namespace App\Http\Controllers;

use App\ItemPenjualan;
use App\ItemReturPembelian;
use App\ItemReturPenjualan;
use App\StokOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Produk;
use App\FotoProduk;
use App\ItemPembelian;
use App\LabelHarga;
use App\BarcodeBarang;
use View;
use DB;
use DateTime;
use Redirect;

class ProdukController extends Controller
{

    public function get_produk($kategori,$search,$limit=10){
        $query=Produk::select('produk.*','satuan_barang.satuan')
            ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan');
        if(!empty($search)){
            $query=$query->where(function ($i) use ($search) {
                $i->where('produk.nama_produk', 'like', "%{$search}%")
                    ->orWhere('produk.kode', 'like', "%{$search}%");
            });
        }
        if(!empty(Session::get('filter_produk'))){
            $filters=Session::get('filter_produk');
            if($filters['kode']!='all'){
                $query=$query->where('produk.kode_kategori','like', "{$filters['kode']}%");
            }
            if(!empty($filters['is_aktif']) && $filters['is_aktif'] !== 'all'){
                $query=$query->where('produk.is_aktif', $filters['is_aktif']);
            }
        }
        if($limit == 'all'){
            $result=$query->orderBy('produk.nama_produk')->get();
        }
        else{
            $result=$query->orderBy('produk.nama_produk')->paginate($limit);
        }
        foreach ($result as $key => $value){
            $petugas=DB::table('anggota')->where('no_anggota',$value->created_by)->first();
            $result[$key]->nama_petugas=(!empty($petugas) ? $petugas->nama_lengkap : 'Undefined');

            $foto=FotoProduk::where('fid_produk',$value->id)->first();
            $result[$key]->foto=(!empty($foto) ? $foto->foto : null );

            $stok=GlobalHelper::stok_barang($value->id);
            $result[$key]->stok_masuk=$stok['stok_awal']+$stok['pembelian'];
            $result[$key]->stok_keluar=$stok['retur']+$stok['terjual'];
            $result[$key]->stok_awal=$stok['stok_awal'];
            $result[$key]->pembelian=$stok['pembelian'];
            $result[$key]->retur=$stok['retur'];
            $result[$key]->terjual=$stok['terjual'];
            $result[$key]->sisa=$stok['sisa'];
            $result[$key]->penyesuaian=$stok['penyesuaian'];

            $kategori=explode('.',$value->kode_kategori);
            $result[$key]->kelompok=GlobalHelper::detail_kategori_produk($kategori[0]);
            $result[$key]->kategori=GlobalHelper::detail_kategori_produk($kategori[1]);
            $result[$key]->sub_kategori=GlobalHelper::detail_kategori_produk($kategori[2]);
        }
        if(!empty($search)){
            $result->withPath('barang?search='.$search);
        }
        return $result;
    }

    public function index(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,21);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $kategori=(!empty($request->kategori) ? $request->kategori : 'all');
            $data['produk']=$this->get_produk($kategori,$search);
            $data['kategori']=DB::table('kategori_produk')->get();
            return view('manajemen_stok.barang.index')
                ->with('data',$data)
                ->with('kategori',$kategori)
                ->with('request',$request)
                ->with('search',$search);
        }
    }

    public function form(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,21);
        if($data['otoritas']['view']=='N' || $data['otoritas']['insert']=='N' || $data['otoritas']['update']=='N'){
            return view('404');
        }
        else{
            $produk=Produk::find($request->id);
            if(!empty($produk)){
                $action='edit';
                $id=$request->id;
                $foto=FotoProduk::where('fid_produk',$id)->first();
                $produk->foto=(!empty($foto) ? $foto->foto : null );
                if(!empty($produk->kode_kategori)){
                    $kategori=explode('.',$produk->kode_kategori);
                    $produk->kelompok=($kategori[0]==0 ? 'all' : $kategori[0]);
                    $produk->kategori=($kategori[1]==0 ? 'all' : $kategori[1]);
                    $produk->subkategori=($kategori[2]==0 ? 'all' : $kategori[2]);
                }
                else{
                    $produk->kelompok='all';
                    $produk->kategori='all';
                    $produk->subkategori='all';
                }
            }
            else{
                $action='add';
                $id=0;
            }
            $data['produk']=$produk;
            $data['kategori']=DB::table('kategori_produk')->get();
            $data['satuan']=DB::table('satuan_barang')->get();
            return view('manajemen_stok.barang.form')
                ->with('data',$data)
                ->with('request',$request)
                ->with('action',$action)
                ->with('id',$id);
        }
    }

    public function get_stok_masuk($id,$tanggal,$search){
        $query=DB::table('v_stok_masuk')->select('v_stok_masuk.*','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
            ->join('anggota','anggota.no_anggota','=','v_stok_masuk.created_by')
            ->where('v_stok_masuk.fid_produk',$id);
        $result=$query->orderBy('v_stok_masuk.created_at','DESC')->paginate(10);
        return $result;
    }

    public function get_stok_keluar($id,$tanggal,$search){
        $query=DB::table('v_stok_keluar')->select('v_stok_keluar.*','anggota.nama_lengkap','anggota.no_anggota','anggota.avatar')
            ->join('anggota','anggota.no_anggota','=','v_stok_keluar.created_by')
            ->where('v_stok_keluar.fid_produk',$id);
        $result=$query->orderBy('v_stok_keluar.created_at','DESC')->paginate(10);
        return $result;
    }


    public function detail(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,21);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $produk=Produk::select('produk.*','satuan_barang.satuan')
                ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan')
                ->where('produk.id',$request->id)
                ->first();
            if(!empty($produk)){
                $id=$request->id;

                $tab=(!empty($request->tab) ? $request->tab : 'informasi');
                $search=(!empty($request->search) ? $request->search : 'all');
                $tanggal=(!empty($request->tanggal) ? $request->tanggal : date('d-m-Y'));

                $foto=FotoProduk::where('fid_produk',$id)->first();
                $produk->foto=(!empty($foto) ? $foto->foto : null );

                $stok=GlobalHelper::stok_barang($produk->id);
                $produk->stok_masuk=$stok['stok_awal']+$stok['pembelian'];
                $produk->stok_keluar=$stok['retur']+$stok['terjual'];
                $produk->stok_awal=$stok['stok_awal'];
                $produk->pembelian=$stok['pembelian'];
                $produk->retur=$stok['retur'];
                $produk->terjual=$stok['terjual'];
                $produk->sisa=$stok['sisa'];

                $kategori=explode('.',$produk->kode_kategori);
                $produk->kelompok=GlobalHelper::detail_kategori_produk($kategori[0]);
                $produk->kategori=GlobalHelper::detail_kategori_produk($kategori[1]);
                $produk->sub_kategori=GlobalHelper::detail_kategori_produk($kategori[2]);
                $data['produk']=$produk;

                $mutasi = [];
                $mutasi[] = [
                    'tanggal' => '',
                    'keterangan' => 'Stok Awal',
                    'jumlah' => $produk->stok_awal,
                ];

                $pembelian = ItemPembelian::select('item_pembelian.*', 'pembelian.tanggal', 'pembelian.no_pembelian')
                    ->join('pembelian', 'pembelian.id', '=', 'item_pembelian.fid_pembelian')
                    ->where('fid_produk', $produk->id)->orderBy('tanggal')->get();
                foreach ($pembelian as $item) {
                    $mutasi[] = [
                        'tanggal' => $item->tanggal,
                        'keterangan' => 'Pembelian ' . $item->no_pembelian . ' dari supplier ' . ($item->pembelian->supplier->nama_supplier ?? ''),
                        'jumlah' => $item->jumlah,
                    ];
                }
                $retur_pembelian = ItemReturPembelian::select('item_retur_pembelian.*', 'retur_pembelian.tanggal', 'retur_pembelian.no_retur')
                    ->join('retur_pembelian', 'retur_pembelian.id', '=', 'item_retur_pembelian.fid_retur_pembelian')
                    ->where('fid_produk', $produk->id)->orderBy('tanggal')->get();
                foreach ($retur_pembelian as $item) {
                    $mutasi[] = [
                        'tanggal' => $item->tanggal,
                        'keterangan' => 'Retur Pembelian ' . $item->no_retur . ' dari supplier ' . $item->retur_pembelian->supplier->nama_supplier,
                        'jumlah' => $item->jumlah * -1,
                    ];
                }
                $penjualan = ItemPenjualan::select('item_penjualan.*', 'penjualan.tanggal', 'penjualan.no_transaksi')
                    ->join('penjualan', 'penjualan.id', '=', 'item_penjualan.fid_penjualan')
                    ->where('penjualan.fid_status', 2)
                    ->where('fid_produk', $produk->id)->orderBy('tanggal')->get();
                foreach ($penjualan as $item) {
                    $mutasi[] = [
                        'tanggal' => $item->tanggal,
                        'keterangan' => 'Penjualan ' . $item->no_transaksi . ' anggota ' . ($item->penjualan->anggota->nama_lengkap ?? ''),
                        'jumlah' => $item->jumlah * -1,
                    ];
                }
                $retur_penjualan = ItemReturPenjualan::select('item_retur_penjualan.*', 'retur_penjualan.tanggal', 'retur_penjualan.no_retur')
                    ->join('retur_penjualan', 'retur_penjualan.id', '=', 'item_retur_penjualan.fid_retur_penjualan')
                    ->where('fid_produk', $produk->id)->orderBy('tanggal')->get();
                foreach ($retur_penjualan as $item) {
                    $mutasi[] = [
                        'tanggal' => $item->tanggal,
                        'keterangan' => 'Retur Penjualan ' . $item->no_retur . ' anggota ' . ($item->retur_penjualan->anggota->nama_lengkap ?? ''),
                        'jumlah' => $item->jumlah,
                    ];
                }

                $opname = StokOpname::where('fid_produk', $produk->id)->orderBy('tanggal')->get();
                foreach ($opname as $item) {
                    $mutasi[] = [
                        'tanggal' => $item->tanggal,
                        'keterangan' => 'Stok Opname ' . $item->keterangan,
                        'jumlah' => $item->jumlah,
                    ];
                }

                $mutasi = collect($mutasi)->sortBy('tanggal')->all();

                return view('manajemen_stok.barang.detail.index')
                    ->with('data',$data)
                    ->with('tab',$tab)
                    ->with('search',$search)
                    ->with('tanggal',$tanggal)
                    ->with('mutasi',$mutasi)
                    ->with('id',$id);
            }
            else{
                return redirect('manajemen_stok/barang')
                    ->with('message','Barang tidak Ditemukan')
                    ->with('message_type','warning');
            }
        }
    }

    public function proses(Request $request){
        if($request->action=='add'){
            $check = Produk::where('kode', $request->kode)->first();
            if (empty($check)) {
                $field = new Produk;
            } else {
                $field = Produk::find($check->id);
            }
            $field->created_at=date('Y-m-d H:i:s');
            $field->created_by=Session::get('useractive')->no_anggota;
            $msg='Data Produk berhasil ditambahkan';
        }
        else{
            $field=Produk::find($request->id);
            $field->updated_at=date('Y-m-d H:i:s');
            $msg='Data Produk berhasil disimpan';
        }
        if($request->action=='delete'){
            $field->delete();
            $msg='Data Produk berhasil dihapus';
        }
        else{
            $field->kode=$request->kode;
            $field->nama_produk=$request->nama_produk;
            $field->deskripsi=$request->deskripsi;
            $field->fid_kategori=($request->sub_kategori != 'all' ? $request->sub_kategori : ( $request->kategori != 'all' ? $request->kategori : ( $request->kelompok == 'all' ? null : $request->kelompok ) ) );
            $field->kode_kategori=GlobalHelper::get_kode_kategori($field->fid_kategori);
            $field->stok_awal=$request->stok_awal;
            $field->stok_minimal=$request->stok_minimal;
            $field->fid_satuan=$request->satuan;
            $field->harga_beli=str_replace('.','',$request->harga_beli);
            $field->margin=$request->margin;
            $field->margin_nominal=str_replace('.','',$request->nominal_margin);
            $field->harga_jual=str_replace('.','',$request->harga_jual);
            $field->expired = unformat_date($request->input('expired'));
            $field->save();
            $this->proses_foto_produk($field->id,$request);
            $this->update_item_pembelian($field->id,$request);
        }
        return redirect('manajemen_stok/barang?page='.$request->halaman)
            ->with('message',$msg)
            ->with('message_type','success');
    }

    public function update_item_pembelian($id,$request){
        $cek_item=ItemPembelian::where('fid_pembelian',0)->where('fid_produk',$id)->first();
        if(!empty($cek_item)){
            $field=ItemPembelian::find($cek_item->id);
        }
        else{
            $field=new ItemPembelian;
            $field->fid_pembelian=0;
            $field->fid_produk=$id;
        }
        $field->jumlah=$request->stok_awal;
        $field->harga=str_replace('.','',$request->harga_beli);
        $field->margin=$request->margin;
        $field->margin_nominal=str_replace('.','',$request->nominal_margin);
        $field->harga_jual=str_replace('.','',$request->harga_jual);
        $field->total=$field->jumlah*$field->harga;
        $field->save();
    }

    public function proses_foto_produk($id,$request){
        $foto_produk=FotoProduk::where('fid_produk',$id)->first();
        if(!empty($foto_produk)){
            $field=FotoProduk::find($foto_produk->id);
        }
        else{
            $field=new FotoProduk;
        }
        if($request->hasFile('foto')){
            if(!empty($field->foto)){
                unlink(storage_path('app/'.$field->foto));
            }
            $uploadedFile = $request->file('foto');
            $path = $uploadedFile->store('foto_produk');
            $field->foto=$path;
        }
        $field->fid_produk=$id;
        $field->created_at=date('Y-m-d H:i:s');
        $field->save();
    }

    //-------------------------------------- Cetak Label Harga --------------------------------------------//

    public function get_label_produk($search = '', $limit = ''){
        $result=LabelHarga::select('produk.*','label_harga.id','label_harga.jumlah','label_harga.created_at','label_harga.created_by','satuan_barang.satuan')
            ->join('produk','produk.id','=','label_harga.fid_produk')
            ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan');
        if ($search != '') {
            $result = $result->where(function ($i) use ($search) {
                $i->where('produk.nama_produk', 'like', "%{$search}%")
                    ->orWhere('produk.kode', 'like', "%{$search}%");
            });
        }
        if ($limit === '') {
            $result = $result->get();
        } else {
            $result = $result->paginate(10);
        }


        foreach ($result as $key => $value) {
            $petugas=DB::table('anggota')->where('no_anggota',$value->created_by)->first();
            $result[$key]->nama_petugas=(!empty($petugas) ? $petugas->nama_lengkap : 'Undefined');

            $foto=FotoProduk::where('fid_produk',$value->id)->first();
            $result[$key]->foto=(!empty($foto) ? $foto->foto : null );

            $kategori=explode('.',$value->kode_kategori);
            $result[$key]->kelompok=GlobalHelper::detail_kategori_produk($kategori[0]);
            $result[$key]->kategori=GlobalHelper::detail_kategori_produk($kategori[1]);
            $result[$key]->sub_kategori=GlobalHelper::detail_kategori_produk($kategori[2]);
        }
        return $result;
    }

    public function label_harga(Request $request){
        if($request->mode == 'kosongi'){
            LabelHarga::whereNotNull('id')->delete();
            return redirect()->back()->with('message','Label Harga berhasil dikosongi')
                ->with('message_type','success');
        }

        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,54);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $kategori=(!empty($request->kategori) ? $request->kategori : 'all');
            $data['produk']=$this->get_label_produk($search, $request->mode != 'cetak' ? 10 : '');
            $data['kategori']=DB::table('kategori_produk')->get();
            if($request->mode == 'cetak'){
                return view('manajemen_stok.cetak.label_harga.cetak')
                    ->with('data',$data)
                    ->with('kategori',$kategori)
                    ->with('request',$request)
                    ->with('search',$search);
            }
            else{
                return view('manajemen_stok.cetak.label_harga.index')
                    ->with('data',$data)
                    ->with('kategori',$kategori)
                    ->with('request',$request)
                    ->with('search',$search);
            }
        }
    }

    public function filter_label_harga(Request $request){
        Session::forget('filter_label_harga');
        $kode='';
        $kode .=($request->kelompok=='all' || $request->kelompok=='' ? 'all' : $request->kelompok);
        $kode .=($request->kategori=='all' || $request->kategori=='' ? '' : '.'.$request->kategori);
        $kode .=($request->sub_kategori=='all' || $request->sub_kategori=='' ? '' : '.'.$request->sub_kategori);
        $filter=array('kode'=>$kode,
            'kelompok'=>($request->kelompok=='' ? 'all' : $request->kelompok ),
            'kategori'=>($request->kategori=='' ? 'all' : $request->kategori ),
            'sub_kategori'=>($request->sub_kategori=='' ? 'all' : $request->sub_kategori ),
            'search'=>($request->sub_kategori=='' ? 'all' : $request->search ));

        $query=Produk::select('*');
        if($filter['kode']!='all'){
            $query=$query->where('produk.kode_kategori','like', "{$filter['kode']}%");
        }
        if($filter['search']!='all'){
            $query=$query->where(function ($i) use ($filter) {
                $i->where('produk.nama_produk', 'like', "%{$filter['search']}%")
                    ->orWhere('produk.kode', 'like', "%{$filter['search']}%");
            });
        }
        $data=$query->get();
//        LabelHarga::truncate();
        foreach ($data as $key => $value) {
            $check = LabelHarga::where('fid_produk', $value->id)->count();
            if ($check == 0) {
                $field = new LabelHarga;
                $field->created_at = date('Y-m-d H:i:s');
                $field->created_by = Session::get('useractive')->no_anggota;
                $field->fid_produk = $value->id;
                $field->jumlah = 1;
                $field->save();
            }
        }
        Session::put('filter_label_harga',$filter);
        return Redirect::back();
    }

    public function proses_label_harga(Request $request){
        $field=LabelHarga::find($request->id);
        if($request->action == 'edit'){
            $field->jumlah=(empty($request->jumlah) ? 1 : $request->jumlah);
            $field->save();
            return $field->jumlah;
        }
        else{
            $field->delete();
            return Redirect::back()
                ->with('message','Label Harga berhasil dihapus')
                ->with('message_type','success');
        }
    }

    //-------------------------------------- Cetak Barcode Barang --------------------------------------------//

    public function get_barcode_barang($search = '', $limit = ''){
        $result=BarcodeBarang::select('produk.*','barcode_barang.id','barcode_barang.jumlah','barcode_barang.created_at','barcode_barang.created_by','satuan_barang.satuan')
            ->join('produk','produk.id','=','barcode_barang.fid_produk')
            ->join('satuan_barang','satuan_barang.id','=','produk.fid_satuan');
        if ($search != '') {
            $result = $result->where(function ($i) use ($search) {
                $i->where('produk.nama_produk', 'like', "%{$search}%")
                    ->orWhere('produk.kode', 'like', "%{$search}%");
            });
        }
        if ($limit === '') {
            $result = $result->get();
        } else {
            $result = $result->paginate(10);
        }
        foreach ($result as $key => $value) {
            $petugas=DB::table('anggota')->where('no_anggota',$value->created_by)->first();
            $result[$key]->nama_petugas=(!empty($petugas) ? $petugas->nama_lengkap : 'Undefined');

            $foto=FotoProduk::where('fid_produk',$value->id)->first();
            $result[$key]->foto=(!empty($foto) ? $foto->foto : null );

            $kategori=explode('.',$value->kode_kategori);
            $result[$key]->kelompok=GlobalHelper::detail_kategori_produk($kategori[0]);
            $result[$key]->kategori=GlobalHelper::detail_kategori_produk($kategori[1]);
            $result[$key]->sub_kategori=GlobalHelper::detail_kategori_produk($kategori[2]);
        }
        return $result;
    }

    public function barcode_barang(Request $request){
        $data['otoritas'] = GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,55);
        if ($data['otoritas']['view'] == 'N') return view('404');
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $kategori=(!empty($request->kategori) ? $request->kategori : 'all');
            $data['produk']=$this->get_barcode_barang($search);
            $data['kategori']=DB::table('kategori_produk')->get();
            return view('manajemen_stok.cetak.barcode_barang.index')
                ->with('data',$data)
                ->with('kategori',$kategori)
                ->with('request',$request)
                ->with('search',$search);
        }
    }

    public function filter_barcode_barang(Request $request){
        Session::forget('filter_barcode_barang');
        $kode='';
        $kode .=($request->kelompok=='all' || $request->kelompok=='' ? 'all' : $request->kelompok);
        $kode .=($request->kategori=='all' || $request->kategori=='' ? '' : '.'.$request->kategori);
        $kode .=($request->sub_kategori=='all' || $request->sub_kategori=='' ? '' : '.'.$request->sub_kategori);
        $filter=array('kode'=>$kode,
            'kelompok'=>($request->kelompok=='' ? 'all' : $request->kelompok ),
            'kategori'=>($request->kategori=='' ? 'all' : $request->kategori ),
            'sub_kategori'=>($request->sub_kategori=='' ? 'all' : $request->sub_kategori ),
            'search'=>($request->sub_kategori=='' ? 'all' : $request->search ));

        $query=Produk::select('*');
        if($filter['kode']!='all'){
            $query=$query->where('produk.kode_kategori','like', "{$filter['kode']}%");
        }
        if($filter['search']!='all'){
            $query=$query->where(function ($i) use ($filter) {
                $i->where('produk.nama_produk', 'like', "%{$filter['search']}%")
                    ->orWhere('produk.kode', 'like', "%{$filter['search']}%");
            });
        }
        $data=$query->get();
        BarcodeBarang::truncate();
        foreach ($data as $key => $value) {
            $field=new BarcodeBarang;
            $field->created_at=date('Y-m-d H:i:s');
            $field->created_by=Session::get('useractive')->no_anggota;
            $field->fid_produk=$value->id;
            $field->jumlah=1;
            $field->save();
        }
        Session::put('filter_barcode_barang',$filter);
        return Redirect::back();
    }

    public function proses_barcode_barang(Request $request){
        $field=BarcodeBarang::find($request->id);
        if($request->action == 'edit'){
            $field->jumlah=(empty($request->jumlah) ? 1 : $request->jumlah);
            $field->save();
            return $field->jumlah;
        }
        else{
            $field->delete();
            return Redirect::back()
                ->with('message','Barcode Barang berhasil dihapus')
                ->with('message_type','success');
        }
    }

}
