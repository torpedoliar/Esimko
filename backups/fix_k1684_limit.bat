@echo off
echo === FIX K 1684 LIMIT ===
echo.
echo Target: Transaction JLK-0019-20260204000011 (Rp 550.000)
echo Problem: Limit not deducting this debt (Tenor/Angsuran 0 or NULL)
echo.
echo [1/2] Updating Penjualan Metadata (Force Tenor=1, Angsuran=Total)...
mysql -u root esimko -e "UPDATE penjualan SET tenor = 1, angsuran = 550000, fid_status = 3 WHERE id = 100703;"

echo.
echo [2/2] Updating Angsuran Belanja (Ensure Status 3)...
mysql -u root esimko -e "UPDATE angsuran_belanja SET fid_status = 3 WHERE fid_penjualan = 100703;"

echo.
echo [VERIFICATION] Checking Limit K 1684...
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'Limit K 1684 Now: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1684');"
