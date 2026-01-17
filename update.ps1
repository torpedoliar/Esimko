# ============================================
# UPDATE.PS1 - One-Click Update Script
# ESIMKO - Elektronik Sistem Informasi dan Manajemen Koperasi
# ============================================

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  ESIMKO - System Update" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

$ErrorActionPreference = "Continue"

# Set working directory
$ScriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ScriptPath

# Check if in correct directory
if (-not (Test-Path "docker-compose.dev.yml") -and -not (Test-Path "docker-compose.prod.yml")) {
    Write-Host "ERROR: docker-compose files not found!" -ForegroundColor Red
    Write-Host "Please run this script from the project directory." -ForegroundColor Red
    exit 1
}

# Step 1: Backup database
Write-Host "[1/6] Backing up database..." -ForegroundColor Yellow
$backupDir = "backups"
if (-not (Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir | Out-Null
}

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupFile = "$backupDir/esimko_$timestamp.sql"

# Check if container is running
$dbContainer = docker ps -q -f "name=esimko-db" 2>$null
if ($dbContainer) {
    docker exec esimko-db mysqldump -u root -proot_password_123 esimko > $backupFile 2>$null
    if ((Test-Path $backupFile) -and (Get-Item $backupFile).Length -gt 1000) {
        Write-Host "OK - Database backed up to: $backupFile" -ForegroundColor Green
        Copy-Item $backupFile "esimko_latest_backup.sql" -Force
    }
    else {
        Write-Host "WARN - Backup may have failed, continuing..." -ForegroundColor Yellow
    }
}
else {
    Write-Host "SKIP - Database container not running" -ForegroundColor Yellow
}

# Step 2: Pull latest code
Write-Host ""
Write-Host "[2/6] Pulling latest code from GitHub..." -ForegroundColor Yellow
git pull origin main
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Git pull failed!" -ForegroundColor Red
    Write-Host "Try: git stash; git pull origin main; git stash pop" -ForegroundColor Yellow
    exit 1
}
Write-Host "OK - Code updated" -ForegroundColor Green

# Step 3: Check for migration changes
Write-Host ""
Write-Host "[3/6] Checking for database migrations..." -ForegroundColor Yellow
$migrationChanged = git diff HEAD~1 --name-only 2>$null | Select-String "database/migrations"
if ($migrationChanged) {
    Write-Host "Migration changes detected - will run after rebuild" -ForegroundColor Cyan
}
else {
    Write-Host "No migration changes detected" -ForegroundColor Green
}

# Step 4: Stop containers
Write-Host ""
Write-Host "[4/6] Stopping containers..." -ForegroundColor Yellow
docker-compose -f docker-compose.dev.yml down 2>$null
docker-compose -f docker-compose.prod.yml down 2>$null
Write-Host "OK - Containers stopped" -ForegroundColor Green

# Step 5: Rebuild and start
Write-Host ""
Write-Host "[5/6] Rebuilding (this may take 2-5 minutes)..." -ForegroundColor Yellow
if (Test-Path "docker-compose.prod.yml") {
    docker-compose -f docker-compose.prod.yml up -d --build
}
else {
    docker-compose -f docker-compose.dev.yml up -d --build
}

if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Build failed!" -ForegroundColor Red
    Write-Host ""
    Write-Host "To restore database from backup:" -ForegroundColor Yellow
    Write-Host "  Get-Content $backupFile | docker exec -i esimko-db mysql -u root -proot_password_123 esimko" -ForegroundColor Cyan
    exit 1
}
Write-Host "OK - Build completed" -ForegroundColor Green

# Wait for services
Write-Host ""
Write-Host "Waiting for services to start..." -ForegroundColor Yellow
Start-Sleep -Seconds 15

# Step 6: Laravel sync
Write-Host ""
Write-Host "[6/6] Running Laravel optimizations..." -ForegroundColor Yellow

# Grant MySQL privileges
docker exec esimko-db mysql -u root -proot_password_123 -e "CREATE USER IF NOT EXISTS 'esimko'@'%' IDENTIFIED BY 'esimko_password_123'; GRANT ALL PRIVILEGES ON esimko.* TO 'esimko'@'%'; FLUSH PRIVILEGES;" 2>$null
Write-Host "OK - Database user configured" -ForegroundColor Green

# Run migrations
docker exec esimko-app php artisan migrate --force 2>&1 | Out-Null
Write-Host "OK - Migrations applied" -ForegroundColor Green

# Clear caches
docker exec esimko-app php artisan config:clear 2>$null
docker exec esimko-app php artisan cache:clear 2>$null
docker exec esimko-app php artisan view:clear 2>$null
docker exec esimko-app php artisan storage:link 2>$null
Write-Host "OK - Caches cleared" -ForegroundColor Green

# For production, cache config with sync fix
if (Test-Path "docker-compose.prod.yml") {
    Start-Sleep -Seconds 2
    docker exec esimko-app touch /var/www/html/.env 2>$null
    Start-Sleep -Seconds 1
    docker exec esimko-app php artisan config:cache 2>$null
    docker exec esimko-app php artisan route:cache 2>$null
    Write-Host "OK - Production cache built" -ForegroundColor Green
}

# Cleanup old backups (keep last 5)
Write-Host ""
Write-Host "Cleaning up old backups (keeping last 5)..." -ForegroundColor Yellow
Get-ChildItem -Path $backupDir -Filter "esimko_*.sql" 2>$null | 
Sort-Object LastWriteTime -Descending | 
Select-Object -Skip 5 | 
Remove-Item -Force 2>$null
Write-Host "OK - Cleanup completed" -ForegroundColor Green

# Show version info
$versionFile = "version.json"
if (Test-Path $versionFile) {
    $version = Get-Content $versionFile | ConvertFrom-Json
    Write-Host ""
    Write-Host "Current Version: v$($version.version)" -ForegroundColor Cyan
}

# Done
Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host "  UPDATE COMPLETE!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Application: http://localhost:8080" -ForegroundColor Cyan
Write-Host "  Backup file: $backupFile" -ForegroundColor Cyan
Write-Host ""
Write-Host "  To restore if needed:" -ForegroundColor Yellow
Write-Host "  Get-Content $backupFile | docker exec -i esimko-db mysql -u root -proot_password_123 esimko" -ForegroundColor DarkGray
Write-Host ""
