<?php
use App\Penjualan;
use App\AngsuranBelanja;
use App\ItemReturPenjualan;
use Illuminate\Support\Facades\DB;

echo "=== REAL LIMIT FORMULA DIAGNOSIS: K 1154 & K 1667 ===\n\n";
echo "Formula: Limit = 1,500,000 - SUM(penjualan.angsuran for sales with status 3 angsuran) + total_retur\n\n";

$members = [
    'K 1154' => ['target' => 414314],
    'K 1667' => ['target' => 750890],
];

foreach ($members as $id => $info) {
    echo "=========================================\n";
    echo "=== $id (Target Limit: " . number_format($info['target']) . ") ===\n";
    echo "=========================================\n\n";
    
    // Step 1: Find penjualan with at least one Status 3 angsuran
    $penjualan_with_debt = Penjualan::where('fid_anggota', $id)
        ->where('fid_metode_pembayaran', 3)
        ->whereIn('fid_status', [2, 4])
        ->whereHas('angsuran_belanja', function ($q) {
            $q->where('fid_status', 3);
        })
        ->get();
    
    echo "Sales with active debt (has Status 3 angsuran):\n";
    $total_hutang = 0;
    foreach ($penjualan_with_debt as $p) {
        $val = $p->angsuran;
        if (is_null($val) || $val == 0) {
            $tenor = $p->tenor > 0 ? $p->tenor : 1;
            $val = $p->total_pembayaran / $tenor;
        }
        
        $s3count = AngsuranBelanja::where('fid_penjualan', $p->id)->where('fid_status', 3)->count();
        $s6count = AngsuranBelanja::where('fid_penjualan', $p->id)->where('fid_status', 6)->count();
        
        echo "  Sale #{$p->id} | No: {$p->no_transaksi} | angsuran/bln: " . number_format($val) . 
             " | tenor: {$p->tenor} | total: " . number_format($p->total_pembayaran) . 
             " | S3: $s3count, S6: $s6count\n";
        $total_hutang += $val;
    }
    
    // Step 2: Retur
    $list_penjualan_id = $penjualan_with_debt->pluck('id')->toArray();
    $item_retur = ItemReturPenjualan::whereHas('retur_penjualan', function ($retur) use ($id, $list_penjualan_id) {
        $retur->where('fid_anggota', $id)->whereIn('fid_penjualan', $list_penjualan_id);
    })->with(['produk'])->get();
    
    $total_retur = 0;
    foreach ($item_retur as $item) {
        $total_retur += ($item->produk->harga_jual * $item->jumlah);
    }
    
    $calcLimit = 1500000 - $total_hutang + $total_retur;
    
    echo "\nTotal Hutang (sum angsuran/bln): " . number_format($total_hutang) . "\n";
    echo "Total Retur: " . number_format($total_retur) . "\n";
    echo "Calculated Limit: 1,500,000 - " . number_format($total_hutang) . " + " . number_format($total_retur) . " = " . number_format($calcLimit) . "\n";
    echo "Target Limit: " . number_format($info['target']) . "\n";
    echo "Gap to target: " . number_format($calcLimit - $info['target']) . "\n";
    
    // Step 3: Find all penjualan with NO Status 3 angsuran (all Status 6)
    echo "\n--- Sales WITHOUT active debt (all angsuran Status 6, candidates to activate): ---\n";
    $penjualan_no_debt = Penjualan::where('fid_anggota', $id)
        ->where('fid_metode_pembayaran', 3)
        ->whereIn('fid_status', [2, 4])
        ->whereDoesntHave('angsuran_belanja', function ($q) {
            $q->where('fid_status', 3);
        })
        ->whereHas('angsuran_belanja', function ($q) {
            $q->where('fid_status', 6)->whereNull('fid_payroll');
        })
        ->get();
    
    $target_debt = 1500000 - $info['target'] + $total_retur;
    $need_more = $target_debt - $total_hutang;
    echo "Need " . number_format($need_more) . " more hutang to reach target limit.\n\n";
    
    foreach ($penjualan_no_debt as $p) {
        $val = $p->angsuran;
        if (is_null($val) || $val == 0) {
            $tenor = $p->tenor > 0 ? $p->tenor : 1;
            $val = $p->total_pembayaran / $tenor;
        }
        $s6np = AngsuranBelanja::where('fid_penjualan', $p->id)->where('fid_status', 6)->whereNull('fid_payroll')->count();
        echo "  Sale #{$p->id} | No: {$p->no_transaksi} | angsuran/bln: " . number_format($val) . 
             " | S6-nopayroll: $s6np\n";
    }
    
    // Step 4: Also check penjualan with debt but more S6 records available
    echo "\n--- Already-active sales with spare S6 records (for reference): ---\n";
    foreach ($penjualan_with_debt as $p) {
        $s6np = AngsuranBelanja::where('fid_penjualan', $p->id)->where('fid_status', 6)->whereNull('fid_payroll')->count();
        if ($s6np > 0) {
            echo "  Sale #{$p->id} | No: {$p->no_transaksi} | S6-nopayroll: $s6np (already active, adding S3 won't change limit)\n";
        }
    }
    
    echo "\n\n";
}
