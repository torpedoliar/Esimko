<?php
use App\Anggota;
use Illuminate\Support\Facades\DB;

$id = 'K 1540';
echo "--- FINAL INSPECTION FOR $id ---\n";

$anggota = Anggota::where('no_anggota', $id)->first();
// Access property using object syntax, column names from clean schema inspection
// [7] => nama_lengkap
echo "NAME: " . ($anggota ? $anggota->nama_lengkap : 'Unknown') . "\n";

// Calculate Total Debt (Status 2/4)
$debts = DB::table('penjualan')
    ->where('fid_anggota', $id)
    ->whereIn('fid_status', [2, 4])
    ->sum('total_pembayaran');

echo "TOTAL DEBT (System): " . number_format($debts) . "\n";

// Simpanan Sum (Rough Plafon Estimate)
// Assuming fid_jenis_transaksi 1 (Pokok), 2 (Wajib), 3 (Sukarela)?
// Need to verify transaction types but usually 1-3 are savings.
$simpananTotal = DB::table('transaksi')
    ->where('fid_anggota', $id)
    ->whereIn('fid_jenis_transaksi', [1, 2, 3]) 
    ->where('fid_status', 4) // Assuming 4 is Approved/Active for savings
    ->sum('nominal'); 
    
echo "SIMPANAN TOTAL: " . number_format($simpananTotal) . "\n";
echo "IMPLIED PLAFON (10x Simpanan?): " . number_format($simpananTotal * 10) . "\n";

// Limit Result
$limitEst = ($simpananTotal * 10) - $debts;
echo "EST. LIMIT: " . number_format($limitEst) . "\n";
