# ============================================
# DATABASE IMPORT SCRIPT
# ESIMKO - Restore Database
# ============================================
# Usage:
#   .\db-import.ps1                    # Menu pilih file backup
#   .\db-import.ps1 -File backup.sql   # Import file spesifik
#   .\db-import.ps1 -Latest            # Import backup terakhir
# ============================================

param(
    [string]$File,
    [switch]$Latest
)

$ErrorActionPreference = "Continue"
$ScriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ScriptPath

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  ESIMKO - Database Import" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Check if Docker container is running
$dbContainer = docker ps -q -f "name=esimko-db" 2>$null
if (-not $dbContainer) {
    Write-Host "ERROR: Docker container esimko-db not running!" -ForegroundColor Red
    Write-Host "Run: docker-compose -f docker-compose.dev.yml up -d" -ForegroundColor Yellow
    exit 1
}

# Determine which file to import
$selectedFile = $null

if ($Latest) {
    # Use latest backup
    if (Test-Path "esimko_latest_backup.sql") {
        $selectedFile = "esimko_latest_backup.sql"
    }
    else {
        Write-Host "ERROR: No latest backup found (esimko_latest_backup.sql)" -ForegroundColor Red
        exit 1
    }
}
elseif ($File) {
    # Use specified file
    if (Test-Path $File) {
        $selectedFile = $File
    }
    else {
        Write-Host "ERROR: File not found: $File" -ForegroundColor Red
        exit 1
    }
}
else {
    # Show menu to select file
    Write-Host "Available backup files:" -ForegroundColor Yellow
    Write-Host ""
    
    $backups = @()
    
    # Add latest backup if exists
    if (Test-Path "esimko_latest_backup.sql") {
        $backups += [PSCustomObject]@{
            Index = 1
            Name  = "esimko_latest_backup.sql (LATEST)"
            Path  = "esimko_latest_backup.sql"
            Size  = [math]::Round((Get-Item "esimko_latest_backup.sql").Length / 1MB, 2)
            Date  = (Get-Item "esimko_latest_backup.sql").LastWriteTime
        }
    }
    
    # Get all backup files from backups folder
    $backupFiles = Get-ChildItem -Path "backups" -Filter "*.sql" -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending
    
    foreach ($backup in $backupFiles) {
        $backups += [PSCustomObject]@{
            Index = $backups.Count + 1
            Name  = $backup.Name
            Path  = $backup.FullName
            Size  = [math]::Round($backup.Length / 1MB, 2)
            Date  = $backup.LastWriteTime
        }
    }
    
    if ($backups.Count -eq 0) {
        Write-Host "No backup files found!" -ForegroundColor Red
        Write-Host ""
        Write-Host "Create a backup first using: .\db-export.ps1" -ForegroundColor Yellow
        exit 1
    }
    
    # Display menu
    Write-Host "  #   | Size (MB) | Date                | Filename" -ForegroundColor Gray
    Write-Host "  ----|-----------|---------------------|--------------------------------" -ForegroundColor Gray
    
    foreach ($backup in $backups) {
        $dateStr = $backup.Date.ToString("yyyy-MM-dd HH:mm:ss")
        Write-Host ("  {0,-3} | {1,9} | {2} | {3}" -f $backup.Index, $backup.Size, $dateStr, $backup.Name)
    }
    
    Write-Host ""
    Write-Host "  0   | Cancel" -ForegroundColor Gray
    Write-Host ""
    
    # Get user selection
    $selection = Read-Host "Select backup number to import"
    
    if ($selection -eq "0" -or $selection -eq "") {
        Write-Host "Cancelled." -ForegroundColor Yellow
        exit 0
    }
    
    $selIndex = [int]$selection
    $selected = $backups | Where-Object { $_.Index -eq $selIndex }
    
    if (-not $selected) {
        Write-Host "Invalid selection!" -ForegroundColor Red
        exit 1
    }
    
    $selectedFile = $selected.Path
}

# Confirm import
$fileSize = [math]::Round((Get-Item $selectedFile).Length / 1MB, 2)
Write-Host ""
Write-Host "Selected: $selectedFile ($fileSize MB)" -ForegroundColor Cyan
Write-Host ""
Write-Host "WARNING: This will REPLACE all data in the local database!" -ForegroundColor Yellow
$confirm = Read-Host "Continue? (y/N)"

if ($confirm -ne "y" -and $confirm -ne "Y") {
    Write-Host "Cancelled." -ForegroundColor Yellow
    exit 0
}

# Import database
Write-Host ""
Write-Host "Importing database..." -ForegroundColor Yellow
Write-Host "This may take several minutes for large databases..." -ForegroundColor Gray

$startTime = Get-Date
Get-Content $selectedFile -Raw | docker exec -i esimko-db mysql -u root -proot_password_123 esimko 2>$null
$endTime = Get-Date
$duration = ($endTime - $startTime).TotalSeconds

# Verify import
$tableCount = docker exec esimko-db mysql -u root -proot_password_123 esimko -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'esimko';" 2>$null

Write-Host ""
if ($tableCount -and [int]$tableCount -gt 50) {
    Write-Host "SUCCESS!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Tables imported: $tableCount" -ForegroundColor Cyan
    Write-Host "Duration: $([math]::Round($duration, 1)) seconds" -ForegroundColor Cyan
}
else {
    Write-Host "WARNING: Import may have issues (found $tableCount tables)" -ForegroundColor Yellow
}

# Clear Laravel cache
Write-Host ""
Write-Host "Clearing Laravel cache..." -ForegroundColor Yellow
docker exec esimko-app php artisan cache:clear 2>$null | Out-Null
docker exec esimko-app php artisan config:clear 2>$null | Out-Null
Write-Host "Cache cleared!" -ForegroundColor Green

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  Database import complete!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
