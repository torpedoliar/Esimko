# ============================================
# ESIMKO - Windows Server 2016 Deployment Script
# ============================================
# This script installs and configures:
# - PHP 8.1 with required extensions
# - MySQL 8.0
# - Composer
# - IIS with URL Rewrite
# ============================================

$ErrorActionPreference = "Stop"
$SourcePath = "C:\Esimko"
$ProjectPath = "C:\IIS\Esimko"
$PhpPath = "C:\php"
$MysqlPath = "C:\mysql"
$ComposerPath = "C:\composer"
$DownloadPath = "C:\Downloads"

# MySQL Configuration
$MysqlRootPassword = "Esimko@2026!"
$MysqlDatabase = "esimko"
$MysqlUser = "esimko_user"
$MysqlUserPassword = "Esimko@User2026!"

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  ESIMKO - Windows Server Deployment" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Create directories
Write-Host "[1/10] Creating directories..." -ForegroundColor Yellow
New-Item -ItemType Directory -Path $DownloadPath -Force | Out-Null
New-Item -ItemType Directory -Path $PhpPath -Force | Out-Null
New-Item -ItemType Directory -Path $MysqlPath -Force | Out-Null
New-Item -ItemType Directory -Path $ComposerPath -Force | Out-Null

# Copy project from source if not exists
if (-not (Test-Path "$ProjectPath\artisan")) {
    Write-Host "  Copying project from $SourcePath to $ProjectPath..." -ForegroundColor Gray
    if (Test-Path $SourcePath) {
        Copy-Item -Path "$SourcePath\*" -Destination $ProjectPath -Recurse -Force
        Write-Host "  Project copied successfully" -ForegroundColor Green
    }
    else {
        Write-Host "ERROR: Source path $SourcePath does not exist!" -ForegroundColor Red
        exit 1
    }
}
else {
    Write-Host "  Project already exists at $ProjectPath" -ForegroundColor Green
}

# ============================================
# STEP 2: Install IIS
# ============================================
Write-Host "[2/10] Installing IIS..." -ForegroundColor Yellow

# Check if IIS is already installed
$iisFeature = Get-WindowsFeature -Name Web-Server
if ($iisFeature.Installed) {
    Write-Host "  IIS already installed" -ForegroundColor Green
}
else {
    Install-WindowsFeature -Name Web-Server -IncludeManagementTools
    Install-WindowsFeature -Name Web-CGI
    Write-Host "  IIS installed successfully" -ForegroundColor Green
}

# ============================================
# STEP 3: Download and Install PHP
# ============================================
Write-Host "[3/10] Installing PHP 8.1..." -ForegroundColor Yellow

$phpZip = "$DownloadPath\php-8.1.zip"
$phpUrl = "https://windows.php.net/downloads/releases/php-8.1.34-nts-Win32-vs16-x64.zip"

if (-not (Test-Path "$PhpPath\php.exe")) {
    Write-Host "  Downloading PHP 8.1..." -ForegroundColor Gray
    
    # Use TLS 1.2
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    
    Invoke-WebRequest -Uri $phpUrl -OutFile $phpZip -UseBasicParsing
    
    Write-Host "  Extracting PHP..." -ForegroundColor Gray
    Expand-Archive -Path $phpZip -DestinationPath $PhpPath -Force
    
    # Configure PHP
    Write-Host "  Configuring PHP..." -ForegroundColor Gray
    Copy-Item "$PhpPath\php.ini-production" "$PhpPath\php.ini"
    
    # Enable required extensions
    $phpIni = Get-Content "$PhpPath\php.ini" -Raw
    $phpIni = $phpIni -replace ";extension_dir = `"ext`"", "extension_dir = `"$PhpPath\ext`""
    $phpIni = $phpIni -replace ";extension=curl", "extension=curl"
    $phpIni = $phpIni -replace ";extension=fileinfo", "extension=fileinfo"
    $phpIni = $phpIni -replace ";extension=gd", "extension=gd"
    $phpIni = $phpIni -replace ";extension=intl", "extension=intl"
    $phpIni = $phpIni -replace ";extension=mbstring", "extension=mbstring"
    $phpIni = $phpIni -replace ";extension=exif", "extension=exif"
    $phpIni = $phpIni -replace ";extension=openssl", "extension=openssl"
    $phpIni = $phpIni -replace ";extension=pdo_mysql", "extension=pdo_mysql"
    $phpIni = $phpIni -replace ";extension=mysqli", "extension=mysqli"
    $phpIni = $phpIni -replace ";extension=zip", "extension=zip"
    $phpIni = $phpIni -replace "upload_max_filesize = 2M", "upload_max_filesize = 50M"
    $phpIni = $phpIni -replace "post_max_size = 8M", "post_max_size = 50M"
    $phpIni = $phpIni -replace "memory_limit = 128M", "memory_limit = 512M"
    $phpIni = $phpIni -replace "max_execution_time = 30", "max_execution_time = 300"
    $phpIni | Set-Content "$PhpPath\php.ini"
    
    Write-Host "  PHP 8.1 installed" -ForegroundColor Green
}
else {
    Write-Host "  PHP already installed" -ForegroundColor Green
}

