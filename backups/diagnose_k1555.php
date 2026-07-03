<?php
use App\Penjualan;
use App\AngsuranBelanja;
use App\ItemReturPenjualan;
use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSIS LIMIT K 1555 ===\n\n";
echo "Formula: Limit = 1,500,000 - SUM(penjualan.angsuran for sales with status 3 angsuran) + total_retur\n\n";

$id = 'K 1555';
$expected_limit = 1377500;
$actual_limit = 1031864; // From user report
$gap = $expected_limit - $actual_limit;

echo "Member: $id\n";
echo "Expected Limit: " . number_format($expected_limit) . "\n";
echo "Reported Limit: " . number_format($actual_limit) . "\n";
echo "Discrepancy (Excess Debt): " . number_format($gap) . "\n\n";

// Step 1: Find sales contributing to debt
$penjualan_with_debt = Penjualan::where('fid_anggota', $id)
    ->where('fid_metode_pembayaran', 3)
    ->whereIn('fid_status', [2, 4])
    ->whereHas('angsuran_belanja', function ($q) {
        $q->where('fid_status', 3);
    })
    ->get();

echo "--- Sales Contributing to Debt (Active) ---\n";
$total_hutang = 0;
foreach ($penjualan_with_debt as $p) {
    $val = $p->angsuran;
    if (is_null($val) || $val == 0) {
        $tenor = $p->tenor > 0 ? $p->tenor : 1;
        $val = $p->total_pembayaran / $tenor;
    }
    
    $s3count = AngsuranBelanja::where('fid_penjualan', $p->id)->where('fid_status', 3)->count();
    $s6count = AngsuranBelanja::where('fid_penjualan', $p->id)->where('fid_status', 6)->count();
    
    echo "  Sale #{$p->id} | No: {$p->no_transaksi} | Date: {$p->tanggal} | Angsuran/bln: " . number_format($val) . 
         " | Total: " . number_format($p->total_pembayaran) . 
         " | Tenor: {$p->tenor} | S3: $s3count\n";
    $total_hutang += $val;
}

// Step 2: Retur
$list_penjualan_id = $penjualan_with_debt->pluck('id')->toArray();
$total_retur = 0;
if (!empty($list_penjualan_id)) {
    $item_retur = ItemReturPenjualan::whereHas('retur_penjualan', function ($retur) use ($id, $list_penjualan_id) {
        $retur->where('fid_anggota', $id)->whereIn('fid_penjualan', $list_penjualan_id);
    })->with(['produk'])->get();
    
    foreach ($item_retur as $item) {
        $total_retur += ($item->produk->harga_jual * $item->jumlah);
    }
}

$start_limit = 1500000;
$calculated_limit = $start_limit - $total_hutang + $total_retur;

echo "\n--- Summary ---\n";
echo "Total Active Debt (Sum Angsuran/bln): " . number_format($total_hutang) . "\n";
echo "Total Retur: " . number_format($total_retur) . "\n";
echo "Calculated Limit: 1,500,000 - " . number_format($total_hutang) . " + " . number_format($total_retur) . " = " . number_format($calculated_limit) . "\n";

echo "\n--- Analysis ---\n";
if (abs($calculated_limit - $actual_limit) < 1000) {
    echo "Diagnosis MATCHES Reported Limit ($actual_limit).\n";
    echo "The user expects $expected_limit (Debt = " . (1500000 - $expected_limit) . ").\n";
    echo "Actual Debt is " . number_format($total_hutang) . ".\n";
    echo "Difference: " . number_format($total_hutang - (1500000 - $expected_limit)) . ".\n";
    echo "This difference likely comes from specific old transactions that are still active.\n";
} else {
    echo "Diagnosis ($calculated_limit) DOES NOT MATCH Reported Limit ($actual_limit).\n";
    echo "There might be another factor.\n";
}
