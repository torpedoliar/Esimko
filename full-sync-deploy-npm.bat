@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

REM ============================================
REM ESIMKO Full Sync & Deploy with NPM
REM ============================================
REM 1. Download database from production
REM 2. Deploy with NGINX Proxy Manager
REM ============================================

echo.
echo ==========================================
echo   ESIMKO Full Sync ^& Deploy (NPM)
echo ==========================================
echo.

cd /d "%~dp0"

REM ============================================
REM PHASE 1: SYNC FROM PRODUCTION
REM ============================================
echo [PHASE 1] Syncing database from production...
echo.

set SSH_HOST=104.248.150.30
set SSH_PORT=22
set SSH_USER=root
set DB_NAME=esimko
set DB_USER=esimko
set DB_PASS=esimko

REM Create backup filename
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set BACKUP_FILE=esimko_prod_%datetime:~0,8%_%datetime:~8,6%.sql

REM Create backups directory
if not exist backups mkdir backups

echo Connecting to production server...
echo Host: %SSH_HOST%
echo.
echo ==========================================
echo   Enter SSH Password: ESIMKO4rt1s4n
echo ==========================================
echo.

ssh -o StrictHostKeyChecking=no -p %SSH_PORT% %SSH_USER%@%SSH_HOST% "mysqldump -u %DB_USER% -p'%DB_PASS%' --single-transaction --routines --triggers %DB_NAME%" > backups\%BACKUP_FILE%

if errorlevel 1 (
    echo [ERROR] Failed to sync from production!
    echo         Continuing with existing backup if available...
) else (
    for %%A in (backups\%BACKUP_FILE%) do set size=%%~zA
    if !size! GTR 1000 (
        echo Database exported successfully!
        copy backups\%BACKUP_FILE% esimko_latest_backup.sql >nul
    ) else (
        echo [WARN] Backup file too small, using existing backup
    )
)

echo.
echo [PHASE 1] Complete!
echo.

REM ============================================
REM PHASE 2: DEPLOY WITH NPM
REM ============================================
echo ==========================================
echo [PHASE 2] Deploying with NGINX Proxy Manager...
echo ==========================================
echo.

REM Check Docker
docker info >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker is not running!
    pause
    exit /b 1
)

REM Stop existing containers
echo Stopping existing containers...
docker-compose down >nul 2>&1
docker-compose -f docker-compose.npm.yml down >nul 2>&1

REM Build and start with NPM
echo Building and starting containers...
docker-compose -f docker-compose.npm.yml up -d --build

if errorlevel 1 (
    echo [ERROR] Failed to start containers!
    pause
    exit /b 1
)

echo Containers started!
echo.

REM Wait for database to be ready
echo Waiting for database to be ready (60 seconds)...
set /a counter=0
:wait_loop
if %counter% GEQ 12 goto wait_done
timeout /t 5 /nobreak >nul
set /a counter+=1
echo Progress: %counter%/12
goto wait_loop
:wait_done

REM Import database
echo.
echo Importing database...
if exist esimko_latest_backup.sql (
    type esimko_latest_backup.sql | docker exec -i esimko-db mysql -u root -proot_password_123 esimko 2>nul
    echo Database imported!
) else (
    echo [WARN] No backup file found
)

REM Laravel optimizations
echo.
echo Running Laravel optimizations...
docker exec esimko-app php artisan config:clear >nul 2>&1
docker exec esimko-app php artisan cache:clear >nul 2>&1
docker exec esimko-app php artisan config:cache >nul 2>&1
docker exec esimko-app php artisan route:cache >nul 2>&1
docker exec esimko-app php artisan view:cache >nul 2>&1

echo.
echo ==========================================
echo   DEPLOYMENT COMPLETE!
echo ==========================================
echo.
docker ps --format "table {{.Names}}\t{{.Status}}"
echo.
echo   Access URLs:
echo   - Application: http://localhost (via NPM after config)
echo   - NPM Admin: http://localhost:81
echo.
echo   NPM Login:
echo   - Email: admin@example.com
echo   - Password: changeme
echo.
echo   Next Steps:
echo   1. Open http://localhost:81
echo   2. Login with credentials above
echo   3. Add Proxy Host:
echo      - Domain: esimko.com (or localhost)
echo      - Forward Host: esimko-app
echo      - Forward Port: 80
echo.
echo   Don't forget to add to hosts file:
echo   127.0.0.1    esimko.com
echo.
echo ==========================================
pause
