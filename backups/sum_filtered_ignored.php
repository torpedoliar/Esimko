<?php
// SUM FILTERED IGNORED (2026 ONLY)
use App\Penjualan;
use App\AngsuranBelanja;

$targets = [
    'K 0558' => 83237,
    'K 2008' => 1179339,
    'K 1606' => 10500
];

echo "--- VERIFIKASI SELISIH USER (FILTER 2026) ---\n";
echo str_pad("MEMBER", 10) . str_pad("USER CLAIM", 15) . str_pad("IGNORED (2026)", 20) . "MATCH?\n";
echo str_repeat("-", 60) . "\n";

foreach ($targets as $id => $claim) {
    $transaksi = Penjualan::where('fid_anggota', $id)
        ->whereIn('fid_status', [2, 4])
        ->where('tanggal', '>=', '2026-01-01') // Filter 2026
        ->get();
        
    $totalIgnored = 0;
    
    foreach($transaksi as $t) {
        $angsuran = AngsuranBelanja::where('fid_penjualan', $t->id)->first();
        if (!$angsuran || $angsuran->fid_status == 6) {
           $totalIgnored += $t->total_pembayaran;
        }
    }
    
    $match = abs($totalIgnored - $claim) < 5000 ? "YES" : "NO (Diff: " . number_format($totalIgnored - $claim) . ")";
    
    echo str_pad($id, 10) . 
         str_pad(number_format($claim), 15) . 
         str_pad(number_format($totalIgnored), 20) . 
         $match . "\n";
}
