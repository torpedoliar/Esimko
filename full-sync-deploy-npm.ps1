# ============================================
# ESIMKO Full Sync & Deploy with NPM
# ============================================
# 1. Download database from production
# 2. Deploy with NGINX Proxy Manager
# ============================================
# Usage:
#   .\full-sync-deploy-npm.ps1           # Full sync + deploy
#   .\full-sync-deploy-npm.ps1 -SkipSync # Deploy only (use existing backup)
#   .\full-sync-deploy-npm.ps1 -Fresh    # Fresh install (delete all data)
# ============================================

param(
    [switch]$SkipSync,
    [switch]$Fresh
)

$ErrorActionPreference = "Continue"
$ScriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ScriptPath

Write-Host ""
Write-Host "==========================================" -ForegroundColor Magenta
Write-Host "   ESIMKO Full Sync & Deploy (NPM)" -ForegroundColor Magenta
Write-Host "==========================================" -ForegroundColor Magenta

# ============================================
# PHASE 1: SYNC FROM PRODUCTION
# ============================================
if (-not $SkipSync) {
    Write-Host ""
    Write-Host "[PHASE 1] Syncing database from production..." -ForegroundColor Cyan
    Write-Host ""
    
    $SSH_HOST = "104.248.150.30"
    $SSH_PORT = 22
    $SSH_USER = "root"
    $DB_NAME = "esimko"
    $DB_USER = "esimko"
    $DB_PASS = "esimko"
    
    $BACKUP_DIR = Join-Path $ScriptPath "backups"
    $BACKUP_FILE = "esimko_prod_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
    $BACKUP_PATH = Join-Path $BACKUP_DIR $BACKUP_FILE
    
    if (-not (Test-Path $BACKUP_DIR)) {
        New-Item -ItemType Directory -Path $BACKUP_DIR -Force | Out-Null
    }
    
    Write-Host "   Connecting to production server..." -ForegroundColor Gray
    Write-Host "   Host: $SSH_HOST" -ForegroundColor Gray
    Write-Host ""
    Write-Host "   ==========================================" -ForegroundColor Yellow
    Write-Host "   Enter SSH Password: ESIMKO4rt1s4n" -ForegroundColor Yellow
    Write-Host "   ==========================================" -ForegroundColor Yellow
    Write-Host ""
    
    $dumpCmd = "mysqldump -u $DB_USER -p'$DB_PASS' --single-transaction --routines --triggers $DB_NAME"
    ssh -o StrictHostKeyChecking=no -o ConnectTimeout=30 -p $SSH_PORT "$SSH_USER@$SSH_HOST" $dumpCmd > $BACKUP_PATH 2>$null
    
    if ($LASTEXITCODE -eq 0 -and (Test-Path $BACKUP_PATH)) {
        $fileSize = (Get-Item $BACKUP_PATH).Length
        if ($fileSize -gt 1000) {
            $fileSizeMB = [math]::Round($fileSize / 1MB, 2)
            Write-Host "   Database exported: $BACKUP_FILE ($fileSizeMB MB)" -ForegroundColor Green
            Copy-Item $BACKUP_PATH (Join-Path $ScriptPath "esimko_latest_backup.sql") -Force
        }
        else {
            Write-Host "   [WARN] Backup file too small, using existing backup" -ForegroundColor Yellow
        }
    }
    else {
        Write-Host "   [WARN] Sync failed, continuing with existing backup" -ForegroundColor Yellow
    }
    
    # Sync APP_KEY from production (IMPORTANT for password decryption!)
    Write-Host ""
    Write-Host "   Syncing APP_KEY from production..." -ForegroundColor Gray
    Write-Host "   (Required for password encryption to work)" -ForegroundColor Gray
    
    $appKeyCmd = "grep '^APP_KEY=' /var/www/html/.env"
    $prodAppKey = ssh -o StrictHostKeyChecking=no -o ConnectTimeout=30 -p $SSH_PORT "$SSH_USER@$SSH_HOST" $appKeyCmd 2>$null
    
    if ($prodAppKey -and $prodAppKey -match "^APP_KEY=") {
        # Save to .env.production for reference
        $prodAppKey | Out-File -FilePath (Join-Path $ScriptPath ".env.production.key") -Encoding UTF8 -Force
        Write-Host "   APP_KEY synced: $($prodAppKey.Substring(0, 30))..." -ForegroundColor Green
        
        # Update local .env file
        $envPath = Join-Path $ScriptPath ".env"
        if (Test-Path $envPath) {
            $envContent = Get-Content $envPath -Raw
            $newEnvContent = $envContent -replace "APP_KEY=.*", $prodAppKey
            $newEnvContent | Set-Content $envPath -Encoding UTF8 -Force
            Write-Host "   Local .env updated with production APP_KEY" -ForegroundColor Green
        }
    }
    else {
        Write-Host "   [WARN] Could not sync APP_KEY, login may not work!" -ForegroundColor Yellow
        Write-Host "   You may need to manually copy APP_KEY from production" -ForegroundColor Yellow
    }
    
    Write-Host ""
    Write-Host "[PHASE 1] Complete!" -ForegroundColor Green
}
else {
    Write-Host ""
    Write-Host "[PHASE 1] Skipping sync (-SkipSync)" -ForegroundColor Yellow
}

