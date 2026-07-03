@echo off
echo === CORRECTING JAN 24 TRANSACTION (K 0863) ===
echo.
echo [1/3] Deleting ALL existing rows for Jan 24 (Cleanup)...
mysql -u root esimko -e "DELETE FROM angsuran_belanja WHERE fid_penjualan = 100122;"

echo.
echo [2/3] Inserting EXACTLY 1 Row (Status 3) to restore valid debt...
mysql -u root esimko -e "INSERT INTO angsuran_belanja (fid_penjualan, total_angsuran, angsuran_ke, fid_status) VALUES (100122, 659890, 1, 3);"

echo.
echo [3/3] Setting Metadata (Tenor=1)...
mysql -u root esimko -e "UPDATE penjualan SET tenor=1, angsuran=659890 WHERE id = 100122;"

echo.
echo [VERIFICATION] Checking Limit K 0863...
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K 0863 Limit Now: ' . App\Helpers\GlobalHelper::limitKaryawan('K 0863');"
