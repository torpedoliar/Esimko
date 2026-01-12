# ============================================
# ESIMKO Sync Database from Production
# ============================================
# Downloads latest database from production server
# and imports to local Docker container
# ============================================
# Usage:
#   .\sync-production.ps1              # Full sync with import
#   .\sync-production.ps1 -SkipImport  # Download only
#   .\sync-production.ps1 -BackupOnly  # Same as SkipImport
# ============================================

param(
    [switch]$SkipImport,
    [switch]$BackupOnly
)

$ErrorActionPreference = "Continue"

# ============================================
# PRODUCTION SERVER CONFIGURATION
# ============================================
$SSH_HOST = "104.248.150.30"
$SSH_PORT = 22
$SSH_USER = "root"
# Password akan diminta secara manual untuk keamanan

$DB_HOST = "localhost"
$DB_NAME = "esimko"
$DB_USER = "esimko"
$DB_PASS = "esimko"

# Local settings
$ScriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$BACKUP_DIR = Join-Path $ScriptRoot "backups"
$BACKUP_FILE = "esimko_prod_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
$BACKUP_PATH = Join-Path $BACKUP_DIR $BACKUP_FILE

# ============================================
# FUNCTIONS
# ============================================

function Write-Step {
    param([string]$Step, [string]$Message)
    Write-Host ""
    Write-Host "[$Step] $Message" -ForegroundColor Yellow
}

function Write-Success {
    param([string]$Message)
    Write-Host "       $Message" -ForegroundColor Green
}

function Write-Info {
    param([string]$Message)
    Write-Host "       $Message" -ForegroundColor Gray
}

function Write-Warn {
    param([string]$Message)
    Write-Host "       [WARN] $Message" -ForegroundColor Yellow
}

# ============================================
# MAIN SCRIPT
# ============================================

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "   ESIMKO Production Database Sync" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# Create backup directory
if (-not (Test-Path $BACKUP_DIR)) {
    New-Item -ItemType Directory -Path $BACKUP_DIR -Force | Out-Null
    Write-Info "Created backup directory: $BACKUP_DIR"
}

# Step 1: Check SSH availability
Write-Step "1/5" "Checking SSH..."
$sshAvailable = Get-Command ssh -ErrorAction SilentlyContinue
$scpAvailable = Get-Command scp -ErrorAction SilentlyContinue

if (-not $sshAvailable -or -not $scpAvailable) {
    Write-Host "[ERROR] SSH not found! Please install OpenSSH." -ForegroundColor Red
    Write-Host "        Go to: Settings > Apps > Optional Features > OpenSSH Client" -ForegroundColor Gray
    exit 1
}
Write-Success "SSH is available"

# Step 2: Show connection info
Write-Step "2/5" "Connection Info"
Write-Info "Host: $SSH_HOST"
Write-Info "User: $SSH_USER"
Write-Info "Database: $DB_NAME"
Write-Host ""
Write-Host "       SSH Password: ESIMKO4rt1s4n" -ForegroundColor Yellow
Write-Host "       (You will need to enter this when prompted)" -ForegroundColor Gray

# Step 3: Export database via SSH (direct pipe method)
Write-Step "3/5" "Exporting database from production..."
Write-Info "Running mysqldump via SSH..."
Write-Info "This may take several minutes for large databases..."
Write-Host ""

try {
    # Direct SSH with mysqldump output piped to local file
    # This is more efficient than creating file on server then downloading
    $dumpCmd = "mysqldump -u $DB_USER -p'$DB_PASS' --single-transaction --routines --triggers $DB_NAME"
    
    Write-Host "       Connecting to $SSH_HOST..." -ForegroundColor Gray
    Write-Host "       Enter password when prompted: ESIMKO4rt1s4n" -ForegroundColor Yellow
    Write-Host ""
    
    # Run SSH and capture output directly to file
    ssh -o StrictHostKeyChecking=no -o ConnectTimeout=30 -p $SSH_PORT "$SSH_USER@$SSH_HOST" $dumpCmd > $BACKUP_PATH 2>$null
    
    $exitCode = $LASTEXITCODE
    
    if ($exitCode -ne 0) {
        Write-Host "[ERROR] SSH command failed with exit code: $exitCode" -ForegroundColor Red
        exit 1
    }
    
    # Verify file was created and has content
    if (-not (Test-Path $BACKUP_PATH)) {
        Write-Host "[ERROR] Backup file was not created" -ForegroundColor Red
        exit 1
    }
    
    $fileSize = (Get-Item $BACKUP_PATH).Length
    if ($fileSize -lt 1000) {
        Write-Host "[ERROR] Backup file is too small ($fileSize bytes). Export may have failed." -ForegroundColor Red
        Write-Host "        Check if database credentials are correct." -ForegroundColor Gray
        exit 1
    }
    
    $fileSizeMB = [math]::Round($fileSize / 1MB, 2)
    Write-Success "Database exported successfully!"
    Write-Success "File: $BACKUP_FILE ($fileSizeMB MB)"
    
}
catch {
    Write-Host "[ERROR] $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Step 4: Copy to latest backup
Write-Step "4/5" "Updating latest backup..."
$latestPath = Join-Path $ScriptRoot "esimko_latest_backup.sql"
Copy-Item $BACKUP_PATH $latestPath -Force
Write-Success "Copied to: esimko_latest_backup.sql"

# Step 5: Import to Docker (optional)
if ($SkipImport -or $BackupOnly) {
    Write-Step "5/5" "Skipping import (backup only mode)"
}
else {
    Write-Step "5/5" "Importing to Docker container..."
    
    # Check for development container
    $devContainer = docker ps -q -f name=esimko-app 2>$null
    # Check for production container  
    $prodContainer = docker ps -q -f name=esimko-db 2>$null
    
    if ($devContainer) {
        Write-Info "Found development container (esimko-app)"
        Write-Info "Importing database..."
        
        Get-Content $BACKUP_PATH -Raw | docker exec -i esimko-app mysql -u root -pMYSQLp4ssw0rd7% esimko 2>$null
        
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Database imported successfully!"
        }
        else {
            Write-Warn "Import completed with warnings (this is usually OK)"
        }
    }
    elseif ($prodContainer) {
        Write-Info "Found production container (esimko-db)"
        Write-Info "Importing database..."
        
        Get-Content $BACKUP_PATH -Raw | docker exec -i esimko-db mysql -u root -proot_password_123 esimko 2>$null
        
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Database imported successfully!"
        }
        else {
            Write-Warn "Import completed with warnings (this is usually OK)"
        }
    }
    else {
        Write-Warn "No Docker container running"
        Write-Info "To import manually later, run:"
        Write-Info "docker exec -i esimko-app mysql -u root -pMYSQLp4ssw0rd7% esimko < esimko_latest_backup.sql"
    }
}

# Summary
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "   SYNC COMPLETE!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""
Write-Host "   Files saved:" -ForegroundColor White
Write-Host "   - $BACKUP_PATH" -ForegroundColor Cyan
Write-Host "   - esimko_latest_backup.sql" -ForegroundColor Cyan
Write-Host ""

# Show file info
$files = Get-ChildItem $BACKUP_DIR -Filter "*.sql" | Sort-Object LastWriteTime -Descending | Select-Object -First 5
if ($files.Count -gt 0) {
    Write-Host "   Recent backups:" -ForegroundColor White
    foreach ($file in $files) {
        $size = [math]::Round($file.Length / 1MB, 2)
        Write-Host "   - $($file.Name) ($size MB)" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
