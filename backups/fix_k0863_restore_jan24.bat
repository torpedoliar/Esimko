@echo off
echo === RESTORE JAN 24 TRANSACTION (K 0863) ===
echo.
echo [1/2] Re-Inserting 9 Rows for Jan 24 (Status 3)...
echo Target: Sales ID 100122 (JLK-0019-20260124000005)
mysql -u root esimko -e "INSERT INTO angsuran_belanja (fid_penjualan, total_angsuran, angsuran_ke, fid_status) VALUES (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3);"

echo.
echo [2/2] Restoring Metadata (Tenor=0 to match Ghost state)...
mysql -u root esimko -e "UPDATE penjualan SET tenor=0 WHERE id = 100122;"

echo.
echo [VERIFICATION] Checking Limit K 0863...
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K 0863 Limit Now: ' . App\Helpers\GlobalHelper::limitKaryawan('K 0863');"
