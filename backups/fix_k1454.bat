@echo off
echo === BEFORE FIX: K1454 pending angsuran ===
mysql -u root -e "SELECT ab.id, ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (SELECT id FROM esimko.penjualan WHERE fid_anggota='K 1454' AND fid_metode_pembayaran=3) AND ab.fid_status=3;"
echo.
echo === FIX: Set pending angsuran to status 5 (paid) for K1454 ghost transactions ===
mysql -u root -e "UPDATE esimko.angsuran_belanja SET fid_status=5 WHERE fid_penjualan IN (SELECT id FROM esimko.penjualan WHERE fid_anggota='K 1454' AND fid_metode_pembayaran=3) AND fid_status=3;"
echo.
echo === AFTER FIX: verify no more pending ===
mysql -u root -e "SELECT COUNT(*) as remaining FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (SELECT id FROM esimko.penjualan WHERE fid_anggota='K 1454' AND fid_metode_pembayaran=3) AND ab.fid_status=3;"
echo.
echo === VERIFY LIMIT ===
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K1454: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1454') . PHP_EOL;"
