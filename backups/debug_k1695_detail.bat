@echo off
echo === Detail Penjualan 100730 ===
mysql -u root -e "SELECT id, no_transaksi, total_pembayaran, angsuran, tenor FROM esimko.penjualan WHERE id=100730;"
echo.
echo === Angsuran Belanja 100730 ===
mysql -u root -e "SELECT * FROM esimko.angsuran_belanja WHERE fid_penjualan=100730;"
echo.
echo === Cek Retur K 1695 ===
cd /d C:\IIS\Esimko
php artisan tinker --execute="$list = App\Penjualan::where('fid_anggota','K 1695')->pluck('id')->toArray(); $retur = App\ItemReturPenjualan::whereHas('retur_penjualan', function($q) use ($list){ $q->whereIn('fid_penjualan', $list); })->with('produk')->get(); $total=0; foreach($retur as $r){ $val = $r->produk->harga_jual * $r->jumlah; $total+=$val; echo 'Retur: '.$val.PHP_EOL; } echo 'Total Retur: '.$total.PHP_EOL;"
