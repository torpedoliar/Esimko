@echo off
echo === Verifying limitKaryawan fix ===
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K1215: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1215') . PHP_EOL; echo 'K1741: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1741') . PHP_EOL; echo 'K1454: ' . App\Helpers\GlobalHelper::limitKaryawan('K 1454') . PHP_EOL;"
