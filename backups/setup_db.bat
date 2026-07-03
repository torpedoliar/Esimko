@echo off
cd /d C:\IIS\Esimko

REM Create database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS esimko CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

REM Import backup if exists
if exist C:\IIS\Esimko\esimko_latest_backup.sql (
    echo Importing database backup...
    mysql -u root esimko < C:\IIS\Esimko\esimko_latest_backup.sql
    echo Done!
) else (
    echo No backup file found, running migrations...
    C:\php\php.exe artisan migrate --force
)

REM Clear Laravel caches
C:\php\php.exe artisan config:clear
C:\php\php.exe artisan cache:clear
C:\php\php.exe artisan view:clear

echo Setup complete!
