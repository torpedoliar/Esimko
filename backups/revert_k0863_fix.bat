@echo off
echo === REVERT FIX K 0863 (GOENAWAN) - SAFETY INJECT (RETRY) ===
echo.
echo [INFO] Target: Transaction 100122 (JLK-0019-20260124000005)
echo [INFO] Action: Re-inserting 9 Ghost Rows to RESTORE BROKEN STATE.
echo.
echo [1/2] Injecting 9 Status 3 Rows (Total 5.9M Debt)...
mysql -u root esimko -e "INSERT INTO angsuran_belanja (fid_penjualan, total_angsuran, angsuran_ke, fid_status) VALUES (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3), (100122, 659890, 1, 3);"
echo.
echo [2/2] Restoring Corrupt Metadata (Tenor=0)...
mysql -u root esimko -e "UPDATE penjualan SET tenor=0 WHERE id = 100122;"
echo.
echo [VERIFICATION] Checking Limit K 0863 (Should be LOW)...
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K 0863 Limit Now: ' . App\Helpers\GlobalHelper::limitKaryawan('K 0863') . PHP_EOL;"
