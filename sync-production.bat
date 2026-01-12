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
set DB_NAME=esimko
set DB_USER=esimko
set DB_PASS=esimko

REM Create backup filename with timestamp
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set BACKUP_FILE=esimko_prod_%datetime:~0,8%_%datetime:~8,6%.sql

REM Create backups directory if not exists
if not exist backups mkdir backups

echo [1/5] Checking SSH...
where ssh >nul 2>&1
if errorlevel 1 (
    echo [ERROR] SSH not found!
    echo         Please install OpenSSH:
    echo         Settings ^> Apps ^> Optional Features ^> OpenSSH Client
    pause
    exit /b 1
)
echo       SSH is available

echo.
echo [2/5] Connection Info
echo       Host: %SSH_HOST%
echo       User: %SSH_USER%
echo       Database: %DB_NAME%
echo.
echo       SSH Password: ESIMKO4rt1s4n
echo       (Enter this when prompted)

echo.
echo [3/5] Exporting database from production...
echo       This may take several minutes...
echo.
echo       When prompted, enter password: ESIMKO4rt1s4n
echo.

REM Direct SSH with mysqldump piped to local file
ssh -o StrictHostKeyChecking=no -p %SSH_PORT% %SSH_USER%@%SSH_HOST% "mysqldump -u %DB_USER% -p'%DB_PASS%' --single-transaction --routines --triggers %DB_NAME%" > backups\%BACKUP_FILE%

if errorlevel 1 (
    echo [ERROR] SSH command failed!
    pause
    exit /b 1
)

REM Check file size
for %%A in (backups\%BACKUP_FILE%) do set size=%%~zA
if %size% LSS 1000 (
    echo [ERROR] Backup file is too small (%size% bytes)
    echo         Export may have failed. Check credentials.
    pause
    exit /b 1
)

set /a sizeMB=%size%/1048576
echo.
echo       Database exported: %BACKUP_FILE% (%sizeMB% MB)

echo.
echo [4/5] Updating latest backup...
copy backups\%BACKUP_FILE% esimko_latest_backup.sql >nul
echo       Done!

echo.
echo [5/5] Importing to Docker...

REM Check for development container
docker ps -q -f name=esimko-app >nul 2>&1
if not errorlevel 1 (
    echo       Found esimko-app container
    echo       Importing database...
    type esimko_latest_backup.sql | docker exec -i esimko-app mysql -u root -pMYSQLp4ssw0rd7%% esimko 2>nul
    echo       Done!
    goto :done
)

REM Check for production container
docker ps -q -f name=esimko-db >nul 2>&1
if not errorlevel 1 (
    echo       Found esimko-db container
    echo       Importing database...
    type esimko_latest_backup.sql | docker exec -i esimko-db mysql -u root -proot_password_123 esimko 2>nul
    echo       Done!
    goto :done
)

echo       [WARN] No Docker container found
echo       Backup saved to: esimko_latest_backup.sql

:done
echo.
echo ==========================================
echo   SYNC COMPLETE!
echo ==========================================
echo.
echo   Files saved:
echo   - backups\%BACKUP_FILE%
echo   - esimko_latest_backup.sql
echo.
echo ==========================================
pause
