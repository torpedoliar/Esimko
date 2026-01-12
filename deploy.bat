@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

REM ============================================
REM ESIMKO One-Click Deploy Script for Windows
REM ============================================

echo.
echo ==========================================
echo   ESIMKO One-Click Deploy Script
echo ==========================================
echo.

REM Set working directory
cd /d "%~dp0"

REM Check if Docker is running
echo [1/7] Checking Docker...
docker info >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker is not running! Please start Docker Desktop first.
    pause
    exit /b 1
)
echo       Docker is running!

REM Export database from existing container (if running)
echo.
echo [2/7] Checking for existing database...
docker ps -q -f name=esimko-app >nul 2>&1
if not errorlevel 1 (
    echo       Exporting database backup...
    docker exec esimko-app mysqldump -u root -pMYSQLp4ssw0rd7%% esimko > esimko_latest_backup.sql 2>nul
    if exist esimko_latest_backup.sql (
        for %%A in (esimko_latest_backup.sql) do set size=%%~zA
        if !size! GTR 1000 (
            echo       Database exported successfully!
        ) else (
            echo       [WARN] Database export may be empty
        )
    )
) else (
    echo       [INFO] No existing container found, skipping export
)

REM Stop existing containers
echo.
echo [3/7] Stopping existing containers...
docker-compose down >nul 2>&1
docker-compose -f docker-compose.npm.yml down >nul 2>&1
echo       Done!

REM Ask for deployment mode
echo.
echo [4/7] Select deployment mode:
echo       1. Development (localhost:8080, without NPM)
echo       2. Production (with NPM, SSL ready)
set /p mode="       Enter choice (1 or 2): "

if "%mode%"=="2" (
    echo.
    echo       Starting Production deployment...
    docker-compose -f docker-compose.npm.yml up -d --build
) else (
    echo.
    echo       Starting Development deployment...
    docker-compose up -d --build
)

if errorlevel 1 (
    echo [ERROR] Failed to start containers!
    pause
    exit /b 1
)
echo       Containers started!

REM Wait for services
echo.
echo [5/7] Waiting for services to be ready (60 seconds)...
set /a counter=0
:wait_loop
if %counter% GEQ 12 goto wait_done
timeout /t 5 /nobreak >nul
set /a counter+=1
echo       Progress: %counter%/12
goto wait_loop
:wait_done
echo       Services should be ready!

REM Import database
echo.
echo [6/7] Importing database...
if exist esimko_latest_backup.sql (
    if "%mode%"=="2" (
        type esimko_latest_backup.sql | docker exec -i esimko-db mysql -u root -proot_password_123 esimko 2>nul
    ) else (
        type esimko_latest_backup.sql | docker exec -i esimko-app mysql -u root -pMYSQLp4ssw0rd7%% esimko 2>nul
    )
    echo       Database imported!
) else (
    echo       [WARN] No backup file found, skipping import
)

REM Run Laravel commands
echo.
echo [7/7] Running Laravel optimizations...
docker exec esimko-app php artisan config:clear >nul 2>&1
docker exec esimko-app php artisan cache:clear >nul 2>&1
docker exec esimko-app php artisan view:clear >nul 2>&1
echo       Done!

REM Show results
echo.
echo ==========================================
echo   DEPLOYMENT COMPLETE!
echo ==========================================
echo.
docker ps --format "table {{.Names}}\t{{.Status}}"
echo.
if "%mode%"=="2" (
    echo   Access URLs:
    echo   - Application: http://localhost
    echo   - NPM Admin: http://localhost:81
    echo.
    echo   NPM Login:
    echo   - Email: admin@example.com
    echo   - Password: changeme
) else (
    echo   Access URL: http://localhost:8080
)
echo.
echo ==========================================
pause
