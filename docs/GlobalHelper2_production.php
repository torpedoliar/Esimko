<?php
namespace App\Helpers;

use App\AngsuranBelanja;
use App\Penjualan;
use App\ReturPembelian;
use App\ReturPenjualan;
use App\StokOpname;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Blade;
use App\Anggota;
use App\Transaksi;
use App\Angsuran;
use App\Produk;
use App\Jurnal;
use App\JurnalDetail;
use App\FotoProduk;
use App\ItemPembelian;
use App\ItemReturPembelian;
use App\ItemPenjualan;
use App\ItemReturPenjualan;
use App\GajiPokok;
use App\VerifikasiTransaksi;
use App\SetoranBerkala;
use DateTime;
use Session;


class GlobalHelper2 {

    public $controller_name;

    public function __construct($controller_name = NULL){
        return $this->controller_name = $controller_name;
    }

    public static function get_modul($id){
        $modul=DB::table('modul')
            ->select('modul.*')
            ->join('otoritas_user','otoritas_user.fid_modul','=','modul.id')
            ->where('otoritas_user.fid_hak_akses','=',Session::get('useractive')->hak_akses)
            ->where('otoritas_user.is_view','Y')
            ->where('modul.parent_id','=',$id)
            ->where('modul.is_active',1)
            ->orderBy('modul.order')->get();
        foreach ($modul as $key => $value) {
            $modul[$key]->submodul=DB::table('modul')
                ->select('modul.*')
                ->join('otoritas_user','otoritas_user.fid_modul','=','modul.id')
                ->where('otoritas_user.fid_hak_akses','=',Session::get('useractive')->hak_akses)
                ->where('otoritas_user.is_view','Y')
                ->where('parent_id','=',$value->id)
                ->where('modul.is_active',1)
                ->orderBy('order')
                ->get();
        }
        return $modul;
    }

    public static function date_range($bulan){
        $calendar=CAL_GREGORIAN;
        $pisah=explode('-',$bulan);
        $hari=cal_days_in_month($calendar,$pisah[0],$pisah[1]);
        for($i=1;$i<=$hari;$i++){
            if($i<=9){
                $date='0'.$i;
            }
            else{
                $date=$i;
            }
            $nama_hari=Self::nama_hari($pisah[1].'-'.$pisah[0].'-'.$date);
            $range[]=array('date'=>$date,'nama_hari'=>$nama_hari);
        }
        return $data=array('jumlah-hari'=>$hari,'date-range'=>$range);
    }

    public static function nama_hari($tgl){
        $day = date('D', strtotime($tgl));
        $dayList = array(
            'Sun' => 'Minggu',
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jumat',
            'Sat' => 'Sabtu'
        );
        return $dayList[$day];
    }

    public static function dateFormat($date,$format){
        $date=date_create($date);
        $date_new=date_format($date,$format);
        return $date_new;
    }

    public static function tgl_indo($tgl){
        $tanggal = substr($tgl,8,2);
        $bulan = Self::getBulan(substr($tgl,5,2));
        $tahun = substr($tgl,0,4);
        return $tanggal.' '.$bulan.' '.$tahun;
    }

    public static function nama_bulan($bulan){
        $pisah=explode('-',$bulan);
        $nama_bulan=Self::getBulan($pisah[0]);
        return $nama_bulan.' '.($pisah[1] ?? '');
    }

    public static function getBulan($bln){
        switch ($bln){
            case 1:
                return "Januari";
                break;
            case 2:
                return "Februari";
                break;
            case 3:
                return "Maret";
                break;
            case 4:
                return "April";
                break;
            case 5:
                return "Mei";
                break;
            case 6:
                return "Juni";
                break;
            case 7:
                return "Juli";
                break;
            case 8:
                return "Agustus";
                break;
            case 9:
                return "September";
                break;
            case 10:
                return "Oktober";
                break;
            case 11:
                return "November";
                break;
            case 12:
                return "Desember";
                break;
        }
    }

