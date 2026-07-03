<?php
use App\Penjualan;
use App\AngsuranBelanja;

$id = 'K 0558';
echo "--- CHECKING CURRENT STATUS FOR $id ---\n";

$transaksi = Penjualan::where('fid_anggota', $id)
    ->whereIn('fid_status', [2, 4])
    ->take(5)
    ->get();

foreach($transaksi as $t) {
    $angsuran = AngsuranBelanja::where('fid_penjualan', $t->id)->first();
    $st = $angsuran ? $angsuran->fid_status : 'NULL';
    echo "{$t->no_transaksi} | Penjualan St:{$t->fid_status} | Angsuran St:$st\n";
}
