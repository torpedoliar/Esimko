@echo off
echo === FINAL FIX K 0863 (GOENAWAN) ===
echo.
echo [1/2] Fixing Feb 4 Transaction (Force Pay)...
echo Target: Sales ID 100718 (JL-0019-20260204000038)
mysql -u root esimko -e "UPDATE angsuran_belanja SET fid_status = 6 WHERE fid_penjualan = 100718; UPDATE penjualan SET fid_status = 6 WHERE id = 100718;"

echo.
echo [2/2] Deleting Jan 24 Ghost Rows...
echo Target: Sales ID 100122 (JLK-0019-20260124000005)
mysql -u root esimko -e "DELETE FROM angsuran_belanja WHERE fid_penjualan = 100122 AND fid_status = 3; UPDATE penjualan SET tenor = 1, angsuran = total_pembayaran WHERE id = 100122;"

echo.
echo [VERIFICATION] Checking Final Limit K 0863...
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K 0863 Limit Now: ' . App\Helpers\GlobalHelper::limitKaryawan('K 0863');"
