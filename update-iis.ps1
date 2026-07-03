# ============================================
# UPDATE-IIS.PS1 - One-Click Update Script for IIS
# ESIMKO - Elektronik Sistem Informasi dan Manajemen Koperasi
# ============================================
# Run as Administrator on the IIS Server
# ============================================

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  ESIMKO - IIS System Update" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

$ErrorActionPreference = "Continue"

# ============================================
# CONFIGURATION - Verified for Server 49.50.9.81
# ============================================
$ProjectPath = "C:\IIS\Esimko"
$PhpPath = "C:\php"
$ComposerPath = "C:\composer"
$MysqlBinPath = "C:\tools\mysql\current\bin"
$BackupDir = "$ProjectPath\backups"
$GitRemote = "origin"
$GitBranch = "main"

# MySQL Configuration
$MysqlHost = "127.0.0.1"
$MysqlUser = "root"
$MysqlPassword = ""
$MysqlDatabase = "esimko"

# Add paths to current session
$env:Path = "$env:Path;$PhpPath;$ComposerPath;$MysqlBinPath"

# ============================================
# VALIDATION
# ============================================
if (-not (Test-Path "$ProjectPath\artisan")) {
    Write-Host "ERROR: Laravel artisan not found at $ProjectPath!" -ForegroundColor Red
    Write-Host "Please verify the project path." -ForegroundColor Red
    exit 1
}

Set-Location $ProjectPath

# ============================================
# STEP 1: Backup Database
# ============================================
Write-Host "[1/7] Backing up database..." -ForegroundColor Yellow

if (-not (Test-Path $BackupDir)) {
    New-Item -ItemType Directory -Path $BackupDir -Force | Out-Null
}

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupFile = "$BackupDir\esimko_$timestamp.sql"

# Find mysqldump
$mysqldump = Get-Command mysqldump -ErrorAction SilentlyContinue
if ($mysqldump) {
    try {
        if ($MysqlPassword) {
            & mysqldump -h $MysqlHost -u $MysqlUser -p"$MysqlPassword" $MysqlDatabase > $backupFile 2>$null
        } else {
            & mysqldump -h $MysqlHost -u $MysqlUser $MysqlDatabase > $backupFile 2>$null
        }
        
        if ((Test-Path $backupFile) -and (Get-Item $backupFile).Length -gt 1000) {
            Write-Host "  OK - Database backed up to: $backupFile" -ForegroundColor Green
            Copy-Item $backupFile "$ProjectPath\esimko_latest_backup.sql" -Force
        } else {
            Write-Host "  WARN - Backup may have failed" -ForegroundColor Yellow
        }
    } catch {
        Write-Host "  WARN - Backup failed: $_" -ForegroundColor Yellow
    }
} else {
    Write-Host "  SKIP - mysqldump not found in PATH" -ForegroundColor Yellow
}

# ============================================
# STEP 2: Pull Latest Code
# ============================================
Write-Host ""
Write-Host "[2/7] Pulling latest code from GitHub..." -ForegroundColor Yellow

git pull $GitRemote $GitBranch 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "  ERROR: Git pull failed!" -ForegroundColor Red
    Write-Host "  Try: git stash; git pull $GitRemote $GitBranch; git stash pop" -ForegroundColor Yellow
    exit 1
}
Write-Host "  OK - Code updated" -ForegroundColor Green

# ============================================
# STEP 3: Check for Migration Changes
# ============================================
Write-Host ""
Write-Host "[3/7] Checking for database migrations..." -ForegroundColor Yellow

$migrationChanged = git diff HEAD~1 --name-only 2>$null | Select-String "database/migrations"
if ($migrationChanged) {
    Write-Host "  Migration changes detected - will run migrations" -ForegroundColor Cyan
    $runMigrations = $true
} else {
    Write-Host "  No migration changes detected" -ForegroundColor Green
    $runMigrations = $false
}

# ============================================
# STEP 4: Install Composer Dependencies
# ============================================
Write-Host ""
Write-Host "[4/7] Updating Composer dependencies..." -ForegroundColor Yellow

$composerChanged = git diff HEAD~1 --name-only 2>$null | Select-String "composer.lock"
if ($composerChanged) {
    Write-Host "  composer.lock changed - installing dependencies..." -ForegroundColor Cyan
    & "$PhpPath\php.exe" "$ComposerPath\composer.phar" install --no-dev --optimize-autoloader 2>&1
    Write-Host "  OK - Dependencies updated" -ForegroundColor Green
} else {
    Write-Host "  No composer changes - skipping" -ForegroundColor Green
}

# ============================================
# STEP 5: Run Migrations (if needed)
# ============================================
Write-Host ""
Write-Host "[5/7] Running database migrations..." -ForegroundColor Yellow

if ($runMigrations) {
    & "$PhpPath\php.exe" artisan migrate --force 2>&1
    Write-Host "  OK - Migrations applied" -ForegroundColor Green
} else {
    Write-Host "  SKIP - No migrations to run" -ForegroundColor Green
}

# ============================================
# STEP 6: Clear and Rebuild Caches
# ============================================
Write-Host ""
Write-Host "[6/7] Clearing Laravel caches..." -ForegroundColor Yellow

& "$PhpPath\php.exe" artisan config:clear 2>$null
& "$PhpPath\php.exe" artisan cache:clear 2>$null
& "$PhpPath\php.exe" artisan view:clear 2>$null
& "$PhpPath\php.exe" artisan route:clear 2>$null
Write-Host "  OK - Caches cleared" -ForegroundColor Green

# Rebuild production cache
Write-Host "  Rebuilding production cache..." -ForegroundColor Gray
& "$PhpPath\php.exe" artisan config:cache 2>$null
& "$PhpPath\php.exe" artisan route:cache 2>$null
& "$PhpPath\php.exe" artisan view:cache 2>$null
Write-Host "  OK - Production cache built" -ForegroundColor Green

# Ensure storage link
& "$PhpPath\php.exe" artisan storage:link 2>$null

# ============================================
# STEP 7: Restart IIS
# ============================================
Write-Host ""
Write-Host "[7/7] Restarting IIS..." -ForegroundColor Yellow

iisreset /restart 2>&1
Write-Host "  OK - IIS restarted" -ForegroundColor Green

# ============================================
# CLEANUP OLD BACKUPS (keep last 5)
# ============================================
Write-Host ""
Write-Host "Cleaning up old backups (keeping last 5)..." -ForegroundColor Yellow
Get-ChildItem -Path $BackupDir -Filter "esimko_*.sql" 2>$null | 
    Sort-Object LastWriteTime -Descending | 
    Select-Object -Skip 5 | 
    Remove-Item -Force 2>$null
Write-Host "  OK - Cleanup completed" -ForegroundColor Green

# ============================================
# SHOW VERSION INFO
# ============================================
$versionFile = "$ProjectPath\version.json"
if (Test-Path $versionFile) {
    $version = Get-Content $versionFile | ConvertFrom-Json
    Write-Host ""
    Write-Host "Current Version: v$($version.version)" -ForegroundColor Cyan
}

# ============================================
# SUMMARY
# ============================================
Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host "  UPDATE COMPLETE!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Application: https://49.50.9.81" -ForegroundColor Cyan
Write-Host "  Backup file: $backupFile" -ForegroundColor Cyan
Write-Host ""
Write-Host "  To restore database if needed:" -ForegroundColor Yellow
Write-Host "  Get-Content $backupFile | mysql -u $MysqlUser $MysqlDatabase" -ForegroundColor DarkGray
Write-Host ""
