<?php
use App\Penjualan;
use App\AngsuranBelanja;
use Illuminate\Support\Facades\DB;

echo "=== All active sales for K 1555 ===\n\n";

$sales = Penjualan::where('fid_anggota', 'K 1555')
    ->where('fid_metode_pembayaran', 3)
    ->whereIn('fid_status', [2, 4])
    ->whereHas('angsuran_belanja', function ($q) {
        $q->where('fid_status', 3);
    })
    ->get();

foreach ($sales as $p) {
    $val = $p->angsuran;
    if (is_null($val) || $val == 0) {
        $tenor = $p->tenor > 0 ? $p->tenor : 1;
        $val = $p->total_pembayaran / $tenor;
    }
    $s3 = AngsuranBelanja::where('fid_penjualan', $p->id)->where('fid_status', 3)->count();
    echo "ID: {$p->id} | No: {$p->no_transaksi} | Date: {$p->tanggal} | Angsuran: " . number_format($val) . " | Total: " . number_format($p->total_pembayaran) . " | S3: $s3\n";
}

echo "\nTotal Sales: " . count($sales) . "\n";
