# ============================================
# ESIMKO Production Deploy Script (No NPM)
# PowerShell Version
# ============================================
# Usage:
#   .\deploy-prod.ps1                    # Normal deploy
#   .\deploy-prod.ps1 -SkipDbExport      # Skip database export
#   .\deploy-prod.ps1 -SkipDbImport      # Skip database import
#   .\deploy-prod.ps1 -Fresh             # Fresh install (delete all data)
# ============================================

param(
    [switch]$SkipDbExport,
    [switch]$SkipDbImport,
    [switch]$Fresh
)

$ErrorActionPreference = "Continue"

# Set working directory to script location
$ScriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ScriptPath

function Write-Step {
    param([string]$Step, [string]$Message)
    Write-Host ""
    Write-Host "[$Step] $Message" -ForegroundColor Yellow
}

function Write-Success {
    param([string]$Message)
    Write-Host "       $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "       [WARN] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "       [ERROR] $Message" -ForegroundColor Red
}

function Write-Info {
    param([string]$Message)
    Write-Host "       $Message" -ForegroundColor Gray
}

# Banner
Write-Host ""
Write-Host "==========================================" -ForegroundColor Magenta
Write-Host "   ESIMKO Production Deploy (No NPM)" -ForegroundColor Magenta
Write-Host "==========================================" -ForegroundColor Magenta
Write-Host ""
Write-Host "Mode: PRODUCTION" -ForegroundColor Green
Write-Host "Compose: docker-compose.prod.yml" -ForegroundColor Gray

# Step 1: Check Docker
Write-Step "1/9" "Checking Docker..."
try {
    docker info 2>&1 | Out-Null
    Write-Success "Docker is running"
}
catch {
    Write-Error "Docker is not running! Please start Docker Desktop."
    exit 1
}

# Step 2: Export database (optional)
Write-Step "2/9" "Database backup..."
if (-not $SkipDbExport) {
    $existingContainer = docker ps -q -f "name=esimko-db"
    if ($existingContainer) {
        $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
        $backupFile = "backups/esimko_prod_$timestamp.sql"
        
        if (-not (Test-Path "backups")) {
            New-Item -ItemType Directory -Path "backups" | Out-Null
        }
        
        Write-Info "Exporting to $backupFile..."
        docker exec esimko-db mysqldump -u root -proot_password_123 esimko > $backupFile 2>$null
        
        if ((Get-Item $backupFile -ErrorAction SilentlyContinue).Length -gt 1000) {
            Write-Success "Backup created: $backupFile"
            Copy-Item $backupFile "esimko_latest_backup.sql" -Force
        }
        else {
            Write-Warning "Backup may be empty or failed"
        }
    }
    else {
        Write-Info "No existing container found, skipping export"
    }
}
else {
    Write-Info "Skipping database export (-SkipDbExport)"
}

# Step 3: Stop existing containers
Write-Step "3/9" "Stopping existing containers..."
docker-compose -f docker-compose.prod.yml down 2>$null
docker-compose -f docker-compose.dev.yml down 2>$null
docker-compose -f docker-compose.npm.yml down 2>$null
docker-compose down 2>$null
Write-Success "Containers stopped"

# Step 4: Fresh install check
Write-Step "4/9" "Checking volumes..."
if ($Fresh) {
    Write-Info "Fresh install requested, removing old volumes..."
    $volumes = @(
        "esimko_mysql_data",
        "esimko_app_storage",
        "esimko_app_framework",
        "esimko_redis_data"
    )
    foreach ($vol in $volumes) {
        docker volume rm $vol 2>$null
    }
    Write-Success "Volumes removed"
}
else {
    Write-Info "Keeping existing volumes"
}

# Step 5: Build and start containers
Write-Step "5/9" "Building and starting containers..."
Write-Info "Using docker-compose.prod.yml..."
docker-compose -f docker-compose.prod.yml up -d --build

if ($LASTEXITCODE -ne 0) {
    Write-Error "Failed to start containers!"
    exit 1
}
Write-Success "Containers started"

# Step 6: Wait for services
Write-Step "6/9" "Waiting for services..."
$maxAttempts = 12
$attempt = 0
$dbReady = $false

