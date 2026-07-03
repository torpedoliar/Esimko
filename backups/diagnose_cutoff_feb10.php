<?php
// DIAGNOSE FAILED CUTOFF (10 FEB 2026)
use App\Penjualan;
use App\AngsuranBelanja;
use App\Helpers\GlobalHelper;
use Illuminate\Support\Facades\DB;

$targets = ['K 0558', 'K 1031', 'K 1540', 'K 1154', 'K 1667', 'K 2008', 'K 1606'];

echo "--- DIAGNOSIS FAILED CUTOFF (FEB 10) --- \n";
echo str_pad("MEMBER", 10) . str_pad("LIMIT SYS", 15) . str_pad("HUTANG (REAL)", 15) . "STATUS\n";
echo str_repeat("-", 60) . "\n";

foreach ($targets as $id) {
    try {
        $limit = GlobalHelper::limitKaryawan($id);
        
        // Manual calc of active debt
        $transaksi = Penjualan::where('fid_anggota', $id)
            ->whereIn('fid_status', [2, 3, 4]) // Active, Pending, Partial
            ->get();
            
        $totalHutang = 0;
        $details = [];
        
        foreach($transaksi as $t) {
            $angsuran = AngsuranBelanja::where('fid_penjualan', $t->id)->first();
            $statusAngsuran = $angsuran ? $angsuran->fid_status : 'NULL';
            $nominal = $t->total_pembayaran;
            
            // Check if ignored by logic
            $logicStatus = "COUNTED";
            if ($statusAngsuran != 3) $logicStatus = "IGNORED (Angsuran St:$statusAngsuran)";
            
            $details[] = "   - {$t->no_transaksi} (Rp " . number_format($nominal) . ") St:{$t->fid_status} | Angsuran: $statusAngsuran [$logicStatus]";
            
            if ($logicStatus == "COUNTED") {
                $totalHutang += $nominal;
            }
        }
        
        echo str_pad($id, 10) . str_pad(number_format($limit), 15) . str_pad(number_format($totalHutang), 15) . "\n";
        foreach($details as $d) echo "$d\n";
        echo "\n";
        
    } catch (\Exception $e) {
        echo "$id ERROR: " . $e->getMessage() . "\n";
    }
}
