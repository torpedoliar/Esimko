@echo off
echo === BEFORE FIX: K1667 data ===
mysql -u root -e "SELECT id, angsuran, total_pembayaran, tenor FROM esimko.penjualan WHERE id IN (94906, 99473);"
echo.
mysql -u root -e "SELECT id, fid_penjualan, angsuran_ke, total_angsuran, fid_status FROM esimko.angsuran_belanja WHERE fid_penjualan IN (94906, 99473) ORDER BY fid_penjualan, angsuran_ke, fid_status;"
echo.
echo === FIX 1: Penjualan 94906 angsuran 175010 to 17501 ===
mysql -u root -e "UPDATE esimko.penjualan SET angsuran=17501 WHERE id=94906;"
mysql -u root -e "UPDATE esimko.angsuran_belanja SET total_angsuran=17501 WHERE fid_penjualan=94906;"
echo.
echo === FIX 2: Delete ghost angsuran_belanja for 99473 ===
mysql -u root -e "DELETE FROM esimko.angsuran_belanja WHERE fid_penjualan=99473 AND angsuran_ke=1 AND fid_status=3;"
echo.
echo === AFTER FIX ===
mysql -u root -e "SELECT id, angsuran, total_pembayaran, tenor FROM esimko.penjualan WHERE id IN (94906, 99473);"
echo.
mysql -u root -e "SELECT id, fid_penjualan, angsuran_ke, total_angsuran, fid_status FROM esimko.angsuran_belanja WHERE fid_penjualan IN (94906, 99473) ORDER BY fid_penjualan, angsuran_ke, fid_status;"
echo.
echo === VERIFY LIMIT ===
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K1667: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1667') . PHP_EOL; echo 'K1215: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1215') . PHP_EOL; echo 'K1741: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1741') . PHP_EOL; echo 'K1454: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1454') . PHP_EOL;"
