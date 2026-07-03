<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Helpers\GlobalHelper;

$anggota_id = 'K 0531';
$limit = 1500000;

$penjualan_with_debt = DB::table('penjualan')
    ->where('fid_anggota', $anggota_id)
    ->where('fid_metode_pembayaran', 3)
    ->whereIn('fid_status', [2, 4])
    ->get();

$total_usage = 0;
foreach ($penjualan_with_debt as $p) {
    // Check if it has pending installments
    $has_pending = DB::table('angsuran_belanja')
        ->where('fid_penjualan', $p->id)
        ->where('fid_status', 3)
        ->exists();
    
    if ($has_pending) {
        $val = $p->angsuran;
        echo "Sale: {$p->no_transaksi} (ID: {$p->id}) | Total: {$p->total_pembayaran} | Angsuran: {$val} | Status: {$p->fid_status}\n";
        $total_usage += $val;
    }
}

echo "\nCalculated Total Usage: {$total_usage}\n";
echo "Final Limit (1.5M - usage): " . (1500000 - $total_usage) . "\n";
echo "GlobalHelper::limitKaryawan Result: " . GlobalHelper::limitKaryawan($anggota_id) . "\n";
