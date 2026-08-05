@echo off
echo ========================================
echo   eSIMKO - Update Optimize (IIS + MySQL)
echo   Hanya composer install jika composer.lock berubah
echo ========================================
echo.

cd /d C:\IIS\Esimko

REM Simpan commit hash sebelum pull
for /f "delims=" %%i in ('git rev-parse HEAD') do set BEFORE=%%i

echo [1/7] Pulling latest code...
git pull origin main

REM Cek apakah composer.lock berubah
git diff --name-only %BEFORE% HEAD | findstr /i "composer.lock" >nul 2>&1

echo [2/7] Maintenance mode ON...
php artisan down

if %errorlevel% equ 0 (
    echo [3/7] composer.lock berubah, menjalankan composer install...
    composer install --no-dev --optimize-autoloader
) else (
    echo [3/7] composer.lock tidak berubah, skip composer install.
)

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