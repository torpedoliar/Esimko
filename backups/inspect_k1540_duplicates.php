<?php
use App\Penjualan;
use App\Anggota;
use Illuminate\Support\Facades\DB;

$id = 'K 1540';
echo "--- INSPECTION FOR $id ---\n";

// 1. Identity
$anggota = Anggota::where('no_anggota', $id)->first();
echo "NAME: " . ($anggota ? $anggota->nama : 'Unknown') . "\n";
echo "PLAFON: " . number_format($anggota ? $anggota->plafon : 0) . "\n";

// 2. Active Transactions
$transaksi = Penjualan::where('fid_anggota', $id)
    ->whereIn('fid_status', [2, 4])
    ->get();

$total = 0;
$grouped = [];

foreach($transaksi as $t) {
    $total += $t->total_pembayaran;
    $key = $t->tanggal . '_' . $t->total_pembayaran;
    if (!isset($grouped[$key])) $grouped[$key] = 0;
    $grouped[$key]++;
}

echo "TOTAL DEBT (Active Sales): " . number_format($total) . "\n";

// 3. Find Duplicates
echo "\n--- POTENTIAL DUPLICATES (Same Date & Amount) ---\n";
$dupCount = 0;
$dupValue = 0;

foreach ($grouped as $key => $count) {
    if ($count > 1) {
        $parts = explode('_', $key);
        $date = $parts[0];
        $amount = $parts[1];
        
        echo "$date : Rp " . number_format($amount) . " x $count\n";
        
        // Count excess as "Ghost"
        $excess = $count - 1;
        $dupCount += $excess;
        $dupValue += ($excess * $amount);
    }
}

echo "\n--- SUMMARY ---\n";
echo "Total Active Records: " . $transaksi->count() . "\n";
echo "Potential Ghost Records: $dupCount\n";
echo "Potential Ghost Value: " . number_format($dupValue) . "\n";
echo "Real Debt Est (Total - Ghost): " . number_format($total - $dupValue) . "\n";
