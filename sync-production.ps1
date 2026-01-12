# ============================================
# ESIMKO Sync Database from Production
# ============================================
# Downloads latest database from production server
# and imports to local Docker container
# ============================================

param(
    [switch]$SkipImport,
    [switch]$BackupOnly
)

$ErrorActionPreference = "Stop"

# ============================================
# PRODUCTION SERVER CONFIGURATION
# ============================================
$SSH_HOST = "104.248.150.30"
$SSH_PORT = 22
$SSH_USER = "root"
$SSH_PASS = "ESIMKO4rt1s4n"

$DB_HOST = "localhost"
$DB_NAME = "esimko"
$DB_USER = "esimko"
$DB_PASS = "esimko"

# Local settings
$BACKUP_DIR = Join-Path $PSScriptRoot "backups"
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

function Test-SSHAvailable {
    $sshPath = Get-Command ssh -ErrorAction SilentlyContinue
    return $null -ne $sshPath
}

function Test-PlinkAvailable {
    $plinkPath = Get-Command plink -ErrorAction SilentlyContinue
    return $null -ne $plinkPath
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
}

# Step 1: Check SSH availability
Write-Step "1/5" "Checking SSH tools..."
$useNativeSSH = Test-SSHAvailable
$usePlink = Test-PlinkAvailable

if ($useNativeSSH) {
    Write-Success "Using native SSH"
}
elseif ($usePlink) {
    Write-Success "Using PuTTY plink"
}
else {
    Write-Host "[ERROR] No SSH client found! Please install OpenSSH or PuTTY." -ForegroundColor Red
    Write-Host "        To install OpenSSH: Settings > Apps > Optional Features > OpenSSH Client" -ForegroundColor Gray
    exit 1
}

# Step 2: Test connection
Write-Step "2/5" "Testing connection to production server..."
Write-Info "Host: $SSH_HOST"

if ($useNativeSSH) {
    # For native SSH, we need to use sshpass or expect, which is complex on Windows
    # Instead, we'll use a different approach with ssh keys or plink
    Write-Info "Note: Native SSH requires key-based auth or manual password entry"
}

# Step 3: Export database from production
Write-Step "3/5" "Exporting database from production..."
Write-Info "This may take a few minutes depending on database size..."

$remoteBackupPath = "/tmp/$BACKUP_FILE"
$dumpCommand = "mysqldump -h $DB_HOST -u $DB_USER -p'$DB_PASS' --single-transaction --routines --triggers $DB_NAME > $remoteBackupPath"

try {
    if ($useNativeSSH) {
        # Create expect-like script using stdin
        $sshCommand = "echo '$SSH_PASS' | ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST `"$dumpCommand`""
        
        # Alternative: Direct SSH (will prompt for password)
        Write-Info "Running mysqldump on production server..."
        Write-Host ""
        Write-Host "       [!] You may be prompted to enter SSH password: $SSH_PASS" -ForegroundColor Yellow
        Write-Host ""
        
        # Run mysqldump via SSH
        ssh -o StrictHostKeyChecking=no -p $SSH_PORT "$SSH_USER@$SSH_HOST" $dumpCommand
        
        if ($LASTEXITCODE -ne 0) {
            throw "Failed to export database"
        }
        Write-Success "Database exported on production server"
        
        # Step 4: Download backup file
        Write-Step "4/5" "Downloading backup file..."
        Write-Info "Downloading from: $remoteBackupPath"
        Write-Info "To: $BACKUP_PATH"
        
        scp -o StrictHostKeyChecking=no -P $SSH_PORT "$SSH_USER@$SSH_HOST`:$remoteBackupPath" $BACKUP_PATH
        
        if ($LASTEXITCODE -ne 0) {
            throw "Failed to download backup"
        }
        
        # Cleanup remote file
        ssh -o StrictHostKeyChecking=no -p $SSH_PORT "$SSH_USER@$SSH_HOST" "rm -f $remoteBackupPath"
        
    }
    else {
        # Use plink (PuTTY)
        Write-Info "Using plink for SSH connection..."
        
        # Export
        $plinkArgs = "-ssh -P $SSH_PORT -l $SSH_USER -pw `"$SSH_PASS`" $SSH_HOST `"$dumpCommand`""
        Start-Process -FilePath "plink" -ArgumentList $plinkArgs -Wait -NoNewWindow
        
        Write-Success "Database exported on production server"
        
        # Download using pscp
        Write-Step "4/5" "Downloading backup file..."
        $pscpArgs = "-P $SSH_PORT -l $SSH_USER -pw `"$SSH_PASS`" $SSH_HOST`:$remoteBackupPath $BACKUP_PATH"
        Start-Process -FilePath "pscp" -ArgumentList $pscpArgs -Wait -NoNewWindow
        
        # Cleanup
        $cleanupArgs = "-ssh -P $SSH_PORT -l $SSH_USER -pw `"$SSH_PASS`" $SSH_HOST `"rm -f $remoteBackupPath`""
        Start-Process -FilePath "plink" -ArgumentList $cleanupArgs -Wait -NoNewWindow
    }
    
    # Verify download
    if (Test-Path $BACKUP_PATH) {
        $fileSize = (Get-Item $BACKUP_PATH).Length
        $fileSizeMB = [math]::Round($fileSize / 1MB, 2)
        Write-Success "Downloaded: $BACKUP_FILE ($fileSizeMB MB)"
        
        # Copy to latest
        Copy-Item $BACKUP_PATH (Join-Path $PSScriptRoot "esimko_latest_backup.sql") -Force
        Write-Success "Copied to: esimko_latest_backup.sql"
    }
    else {
        throw "Backup file not found after download"
    }
    
}
catch {
    Write-Host "[ERROR] $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Step 5: Import to Docker
if (-not $SkipImport -and -not $BackupOnly) {
    Write-Step "5/5" "Importing to Docker container..."
    
    $containerExists = docker ps -q -f name=esimko-app 2>$null
    if ($containerExists) {
        Write-Info "Importing to esimko-app container..."
        Get-Content $BACKUP_PATH -Raw | docker exec -i esimko-app mysql -u root -pMYSQLp4ssw0rd7% esimko 2>$null
        
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Database imported to Docker!"
        }
        else {
            Write-Host "       [WARN] Import may have issues, check manually" -ForegroundColor Yellow
        }
    }
    else {
        # Try production container
        $containerExists = docker ps -q -f name=esimko-db 2>$null
        if ($containerExists) {
            Write-Info "Importing to esimko-db container..."
            Get-Content $BACKUP_PATH -Raw | docker exec -i esimko-db mysql -u root -proot_password_123 esimko 2>$null
            Write-Success "Database imported to Docker!"
        }
        else {
            Write-Host "       [WARN] No Docker container found, backup saved only" -ForegroundColor Yellow
        }
    }
}
else {
    Write-Step "5/5" "Skipping import (backup only mode)"
}

# Summary
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "   SYNC COMPLETE!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""
Write-Host "   Backup saved to:" -ForegroundColor White
Write-Host "   - $BACKUP_PATH" -ForegroundColor Cyan
Write-Host "   - esimko_latest_backup.sql" -ForegroundColor Cyan
Write-Host ""
Write-Host "   To import manually:" -ForegroundColor White
Write-Host "   docker exec -i esimko-app mysql -u root -pMYSQLp4ssw0rd7% esimko < esimko_latest_backup.sql" -ForegroundColor Gray
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
