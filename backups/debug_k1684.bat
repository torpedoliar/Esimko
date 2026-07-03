@echo off
echo === DEBUG FIX K 1684 ===
echo Target ID: 100703

echo [1] BEFORE UPDATE:
mysql -u root esimko -e "SELECT id, angsuran, tenor, fid_status FROM penjualan WHERE id=100703; SELECT id, fid_status, fid_payroll FROM angsuran_belanja WHERE fid_penjualan=100703;"

echo.
echo [2] EXECUTING UPDATE:
mysql -u root esimko -e "UPDATE penjualan SET angsuran=550000, tenor=1, fid_status=3 WHERE id=100703; UPDATE angsuran_belanja SET fid_status=3 WHERE fid_penjualan=100703;"

echo.
echo [3] AFTER UPDATE:
mysql -u root esimko -e "SELECT id, angsuran, tenor, fid_status FROM penjualan WHERE id=100703; SELECT id, fid_status, fid_payroll FROM angsuran_belanja WHERE fid_penjualan=100703;"

echo.
echo [4] CHECK LIMIT:
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'Limit: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1684');"