# ============================================
# PHASE 2: DEPLOY WITH NPM
# ============================================
Write-Host ""
Write-Host "==========================================" -ForegroundColor Magenta
Write-Host "[PHASE 2] Deploying with NGINX Proxy Manager..." -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Magenta
Write-Host ""

# Check Docker
Write-Host "   Checking Docker..." -ForegroundColor Gray
$null = docker info 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Docker is not running!" -ForegroundColor Red
    exit 1
}
Write-Host "   Docker is running" -ForegroundColor Green

# Stop existing containers
Write-Host "   Stopping existing containers..." -ForegroundColor Gray
docker-compose down 2>$null
docker-compose -f docker-compose.npm.yml down 2>$null

# Fresh install
if ($Fresh) {
    Write-Host "   Removing old volumes (fresh install)..." -ForegroundColor Yellow
    $volumes = @("esimko_mysql_data", "esimko_npm_data", "esimko_npm_letsencrypt", "esimko_app_storage", "esimko_app_framework", "esimko_redis_data")
    foreach ($vol in $volumes) {
        docker volume rm $vol 2>$null
    }
}

# Build and start
Write-Host "   Building and starting containers..." -ForegroundColor Gray
docker-compose -f docker-compose.npm.yml up -d --build

if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Failed to start containers!" -ForegroundColor Red
    exit 1
}
Write-Host "   Containers started!" -ForegroundColor Green

# Wait for database
Write-Host ""
Write-Host "   Waiting for database to be ready..." -ForegroundColor Gray
$maxAttempts = 12
$attempt = 0
$dbReady = $false

while ($attempt -lt $maxAttempts -and -not $dbReady) {
    Start-Sleep -Seconds 5
    $attempt++
    $check = docker exec esimko-db mysql -u root -proot_password_123 -e "SELECT 1" 2>$null
    if ($check) {
        $dbReady = $true
        Write-Host "   Database is ready! ($($attempt * 5) seconds)" -ForegroundColor Green
    }
    else {
        Write-Host "   Waiting... ($($attempt * 5)/$($maxAttempts * 5) seconds)" -ForegroundColor Gray
    }
}

# Import database
Write-Host ""
Write-Host "   Importing database..." -ForegroundColor Gray
$latestBackup = Join-Path $ScriptPath "esimko_latest_backup.sql"
if (Test-Path $latestBackup) {
    $fileSize = (Get-Item $latestBackup).Length
    if ($fileSize -gt 1000) {
        Get-Content $latestBackup -Raw | docker exec -i esimko-db mysql -u root -proot_password_123 esimko 2>$null
        Write-Host "   Database imported!" -ForegroundColor Green
    }
}
else {
    Write-Host "   [WARN] No backup file found" -ForegroundColor Yellow
}

# Laravel optimizations
Write-Host ""
Write-Host "   Running Laravel optimizations..." -ForegroundColor Gray
docker exec esimko-app php artisan config:clear 2>$null
docker exec esimko-app php artisan cache:clear 2>$null
docker exec esimko-app php artisan storage:link 2>$null
docker exec esimko-app php artisan config:cache 2>$null
docker exec esimko-app php artisan route:cache 2>$null
docker exec esimko-app php artisan view:cache 2>$null
Write-Host "   Laravel optimization complete!" -ForegroundColor Green

# Summary
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "   DEPLOYMENT COMPLETE!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green

Write-Host ""
Write-Host "Container Status:" -ForegroundColor Cyan
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | ForEach-Object { Write-Host $_ }

Write-Host ""
Write-Host "Access URLs:" -ForegroundColor White
Write-Host "   - Application (after NPM config): " -NoNewline; Write-Host "http://localhost" -ForegroundColor Cyan
Write-Host "   - NPM Admin Panel: " -NoNewline; Write-Host "http://localhost:81" -ForegroundColor Cyan

Write-Host ""
Write-Host "NPM Login:" -ForegroundColor White
Write-Host "   - Email: admin@example.com" -ForegroundColor Gray
Write-Host "   - Password: changeme" -ForegroundColor Gray

Write-Host ""
Write-Host "Next Steps:" -ForegroundColor White
Write-Host "   1. Open http://localhost:81" -ForegroundColor Gray
Write-Host "   2. Login with credentials above" -ForegroundColor Gray
Write-Host "   3. Add Proxy Host:" -ForegroundColor Gray
Write-Host "      - Domain: esimko.com (or localhost)" -ForegroundColor Gray
Write-Host "      - Forward Host: esimko-app" -ForegroundColor Gray
Write-Host "      - Forward Port: 80" -ForegroundColor Gray

Write-Host ""
Write-Host "Don't forget to add to hosts file:" -ForegroundColor Yellow
Write-Host "   127.0.0.1    esimko.com" -ForegroundColor Gray

Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
