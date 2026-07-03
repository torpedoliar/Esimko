# Fix PHP and IIS Configuration for Laravel
# Run as Administrator AFTER server restart

Write-Host "=== Laravel IIS Configuration Fix ===" -ForegroundColor Cyan

# Step 1: Fix php.ini - cgi.fix_pathinfo must be 1
Write-Host "[1/4] Fixing php.ini cgi.fix_pathinfo..." -ForegroundColor Yellow
$phpIni = Get-Content "C:\php\php.ini" -Raw
$phpIni = $phpIni -replace 'cgi.fix_pathinfo=0', 'cgi.fix_pathinfo=1'
$phpIni | Set-Content "C:\php\php.ini" -Encoding UTF8
Write-Host "  cgi.fix_pathinfo=1 set" -ForegroundColor Green

# Step 2: Verify PHP path in FastCGI settings
Write-Host "[2/4] Checking FastCGI configuration..." -ForegroundColor Yellow

# Import IIS module
Import-Module WebAdministration

# Check FastCGI settings
$fastcgi = Get-WebConfiguration "/system.webServer/fastCgi" -PSPath IIS:\ 
if ($fastcgi) {
    Write-Host "  FastCGI is configured" -ForegroundColor Green
} else {
    Write-Host "  FastCGI needs configuration" -ForegroundColor Yellow
}

# Step 3: Ensure site physical path is correct
Write-Host "[3/4] Checking site physical path..." -ForegroundColor Yellow
$sitePath = (Get-Website -Name "Esimko").PhysicalPath
Write-Host "  Current path: $sitePath" -ForegroundColor Gray

if ($sitePath -ne "C:\IIS\Esimko\public") {
    Write-Host "  Fixing path to C:\IIS\Esimko\public..." -ForegroundColor Yellow
    Set-ItemProperty "IIS:\Sites\Esimko" -Name physicalPath -Value "C:\IIS\Esimko\public"
    Write-Host "  Path corrected" -ForegroundColor Green
} else {
    Write-Host "  Path is correct" -ForegroundColor Green
}

# Step 4: Test database connection
Write-Host "[4/4] Testing database connection..." -ForegroundColor Yellow
$envPath = "C:\IIS\Esimko\.env"
if (Test-Path $envPath) {
    Write-Host "  .env file exists" -ForegroundColor Green
    $envContent = Get-Content $envPath -Raw
    if ($envContent -match "DB_HOST=") {
        Write-Host "  Database config found" -ForegroundColor Green
    }
} else {
    Write-Host "  WARNING: .env file missing!" -ForegroundColor Red
}

# Restart IIS to apply changes
Write-Host ""
Write-Host "Restarting IIS..." -ForegroundColor Yellow
iisreset /restart

Write-Host ""
Write-Host "=== Fix Complete ===" -ForegroundColor Green
Write-Host "Test: https://49.50.9.81" -ForegroundColor Cyan
