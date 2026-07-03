<?php
use App\Penjualan;
use App\AngsuranBelanja;
use App\Anggota;

$ids = ['K 1540', 'K 0558'];

echo "--- REALTIME LIMIT CHECK (MANUAL CALC) ---\n";

foreach ($ids as $id) {
    $anggota = Anggota::where('no_anggota', $id)->first();
    $plafon = $anggota ? $anggota->plafon : 0;
    
    // Calculate Active Debt manually
    // Usually: Sum of (Total Pembayaran - Sudah Bayar) for Active Transactions
    // Or just Sum of 'Total Pembayaran' if we assume no partial pay logic complexity for now
    
    $transaksi = Penjualan::where('fid_anggota', $id)
        ->whereIn('fid_status', [2, 4])
        ->get();
        
    $totalHutang = 0;
    $countSt3 = 0;
    $countSt6 = 0;
    
    foreach ($transaksi as $t) {
        $angsuran = AngsuranBelanja::where('fid_penjualan', $t->id)->first();
        $st = $angsuran ? $angsuran->fid_status : 'NULL';
        
        if ($st == 3) $countSt3++;
        if ($st == 6) $countSt6++;
        
        // If Status 2 (Belum Lunas) and Angsuran is NOT 6 (Lunas/Ignored) -> Count as Debt
        // Wait, if Angsuran is 6, does system count it as debt?
        // User's issue is about "Missed Cutoff" -> Status 6 is IGNORED by Payroll.
        // But does "Limit" calculation ignore it?
        // If "Limit" ignores it, then Limit is HIGH.
        // User says K 1540 limit is HIGH (784k) but should be LOW (22k).
        // This implies User expects these debts to be COUNTED (Low Limit).
        // But if they are Status 6, they might be IGNORED (High Limit).
        
        // Let's print the specific debt calculation
        // If Angsuran is 6, we assume it's "Paid" in the eyes of the system?
        
        // Just sum the total_pembayaran for now to see the magnitude
        $totalHutang += $t->total_pembayaran;
    }
    
    $limitEst = $plafon - $totalHutang;

    echo "MEMBER: $id\n";
    echo "PLAFON SYSTEM : " . number_format($plafon) . "\n";
    echo "TOTAL HUTANG (Raw Sum of Active Tx): " . number_format($totalHutang) . "\n";
    echo "EST SISA LIMIT (if all counted): " . number_format($limitEst) . "\n";
    echo "STATS : St3(Pending)=$st3 | St6(Lunas/Ignored)=$st6\n";
    echo "--------------------------\n";
}
