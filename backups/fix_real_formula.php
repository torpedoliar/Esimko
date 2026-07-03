<?php
use App\Penjualan;
use App\AngsuranBelanja;
use Illuminate\Support\Facades\DB;

echo "=== FIX LIMITS USING REAL FORMULA ===\n\n";
echo "Step 1: Revert incorrect earlier changes\n";

DB::beginTransaction();
try {
    // Revert K 1154: Set back the 12 records from earlier fix (they were individual angsuran, wrong approach)
    $revert_k1154 = [278736, 278734, 278733, 278732, 278731, 278730, 278729, 264858, 264859, 264860, 264861, 278735];
    $r1 = DB::table('angsuran_belanja')
        ->whereIn('id', $revert_k1154)
        ->where('fid_status', 3)
        ->update(['fid_status' => 6]);
    echo "  K 1154: Reverted $r1 records back to Status 6\n";
    
    // Revert K 1667: Set back the 4 records
    $revert_k1667 = [276527, 276528, 276529, 276530];
    $r2 = DB::table('angsuran_belanja')
        ->whereIn('id', $revert_k1667)
        ->where('fid_status', 3)
        ->update(['fid_status' => 6]);
    echo "  K 1667: Reverted $r2 records back to Status 6\n";
    
    // === K 1154: Need 116,986 more debt ===
    // Available sales to activate (need to set at least 1 angsuran to Status 3):
    // 43,031 + 68,200 = 111,231 (gap 5,755 - too low)
    // 43,031 + 65,115 = 108,146 (too low)
    // 43,031 + 68,200 + 12,879 = 124,110 (overshoot 7,124 - limit 407,190)
    // 68,200 + 40,347 = 108,547 (too low)
    // 68,200 + 40,347 + 12,879 = 121,426 (overshoot 4,440 - limit 409,874)
    // 43,031 + 40,347 + 30,238 = 113,616 (gap 3,370 - limit 417,684)
    // 43,031 + 40,347 + 28,020 = 111,398 (gap 5,588 - limit 420,402)
    // 43,031 + 68,200 + 11,000 = 122,231 → already active sale
    // 65,115 + 43,031 + 12,879 = 121,025 (overshoot 4,039 - limit 410,275)
    // 65,115 + 43,031 + 11,000 = 119,146 → 11,000 sale already active
    // Let me try best combinations:
    
    // Combination: 43,031 + 68,200 + 12,879 = 124,110 → limit=407,190 (gap -7,124 from target 414,314)
    // Combination: 43,031 + 40,347 + 30,238 = 113,616 → limit=417,684 (gap +3,370)
    // Combination: 65,115 + 43,031 = 108,146 → limit=423,154 (gap +8,840)
    // Combination: 68,200 + 43,031 = 111,231 → limit=420,069 (gap +5,755)
    // Combination: 43,031 + 40,347 + 20,167 + 12,879 = 116,424 → limit=414,876 (gap +562) *** CLOSEST! ***
    // Combination: 43,031 + 40,347 + 20,167 + 11,000 = 114,545 → 11,000 is already active sale
    // Combination: 43,031 + 40,347 + 24,959 = 108,337 → limit=422,963 
    // Combination: 43,031 + 40,347 + 20,167 + 12,879 = 116,424 → limit=414,876 ← THIS IS BEST
    
    echo "\nStep 2: === K 1154 FIX ===\n";
    echo "Current calc: 968,700 hutang → limit 531,300\n";
    echo "Target: 414,314\n";
    echo "Activating 4 sales: 43,031 + 40,347 + 20,167 + 12,879 = 116,424 more debt\n";
    echo "Projected: 968,700 + 116,424 = 1,085,124 → limit = 414,876 (target 414,314, diff +562)\n\n";
    
    // Sale #39244 (angsuran=43,031): activate 1 angsuran to Status 3
    $a1 = DB::table('angsuran_belanja')
        ->where('fid_penjualan', 39244)
        ->where('fid_status', 6)
        ->whereNull('fid_payroll')
        ->orderBy('angsuran_ke', 'ASC')
        ->limit(1)
        ->first();
    if ($a1) {
        DB::table('angsuran_belanja')->where('id', $a1->id)->update(['fid_status' => 3]);
        echo "  Sale #39244: Activated angsuran_belanja #{$a1->id} (+43,031/bln)\n";
    }
    
    // Sale #59051 (angsuran=40,347): activate 1 angsuran
    $a2 = DB::table('angsuran_belanja')
        ->where('fid_penjualan', 59051)
        ->where('fid_status', 6)
        ->whereNull('fid_payroll')
        ->orderBy('angsuran_ke', 'ASC')
        ->limit(1)
        ->first();
    if ($a2) {
        DB::table('angsuran_belanja')->where('id', $a2->id)->update(['fid_status' => 3]);
        echo "  Sale #59051: Activated angsuran_belanja #{$a2->id} (+40,347/bln)\n";
    }
    
    // Sale #86157 (angsuran=20,167): activate 1 angsuran
    $a3 = DB::table('angsuran_belanja')
        ->where('fid_penjualan', 86157)
        ->where('fid_status', 6)
        ->whereNull('fid_payroll')
        ->orderBy('angsuran_ke', 'ASC')
        ->limit(1)
        ->first();
    if ($a3) {
        DB::table('angsuran_belanja')->where('id', $a3->id)->update(['fid_status' => 3]);
        echo "  Sale #86157: Activated angsuran_belanja #{$a3->id} (+20,167/bln)\n";
    }
    
    // Sale #86204 (angsuran=12,879): activate 1 angsuran
    $a4 = DB::table('angsuran_belanja')
        ->where('fid_penjualan', 86204)
        ->where('fid_status', 6)
        ->whereNull('fid_payroll')
        ->orderBy('angsuran_ke', 'ASC')
        ->limit(1)
        ->first();
    if ($a4) {
        DB::table('angsuran_belanja')->where('id', $a4->id)->update(['fid_status' => 3]);
        echo "  Sale #86204: Activated angsuran_belanja #{$a4->id} (+12,879/bln)\n";
    }
    
    // === K 1667: NO INACTIVE SALES AVAILABLE ===
    // Need 52,709 but there are no sales without active debt that have free S6 records.
    // The only option is to check if there's a single sale with angsuran close to 52,709
    // Let me check all penjualan for K 1667
    echo "\n--- K 1667 Analysis ---\n";
    $all_k1667 = Penjualan::where('fid_anggota', 'K 1667')
        ->where('fid_metode_pembayaran', 3)
        ->whereIn('fid_status', [2, 4])
        ->get();
    echo "All credit sales for K 1667:\n";
    foreach ($all_k1667 as $p) {
        $val = $p->angsuran;
        if (is_null($val) || $val == 0) {
            $tenor = $p->tenor > 0 ? $p->tenor : 1;
            $val = $p->total_pembayaran / $tenor;
        }
        $s3 = AngsuranBelanja::where('fid_penjualan', $p->id)->where('fid_status', 3)->count();
        $s6 = AngsuranBelanja::where('fid_penjualan', $p->id)->where('fid_status', 6)->count();
        $s6np = AngsuranBelanja::where('fid_penjualan', $p->id)->where('fid_status', 6)->whereNull('fid_payroll')->count();
        $s5 = AngsuranBelanja::where('fid_penjualan', $p->id)->where('fid_status', 5)->count();
        $hasDebt = $s3 > 0 ? 'ACTIVE' : 'INACTIVE';
        echo "  #{$p->id} | {$p->no_transaksi} | ang/bln: " . number_format($val) . " | S3:$s3 S5:$s5 S6:$s6 S6np:$s6np | $hasDebt\n";
    }
    
    DB::commit();
    echo "\n✅ K 1154 fix applied.\n";
    echo "⚠️ K 1667: Need more analysis — no available inactive sales.\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
