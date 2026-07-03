@echo off
echo === PILOT FIX DATA K 1662 (Eri Firmansyah) ===
echo.
echo [INFO] Target Fix: Transaksi ID 96793 (Rp 895.600)
echo [1/3] Updating Parent Table (Penjualan)...
mysql -u root -e "UPDATE esimko.penjualan SET tenor=10, angsuran=89560 WHERE id=96793;"

echo [2/3] Updating First Installment (Angsuran Belanja Row 1)...
mysql -u root -e "UPDATE esimko.angsuran_belanja SET total_angsuran=89560 WHERE fid_penjualan=96793 AND angsuran_ke=1;"

echo [3/3] Generating Missing Installments (Rows 2-10)...
mysql -u root -e "INSERT INTO esimko.angsuran_belanja (fid_penjualan, angsuran_ke, total_angsuran, fid_status) VALUES (96793, 2, 89560, 3), (96793, 3, 89560, 3), (96793, 4, 89560, 3), (96793, 5, 89560, 3), (96793, 6, 89560, 3), (96793, 7, 89560, 3), (96793, 8, 89560, 3), (96793, 9, 89560, 3), (96793, 10, 89560, 3);"

echo.
echo [VERIFICATION] Checking Limit K 1662 after fix...
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K1662 Limit Now: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1662') . PHP_EOL;"
