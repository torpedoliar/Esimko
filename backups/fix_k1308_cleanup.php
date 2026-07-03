<?php
// CLEANUP K 1308 (SUSMADI)
use App\Penjualan;
use App\AngsuranBelanja;
use App\Helpers\GlobalHelper;

echo "--- CLEANUP K 1308 STARTED ---\n";

// Target: All Active Debts (Status 2,3,4) for K 1308
$transactions = Penjualan::where('fid_anggota', 'K 1308')
    ->whereIn('fid_status', [2, 3, 4])
    ->get();

echo "Found " . $transactions->count() . " active/zombie transactions.\n";

foreach ($transactions as $t) {
    echo "Processing ID {$t->id} ({$t->no_transaksi})... ";
    
    // 1. Set Parent to PAID (6)
    $t->fid_status = 6;
    $t->save();
    
    // 2. Set Children to PAID (6)
    AngsuranBelanja::where('fid_penjualan', $t->id)->update(['fid_status' => 6]);
    echo "MARKED LUNAS.\n";
}

echo "\n--- VERIFICATION ---\n";
echo "Limit System Now: " . number_format(GlobalHelper::limitKaryawan('K 1308')) . "\n";
echo "(Target: 1,500,000)\n";
