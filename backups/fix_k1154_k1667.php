<?php
use App\AngsuranBelanja;
use Illuminate\Support\Facades\DB;

echo "=== ACTIVATING ADDITIONAL DEBT FOR K 1154 & K 1667 ===\n\n";

DB::beginTransaction();
try {
    // K 1154: Activate 12 records (132,000 total)
    $ids_k1154 = [278736, 278734, 278733, 278732, 278731, 278730, 278729, 264858, 264859, 264860, 264861, 278735];
    $updated1 = DB::table('angsuran_belanja')
        ->whereIn('id', $ids_k1154)
        ->where('fid_status', 6)
        ->whereNull('fid_payroll')
        ->update(['fid_status' => 3]);
    echo "K 1154: Updated $updated1 records to Status 3 (132,000 additional debt)\n";
    
    // K 1667: Activate 4 records (70,004 total)
    $ids_k1667 = [276527, 276528, 276529, 276530];
    $updated2 = DB::table('angsuran_belanja')
        ->whereIn('id', $ids_k1667)
        ->where('fid_status', 6)
        ->whereNull('fid_payroll')
        ->update(['fid_status' => 3]);
    echo "K 1667: Updated $updated2 records to Status 3 (70,004 additional debt)\n";
    
    DB::commit();
    echo "\n✅ SUCCESS\n";
    echo "K 1154 projected limit: ~421,300 (target 414,314)\n";
    echo "K 1667 projected limit: ~751,096 (target 750,890)\n";
} catch (\Exception $e) {
    DB::rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
}
