# ============================================
# ESIMKO Full Sync & Deploy
# ============================================
# 1. Download database from production
# 2. Deploy to local Docker
# ============================================

param(
    [switch]$Production,
    [switch]$SkipSync,
    [switch]$Fresh
)

$ErrorActionPreference = "Continue"
$ScriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ScriptPath

Write-Host ""
Write-Host "==========================================" -ForegroundColor Magenta
Write-Host "   ESIMKO Full Sync & Deploy" -ForegroundColor Magenta
Write-Host "==========================================" -ForegroundColor Magenta
Write-Host ""

# Step 1: Sync from production
if (-not $SkipSync) {
    Write-Host "[PHASE 1] Syncing database from production..." -ForegroundColor Cyan
    Write-Host ""
    
    # Run sync script
    & "$ScriptPath\sync-production.ps1" -SkipImport
    
    if (-not (Test-Path "esimko_latest_backup.sql")) {
        Write-Host "[ERROR] Sync failed, no backup file created!" -ForegroundColor Red
        exit 1
    }
    
    Write-Host ""
    Write-Host "[PHASE 1] Sync complete!" -ForegroundColor Green
}
else {
    Write-Host "[PHASE 1] Skipping sync (-SkipSync)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Magenta
Write-Host ""

# Step 2: Deploy
Write-Host "[PHASE 2] Deploying to Docker..." -ForegroundColor Cyan
Write-Host ""

$deployArgs = @()
if ($Production) { $deployArgs += "-Production" }
if ($Fresh) { $deployArgs += "-Fresh" }

& "$ScriptPath\deploy.ps1" @deployArgs

Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "   FULL SYNC & DEPLOY COMPLETE!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""
Write-Host "   Your local environment now has:" -ForegroundColor White
Write-Host "   - Latest database from production" -ForegroundColor Cyan
Write-Host "   - Fresh Docker containers" -ForegroundColor Cyan
Write-Host ""
if ($Production) {
    Write-Host "   Access: http://localhost (via NPM)" -ForegroundColor Cyan
    Write-Host "   NPM Admin: http://localhost:81" -ForegroundColor Cyan
}
else {
    Write-Host "   Access: http://localhost:8080" -ForegroundColor Cyan
}
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
