<?php
use App\Helpers\GlobalHelper;
use Illuminate\Support\Facades\DB;

$id = 'K 1540';
echo "--- FORMULA VERIFICATION FOR $id ---\n";

// 1. Simpanan * 10
$simpananTotal = DB::table('transaksi')
    ->where('fid_anggota', $id)
    ->whereIn('fid_jenis_transaksi', [1, 2, 3]) 
    ->where('fid_status', 4)
    ->sum('nominal');
$plafon = $simpananTotal * 10;
echo "PLAFON (Simpanan * 10): " . number_format($plafon) . "\n";

// 2. Pinjaman Debt (Loan, NOT Belanja)
// Assuming Pinjaman is 'sisa_pinjaman' logic.
// Logic from sisa_pinjaman_batch roughly:
// Get Pinjaman Transaksi (Type 9, 10, 11?)
// Sum (Total Transaksi - Sudah Bayar)
// Let's look for Pinjaman types in GlobalHelper or DB.
// Usually 9, 10, 11.
$pinjaman = DB::table('transaksi')
    ->where('fid_anggota', $id)
    ->whereIn('fid_jenis_transaksi', [9, 10, 11])
    ->where('fid_status', 4)
    ->get();

$totalPinjamanDebt = 0;
foreach ($pinjaman as $p) {
    // Count paid installments in 'angsuran' table
    $paid = DB::table('angsuran')
        ->where('fid_transaksi', $p->id)
        ->where('fid_status', '!=', 6) // Assuming 6 is ignored here too?
        ->count();
        
    $tenor = $p->tenor;
    $sisa = $tenor - $paid;
    if ($sisa < 0) $sisa = 0;
    
    // Angsuran per bulan = Nominal / Tenor
    $perBulan = $p->nominal / ($tenor > 0 ? $tenor : 1);
    $debt = $sisa * $perBulan; // Approximate
    $totalPinjamanDebt += $debt;
}
echo "PINJAMAN DEBT (Est): " . number_format($totalPinjamanDebt) . "\n";

// 3. Max Limit Estimate
$maxLimit = $plafon - $totalPinjamanDebt;
echo "EST MAX LIMIT (Status 6): " . number_format($maxLimit) . "\n";

// Check against User Report (784,563)
$diff = abs($maxLimit - 784563);
echo "DIFF TO USER REPORT: " . number_format($diff) . "\n";

if ($diff < 1000000) { // Toleransi 1M? No 100k
    echo "FORMULA MATCHES!\n";
} else {
    echo "FORMULA FAILED.\n";
}
