<?php
// Debug Limit K 0551 (Run via: php artisan tinker C:\IIS\Esimko\debug_limit_k0551.php)

use App\Penjualan;
use App\AngsuranBelanja;
use App\Helpers\GlobalHelper;

$anggota_id = 'K 0551';
echo "--- DEBUG K 0551 ---\n";

// 1. REPLICATE LOGIC FROM GlobalHelper
// Using correct Namespace App\Penjualan
$penjualan_with_debt = Penjualan::where('fid_anggota', $anggota_id)
    ->where('fid_metode_pembayaran', 3)
    ->whereIn('fid_status', [2, 4])
    ->whereHas('angsuran_belanja', function ($q) {
        $q->where('fid_status', 3);
    })
    ->get();

echo "Found " . $penjualan_with_debt->count() . " active debt transactions.\n";

$total_hutang = 0;
foreach ($penjualan_with_debt as $p) {
    echo "ID: " . $p->id . " | Total: " . number_format($p->total_pembayaran) . " | Angsuran: " . $p->angsuran . " | Tenor: " . $p->tenor . "\n";
    
    $val = $p->angsuran;
    if (is_null($val) || $val == 0) {
        $tenor = $p->tenor > 0 ? $p->tenor : 1;
        $val = $p->total_pembayaran / $tenor;
        echo " -> [CALC] Use Total/Tenor: $val\n";
    } else {
        echo " -> [DIRECT] Use Angsuran: $val\n";
    }
    $total_hutang += $val;
}

echo "Total Hutang Calculated: " . number_format($total_hutang) . "\n";
echo "Limit System: " . number_format(GlobalHelper::limitKaryawan($anggota_id)) . "\n";

// 2. CHECK SPECIFIC IGNORED TRANSACTION
$ignored_id = 101403;
$ignored = Penjualan::find($ignored_id);
if ($ignored) {
    echo "\n--- DIAGNOSTIC ID $ignored_id ---\n";
    echo "Metode: " . $ignored->fid_metode_pembayaran . " (Target: 3)\n";
    echo "Status: " . $ignored->fid_status . " (Target: 2 or 4)\n";
    
    $hasAngsuran = $ignored->angsuran_belanja()->where('fid_status', 3)->exists();
    echo "Has Angsuran Belanja (Status 3): " . ($hasAngsuran ? "YES" : "NO") . "\n";
    
    // Check if Query finds it individually
    $queryFind = Penjualan::where('id', $ignored_id)
        ->where('fid_anggota', $anggota_id)
        ->where('fid_metode_pembayaran', 3)
        ->whereIn('fid_status', [2, 4])
        ->whereHas('angsuran_belanja', function ($q) {
            $q->where('fid_status', 3);
        })->count();
    echo "Query Match Check: " . ($queryFind > 0 ? "MATCHED" : "IGNORED") . "\n";
} else {
    echo "ID $ignored_id NOT FOUND in Model.\n";
}
