@echo off
echo === CLEANUP GHOST DATA K 0863 (GOENAWAN) - RETRY ===
echo.
echo [INFO] Target: Transaction JLK-0019-20260124000005
echo [INFO] Problem: 9 Duplicate 'Unpaid' rows killing the limit.
echo.
echo [1/2] Deleting Ghost Rows (Status 3) for this transaction...
mysql -u root esimko -e "DELETE ab FROM angsuran_belanja ab JOIN penjualan p ON ab.fid_penjualan = p.id WHERE p.no_transaksi = 'JLK-0019-20260124000005' AND ab.fid_status = 3;"
echo.
echo [2/2] Tidying up Parent Metadata (Optional, safe)...
mysql -u root esimko -e "UPDATE penjualan SET tenor=1 WHERE no_transaksi = 'JLK-0019-20260124000005';"
echo.
echo [VERIFICATION] Checking Limit K 0863 after cleanup...
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K 0863 Limit Now: ' . App\Helpers\GlobalHelper::limitKaryawan('K 0863') . PHP_EOL;"
