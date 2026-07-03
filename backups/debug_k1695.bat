@echo off
echo === K1695 Transactions ===
mysql -u root -e "SELECT p.id, p.no_transaksi, p.tanggal, p.total_pembayaran, p.angsuran, p.tenor, p.fid_status, p.fid_metode_pembayaran FROM esimko.penjualan p WHERE p.fid_anggota='K 1695' AND (p.fid_metode_pembayaran=3 OR p.fid_status=2) ORDER BY p.id DESC;"
echo.
echo === K1695 Angsuran Pending ===
mysql -u root -e "SELECT ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (SELECT id FROM esimko.penjualan WHERE fid_anggota='K 1695') AND ab.fid_status=3 ORDER BY ab.fid_penjualan, ab.angsuran_ke;"
echo.
echo === Verify Limit ===
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K1695 Limit: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1695') . PHP_EOL;"