while ($attempt -lt $maxAttempts -and -not $dbReady) {
    Start-Sleep -Seconds 5
    $attempt++
    
    $check = docker exec esimko-db mysql -u root -proot_password_123 -e "SELECT 1" 2>$null
    
    if ($check) {
        $dbReady = $true
        Write-Success "Database is ready! ($($attempt * 5) seconds)"
    }
    else {
        Write-Info "Waiting... ($($attempt * 5)/$($maxAttempts * 5) seconds)"
    }
}

if (-not $dbReady) {
    Write-Warning "Database may not be ready yet, continuing anyway..."
}

# Step 7: Grant MySQL user privileges
Write-Step "7/9" "Setting up database user..."
docker exec esimko-db mysql -u root -proot_password_123 -e "CREATE USER IF NOT EXISTS 'esimko'@'%' IDENTIFIED BY 'esimko_password_123'; GRANT ALL PRIVILEGES ON esimko.* TO 'esimko'@'%'; FLUSH PRIVILEGES;" 2>$null
Write-Success "Database user configured"

# Step 8: Import database
Write-Step "8/9" "Importing database..."
if (-not $SkipDbImport) {
    if (Test-Path "esimko_latest_backup.sql") {
        $fileSize = (Get-Item "esimko_latest_backup.sql").Length
        $fileSizeMB = [math]::Round($fileSize / 1MB, 2)
        
        if ($fileSize -gt 1000) {
            Write-Info "Importing esimko_latest_backup.sql ($fileSizeMB MB)..."
            Write-Info "This may take several minutes for large databases..."
            
            Get-Content "esimko_latest_backup.sql" -Raw | docker exec -i esimko-db mysql -u root -proot_password_123 esimko 2>$null
            $tableCount = docker exec esimko-db mysql -u root -proot_password_123 esimko -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'esimko';" 2>$null
            
            if ($tableCount -and [int]$tableCount -gt 50) {
                Write-Success "Database imported successfully! ($tableCount tables)"
            }
            else {
                Write-Warning "Import may have issues (found $tableCount tables)"
            }
        }
        else {
            Write-Warning "Backup file is too small ($fileSizeMB MB), skipping import"
        }
    }
    else {
        Write-Warning "No backup file found (esimko_latest_backup.sql)"
    }
}
else {
    Write-Info "Skipping database import (-SkipDbImport)"
}

# Step 9: Laravel optimizations (PRODUCTION)
Write-Step "9/9" "Running Laravel optimizations..."

# First, clear all caches
Write-Info "Clearing old caches..."
docker exec esimko-app php artisan config:clear 2>$null
docker exec esimko-app php artisan cache:clear 2>$null
docker exec esimko-app php artisan view:clear 2>$null
docker exec esimko-app php artisan route:clear 2>$null

# Force sync by touching .env
Write-Info "Syncing environment..."
docker exec esimko-app touch /var/www/html/.env 2>$null
Start-Sleep -Seconds 2

# Now cache for production performance
Write-Info "Building production cache..."
docker exec esimko-app php artisan config:cache 2>$null
docker exec esimko-app php artisan route:cache 2>$null
docker exec esimko-app php artisan view:cache 2>$null
docker exec esimko-app php artisan storage:link 2>$null

Write-Success "Production optimization complete"

# Summary
Write-Host ""
Write-Host "==========================================" -ForegroundColor Magenta
Write-Host "   PRODUCTION DEPLOY COMPLETE!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Magenta

$containers = docker ps --format "{{.Names}}: {{.Status}}" --filter "name=esimko"
Write-Host ""
Write-Host "Running Containers:" -ForegroundColor Yellow
Write-Host $containers

Write-Host ""
Write-Host "Access URLs:" -ForegroundColor Yellow
Write-Host "   - Application: http://localhost:8080" -ForegroundColor Cyan
Write-Host ""
Write-Host "Quick Commands:" -ForegroundColor Yellow
Write-Host "   docker logs -f esimko-app              # View logs"
Write-Host "   docker-compose -f docker-compose.prod.yml down    # Stop all"
Write-Host "   docker-compose -f docker-compose.prod.yml restart # Restart"
Write-Host ""
Write-Host "==========================================" -ForegroundColor Magenta
