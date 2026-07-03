# Install PHP 7.4 for Laravel 7 Compatibility
# Run as Administrator

Write-Host "Installing PHP 7.4..." -ForegroundColor Yellow

# Stop IIS first
Stop-Service W3SVC -Force -ErrorAction SilentlyContinue

# Backup current PHP 8.1
if (Test-Path "C:\php") {
    if (Test-Path "C:\php81") {
        Remove-Item "C:\php81" -Recurse -Force
    }
    Rename-Item "C:\php" "C:\php81"
    Write-Host "  Backed up PHP 8.1 to C:\php81" -ForegroundColor Gray
}

# Create new PHP directory
New-Item -ItemType Directory -Path "C:\php" -Force | Out-Null

# Download PHP 7.4.33 (VC15 x64 Thread Safe)
$phpUrl = "https://windows.php.net/downloads/releases/archives/php-7.4.33-nts-Win32-vc15-x64.zip"
$phpZip = "C:\Downloads\php74.zip"

Write-Host "  Downloading PHP 7.4.33..." -ForegroundColor Gray
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
Invoke-WebRequest -Uri $phpUrl -OutFile $phpZip -UseBasicParsing

Write-Host "  Extracting PHP 7.4..." -ForegroundColor Gray
Expand-Archive -Path $phpZip -DestinationPath "C:\php" -Force

# Configure php.ini
Copy-Item "C:\php\php.ini-production" "C:\php\php.ini"

$phpIni = Get-Content "C:\php\php.ini"
$phpIni = $phpIni -replace ';extension_dir = "ext"', 'extension_dir = "C:\php\ext"'
$phpIni = $phpIni -replace ';extension=curl', 'extension=curl'
$phpIni = $phpIni -replace ';extension=fileinfo', 'extension=fileinfo'
$phpIni = $phpIni -replace ';extension=gd2', 'extension=gd2'
$phpIni = $phpIni -replace ';extension=intl', 'extension=intl'
$phpIni = $phpIni -replace ';extension=mbstring', 'extension=mbstring'
$phpIni = $phpIni -replace ';extension=openssl', 'extension=openssl'
$phpIni = $phpIni -replace ';extension=pdo_mysql', 'extension=pdo_mysql'
$phpIni = $phpIni -replace ';extension=mysqli', 'extension=mysqli'
$phpIni = $phpIni -replace ';extension=exif', 'extension=exif'
$phpIni = $phpIni -replace 'memory_limit = 128M', 'memory_limit = 512M'
$phpIni = $phpIni -replace 'upload_max_filesize = 2M', 'upload_max_filesize = 50M'
$phpIni = $phpIni -replace 'post_max_size = 8M', 'post_max_size = 50M'
$phpIni = $phpIni -replace ';cgi.fix_pathinfo=1', 'cgi.fix_pathinfo=0'
$phpIni | Set-Content "C:\php\php.ini"

Write-Host "  PHP 7.4 configured" -ForegroundColor Green

# Restart IIS
Start-Service W3SVC

# Test PHP version
& C:\php\php.exe -v

Write-Host ""
Write-Host "PHP 7.4 installed successfully!" -ForegroundColor Green
Write-Host "Now run: C:\IIS\Esimko\setup_db.bat" -ForegroundColor Cyan
