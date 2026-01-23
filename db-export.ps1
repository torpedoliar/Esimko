# ============================================
# DATABASE EXPORT SCRIPT
# ESIMKO - Backup Database
# ============================================
# Usage:
#   .\db-export.ps1                    # Export lokal
#   .\db-export.ps1 -Production        # Export dari production
# ============================================

param(
    [switch]$Production
)

$ErrorActionPreference = "Continue"
$ScriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ScriptPath

# Create backups directory if not exists
if (-not (Test-Path "backups")) {
    New-Item -ItemType Directory -Path "backups" | Out-Null
}

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  ESIMKO - Database Export" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

if ($Production) {
    # Export from Production Server
    Write-Host "Mode: PRODUCTION (104.248.150.30)" -ForegroundColor Yellow
    $backupFile = "backups/esimko_production_$timestamp.sql"
    
    Write-Host ""
    Write-Host "Exporting database from production server..." -ForegroundColor Yellow
    
    # Check if plink exists
    $plink = Get-Command plink -ErrorAction SilentlyContinue
    if (-not $plink) {
        Write-Host "ERROR: plink.exe not found! Please install PuTTY." -ForegroundColor Red
        exit 1
    }
    
    echo y | plink -ssh root@104.248.150.30 -pw "ESIMKO4rt1s4n" "mysqldump -u root -proot_password_123 esimko" > $backupFile 2>$null
    
}
else {
    # Export from Local Docker
    Write-Host "Mode: LOCAL (Docker)" -ForegroundColor Green
    $backupFile = "backups/esimko_local_$timestamp.sql"
    
    # Check if container is running
    $dbContainer = docker ps -q -f "name=esimko-db" 2>$null
    if (-not $dbContainer) {
        Write-Host "ERROR: Docker container esimko-db not running!" -ForegroundColor Red
        Write-Host "Run: docker-compose -f docker-compose.dev.yml up -d" -ForegroundColor Yellow
        exit 1
    }
    
    Write-Host ""
    Write-Host "Exporting database from local Docker..." -ForegroundColor Yellow
    
    docker exec esimko-db mysqldump -u root -proot_password_123 esimko > $backupFile 2>$null
}

# Verify backup
if ((Test-Path $backupFile) -and (Get-Item $backupFile).Length -gt 1000) {
    $fileSize = [math]::Round((Get-Item $backupFile).Length / 1MB, 2)
    Write-Host ""
    Write-Host "SUCCESS!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Backup file: $backupFile" -ForegroundColor Cyan
    Write-Host "Size: $fileSize MB" -ForegroundColor Cyan
    
    # Also copy to latest backup
    Copy-Item $backupFile "esimko_latest_backup.sql" -Force
    Write-Host ""
    Write-Host "Also saved as: esimko_latest_backup.sql" -ForegroundColor Gray
}
else {
    Write-Host ""
    Write-Host "ERROR: Backup failed or file is empty!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
