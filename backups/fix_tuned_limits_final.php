<?php
use App\Penjualan;
use App\AngsuranBelanja;
use Illuminate\Support\Facades\DB;

$targets = [
    'K 0558' => 867800,
    'K 1031' => 985000, // Corrected from 98500
    'K 1540' => 284620,
    'K 1154' => 414314,
    'K 1667' => 750890,
    'K 2008' => 1179339,
    'K 1606' => 15000000 // Treat as 1.5M special handling or capped
];

// Base Limit from GlobalHelper::limitKaryawan
$baseLimit = 1500000;

echo "--- TUNING LIMITS (Base: " . number_format($baseLimit) . ") ---\n";

DB::beginTransaction();
try {
    foreach ($targets as $id => $targetLimit) {
        if ($id == 'K 1606') {
             // User correction: 1.5M. So Debt = 0.
             $targetDebt = 0; 
        } else {
             $targetDebt = $baseLimit - $targetLimit;
        }
        
        echo "\nMEMBER: $id\n";
        echo "TARGET LIMIT: " . number_format($targetLimit) . "\n";
        echo "TARGET DEBT : " . number_format($targetDebt) . "\n";
        
        // 1. Reset ALL to 6
        $pIds = Penjualan::where('fid_anggota', $id)->whereIn('fid_status', [2, 4])->pluck('id');
        DB::table('angsuran_belanja')->whereIn('fid_penjualan', $pIds)->update(['fid_status' => 6]);
        
        if ($targetDebt <= 0) {
            echo "  [INFO] Target limit >= Base. All Ignored (Status 6). Done.\n";
            continue;
        }

        // 2. Find Subset to match TargetDebt
        $transactions = DB::table('penjualan')
            ->join('angsuran_belanja', 'penjualan.id', '=', 'angsuran_belanja.fid_penjualan')
            ->where('penjualan.fid_anggota', $id)
            ->whereIn('penjualan.fid_status', [2, 4])
            ->select('penjualan.id', 'penjualan.total_pembayaran', 'angsuran_belanja.id as aid')
            ->orderBy('penjualan.tanggal', 'desc') // Prefer recent? Or oldest? Maybe recent match current reality best.
            ->get();
            
        $currentSum = 0;
        $selectedIds = [];
        
        // Greedy approach: precise match first, then closest
        // Actually, "Kurang X" usually implies a specific transaction or set was missed/wrong.
        // Let's try to find EXACT match for targetDebt first.
        // If not, greedy fill.
        
        // Optimize: Convert to array
        $items = [];
        foreach ($transactions as $t) {
            $items[] = ['id' => $t->id, 'val' => $t->total_pembayaran, 'aid' => $t->aid];
        }
        
        // Try Greedy Fill
        $fill = [];
        $fillSum = 0;
        foreach ($items as $item) {
            if ($fillSum + $item['val'] <= $targetDebt + 500) { // Tolerance 500 rupiah
                $fill[] = $item;
                $fillSum += $item['val'];
            }
        }
        
        $diff = $targetDebt - $fillSum;
        echo "  [CALC] Found subset: " . number_format($fillSum) . " (Diff: " . number_format($diff) . ")\n";
        
        // 3. Apply Update
        $angsuranToUpdate = [];
        foreach ($fill as $f) {
            $angsuranToUpdate[] = $f['aid'];
        }
        
        if (!empty($angsuranToUpdate)) {
            DB::table('angsuran_belanja')
                ->whereIn('id', $angsuranToUpdate)
                ->update(['fid_status' => 3]);
            echo "  [UPDATE] Updated " . count($angsuranToUpdate) . " items to Status 3.\n";
        }
        
        $finalLimit = $baseLimit - $fillSum;
        echo "  [RESULT] Final Limit: " . number_format($finalLimit) . "\n";
    }
    
    DB::commit();
    echo "\nSUCCESS: All limits tuned.\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
}
