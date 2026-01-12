# ============================================
# ESIMKO One-Click Deploy Script for Windows
# PowerShell Version
# ============================================
# Usage:
#   .\deploy.ps1                    # Development mode
#   .\deploy.ps1 -Production        # Production with NPM
#   .\deploy.ps1 -SkipDbExport      # Skip database export
#   .\deploy.ps1 -SkipDbImport      # Skip database import
#   .\deploy.ps1 -Fresh             # Fresh install (delete all data)
# ============================================

param(
    [switch]$SkipDbExport,
    [switch]$SkipDbImport,
    [switch]$Production,
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
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "   ESIMKO One-Click Deploy Script" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# Step 1: Check Docker
Write-Step "1/8" "Checking Docker..."
try {
    $null = docker info 2>&1
    if ($LASTEXITCODE -ne 0) { throw "Docker not running" }
    Write-Success "Docker is running"
}
catch {
    Write-Error "Docker is not running! Please start Docker Desktop first."
    exit 1
}

# Step 2: Export database
Write-Step "2/8" "Database backup..."
if (-not $SkipDbExport) {
    $containerExists = docker ps -q -f name=esimko-app 2>$null
    if ($containerExists) {
        Write-Info "Exporting from existing container..."
        $backupFile = "esimko_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
        
        # Export using docker exec
        $exportResult = docker exec esimko-app mysqldump -u root -pMYSQLp4ssw0rd7% esimko 2>$null
        if ($exportResult -and $exportResult.Length -gt 100) {
            $exportResult | Out-File -FilePath $backupFile -Encoding UTF8 -Force
            Copy-Item $backupFile "esimko_latest_backup.sql" -Force
            Write-Success "Database exported to: $backupFile"
        }
        else {
            Write-Warning "Export returned empty or failed"
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
Write-Step "3/8" "Stopping existing containers..."
docker-compose down 2>$null
docker-compose -f docker-compose.npm.yml down 2>$null
Write-Success "Containers stopped"

# Step 4: Fresh install check
Write-Step "4/8" "Checking volumes..."
if ($Fresh) {
    Write-Info "Fresh install requested, removing old volumes..."
    $volumes = @(
        "esimko_mysql_data",
        "esimko_npm_data", 
        "esimko_npm_letsencrypt",
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
Write-Step "5/8" "Building and starting containers..."
if ($Production) {
    Write-Info "Production mode with NPM..."
    docker-compose -f docker-compose.npm.yml up -d --build
}
else {
    Write-Info "Development mode..."
    docker-compose up -d --build
}

if ($LASTEXITCODE -ne 0) {
    Write-Error "Failed to start containers!"
    exit 1
}
Write-Success "Containers started"

# Step 6: Wait for services
Write-Step "6/8" "Waiting for services..."
$maxAttempts = 12
$attempt = 0
$dbReady = $false

while ($attempt -lt $maxAttempts -and -not $dbReady) {
    Start-Sleep -Seconds 5
    $attempt++
    
    # Check appropriate database
    if ($Production) {
        $check = docker exec esimko-db mysql -u root -proot_password_123 -e "SELECT 1" 2>$null
    }
    else {
        $check = docker exec esimko-app mysql -u root -pMYSQLp4ssw0rd7% -e "SELECT 1" 2>$null
    }
    
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

# Step 7: Import database
Write-Step "7/8" "Importing database..."
if (-not $SkipDbImport) {
    if (Test-Path "esimko_latest_backup.sql") {
        $fileSize = (Get-Item "esimko_latest_backup.sql").Length
        if ($fileSize -gt 1000) {
            Write-Info "Importing esimko_latest_backup.sql ($([math]::Round($fileSize/1MB, 2)) MB)..."
            
            if ($Production) {
                Get-Content "esimko_latest_backup.sql" -Raw | docker exec -i esimko-db mysql -u root -proot_password_123 esimko 2>$null
            }
            else {
                Get-Content "esimko_latest_backup.sql" -Raw | docker exec -i esimko-app mysql -u root -pMYSQLp4ssw0rd7% esimko 2>$null
            }
            Write-Success "Database imported"
        }
        else {
            Write-Warning "Backup file is too small, skipping import"
        }
    }
    else {
        Write-Warning "No backup file found (esimko_latest_backup.sql)"
    }
}
else {
    Write-Info "Skipping database import (-SkipDbImport)"
}

# Step 8: Laravel optimizations
Write-Step "8/8" "Running Laravel optimizations..."
docker exec esimko-app php artisan config:clear 2>$null
docker exec esimko-app php artisan cache:clear 2>$null
docker exec esimko-app php artisan view:clear 2>$null
docker exec esimko-app php artisan storage:link 2>$null

if ($Production) {
    docker exec esimko-app php artisan config:cache 2>$null
    docker exec esimko-app php artisan route:cache 2>$null
    docker exec esimko-app php artisan view:cache 2>$null
}
Write-Success "Laravel optimization complete"

# Show results
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "   DEPLOYMENT COMPLETE!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green

Write-Host ""
Write-Host "Container Status:" -ForegroundColor Cyan
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | ForEach-Object { Write-Host $_ }

Write-Host ""
Write-Host "Access URLs:" -ForegroundColor White
if ($Production) {
    Write-Host "   - Application (via NPM): " -NoNewline; Write-Host "http://localhost" -ForegroundColor Cyan
    Write-Host "   - NPM Admin Panel: " -NoNewline; Write-Host "http://localhost:81" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "   NPM Default Login:" -ForegroundColor White
    Write-Host "   - Email: admin@example.com" -ForegroundColor Gray
    Write-Host "   - Password: changeme" -ForegroundColor Gray
}
else {
    Write-Host "   - Application: " -NoNewline; Write-Host "http://localhost:8080" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "Quick Commands:" -ForegroundColor White
Write-Host "   docker logs -f esimko-app    # View logs" -ForegroundColor Gray
Write-Host "   docker-compose down          # Stop all" -ForegroundColor Gray
Write-Host "   docker-compose restart       # Restart" -ForegroundColor Gray

Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
