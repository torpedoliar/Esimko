<?php
// REVERT FAILED CUTOFF FIX (FEB 15) - TRY 2
// REVERT ANGSURAN STATUS FROM 3 (PENDING) BACK TO 6 (LUNAS/IGNORED)
// FILTER BY OLD TRANSACTION DATE TO AVOID TOUCHING NEW SALES

use App\Penjualan;
use App\AngsuranBelanja;
use Illuminate\Support\Facades\DB;

$targets = ['K 0558', 'K 1031', 'K 1540', 'K 1154', 'K 1667', 'K 2008', 'K 1606'];
$cutoffDate = '2026-02-01'; // Safe date. All "Missed" items are older than this.

echo "--- REVERTING FIX (TARGET: PRE-FEB 2026 ITEMS) ---\n";

DB::beginTransaction();
try {
    foreach ($targets as $id) {
        $count = 0;
        
        // Find Old Transactions for this member that are 'Unpaid' (St 2 or 4)
        $transaksiOld = Penjualan::where('fid_anggota', $id)
            ->whereIn('fid_status', [2, 4])
            ->where('tanggal', '<', $cutoffDate)
            ->get();
            
        foreach($transaksiOld as $t) {
            $angsuran = AngsuranBelanja::where('fid_penjualan', $t->id)->first();
            
            // If Angsuran exists and is Status 3 (Pending), it's likely the one we just fixed.
            // Revert it to 6.
            if ($angsuran && $angsuran->fid_status == 3) {
                $angsuran->fid_status = 6;
                $angsuran->save();
                $count++;
            }
        }
        
        echo "=> $id : Reverted $count transactions to Status 6.\n";
    }
    
    DB::commit();
    echo "SUCCESS: Revert complete.\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
}
