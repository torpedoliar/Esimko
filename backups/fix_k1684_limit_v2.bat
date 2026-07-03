@echo off
echo === FIX K 1684 LIMIT (ATTEMPT 2: STATUS 4) ===
echo.
echo Target: Transaction JLK-0019-20260204000011 (Rp 550.000)
echo Problem: Status 3 is IGNORED by Limit Helper. Status 4 (matches existing debt) is REQUIRED.
echo.
echo [1/2] Updating Penjualan Metadata (Force Status=4)...
mysql -u root esimko -e "UPDATE penjualan SET angsuran=550000, tenor=1, fid_status=4 WHERE id=100703;"

echo.
echo [2/2] Updating Angsuran Belanja (Ensure Status 3)...
echo Note: Angsuran rows can remain 3 (Unpaid), as Helper only checks Parent Status for inclusion.
mysql -u root esimko -e "UPDATE angsuran_belanja SET fid_status=3 WHERE fid_penjualan=100703;"

echo.
echo [VERIFICATION] Checking Limit K 1684...
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'Limit K 1684 Now: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1684');"
