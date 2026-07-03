# Import SSL and configure HTTPS binding for IIS
# Run as Administrator

Import-Module WebAdministration

$siteName = "Esimko"
$pfxPath = "C:\IIS\Esimko\esimko.pfx"
$password = "Esimko2026"

Write-Host "=== SSL Certificate Import ===" -ForegroundColor Cyan

# Step 1: Import using certutil
Write-Host "[1/3] Importing certificate..." -ForegroundColor Yellow
$result = & certutil -f -p $password -importpfx $pfxPath
Write-Host $result

# Step 2: Get the thumbprint
Write-Host "[2/3] Getting certificate thumbprint..." -ForegroundColor Yellow
$cert = Get-ChildItem Cert:\LocalMachine\My | Where-Object { $_.Subject -like "*esimko*" -or $_.Subject -like "*Let's Encrypt*" } | Sort-Object NotBefore -Descending | Select-Object -First 1
if ($cert) {
    $thumbprint = $cert.Thumbprint
    Write-Host "  Found: $thumbprint" -ForegroundColor Green
} else {
    Write-Host "  Certificate not found!" -ForegroundColor Red
    exit 1
}

# Step 3: Configure HTTPS binding
Write-Host "[3/3] Configuring HTTPS binding..." -ForegroundColor Yellow

# Remove existing HTTPS binding
try {
    Remove-WebBinding -Name $siteName -Protocol https -ErrorAction SilentlyContinue
    netsh http delete sslcert ipport=0.0.0.0:443 2>$null
} catch {}

# Add HTTPS binding
New-WebBinding -Name $siteName -Protocol https -Port 443 -IPAddress "*"

# Bind certificate using IIS approach
$binding = Get-WebBinding -Name $siteName -Protocol https
if ($binding) {
    $binding.AddSslCertificate($thumbprint, "My")
    Write-Host "  HTTPS binding configured!" -ForegroundColor Green
} else {
    Write-Host "  Failed to get binding" -ForegroundColor Red
}

# Restart IIS
Write-Host "Restarting IIS..." -ForegroundColor Yellow
iisreset /restart

Write-Host ""
Write-Host "=== SSL Configuration Complete ===" -ForegroundColor Green
Write-Host "Access: https://49.50.9.81" -ForegroundColor Cyan
