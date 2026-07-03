@echo off
echo === MASS FIX: KONSINYASI LIMIT (STATUS 3 -> 4) ===
echo.
echo Problem: 'Unpaid' Status 3 is ignored by Limit Calculator.
echo Solution: Force Status 4 (Credit Debt) for all recently reverted Consignment transactions.
echo.
echo [1/2] Finding Target IDs (Safety Check)...
echo Target: Consignment transactions between 2026-01-26 and 2026-02-10 with Status 3.
mysql -u root esimko -e "SELECT COUNT(*) as RowsToFix FROM penjualan WHERE jenis_belanja='konsinyasi' AND fid_status=3 AND tanggal BETWEEN '2026-01-26' AND '2026-02-10';"

echo.
echo [2/2] Executing Update (Status 3 -> 4)...
mysql -u root esimko -e "UPDATE penjualan SET fid_status=4, updated_at=NOW() WHERE jenis_belanja='konsinyasi' AND fid_status=3 AND tanggal BETWEEN '2026-01-26' AND '2026-02-10';"

echo.
echo [VERIFICATION] Checking Limit K 1843 (Should drop to Negative/Low)...
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'Limit K 1843: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1843');"