    public static function pembulatan_nominal($uang){
        $total_harga = $uang;

        $tes = substr($total_harga, strlen($total_harga)-2, 2);
        if($tes > 0) $total_harga = (floatval(substr($total_harga, 0, strlen($total_harga) - 2)) + 1) . '00';
        return $total_harga;

//      if(substr($totalharga,-2)>49){
//        $total_harga=round($totalharga,-2);
//      } else {
//        $total_harga=round($totalharga,-2)+100;
//      }
//      return $total_harga;
    }

    public static function jumlah_hari($bulan){
        $calendar=CAL_GREGORIAN;
        $pisah=explode('-',$bulan);
        $hari=cal_days_in_month($calendar,$pisah[0],$pisah[1]);
        return $hari;
    }

    public static function detail_kategori_produk($id){
        $kategori=DB::table('kategori_produk')->find($id);
        return (!empty($kategori) ? $kategori->nama_kategori : '-');
    }

    public static function get_bulan($bulan = NULL){
        $bulan = empty($bulan) ? date("m-Y") : $bulan;
        $bulanPisah=explode("-",$bulan);
        $bulanGabung=$bulanPisah[1]."-".$bulanPisah[0];

        if($bulanPisah[0]==10){
            $bln=$bulanPisah[0];
        } else {
            $bln=str_replace('0','',$bulanPisah[0]);
        }

        $namaBln=array(1 => "Januari", "Februari", "Maret", "April", "Mei","Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
        $bulanTampil=$namaBln[$bln]." ".$bulanPisah[1];

        $bulanLama=date('Y-m-d', strtotime(date($bulanGabung) . '- 1 month'));
        $bulanLamaPisah=explode("-",$bulanLama);
        $bulanLalu=$bulanLamaPisah[1]."-".$bulanLamaPisah[0];
        $data=array($bulanLalu,$bulanTampil,$bulanGabung);
        return $data;
    }

    public static function range_bulan($bulan_awal,$tenor){
        $periode=GlobalHelper2::get_bulan($bulan_awal);
        $tenor=$tenor-1;
        for($bln=0;$bln<=$tenor;$bln++){
            $range_bulan=date('m-Y', strtotime(date($periode[2]). '+ '.$bln.' month'));
            $data[]=$range_bulan;
        }
        return $data;
    }

    public static function get_range_periode($periode){
        $nama_bulan=array(1=> "Jan", "Feb", "Mar", "Apr", "Mei","Jun", "Jul", "Agst", "Sep", "Okt", "Nov", "Des");
        $periode=GlobalHelper2::get_bulan($periode);
        $data='';
        for($q=11;$q>=0;$q--) {
            $range_bulan=date('Y-m-d', strtotime(date($periode[2]). '- '.$q.' month'));
            $range_bulan_pisah=explode("-",$range_bulan);
            if($range_bulan_pisah[1]<10){
                $bln2=str_replace('0','',$range_bulan_pisah[1]);
            }
            else{
                $bln2=$range_bulan_pisah[1];
            }
            $range_bulan_tampil=$nama_bulan[$bln2]."-".$range_bulan_pisah[0];
            $data .="'".$range_bulan_tampil."',";
        }
        return $data;
    }


    public static function otoritas_modul($hak_akses,$modul){
        $otoritas=DB::table('otoritas_user')->where('fid_hak_akses','=',$hak_akses)->where('fid_modul','=',$modul)->first();
        if(!empty($otoritas)){
            $data=array('view'=>$otoritas->is_view,
                'insert'=>$otoritas->is_insert,
                'update'=>$otoritas->is_update,
                'delete'=>$otoritas->is_delete,
                'all_user'=>$otoritas->is_all_user,
                'print'=>$otoritas->is_print,
                'verified'=>$otoritas->is_verified );
        }
        else{
            $data=array('view'=>'N',
                'insert'=>'N',
                'update'=>'N',
                'delete'=>'N',
                'all_user'=>'N',
                'print'=>'N',
                'verified'=>'N');
        }
        return $data;
    }

    public static function formatSizeUnits($bytes){
        if ($bytes >= 1073741824){
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576){
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024){
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1){
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1){
            $bytes = $bytes . ' byte';
        }
        else{
            $bytes = '0 bytes';
        }
        return $bytes;
    }

    public static function sisa_pinjaman($anggota,$jenis){
        $pinjaman=DB::table('transaksi')->where('fid_anggota',$anggota)->where('fid_status',4);
        if($jenis=='all'){
            $pinjaman=$pinjaman->whereIn('fid_jenis_transaksi',array(9,10,11))->get();
        }
        else{
            $pinjaman=$pinjaman->where('fid_jenis_transaksi',$jenis)->get();
        }
        $total_angsuran=0;
        foreach ($pinjaman as $key => $value) {
            $sisa_pinjaman=Angsuran::where('fid_transaksi',$value->id)->where('fid_status','!=',6)->first();
            $angsuran_pokok=(!empty($sisa_pinjaman) ? $sisa_pinjaman->sisa_hutang  : 0 );
            $total_angsuran=$total_angsuran+$angsuran_pokok;
        }
        return $total_angsuran;
    }

    public static function sisa_tenor_pinjaman($anggota,$jenis){
        $pinjaman=DB::table('transaksi')
            ->where('fid_anggota',$anggota)
            ->where('fid_status',4)
            ->where('fid_jenis_transaksi',$jenis)
            ->first();
        if(!empty($pinjaman)){
            $sisa_tenor=Angsuran::where('fid_transaksi',$pinjaman->id)->where('fid_status','!=',6)->count();
            return array('tenor'=>$pinjaman->tenor,'sisa'=>$sisa_tenor);
        }
        else{
            return array('tenor'=>0,'sisa'=>0);
        }
    }

    public static function sisa_kredit_belanja($anggota,$jenis){
        $kredit=DB::table('penjualan')->where('fid_anggota',$anggota)->where('fid_metode_pembayaran',3);
        if($jenis!='all'){
            $kredit=$kredit->where('jenis_belanja',$jenis)->get();
        }
        else{
            $kredit=$kredit->get();
        }
        $sisa_kredit=0;
        foreach ($kredit as $key => $value) {
            $sisa_angsuran=DB::table('angsuran_belanja')->where('fid_penjualan',$value->id)->where('fid_status','!=',6)->sum('total_angsuran');
            $sisa_kredit=$sisa_kredit+$sisa_angsuran;
        }
        return $sisa_kredit;
    }

    public static function saldo_simpanan($list_fid_anggota) {
        $data = Transaksi::select('fid_anggota', 'fid_jenis_transaksi', DB::raw('SUM(nominal) as nominal'))
            ->whereIn('fid_anggota', $list_fid_anggota)
            ->whereIn('fid_jenis_transaksi', [1, 2, 3, 4])
            ->where('fid_status', 4)
            ->groupBy(['fid_anggota', 'fid_jenis_transaksi'])
            ->get();
        $result = [];
        foreach ($data as $value) $result[$value->fid_anggota . '_' . $value->fid_jenis_transaksi] = $value->nominal;
        return $result;
    }

    public static function saldo_tabungan($anggota,$jenis){
        $query=DB::table('transaksi')->where('fid_anggota',$anggota)->where('fid_status',4);
        if($jenis!='all'){
            if($jenis=='Total Simpanan'){
                $query=$query->whereIn('fid_jenis_transaksi',array(1,2,3,4,5,6,7,8));
            }
            elseif($jenis=='Simpanan Sukarela'){
                $query=$query->whereIn('fid_jenis_transaksi',array(4,5,6));
            }
            elseif($jenis=='Simpanan Hari Raya'){
                $query=$query->whereIn('fid_jenis_transaksi',array(3,7));
            }
            else{
                $query=$query->where('fid_jenis_transaksi',$jenis);
            }
        }
        $saldo=$query->sum('nominal');
        return $saldo;
    }

    public static function pembulatan($nilai,$pembagi){
        return ROUND($nilai/$pembagi,0)*$pembagi;
    }

    public static function bulan_bekerja($kode){
        $tahun=substr($kode,1,2);
        $tahun=($tahun > date('y') ? '19'.$tahun : '20'.$tahun );
        $bulan=substr($kode,3,2);
        return date('Y-m-01', strtotime('01-' . $bulan.'-'.$tahun));
    }

    public static function change_format_nomor($no_anggota){
        if(strlen($no_anggota)==5){
            $no_anggota=str_replace('K','K ',$no_anggota);
        }
        else{
            $no_anggota=str_replace('AK','AK ',$no_anggota);
        }
        return $no_anggota;
    }

    public static function get_nomor_jurnal($kode,$tanggal){
        $bulan=GlobalHelper2::dateFormat($tanggal,'Y-m');
        $cek_jurnal=Jurnal::where('tanggal', 'like', "{$bulan}%")->orderBy('created_at','DESC')->first();
        if(!empty($cek_jurnal)){
            $pisah=explode('-',$cek_jurnal->nomor_jurnal);
            $nomor=$pisah[3];
            $temp = intval($nomor)+1;
            for($i = 1; 5 - strlen($temp); $i++) {
                $temp = '0' . $temp;
            }
            $no_urut=$temp;
        }
        else{
            $no_urut='00001';
        }
        return $kode.'-'.GlobalHelper2::dateFormat($tanggal,'m-Y').'-'.$no_urut;
    }

    public static function get_nomor_anggota($lokasi){
        $kode=($lokasi=='SJA-1' ? 'K' : 'AK');

        $anggota=Anggota::where('no_anggota','like',"{$kode}%")->orderBy('no_anggota','DESC')->first();
        if(!empty($anggota)){
            $no_anggota=str_replace($kode,'',$anggota->no_anggota);
            $temp = intval($no_anggota)+1;
            for($i = 1; 4 - strlen($temp); $i++) {
                $temp = '0' . $temp;
            }
            $no_urut=$kode.' '.$temp;
        }
        else{
            $no_urut=$kode.' 0001';
        }
        return $no_urut;
    }

    public static function get_nomor_anggota2(){
        return [
            self::get_nomor_anggota('SJA-1'),
            self::get_nomor_anggota('SJA-0'),
        ];
    }

    public static function get_nomor_penjualan($waktu){
        $tanggal=Self::dateFormat($waktu,'Y-m-d');
        $penjualan=DB::table('penjualan')->whereDate('created_at',$tanggal)->where('jenis_belanja','toko')->orderBy('created_at','DESC')->first();
        if(!empty($penjualan)){
            $pisah=explode('/',$penjualan->no_transaksi);
            $kode=substr($pisah[0],-6,6);
            $temp = intval($kode)+1;
            for($i = 1; 6 - strlen($temp); $i++) {
                $temp = '0' . $temp;
            }
            $no_urut=$temp;
        }
        else{
            $no_urut='000001';
        }
        return 'JL-0019-'.Self::dateFormat($waktu,'Ymd').''.$no_urut;
    }

    public static function get_nomor_retur_penjualan($tanggal){
        $bulan=Self::dateFormat($tanggal,'m');
        $tahun=Self::dateFormat($tanggal,'Y');
        $retur_penjualan=DB::table('retur_penjualan')->whereMonth('created_at',$bulan)->whereYear('created_at',$tahun)->orderBy('created_at','DESC')->first();
        if(!empty($retur_penjualan)){
            $pisah=explode('/',$retur_penjualan->no_retur);
            $kode=substr($pisah[0],-6,6);
            $temp = intval($kode)+1;
            for($i = 1; 6 - strlen($temp); $i++) {
                $temp = '0' . $temp;
            }
            $no_urut=$temp;
        }
        else{
            $no_urut='0001';
        }
        return 'RJL-0019-'.Self::dateFormat($tanggal,'Ym').''.$no_urut;
    }

    public static function get_nomor_penjualan_konsinyasi($waktu){
        $tanggal=Self::dateFormat($waktu,'Y-m-d');
        $penjualan=DB::table('penjualan')->whereDate('created_at',$tanggal)->where('jenis_belanja','konsinyasi')->orderBy('created_at','DESC')->first();
        if(!empty($penjualan)){
            $pisah=explode('/',$penjualan->no_transaksi);
            $kode=substr($pisah[0],-6,6);
            $temp = intval($kode)+1;
            for($i = 1; 6 - strlen($temp); $i++) {
                $temp = '0' . $temp;
            }
            $no_urut=$temp;
        }
        else{
            $no_urut='000001';
        }
        return 'JLK-0019-'.Self::dateFormat($waktu,'Ymd').''.$no_urut;
    }


    public static function get_nomor_pembelian($tanggal){
        $bulan=Self::dateFormat($tanggal,'m');
        $tahun=Self::dateFormat($tanggal,'Y');
        $pembelian=DB::table('pembelian')->whereMonth('created_at',$bulan)->whereYear('created_at',$tahun)->orderBy('no_pembelian','DESC')->first();
        if(!empty($pembelian)){
            $pisah=explode('/',$pembelian->no_pembelian);
            $kode=substr($pisah[0],-6,6);
            $temp = intval($kode) + 1;
            for($i = 1; 6 - strlen($temp); $i++) {
                $temp = '0' . $temp;
            }
            $no_urut=$temp;
        }
        else{
            $no_urut='000001';
        }
        return 'BL-0019-'.Self::dateFormat($tanggal,'Ym').''.$no_urut;
    }

    public static function get_nomor_retur_pembelian($tanggal){
        $bulan=Self::dateFormat($tanggal,'m');
        $tahun=Self::dateFormat($tanggal,'Y');

        $retur_pembelian=DB::table('retur_pembelian')->whereMonth('created_at',$bulan)->whereYear('created_at',$tahun)->orderBy('created_at','DESC')->first();
        if(!empty($retur_pembelian)){
            $pisah=explode('/',$retur_pembelian->no_retur);
            $kode=substr($pisah[0],-6,6);
            $temp = intval($kode)+1;
            for($i = 1; 6 - strlen($temp); $i++) {
                $temp = '0' . $temp;
            }
            $no_urut=$temp;
        }
        else{
            $no_urut='000001';
        }
        return 'RBL-0019-'.Self::dateFormat($tanggal,'Ym').''.$no_urut;
    }

    public static function get_user_akses($id){
        $data=DB::table('user_akses')->select('hak_akses.*')
            ->join('hak_akses','hak_akses.id','=','user_akses.fid_hak_akses')
            ->where('user_akses.fid_anggota',$id)
            ->get();
        return $data;
    }

    public static function hitung_hari($start,$end,$format){
        $awal  = date_create($start);
        $akhir = date_create($end);
        $diff  = date_diff( $awal, $akhir );
        return $diff->$format;
    }

    public static function tanggal_posting_bunga(){
        $awal_simpanan=DB::table('transaksi')->where('fid_jenis_transaksi',4)->where('fid_status',4)->orderBy('tanggal','ASC')->first();
        $tanggal_simpanan=(!empty($awal_simpanan) ? $awal_simpanan->tanggal : null );

        $last_posting=DB::table('transaksi')->where('fid_jenis_transaksi',5)->orderBy('tanggal','DESC')->first();
        $tanggal_posting=(!empty($last_posting) ? $last_posting->tanggal : $tanggal_simpanan );

        $posisi_tanggal=date('Y-m-d', strtotime($tanggal_posting. '+ 1 day'));
        $posisi_tanggal=(!empty($last_posting) ? $posisi_tanggal : $tanggal_simpanan );

        return array('awal'=>$tanggal_simpanan,'akhir'=>$tanggal_posting,'posisi'=>$posisi_tanggal);
    }

    public static function bulan_payroll($jenis){
        if($jenis=='simpanan'){
            $awal_transaksi=DB::table('payroll_simpanan')->orderByRaw("SUBSTRING(bulan, 4, 4) desc, SUBSTRING(bulan, 1, 2) asc")->first();
            $bulan_pertama=(!empty($awal_transaksi) ? $awal_transaksi->bulan : date('m-Y') );
        }
        else{
            if($jenis=='angsuran'){
                $awal_transaksi=DB::table('transaksi')->whereIn('fid_jenis_transaksi',array(9,10,11))->where('fid_status',4)->orderBy('tanggal','ASC')->first();
            }
            else{
                $awal_transaksi=DB::table('penjualan')->where(function ($a){
                    $a->where(function ($i){
                        $i->where('jenis_belanja','toko')
                            ->Where('fid_status',3);
                    })->orWhere(function ($i){
                        $i->where('jenis_belanja','!=','toko')
                            ->Where('fid_status',4);
                    });
                })->where('fid_metode_pembayaran',3)->orderBy('penjualan.tanggal','ASC')->first();
            }
            $bulan_pertama=(!empty($awal_transaksi) ? Self::dateFormat($awal_transaksi->tanggal,'m-Y') : date('m-Y') );
        }
        $payroll_pertama=DB::table('payroll_'.$jenis)->orderByRaw("SUBSTRING(bulan, 4, 4) asc, SUBSTRING(bulan, 1, 2) asc")->first();
        $bulan_pertama=(!empty($payroll_pertama) ? $payroll_pertama->bulan : $bulan_pertama );

        $payroll_terakhir=DB::table('payroll_'.$jenis)->orderByRaw("SUBSTRING(bulan, 4, 4) desc, SUBSTRING(bulan, 1, 2) desc")->first();
        $bulan_terakhir=(!empty($payroll_terakhir) ? $payroll_terakhir->bulan : $bulan_pertama );

        $bulan=GlobalHelper2::get_bulan($bulan_terakhir);
        $bulan_payroll=date('m-Y', strtotime($bulan[2]. '+ 1 month'));
        $bulan_payroll=(!empty($payroll_terakhir) ? $bulan_payroll : $bulan_pertama );
        return array('awal'=>$bulan_pertama,
            'akhir'=>$bulan_terakhir,
            'posisi'=>$bulan_payroll);
    }

    public static function stok_barang($id, $penjualan='all'){
        $produk=Produk::find($id);
        if(!empty($produk)){
            $pembelian=ItemPembelian::where('fid_produk',$id)->where('fid_pembelian','<>',0)->sum('jumlah');
            $return_pembelian=ItemReturPembelian::where('fid_produk',$id)->where('metode','Kembali Uang')->sum('jumlah');
            $item_penjualan=ItemPenjualan::join('penjualan','penjualan.id','item_penjualan.fid_penjualan')
                ->where('item_penjualan.fid_produk',$id)
                ->where('penjualan.fid_status', 2);
            $penyesuaian = StokOpname::where('fid_produk', $id)->sum('jumlah');
            if($penjualan!='all'){
                $terjual=$item_penjualan->where('item_penjualan.fid_penjualan','<>',$penjualan)->sum('item_penjualan.jumlah');
            }
            else{
                $terjual=$item_penjualan->sum('item_penjualan.jumlah');
            }

            $retur_penjualan = ItemReturPenjualan::where('fid_produk', $id)->sum('jumlah');

            $sisa = $produk->stok_awal + ($pembelian - $return_pembelian) - ($terjual - $retur_penjualan) + $penyesuaian;

            $data = [
                'stok_awal' => $produk->stok_awal,
                'pembelian' => $pembelian,
                'retur' => $return_pembelian,
                'retur_penjualan' => $retur_penjualan,
                'terjual' => ($terjual - $retur_penjualan),
                'sisa' => $sisa,
                'penyesuaian' => $penyesuaian
            ];
        }
        else{
            $data=array('stok_awal'=>0,'pembelian'=>0,'retur'=>0,'terjual'=>0,'sisa'=>0, 'penyesuaian' => 0);
        }
        return $data;
    }

    public static function total_angsuran($id){
        $transaksi=Transaksi::find($id);
        if(!empty($transaksi)){
            $total_simpanan=350000;
            $pinjaman=Transaksi::where('fid_anggota',$transaksi->fid_anggota)
                ->whereIn('transaksi.fid_jenis_transaksi',array(9,10,11))
                ->where('fid_status',4)
                ->where('status_pinjaman','Belum Lunas')
                ->orWhere('id',$id)
                ->get();
            $total_angsuran=0;
            foreach ($pinjaman as $key => $value) {
                $list_angsuran=Angsuran::select('angsuran.*','status_angsuran.status_angsuran','status_angsuran.color')
                    ->join('status_angsuran','status_angsuran.id','=','angsuran.fid_status')
                    ->where('angsuran.fid_transaksi',$value->id)
                    ->first();
                $angsuran=$list_angsuran->angsuran_bunga+$list_angsuran->angsuran_pokok;
                $total_angsuran=$total_angsuran+$angsuran;
            }
            return array('simpanan'=>$total_simpanan,'angsuran'=>$total_angsuran);
        }
    }

    public static function angsuran_pinjaman($anggota,$jenis){
        $query=Transaksi::where('fid_anggota',$anggota)->where('fid_status',4);
        if($jenis=='all'){
            $pinjaman=$query->whereIn('transaksi.fid_jenis_transaksi',array(9,10,11))->get();
        }
        else{
            $pinjaman=$query->where('transaksi.fid_jenis_transaksi',$jenis)->get();
        }
        $total_angsuran=0;
        foreach ($pinjaman as $key => $value) {
            $list_angsuran=Angsuran::select('angsuran.*','status_angsuran.status_angsuran','status_angsuran.color')
                ->join('status_angsuran','status_angsuran.id','=','angsuran.fid_status')
                ->where('angsuran.fid_transaksi',$value->id)
                ->first();
            $angsuran=$list_angsuran->angsuran_bunga+$list_angsuran->angsuran_pokok;
            $total_angsuran=$total_angsuran+$angsuran;
        }
        return $total_angsuran;
    }

    public static function setoran_berkala($anggota,$bulan=null){
        $bulan=($bulan==null ? date('m-Y') : $bulan);
        $setoran=SetoranBerkala::where('fid_anggota',$anggota)->where('fid_status',1)->first();
        if(!empty($setoran)){
            if($setoran->mulai_bulan <= $bulan){
                if($setoran->bulan_akhir == 'Belum Ditentukan'){
                    $nominal=$setoran->nominal;
                }
                elseif($setoran->bulan_akhir >= $bulan){
                    $nominal=$setoran->nominal;
                }
                else{
                    $nominal=0;
                }
            }
            else{
                $nominal=0;
            }
        }
        else{
            $nominal=0;
        }
        return $nominal;
    }

    public static function gaji_pokok($anggota,$bulan=null){
        $bulan=($bulan==null ? date('m-Y') : $bulan);
        $bulan_lalu=Self::get_bulan($bulan)[0];
        $gaji_bulan_lalu=GajiPokok::where('fid_anggota',$anggota)->where('bulan',$bulan_lalu)->first();
        $gaji_pokok=(!empty($gaji_bulan_lalu) ? $gaji_bulan_lalu->gaji_pokok : 0 );
        return array($bulan_lalu,$gaji_pokok);
    }

    public static function validasi_pinjaman($id){
        $transaksi=Transaksi::find($id);
        if(!empty($transaksi)){
            $setengah_gaji=Self::gaji_pokok($transaksi->fid_anggota)[1]/2;
            $angsuran=Self::total_angsuran($id);
            $total_angsuran=$angsuran['angsuran']+$angsuran['simpanan'];
            if($setengah_gaji <= $total_angsuran){
                $status='<div class="alert alert-danger" role="alert">
                    Maaf anda belum bisa mengajukan pinjaman dengan total angsuran perbulan Rp '.number_format($total_angsuran,0,',','.').' (Angsuran Pinjaman + Angsuran Simpanan) karena melebihi 50% Gaji Pokok. Silahkan masukkan jumlah pinjaman dan tenor yang sesuai.
                  </div>';
            }
            else{
                $status=null;
            }
        }
        else{
            $status=null;
        }

        return $status;
    }

    public static function angsuran_belanja($anggota,$jenis){
        $belanja=DB::table('penjualan')
            ->select('penjualan.*')
            ->join('rekening_pembayaran','rekening_pembayaran.id','=','penjualan.fid_metode_pembayaran')
            ->where('penjualan.fid_anggota',$anggota)
            ->where('penjualan.jenis_belanja',$jenis)
            ->where('rekening_pembayaran.fid_metode_pembayaran',3)
            ->where('penjualan.fid_status',($jenis=='toko' ? 3 : 4))
            ->get();
        $total_angsuran=0;
        foreach ($belanja as $key => $value) {
            $angsuran=DB::table('angsuran_belanja')->where('fid_penjualan',$value->id)->where('fid_status','!=',6)->first();
            $total_angsuran=$total_angsuran+(!empty($angsuran) ? $angsuran->total_angsuran : 0 );
        }
        return $total_angsuran;
    }

    public static function total_angsuran_belanja($anggota){
        $belanja_toko=Self::angsuran_belanja($anggota,'toko');
        $belanja_online=Self::angsuran_belanja($anggota,'online');
        $belanja_konsinyasi=Self::angsuran_belanja($anggota,'konsinyasi');
        $total_angsuran=$belanja_toko+$belanja_online+$belanja_konsinyasi;
        return $total_angsuran;
    }

    public static function kategori_produk($limit){
        $data=DB::table('kategori_produk')->limit($limit)->get();
        return $data;
    }

    public static function add_verifikasi_transaksi($jenis,$id,$caption,$keterangan){
        $field=new VerifikasiTransaksi;
        $field->fid_transaksi=$id;
        $field->jenis=$jenis;
        $field->caption=$caption;
        $field->keterangan=$keterangan;
        $field->created_at=date('Y-m-d H:i:s');
        $field->created_by=Session::get('useractive')->no_anggota;
        $field->save();
    }

    public static function get_verifikasi_transaksi($id,$jenis){
        $data=VerifikasiTransaksi::select('verifikasi_transaksi.*','anggota.no_anggota','anggota.nama_lengkap')
            ->leftJoin('anggota','anggota.no_anggota','=','verifikasi_transaksi.created_by')
            ->where('verifikasi_transaksi.fid_transaksi',$id)
            ->where('verifikasi_transaksi.jenis',$jenis)
            ->get();
        return $data;
    }

    public static function get_kode_kategori($id){
        $kategori=DB::table('kategori_produk')->find($id);
        if(!empty($kategori)){
            $kode=$kategori->id;
            $cek_parent=DB::table('kategori_produk')->find($kategori->parent_id);
            if(!empty($cek_parent)){
                $kode=$cek_parent->id.'.'.$kode;
                $cek_parent=DB::table('kategori_produk')->find($cek_parent->parent_id);
                if(!empty($cek_parent)){
                    $kode=$cek_parent->id.'.'.$kode;
                }
                else{
                    $kode=$kode.'.0';
                }
            }
            else{
                $kode=$kode.'.0.0';
            }
        }
        return $kode;
    }

    public static function limitKaryawan($anggota_id)
    {
        $limit = 1500000;
        $list_id = Penjualan::where('fid_anggota', $anggota_id)->where('fid_metode_pembayaran', 3)->select('id')->get()->pluck('id')->toArray();
        $angsuran = AngsuranBelanja::select(DB::raw('a.*'))
            ->whereIn('a.fid_penjualan', $list_id)
            ->from(DB::raw('(SELECT * FROM angsuran_belanja where fid_status = 3 ORDER BY angsuran_ke ASC) a'))
            ->groupBy('a.fid_penjualan')
            ->get();
        $angsuran2 = DB::select("select * from angsuran_belanja where fid_penjualan in ( select id from penjualan where fid_anggota = '". $anggota_id ."' and fid_metode_pembayaran = 3) and fid_payroll is null GROUP BY fid_penjualan having angsuran_ke = min(angsuran_ke)");
        $total_angsuran = array_sum(array_column($angsuran2, 'total_angsuran'));


        $list_penjualan_id = $angsuran->pluck('fid_penjualan')->toArray();
        $item_retur = ItemReturPenjualan::whereHas('retur_penjualan', function ($retur) use ($anggota_id, $list_penjualan_id) {
            $retur->where('fid_anggota', $anggota_id)->whereIn('fid_penjualan', $list_penjualan_id);
        })->with(['produk'])->get();
        $total_retur = $item_retur->sum('produk.harga_jual') * $item_retur->sum('jumlah');

//        dd($limit, $angsuran->sum('total_angsuran'), $total_retur);


//        return $limit - $angsuran->sum('total_angsuran') + $total_retur;
        return $limit - $total_angsuran + $total_retur;
    }
}
