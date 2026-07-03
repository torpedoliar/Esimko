<?php

namespace App\Http\Controllers;

use App\Anggota;
use App\AngsuranBelanja;
use App\Helpers\GlobalHelper;
use App\ItemPenjualan;
use App\Penjualan;
use App\Produk;
use App\RekeningPembayaran;
use Illuminate\Http\Request;

class PenjualanBaruController extends Controller
{
    public function index(Request $request)
    {
        $no_transaksi = $request->input('no_transaksi') ?? '';
        $penjualan = Penjualan::where('no_transaksi', $no_transaksi)->first();
        if (!empty($penjualan) && $penjualan->fid_status == 2) {
            return redirect('pos/penjualan_baru')
                ->with('message','Penjualan sudah selesai')
                ->with('message_type','error');
        }
        $metode_pembayaran = RekeningPembayaran::where('jenis_transaksi','like','%belanja%')->get();
        return view('pos.penjualan_baru.index', compact(
            'metode_pembayaran', 'no_transaksi', 'penjualan'
        ));
    }

    public function create(Request $request)
    {
        $request->merge(['kasir' => session('useractive')->no_anggota]);
        $request->merge(['created_by' => session('useractive')->no_anggota]);
        $request->merge(['fid_status' => 5]);
        $request->merge(['jenis_belanja' => 'toko']);
        $request->merge(['tanggal' => date('Y-m-d')]);
        $request->merge(['no_transaksi' => GlobalHelper::get_nomor_penjualan(date('Y-m-d H:i:s'))]);
        $no_anggota = $request->input('no_anggota') ?? '';
        $no_anggota = strtoupper($no_anggota);
        $request->merge(['fid_anggota' => $no_anggota === '' ? '0000' : $no_anggota]);
        return Penjualan::create($request->all());
    }

    public function update(Request $request, $id)
    {
        if ($request->has('no_anggota')) {
            $no_anggota = $request->input('no_anggota') ?? '';
            $no_anggota = strtoupper($no_anggota);
            $request->merge(['fid_anggota' => $no_anggota === '' ? '0000' : $no_anggota]);
        }
        if ($request->has('fid_metode_pembayaran')) {
            if ($request->input('fid_metode_pembayaran') == 3) {
                // Fix: Hitung angsuran bulanan (Total / Tenor) agar tidak langsung lunas
                $total = unformat_number($request->input('total_pembayaran'));
                $tenor = $request->input('tenor') ?? 1; // Default 1 jika null
                $angsuran_bulanan = ($tenor > 0) ? ($total / $tenor) : $total;

                // Fix: Simpan ke tabel penjualan agar tidak NULL (Root Cause Fix)
                $request->merge([
                    'angsuran' => $angsuran_bulanan,
                    'tenor' => $tenor
                ]);

                AngsuranBelanja::updateOrCreate([
                    'fid_penjualan' => $id,
                    'angsuran_ke' => 1,
                    'fid_status' => 3
                ], [
                    'total_angsuran' => $angsuran_bulanan
                ]);
            } else {
                AngsuranBelanja::where('fid_penjualan', $id)->delete();
            }
        }
        $penjualan = Penjualan::find($id);
        $penjualan->update($request->all());
    }

    public function delete(Request $request, $id)
    {
        $penjualan = Penjualan::find($id);
        if ($penjualan) {
            // Simpan alasan pembatalan dan ubah status ke dibatalkan (3)
            $penjualan->update([
                'fid_status' => 3,
                'alasan_batal' => $request->input('alasan_batal'),
                'dibatalkan_oleh' => session('useractive')->no_anggota,
                'tanggal_batal' => now(),
            ]);
        }
        
        // Hapus items terkait
        AngsuranBelanja::where('fid_penjualan', $id)->delete();
        ItemPenjualan::where('fid_penjualan', $id)->delete();
        
        return response()->json(['success' => true]);
    }

    public function list_tunda()
    {
        $list_penjualan = Penjualan::where('fid_status', 5)->get();
        return view('pos.penjualan_baru._list_tunda', compact('list_penjualan'));
    }

    public function cari_anggota(Request $request)
    {
        $no_anggota = strtoupper(trim($request->input('no_anggota')));
        
        // Try exact match first (uses index)
        $anggota = Anggota::select('id', 'no_anggota', 'nama_lengkap', 'avatar')
            ->where('no_anggota', $no_anggota)
            ->first();
        
        // If not found, try with spaces removed (slower but handles format variations)
        if (empty($anggota)) {
            $no_anggota_clean = str_replace(' ', '', $no_anggota);
            $anggota = Anggota::select('id', 'no_anggota', 'nama_lengkap', 'avatar')
                ->whereRaw("REPLACE(no_anggota, ' ', '') = ?", [$no_anggota_clean])
                ->first();
        }
        
        if (!empty($anggota)) return $anggota;
        return ['error' => 'No. Anggota tidak ditemukan !'];
    }

    /**
     * Customer Display for Dual Monitor POS
     * Auto-refreshes every 2 seconds to sync with cashier
     */
    public function customer_display($id = null)
    {
        $penjualan = null;
        $items = collect([]);
        
        if ($id) {
            $penjualan = Penjualan::with('anggota')->find($id);
            if ($penjualan) {
                $items = ItemPenjualan::with('produk')
                    ->where('fid_penjualan', $penjualan->id)
                    ->get();
            }
        }
        
        return view('pos.penjualan_baru.customer_display', compact('penjualan', 'items'));
    }

    public function cetak_struk($id)
    {
        $penjualan = Penjualan::find($id);
        if (empty($penjualan)) abort(404);
        return view('pos.penjualan_baru.cetak_struk', compact('penjualan'));
    }

    public function cari_produk(Request $request)
    {
        // Optimized search with limit and simplified stok
        $nama = $request->input('nama');
        
        // Get all columns needed by view + relationships (foto for foto_url accessor)
        $produk = Produk::with(['satuan_barang', 'foto'])
            ->where(function($query) use ($nama) {
                $query->where('nama_produk', 'like', '%'. $nama .'%')
                      ->orWhere('kode', 'like', '%'. $nama .'%');
            })
            ->limit(8)
            ->get();
        
        // Batch get stok for all products in one set of queries instead of N+1
        if ($produk->count() > 0) {
            $produkIds = $produk->pluck('id')->toArray();
            
            // Batch query pembelian
            $pembelian = \DB::table('item_pembelian')
                ->select('fid_produk', \DB::raw('SUM(jumlah) as total'))
                ->whereIn('fid_produk', $produkIds)
                ->groupBy('fid_produk')
                ->pluck('total', 'fid_produk')
                ->toArray();
            
            // Batch query penjualan
            $penjualan = \DB::table('item_penjualan')
                ->join('penjualan', 'penjualan.id', 'item_penjualan.fid_penjualan')
                ->select('fid_produk', \DB::raw('SUM(item_penjualan.jumlah) as total'))
                ->whereIn('fid_produk', $produkIds)
                ->where('penjualan.fid_status', 2)
                ->groupBy('fid_produk')
                ->pluck('total', 'fid_produk')
                ->toArray();
            
            foreach ($produk as $item) {
                $stok_pembelian = $pembelian[$item->id] ?? 0;
                $stok_penjualan = $penjualan[$item->id] ?? 0;
                $item->stok = [
                    'sisa' => $item->stok_awal + $stok_pembelian - $stok_penjualan
                ];
            }
        }
        
        return view('pos.penjualan_baru._list_produk', compact('produk'));
    }
}
