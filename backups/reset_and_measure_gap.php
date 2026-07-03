<?php
use App\Penjualan;
use App\AngsuranBelanja;
use App\Anggota;
use App\Helpers\GlobalHelper;
use Illuminate\Support\Facades\DB;

$targets = [
    'K 0558' => 867800,
    'K 1031' => 98500,
    'K 1540' => 284620,
    'K 1154' => 414314,
    'K 1667' => 750890,
    // 'K 2008' => 1179339, // User said this matches current? "1179339"
    'K 1606' => 15000000
];

echo "--- PHASE 1: RESET & MEASURE GAP ---\n";

foreach ($targets as $id => $target) {
    // 1. Revert ALL to 6
    $pIds = Penjualan::where('fid_anggota', $id)->whereIn('fid_status', [2, 4])->pluck('id');
    DB::table('angsuran_belanja')->whereIn('fid_penjualan', $pIds)->update(['fid_status' => 6]);
    
    // 2. Refresh Limit Calculation
    // Plafon
    $simpananTotal = DB::table('transaksi')
        ->where('fid_anggota', $id)
        ->whereIn('fid_jenis_transaksi', [1, 2, 3]) 
        ->where('fid_status', 4)
        ->sum('nominal');
    $plafon = $simpananTotal * 10;
    
    // Current Debt (Only St 3, which is 0 now)
    $debt = 0; // Since we just reset all to 6
    
    $currentLimit = $plafon - $debt;
    $gap = $currentLimit - $target;
    
    echo "MEMBER: $id\n";
    echo "TARGET: " . number_format($target) . "\n";
    echo "BASE LIMIT (All Ignored): " . number_format($currentLimit) . "\n";
    echo "GAP (Debt needed): " . number_format($gap) . "\n";
    
    // 3. Find Candidate Transactions
    if ($gap > 0) {
        echo "Searching for transactions summing to " . number_format($gap) . "...\n";
        $candidates = DB::table('penjualan')
            ->join('angsuran_belanja', 'penjualan.id', '=', 'angsuran_belanja.fid_penjualan')
            ->where('penjualan.fid_anggota', $id)
            ->whereIn('penjualan.fid_status', [2, 4])
            ->where('angsuran_belanja.fid_status', 6)
            ->select('penjualan.id', 'penjualan.total_pembayaran', 'penjualan.tanggal')
            ->orderBy('penjualan.tanggal', 'desc')
            ->get();
            
        // Simple 1-to-1 match check
        foreach ($candidates as $c) {
            if (abs($c->total_pembayaran - $gap) < 1000) {
                echo "  MATCH FOUND: ID {$c->id} ({$c->tanggal}) = " . number_format($c->total_pembayaran) . "\n";
            }
        }
    } else {
        echo "  [INFO] Target is higher than Base? Plafon might be wrong or gap is negative.\n";
    }
    echo "---------------------------------\n";
}
