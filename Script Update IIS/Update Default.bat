@echo off
echo ========================================
echo   eSIMKO - Update Default (IIS + MySQL)
echo ========================================
echo.

cd /d C:\IIS\Esimko

echo [1/7] Pulling latest code...
git pull origin main

echo [2/7] Installing dependencies...
composer install --no-dev --optimize-autoloader

echo [3/7] Maintenance mode ON...
php artisan down

echo [4/7] Running migrations...
php artisan migrate --force

echo [5/7] Clearing all caches...
php artisan optimize:clear
php artisan config:cache
php artisan view:cache

echo [6/7] Maintenance mode OFF...
php artisan up

echo [7/7] Recycling IIS App Pool...
%windir%\system32\inetsrv\appcmd recycle apppool /apppool.name:"Esimko"

echo.
echo ========================================
echo   Update selesai!
echo ========================================
pause