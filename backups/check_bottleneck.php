<?php
use App\Penjualan;

echo "Checking Potential Bottleneck for Payroll...\n";
$start = microtime(true);

$belanja = Penjualan::select('id')
    ->where(function ($a){
        $a->where(function ($i){
            $i->where('jenis_belanja','toko')
                ->Where('fid_status',2);
        })->orWhere(function ($i){
            $i->where('jenis_belanja','!=','toko')
                ->Where('fid_status',4);
        });
    })
    ->where('fid_metode_pembayaran',3)
    ->where('tanggal', '<=', date('Y-m-d')) // Current date
    ->count();

echo "Total Candidate Transactions: " . $belanja . "\n";
echo "Time taken to count: " . (microtime(true) - $start) . "s\n";

if ($belanja > 1000) {
    echo "WARNING: High record count! N+1 loop in Controller will likely timeout.\n";
}
