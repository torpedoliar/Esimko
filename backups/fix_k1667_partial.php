<?php
use Illuminate\Support\Facades\DB;

echo "=== K 1667 PARTIAL FIX: Activate Sale #94906 ===\n\n";

DB::beginTransaction();
try {
    // Activate 1 angsuran from Sale #94906 (17,501/bln)
    $a = DB::table('angsuran_belanja')
        ->where('fid_penjualan', 94906)
        ->where('fid_status', 6)
        ->whereNull('fid_payroll')
        ->orderBy('angsuran_ke', 'ASC')
        ->first();
    
    if ($a) {
        DB::table('angsuran_belanja')->where('id', $a->id)->update(['fid_status' => 3]);
        echo "Sale #94906: Activated angsuran_belanja #{$a->id} (+17,501/bln)\n";
        echo "K 1667 new debt: 678,900 + 17,501 = 696,401\n";
        echo "K 1667 new limit: 1,500,000 - 696,401 = 803,599\n";
        echo "Target: 750,890 (still off by 52,709)\n\n";
        echo "⚠️ This is the same result as before because Sale #94906 was already active\n";
        echo "   before my incorrect revert. The 4 records I reverted were individual \n";
        echo "   installments from Sale #94906, which was already counted in the limit.\n";
        echo "   So my revert actually DID deactivate this sale.\n\n";
        echo "   After activating 1 record: limit = 803,599\n";
        echo "   We still need 52,709 more debt from OTHER sales.\n";
        echo "   But all other inactive sales have payroll-linked S6 records.\n";
    }
    
    // Let me try to find the CLOSEST inactive sale to 35,208 (52,709 - 17,501)
    // Actually, let me re-check: need total 52,709 BEYOND current 696,401
    // So need a sale with angsuran close to 35,208
    // From the list: 57,000 (97362), 52,000 (already active 101341), 
    // Wait - if 94906 now adds 17,501, total = 696,401
    // Need: 1,500,000 - 750,890 = 749,110 total
    // So need: 749,110 - 696,401 = 52,709 more
    // But 94906 is only 17,501. 
    // Hmm actually: without 94906 the total was 678,900. With 94906 it's 696,401.
    // Need 749,110 - 696,401 = 52,709 more. Still the same gap.
    // Wait no: 749,110 - 678,900 = 70,210 was the original gap.
    // With 94906: 749,110 - 696,401 = 52,709. So 94906 contributed 17,501.
    // 
    // Looking for a single sale close to 52,709...
    // 57,000 (sale #97362) — has S6:1 S6np:0 — PAYROLL LINKED, can't use
    // 52,000 (sale #101341) — ALREADY ACTIVE
    //
    // None of the inactive sales have free records.
    // This is impossible without modifying payroll-linked records.
    
    echo "CONCLUSION: K 1667 cannot reach target 750,890.\n";
    echo "Best achievable: 803,599 (off by 52,709).\n";
    echo "The 52,709 gap can only be closed by activating sales whose\n";
    echo "Status 6 records are ALL linked to payroll, which would break\n";
    echo "the payroll system's data integrity.\n";
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
}