# Add PHP to PATH
$currentPath = [Environment]::GetEnvironmentVariable("Path", "Machine")
if ($currentPath -notlike "*$PhpPath*") {
    Write-Host "  Adding PHP to system PATH..." -ForegroundColor Gray
    [Environment]::SetEnvironmentVariable("Path", "$currentPath;$PhpPath", "Machine")
}
$env:Path = "$env:Path;$PhpPath"

# ============================================
# STEP 4: Install MySQL using Chocolatey
# ============================================
Write-Host "[4/10] Installing MySQL 8.0..." -ForegroundColor Yellow

# Check if MySQL is already installed
$mysqlService = Get-Service -Name "MySQL*" -ErrorAction SilentlyContinue
if (-not $mysqlService) {
    # Install Chocolatey if not present
    if (-not (Get-Command choco -ErrorAction SilentlyContinue)) {
        Write-Host "  Installing Chocolatey..." -ForegroundColor Gray
        Set-ExecutionPolicy Bypass -Scope Process -Force
        [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
        Invoke-Expression ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
        $env:Path = "$env:Path;C:\ProgramData\chocolatey\bin"
    }
    
    Write-Host "  Installing MySQL via Chocolatey..." -ForegroundColor Gray
    choco install mysql -y --params "/port:3306 /password:$MysqlRootPassword"
    
    # Refresh PATH
    $env:Path = [System.Environment]::GetEnvironmentVariable("Path", "Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path", "User")
    
    # Wait for MySQL to start
    Start-Sleep -Seconds 10
    
    Write-Host "  MySQL 8.0 installed" -ForegroundColor Green
}
else {
    Write-Host "  MySQL already installed" -ForegroundColor Green
}

# Find MySQL path
$mysqlBinPath = "C:\tools\mysql\current\bin"
if (-not (Test-Path $mysqlBinPath)) {
    $mysqlBinPath = "C:\Program Files\MySQL\MySQL Server 8.0\bin"
}
$MysqlPath = Split-Path $mysqlBinPath -Parent

# Add MySQL to PATH
$currentPath = [Environment]::GetEnvironmentVariable("Path", "Machine")
if ($currentPath -notlike "*$MysqlPath\bin*") {
    [Environment]::SetEnvironmentVariable("Path", "$currentPath;$MysqlPath\bin", "Machine")
}
$env:Path = "$env:Path;$MysqlPath\bin"

# ============================================
# STEP 5: Create Database
# ============================================
Write-Host "[5/10] Creating database..." -ForegroundColor Yellow

# Find mysql executable
$mysqlExe = Get-Command mysql -ErrorAction SilentlyContinue
if (-not $mysqlExe) {
    $mysqlExe = "$mysqlBinPath\mysql.exe"
}

try {
    $sqlCommands = @"
CREATE DATABASE IF NOT EXISTS $MysqlDatabase CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$MysqlUser'@'localhost' IDENTIFIED BY '$MysqlUserPassword';
GRANT ALL PRIVILEGES ON $MysqlDatabase.* TO '$MysqlUser'@'localhost';
FLUSH PRIVILEGES;
"@
    $sqlCommands | & mysql -u root -p"$MysqlRootPassword" 2>$null
    Write-Host "  Database created" -ForegroundColor Green
}
catch {
    Write-Host "  Note: Database may already exist or MySQL needs manual setup" -ForegroundColor Yellow
}

# ============================================
# STEP 6: Install Composer
# ============================================
Write-Host "[6/10] Installing Composer..." -ForegroundColor Yellow

$composerSetup = "$DownloadPath\composer-setup.php"
$composerPhar = "$ComposerPath\composer.phar"
$composerBat = "$ComposerPath\composer.bat"

if (-not (Test-Path $composerPhar)) {
    Write-Host "  Downloading Composer..." -ForegroundColor Gray
    Invoke-WebRequest -Uri "https://getcomposer.org/installer" -OutFile $composerSetup -UseBasicParsing
    
    & "$PhpPath\php.exe" $composerSetup --install-dir=$ComposerPath --filename=composer.phar
    
    # Create composer.bat
    "@echo off`r`nphp `"$ComposerPath\composer.phar`" %*" | Set-Content $composerBat
    
    Write-Host "  Composer installed" -ForegroundColor Green
}
else {
    Write-Host "  Composer already installed" -ForegroundColor Green
}

# Add Composer to PATH
$currentPath = [Environment]::GetEnvironmentVariable("Path", "Machine")
if ($currentPath -notlike "*$ComposerPath*") {
    [Environment]::SetEnvironmentVariable("Path", "$currentPath;$ComposerPath", "Machine")
}
$env:Path = "$env:Path;$ComposerPath"

# ============================================
# STEP 7: Install URL Rewrite for IIS
# ============================================
Write-Host "[7/10] Installing URL Rewrite module..." -ForegroundColor Yellow

$urlRewriteMsi = "$DownloadPath\rewrite_amd64_en-US.msi"

# Check if URL Rewrite is installed
$urlRewriteInstalled = Get-WebGlobalModule -Name "RewriteModule" -ErrorAction SilentlyContinue
if (-not $urlRewriteInstalled) {
    Write-Host "  Downloading URL Rewrite..." -ForegroundColor Gray
    Invoke-WebRequest -Uri "https://download.microsoft.com/download/1/2/8/128E2E22-C1B9-44A4-BE2A-5859ED1D4592/rewrite_amd64_en-US.msi" -OutFile $urlRewriteMsi -UseBasicParsing
    
    Write-Host "  Installing URL Rewrite..." -ForegroundColor Gray
    Start-Process msiexec.exe -ArgumentList "/i `"$urlRewriteMsi`" /quiet /norestart" -Wait
    Write-Host "  URL Rewrite installed" -ForegroundColor Green
}
else {
    Write-Host "  URL Rewrite already installed" -ForegroundColor Green
}

# ============================================
# STEP 8: Configure Laravel
# ============================================
Write-Host "[8/10] Configuring Laravel..." -ForegroundColor Yellow

# Create .env file
$envContent = @"
APP_NAME=ESIMKO
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://49.50.9.81

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$MysqlDatabase
DB_USERNAME=$MysqlUser
DB_PASSWORD=$MysqlUserPassword

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

TRUSTED_PROXIES=*
"@
$envContent | Set-Content "$ProjectPath\.env"

# Install dependencies
Write-Host "  Installing Composer dependencies..." -ForegroundColor Gray
Set-Location $ProjectPath
& "$PhpPath\php.exe" "$ComposerPath\composer.phar" install --no-dev --optimize-autoloader

# Generate key
Write-Host "  Generating application key..." -ForegroundColor Gray
& "$PhpPath\php.exe" artisan key:generate --force

# Set permissions
Write-Host "  Setting storage permissions..." -ForegroundColor Gray
$acl = Get-Acl "$ProjectPath\storage"
$accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule("IIS_IUSRS", "FullControl", "ContainerInherit,ObjectInherit", "None", "Allow")
$acl.SetAccessRule($accessRule)
Set-Acl "$ProjectPath\storage" $acl
Set-Acl "$ProjectPath\bootstrap\cache" $acl

Write-Host "  Laravel configured" -ForegroundColor Green

# ============================================
# STEP 9: Import Database (if backup exists)
# ============================================
Write-Host "[9/10] Checking for database backup..." -ForegroundColor Yellow

$latestBackup = "$ProjectPath\esimko_latest_backup.sql"
if (Test-Path $latestBackup) {
    Write-Host "  Found backup, importing database..." -ForegroundColor Gray
    Write-Host "  This may take several minutes..." -ForegroundColor Gray
    Get-Content $latestBackup -Raw | & "$MysqlPath\bin\mysql.exe" -u root -p"$MysqlRootPassword" $MysqlDatabase
    Write-Host "  Database imported" -ForegroundColor Green
}
else {
    Write-Host "  No backup found, running migrations..." -ForegroundColor Gray
    & "$PhpPath\php.exe" artisan migrate --force
}


# ============================================
# STEP 10: Configure IIS Site
# ============================================
Write-Host "[10/10] Configuring IIS website..." -ForegroundColor Yellow

Import-Module WebAdministration

# Create FastCGI handler for PHP
$fastCgiPath = "$PhpPath\php-cgi.exe"
$fastCgiSection = Get-WebConfigurationProperty -Filter /system.webServer/fastCgi -Name collection

$phpHandler = $fastCgiSection | Where-Object { $_.fullPath -eq $fastCgiPath }
if (-not $phpHandler) {
    Add-WebConfiguration -Filter /system.webServer/fastCgi -Value @{
        fullPath            = $fastCgiPath
        arguments           = ""
        maxInstances        = 4
        instanceMaxRequests = 10000
        activityTimeout     = 600
        requestTimeout      = 600
    }
    Write-Host "  FastCGI handler added" -ForegroundColor Green
}
else {
    Write-Host "  FastCGI handler already exists" -ForegroundColor Green
}

# Use existing ESIMKO website (already created in IIS)
$siteName = "Esimko"
Write-Host "  Using existing IIS site: $siteName" -ForegroundColor Green

# Create web.config for Laravel URL Rewrite
$webConfig = @"
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <handlers>
            <remove name="PHP_via_FastCGI" />
            <add name="PHP_via_FastCGI" path="*.php" verb="*" modules="FastCgiModule" scriptProcessor="$fastCgiPath" resourceType="Either" requireAccess="Script" />
        </handlers>
        <rewrite>
            <rules>
                <rule name="Imported Rule 1" stopProcessing="true">
                    <match url="^(.*)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php/{R:1}" />
                </rule>
            </rules>
        </rewrite>
        <defaultDocument>
            <files>
                <clear />
                <add value="index.php" />
                <add value="index.html" />
            </files>
        </defaultDocument>
        <directoryBrowse enabled="false" />
    </system.webServer>
</configuration>
"@
$webConfig | Set-Content "$ProjectPath\public\web.config"
Write-Host "  web.config created for URL rewrite" -ForegroundColor Green

# Restart website
try {
    Stop-Website -Name $siteName -ErrorAction SilentlyContinue
    Start-Website -Name $siteName
    Write-Host "  IIS website restarted" -ForegroundColor Green
}
catch {
    Write-Host "  Note: Please restart IIS site manually if needed" -ForegroundColor Yellow
}

Write-Host "  IIS website configured" -ForegroundColor Green

# ============================================
# SUMMARY
# ============================================
Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host "  DEPLOYMENT COMPLETE!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Host "Application URL: http://49.50.9.81" -ForegroundColor Cyan
Write-Host ""
Write-Host "MySQL Credentials:" -ForegroundColor Yellow
Write-Host "  Root Password: $MysqlRootPassword" -ForegroundColor White
Write-Host "  Database: $MysqlDatabase" -ForegroundColor White
Write-Host "  User: $MysqlUser" -ForegroundColor White
Write-Host "  Password: $MysqlUserPassword" -ForegroundColor White
Write-Host ""
Write-Host "Paths:" -ForegroundColor Yellow
Write-Host "  PHP: $PhpPath" -ForegroundColor White
Write-Host "  MySQL: $MysqlPath" -ForegroundColor White
Write-Host "  Project: $ProjectPath" -ForegroundColor White
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
