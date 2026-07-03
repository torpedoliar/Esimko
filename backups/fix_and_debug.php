<?php
// Fix K 0551 & Debug K 1308
use App\Penjualan;
use App\AngsuranBelanja;
use App\Helpers\GlobalHelper;

echo "--- EXECUTION STARTED ---\n";

// --- PART 1: FIX K 0551 (Pay Consignment) ---
echo "\n--- [1] FIX K 0551 ---\n";
$id_consign = 100331; // Rp 210.000
$t = Penjualan::find($id_consign);
if ($t && $t->fid_status != 6) {
    echo "Updating ID $id_consign to PAID (Status 6)...\n";
    $t->fid_status = 6;
    $t->save();
    
    // Update Angsuran Belanja too
    AngsuranBelanja::where('fid_penjualan', $id_consign)->update(['fid_status' => 6]);
    echo "Done.\n";
} else {
    echo "ID $id_consign already PAID or not found.\n";
}

echo "New Limit K 0551: " . number_format(GlobalHelper::limitKaryawan('K 0551')) . "\n";


// --- PART 2: DEBUG K 1308 ---
echo "\n--- [2] DEBUG K 1308 ---\n";
$anggota_id = 'K 1308';
$limit = GlobalHelper::limitKaryawan($anggota_id);
echo "System Limit: " . number_format($limit) . "\n";
echo "Calculated Debt: " . number_format(1500000 - $limit) . "\n";

// Manual Query
$txs = Penjualan::where('fid_anggota', $anggota_id)
    ->where('fid_metode_pembayaran', 3)
    ->whereIn('fid_status', [2, 4])
    ->whereHas('angsuran_belanja', function ($q) {
        $q->where('fid_status', 3);
    })
    ->get();

echo "Found " . $txs->count() . " active debt transactions:\n";
foreach ($txs as $p) {
    echo "ID: " . $p->id . " | Date: " . $p->tanggal . " | Total: " . number_format($p->total_pembayaran) . "\n";
}

// Check Ignored Transaction 99292 (131k)
$check_id = 99292;
$check = Penjualan::find($check_id);
if ($check) {
    echo "\nDiagnostic ID $check_id:\n";
    echo "Status: " . $check->fid_status . "\n";
    echo "Metode: " . $check->fid_metode_pembayaran . "\n";
    $hasAngsuran = $check->angsuran_belanja()->where('fid_status', 3)->exists();
    echo "Has Angsuran (Status 3): " . ($hasAngsuran ? "YES" : "NO") . "\n";
}
