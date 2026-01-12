# ============================================
# ESIMKO One-Click Deploy Script for Windows
# PowerShell Version (More Robust)
# ============================================

param(
    [switch]$SkipDbExport,
    [switch]$SkipDbImport,
    [switch]$Production
)

$ErrorActionPreference = "Continue"

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "   ESIMKO One-Click Deploy Script" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Configuration
$ProjectPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ProjectPath

# Check Docker
Write-Host "[1/8] Checking Docker..." -ForegroundColor Yellow
$dockerInfo = docker info 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Docker is not running! Please start Docker Desktop first." -ForegroundColor Red
    exit 1
}
Write-Host "       Docker is running" -ForegroundColor Green

# Export database from existing container
if (-not $SkipDbExport) {
    Write-Host ""
    Write-Host "[2/8] Exporting database from current container..." -ForegroundColor Yellow
    $backupFile = "esimko_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
    $result = docker exec esimko-app mysqldump -u root -pMYSQLp4ssw0rd7% esimko 2>$null
    if ($result) {
        $result | Out-File -FilePath $backupFile -Encoding UTF8
        Copy-Item $backupFile "esimko_latest_backup.sql" -Force
        Write-Host "       Database exported to: $backupFile" -ForegroundColor Green
    } else {
        Write-Host "       [WARN] No existing container or database found" -ForegroundColor Yellow
    }
} else {
    Write-Host ""
    Write-Host "[2/8] Skipping database export (--SkipDbExport)" -ForegroundColor Yellow
}

# Stop existing containers
Write-Host ""
Write-Host "[3/8] Stopping existing containers..." -ForegroundColor Yellow
docker-compose down 2>$null
docker-compose -f docker-compose.npm.yml down 2>$null
Write-Host "       Containers stopped" -ForegroundColor Green

# Remove old volumes if fresh install
Write-Host ""
Write-Host "[4/8] Checking for fresh install..." -ForegroundColor Yellow
$freshInstall = Read-Host "       Do you want a FRESH install (delete all data)? (y/N)"
if ($freshInstall -eq 'y' -or $freshInstall -eq 'Y') {
    Write-Host "       Removing old volumes..." -ForegroundColor Yellow
    docker volume rm esimko_mysql_data esimko_npm_data esimko_npm_letsencrypt esimko_app_storage esimko_app_cache 2>$null
    Write-Host "       Volumes removed" -ForegroundColor Green
}

# Build and start containers
Write-Host ""
Write-Host "[5/8] Building and starting containers..." -ForegroundColor Yellow
if ($Production) {
    docker-compose -f docker-compose.npm.yml up -d --build
} else {
    docker-compose up -d --build
}

if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Failed to start containers!" -ForegroundColor Red
    exit 1
}
Write-Host "       Containers started" -ForegroundColor Green

# Wait for services
Write-Host ""
Write-Host "[6/8] Waiting for services to be ready..." -ForegroundColor Yellow
$maxWait = 60
$waited = 0
while ($waited -lt $maxWait) {
    $dbReady = docker exec esimko-db mysql -u root -proot_password_123 -e "SELECT 1" 2>$null
    if ($dbReady) {
        Write-Host "       Database is ready!" -ForegroundColor Green
        break
    }
    Start-Sleep -Seconds 5
    $waited += 5
    Write-Host "       Waiting... ($waited/$maxWait seconds)" -ForegroundColor Gray
}

# Import database
if (-not $SkipDbImport) {
    Write-Host ""
    Write-Host "[7/8] Importing database..." -ForegroundColor Yellow
    if (Test-Path "esimko_latest_backup.sql") {
        Get-Content "esimko_latest_backup.sql" | docker exec -i esimko-db mysql -u root -proot_password_123 esimko 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "       Database imported successfully!" -ForegroundColor Green
        } else {
            Write-Host "       [WARN] Database import may have issues" -ForegroundColor Yellow
        }
    } else {
        Write-Host "       [WARN] No backup file found (esimko_latest_backup.sql)" -ForegroundColor Yellow
    }
} else {
    Write-Host ""
    Write-Host "[7/8] Skipping database import (--SkipDbImport)" -ForegroundColor Yellow
}

# Laravel optimizations
Write-Host ""
Write-Host "[8/8] Running Laravel optimizations..." -ForegroundColor Yellow
docker exec esimko-app php artisan config:clear 2>$null
docker exec esimko-app php artisan cache:clear 2>$null
docker exec esimko-app php artisan view:clear 2>$null
if ($Production) {
    docker exec esimko-app php artisan config:cache 2>$null
    docker exec esimko-app php artisan route:cache 2>$null
    docker exec esimko-app php artisan view:cache 2>$null
}
Write-Host "       Laravel optimization complete" -ForegroundColor Green

# Show container status
Write-Host ""
Write-Host "Container Status:" -ForegroundColor Cyan
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# Final output
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "   DEPLOYMENT COMPLETE!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""
Write-Host "   Access URLs:" -ForegroundColor White
if ($Production) {
    Write-Host "   - Application (via NPM): http://localhost" -ForegroundColor Cyan
    Write-Host "   - NPM Admin Panel: http://localhost:81" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "   NPM Default Login:" -ForegroundColor White
    Write-Host "   - Email: admin@example.com" -ForegroundColor Gray
    Write-Host "   - Password: changeme" -ForegroundColor Gray
} else {
    Write-Host "   - Application: http://localhost:8080" -ForegroundColor Cyan
}
Write-Host ""
Write-Host "   Quick Commands:" -ForegroundColor White
Write-Host "   - View logs: docker logs -f esimko-app" -ForegroundColor Gray
Write-Host "   - Stop: docker-compose down" -ForegroundColor Gray
Write-Host "   - Restart: docker-compose restart" -ForegroundColor Gray
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
