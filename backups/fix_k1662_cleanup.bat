@echo off
echo === CLEANUP GHOST DATA K 1662 (Eri Firmansyah) ===
echo.
echo [INFO] Target: Transaction JL-0019-20251212000126 (ID 96793)
echo [INFO] Problem: 4 Duplicate 'Unpaid' rows exist alongside 'Paid' rows.
echo.
echo [1/2] Deleting Ghost Rows (Status 3) for ID 96793...
mysql -u root -e "DELETE FROM esimko.angsuran_belanja WHERE fid_penjualan = 96793 AND fid_status = 3;"
echo.
echo [2/2] Updating Parent Metadata (Set Tenor=1 for tidiness)...
mysql -u root -e "UPDATE esimko.penjualan SET tenor=1, angsuran=895600 WHERE id = 96793;"
echo.
echo [VERIFICATION] Checking Limit K 1662 after cleanup...
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K1662 Limit Now: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1662') . PHP_EOL;"
