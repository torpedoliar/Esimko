@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

REM ============================================
REM ESIMKO Sync Database from Production
REM ============================================

echo.
echo ==========================================
echo   ESIMKO Production Database Sync
echo ==========================================
echo.

cd /d "%~dp0"

REM Configuration
set SSH_HOST=104.248.150.30
set SSH_PORT=22
set SSH_USER=root
set SSH_PASS=ESIMKO4rt1s4n
set DB_NAME=esimko
set DB_USER=esimko
set DB_PASS=esimko

set BACKUP_FILE=esimko_prod_%date:~10,4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%.sql
set BACKUP_FILE=%BACKUP_FILE: =0%

echo [1/4] Connecting to production server...
echo       Host: %SSH_HOST%
echo       User: %SSH_USER%
echo.

REM Check if ssh is available
where ssh >nul 2>&1
if errorlevel 1 (
    echo [ERROR] SSH not found! Please install OpenSSH.
    echo         Go to: Settings ^> Apps ^> Optional Features ^> OpenSSH Client
    pause
    exit /b 1
)

echo [2/4] Exporting database from production...
echo       This may take a few minutes...
echo.
echo       [!] When prompted, enter password: %SSH_PASS%
echo.

REM Export database via SSH
ssh -o StrictHostKeyChecking=no -p %SSH_PORT% %SSH_USER%@%SSH_HOST% "mysqldump -u %DB_USER% -p'%DB_PASS%' --single-transaction %DB_NAME%" > %BACKUP_FILE%

if errorlevel 1 (
    echo [ERROR] Failed to export database!
    pause
    exit /b 1
)

REM Check file size
for %%A in (%BACKUP_FILE%) do set size=%%~zA
if %size% LSS 1000 (
    echo [ERROR] Backup file is too small, export may have failed!
    pause
    exit /b 1
)

echo.
echo [3/4] Backup downloaded successfully!
echo       File: %BACKUP_FILE%
echo       Size: %size% bytes
copy %BACKUP_FILE% esimko_latest_backup.sql >nul

echo.
echo [4/4] Importing to Docker...

REM Try development container first
docker ps -q -f name=esimko-app >nul 2>&1
if not errorlevel 1 (
    echo       Importing to esimko-app container...
    type esimko_latest_backup.sql | docker exec -i esimko-app mysql -u root -pMYSQLp4ssw0rd7%% esimko 2>nul
    echo       Done!
) else (
    REM Try production container
    docker ps -q -f name=esimko-db >nul 2>&1
    if not errorlevel 1 (
        echo       Importing to esimko-db container...
        type esimko_latest_backup.sql | docker exec -i esimko-db mysql -u root -proot_password_123 esimko 2>nul
        echo       Done!
    ) else (
        echo       [WARN] No Docker container found, backup saved only
    )
)

echo.
echo ==========================================
echo   SYNC COMPLETE!
echo ==========================================
echo.
echo   Backup saved to: %BACKUP_FILE%
echo   Latest backup: esimko_latest_backup.sql
echo.
echo ==========================================
pause
