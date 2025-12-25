<?php

namespace App\Http\Controllers;

use App\Exports\SaldoSimpananExport;
use App\Exports\SimpananExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Anggota;
use App\Transaksi;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use View;
use DB;
use DateTime;
use Redirect;

class MonitoringController extends Controller
{
    public function get_anggota($jenis,$search,$status, $paginate = true){
        $query=Anggota::select('anggota.*','status_anggota.status_anggota','status_anggota.color')
            ->join('status_anggota','status_anggota.id','=','anggota.fid_status')
            ->where('no_anggota','<>',null);
        if(!empty($search)){
            $query=$query->where(function ($i) use ($search) {
                $i->where('anggota.nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('anggota.no_anggota', 'like', "%{$search}%");
            });
        }
        if($status !='all'){
            $query=$query->where('anggota.fid_status',$status);
        }
        else{
            $query=$query->whereIn('anggota.fid_status',array(1,2,3,5));
        }

        if ($paginate === true) {
            $result = $query->orderBy('anggota.nama_lengkap')->paginate(10);
            foreach ($result as $key => $value) {
                if ($jenis == 'saldo_simpanan') {
                    $result[$key]->simpanan_pokok = GlobalHelper::saldo_tabungan($value->no_anggota, 1); //Simpanan Pokok
                    $result[$key]->simpanan_wajib = GlobalHelper::saldo_tabungan($value->no_anggota, 2); //Simpanan Wajib
                    $result[$key]->simpanan_sukarela = GlobalHelper::saldo_tabungan($value->no_anggota, 'Simpanan Sukarela'); //Simpanan Sukarela
                    $result[$key]->simpanan_hari_raya = GlobalHelper::saldo_tabungan($value->no_anggota, 'Simpanan Hari Raya'); //Simpanan Hari Raya
                    $result[$key]->total_simpanan = GlobalHelper::saldo_tabungan($value->no_anggota, 'Total Simpanan'); //Total Simpanan
                } elseif ($jenis == 'sisa_pinjaman') {
                    $result[$key]->sisa_jangka_panjang = GlobalHelper::sisa_pinjaman($value->no_anggota, 9); //Sisa Jangka Panjang
                    $result[$key]->sisa_jangka_pendek = GlobalHelper::sisa_pinjaman($value->no_anggota, 10); //Sisa Jangka Pendek
                    $result[$key]->sisa_barang = GlobalHelper::sisa_pinjaman($value->no_anggota, 11); //Sisa Barang

                    $result[$key]->tenor_jangka_panjang = GlobalHelper::sisa_tenor_pinjaman($value->no_anggota, 9); //Sisa Jangka Panjang
                    $result[$key]->tenor_jangka_pendek = GlobalHelper::sisa_tenor_pinjaman($value->no_anggota, 10); //Sisa Jangka Pendek
                    $result[$key]->tenor_barang = GlobalHelper::sisa_tenor_pinjaman($value->no_anggota, 11); //Sisa Barang
                }
            }
            if (!empty($search)) {
                $result->withPath($jenis . '?status=' . $status . '&search=' . $search);
            } else {
                $result->withPath($jenis . '?status=' . $status);
            }
        } else {
            $result = $query->orderBy('anggota.nama_lengkap')->get();
            $saldo = GlobalHelper::saldo_simpanan($result->pluck('no_anggota')->ToArray());
            foreach ($result as $value) {
                $value->no_anggota = strtoupper($value->no_anggota);
                $value->simpanan_pokok = $saldo[$value->no_anggota . '_1'] ?? 0;
                $value->simpanan_wajib = $saldo[$value->no_anggota . '_2'] ?? 0;
                $value->simpanan_sukarela = ($saldo[$value->no_anggota . '_3'] ?? 0) + ($saldo[$value->no_anggota . '_5'] ?? 0) + ($saldo[$value->no_anggota . '_6'] ?? 0);
                $value->simpanan_hari_raya = ($saldo[$value->no_anggota . '_4'] ?? 0) + ($saldo[$value->no_anggota . '_7'] ?? 0);
                $value->total_simpanan = $value->simpanan_pokok + $value->simpanan_wajib + $value->simpanan_sukarela + $value->simpanan_hari_raya;
            }
        }
        return $result;
    }

    public function saldo_simpanan(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,39);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $status=(!empty($request->status) ? $request->status : 'all' );
            $data['saldo']=$this->get_anggota('saldo_simpanan',$search,$status);
            $data['status']=DB::table('status_anggota')->get();
            return view('monitoring.saldo_simpanan')
                ->with('data',$data)
                ->with('status',$status)
                ->with('search',$search);
        }
    }

    public function saldo_simpanan_cetak(Request $request)
    {
        $search=(!empty($request->search) ? $request->search : null);
        $status=(!empty($request->status) ? $request->status : 'all' );
        $data = $this->get_anggota('saldo_simpanan', $search, $status, false);
        return Excel::download(new SimpananExport($data), 'saldo_simpanan.xlsx');
//        return view('monitoring.saldo_simpanan_cetak')
//            ->with('data',$data)
//            ->with('status',$status)
//            ->with('search',$search);
    }

    public function sisa_pinjaman(Request $request){
        $data['otoritas']=GlobalHelper::otoritas_modul(Session::get('useractive')->hak_akses,40);
        if($data['otoritas']['view']=='N'){
            return view('404');
        }
        else{
            $search=(!empty($request->search) ? $request->search : null);
            $status=(!empty($request->status) ? $request->status : 'all' );
            $data['sisa_pinjaman']=$this->get_anggota('sisa_pinjaman',$search,$status);
            $data['status']=DB::table('status_anggota')->get();
            return view('monitoring.sisa_pinjaman')
                ->with('data',$data)
                ->with('status',$status)
                ->with('search',$search);
        }
    }

    public function update_saldo_simpanan(Request $request)
    {
        $simpanan = Transaksi::find($request->input('id'));
        $simpanan->tanggal = unformat_date($request->input('tanggal'));
        $simpanan->nominal = unformat_number($request->input('nominal'));
        $simpanan->keterangan = $request->input('keterangan');
        $simpanan->save();
    }

    public function delete_saldo_simpanan(Request $request)
    {
        $simpanan = Transaksi::find($request->input('id'));
        $simpanan->delete();
    }

    public function detail_saldo_simpanan(Request $request)
    {
        $no_anggota = $request->input('no_anggota') ?? '';
        $anggota = Anggota::where('no_anggota', $no_anggota)->first();

        $tanggal_awal = $request->input('tanggal_awal') ?? date('01-m-Y');
        $tanggal_akhir = $request->input('tanggal_akhir') ?? date('t-m-Y');
        if (!empty($anggota)) $tanggal_awal = '';
        if (!empty($anggota)) $tanggal_akhir = '';

        $jenis = $request->input('jenis') ?? 'pokok';
        $list_jenis = [
            'pokok' => 'Simpanan Pokok',
            'wajib' => 'Simpanan Wajib',
            'sukarela' => 'Simpanan Sukarela',
            'hari_raya' => 'Simpanan Hari Raya',
        ];

        $data = [];
        if ($jenis == 'pokok') $data = GlobalHelper::detail_tabungan($anggota->no_anggota ?? '', 1, $tanggal_awal, $tanggal_akhir); //Simpanan Pokok
        if ($jenis == 'wajib') $data = GlobalHelper::detail_tabungan($anggota->no_anggota ?? '', 2, $tanggal_awal, $tanggal_akhir); //Simpanan Wajib
        if ($jenis == 'sukarela') $data = GlobalHelper::detail_tabungan($anggota->no_anggota ?? '', 'Simpanan Sukarela', $tanggal_awal, $tanggal_akhir); //Simpanan Sukarela
        if ($jenis == 'hari_raya') $data = GlobalHelper::detail_tabungan($anggota->no_anggota ?? '', 'Simpanan Hari Raya', $tanggal_awal, $tanggal_akhir); //Simpanan Hari Raya

        $result = [
            'anggota' => $anggota,
            'no_anggota' => $no_anggota,
            'list_jenis' => $list_jenis,
            'jenis' => $jenis,
            'data' => $data,
            'tanggal_awal' => $tanggal_awal,
            'tanggal_akhir' => $tanggal_akhir
        ];
        if ($request->has('ajax')) return $result;

        return view('monitoring.detail_saldo_simpanan', $result);
    }

    public function export_saldo_simpanan(Request $request)
    {
        $request->merge(['ajax' => 1]);
        $result = $this->detail_saldo_simpanan($request);

        return Excel::download(new SaldoSimpananExport($result['data'], $request), 'saldo_simpanan'. Str::slug($request->jenis) .'.xlsx');
    }
}
