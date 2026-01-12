@echo off
REM ============================================
REM ESIMKO One-Click Deploy Script for Windows
REM ============================================
REM This script will:
REM 1. Export latest database from current container
REM 2. Stop existing containers
REM 3. Build and start all containers with NPM
REM 4. Import database
REM 5. Configure application
REM ============================================

echo.
echo ==========================================
echo   ESIMKO One-Click Deploy Script
echo ==========================================
echo.

REM Check if Docker is running
docker info >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker is not running! Please start Docker Desktop first.
    pause
    exit /b 1
)

echo [1/6] Exporting latest database from current container...
docker exec esimko-app mysqldump -u root -pMYSQLp4ssw0rd7%% esimko > esimko_backup_%date:~10,4%%date:~4,2%%date:~7,2%.sql 2>nul
if exist esimko_backup_%date:~10,4%%date:~4,2%%date:~7,2%.sql (
    echo       Database exported to: esimko_backup_%date:~10,4%%date:~4,2%%date:~7,2%.sql
    copy esimko_backup_%date:~10,4%%date:~4,2%%date:~7,2%.sql esimko_latest_backup.sql >nul
) else (
    echo       [WARN] No existing database found, will use esimko_latest_backup.sql if exists
)

echo.
echo [2/6] Stopping existing containers...
docker-compose down 2>nul
docker-compose -f docker-compose.npm.yml down 2>nul

echo.
echo [3/6] Building and starting containers...
docker-compose -f docker-compose.npm.yml up -d --build

echo.
echo [4/6] Waiting for services to be ready...
timeout /t 30 /nobreak >nul

echo.
echo [5/6] Importing database...
if exist esimko_latest_backup.sql (
    docker exec -i esimko-db mysql -u root -proot_password_123 esimko < esimko_latest_backup.sql 2>nul
    echo       Database imported successfully!
) else (
    echo       [WARN] No backup file found, skipping database import
)

echo.
echo [6/6] Running Laravel optimizations...
docker exec esimko-app php artisan config:cache 2>nul
docker exec esimko-app php artisan route:cache 2>nul
docker exec esimko-app php artisan view:cache 2>nul

echo.
echo ==========================================
echo   DEPLOYMENT COMPLETE!
echo ==========================================
echo.
echo   Access URLs:
echo   - Application: http://localhost (via NPM)
echo   - Application Direct: http://localhost:8080
echo   - NPM Admin Panel: http://localhost:81
echo.
echo   NPM Default Login:
echo   - Email: admin@example.com
echo   - Password: changeme
echo.
echo   Next Steps:
echo   1. Open http://localhost:81 to configure NPM
echo   2. Add Proxy Host: esimko-app port 80
echo   3. Add esimko.com to hosts file
echo.
echo ==========================================
pause
