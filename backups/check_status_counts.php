<?php
use Illuminate\Support\Facades\DB;

$id = 'K 1540';
echo "--- CHECK STATUS COUNTS FOR $id ---\n";

$pIds = DB::table('penjualan')
    ->where('fid_anggota', $id)
    ->whereIn('fid_status', [2, 4])
    ->pluck('id');
    
$st3 = DB::table('angsuran_belanja')
    ->whereIn('fid_penjualan', $pIds)
    ->where('fid_status', 3)
    ->count();

$st6 = DB::table('angsuran_belanja')
    ->whereIn('fid_penjualan', $pIds)
    ->where('fid_status', 6)
    ->count();

echo "Active Sales (Unpaid Header): " . count($pIds) . "\n";
echo "Angsuran Status 3 (Pending/Counted): $st3\n";
echo "Angsuran Status 6 (Lunas/Ignored): $st6\n";

if ($st6 > 100) {
    echo "CONCLUSION: Revert SUCCESSFUL (Many items are Ignored).\n";
} else {
    echo "CONCLUSION: Revert FAILED or Data Mismatch.\n";
}
