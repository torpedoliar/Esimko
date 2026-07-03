@echo off
echo === FIXING K 0531 (Endah Setiarini) ===
echo.
echo 1. Fixing Sale ID 100881: Set tenor=10 and angsuran=6720 in penjualan table
mysql -u root -e "UPDATE esimko.penjualan SET tenor=10, angsuran=6720 WHERE id=100881;"
echo.
echo 2. Fixing Angsuran ID 283474: Set total_angsuran=6720 in angsuran_belanja table
mysql -u root -e "UPDATE esimko.angsuran_belanja SET total_angsuran=6720 WHERE id=283474;"
echo.
echo 3. Verify Limit after fix
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K0531 Limit Now: ' . App\Helpers\GlobalHelper::limitKaryawan('K 0531') . PHP_EOL;"
