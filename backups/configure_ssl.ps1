# Configure SSL for IIS Esimko Site
# Run as Administrator

Import-Module WebAdministration

$siteName = "Esimko"
$certPath = "C:\IIS\Esimko\esimko.com"
$pfxPath = "C:\IIS\Esimko\esimko.com\esimko.pfx"
$password = "Esimko@2026!"

Write-Host "Configuring SSL for $siteName..." -ForegroundColor Yellow

# Step 1: Convert PEM to PFX using OpenSSL (included in Git or can be installed)
Write-Host "  [1/4] Converting PEM to PFX..." -ForegroundColor Gray

# Check if OpenSSL is available
$openssl = Get-Command openssl -ErrorAction SilentlyContinue
if (-not $openssl) {
    # Try common paths
    $opensslPaths = @(
        "C:\Program Files\Git\usr\bin\openssl.exe",
        "C:\Program Files\OpenSSL-Win64\bin\openssl.exe",
        "C:\OpenSSL-Win64\bin\openssl.exe"
    )
    foreach ($path in $opensslPaths) {
        if (Test-Path $path) {
            $openssl = $path
            break
        }
    }
}

if (-not $openssl) {
    Write-Host "OpenSSL not found. Installing via Chocolatey..." -ForegroundColor Yellow
    choco install openssl -y
    $openssl = "C:\Program Files\OpenSSL-Win64\bin\openssl.exe"
}

# Convert to PFX
$opensslCmd = "& `"$openssl`" pkcs12 -export -out `"$pfxPath`" -inkey `"$certPath\privkey.pem`" -in `"$certPath\cert.pem`" -certfile `"$certPath\chain.pem`" -passout pass:$password"
Invoke-Expression $opensslCmd

if (Test-Path $pfxPath) {
    Write-Host "    PFX created successfully" -ForegroundColor Green
} else {
    Write-Host "    Failed to create PFX" -ForegroundColor Red
    exit 1
}

# Step 2: Import certificate to Windows certificate store
Write-Host "  [2/4] Importing certificate to Windows store..." -ForegroundColor Gray
$securePassword = ConvertTo-SecureString -String $password -Force -AsPlainText
$cert = Import-PfxCertificate -FilePath $pfxPath -CertStoreLocation Cert:\LocalMachine\My -Password $securePassword
$thumbprint = $cert.Thumbprint
Write-Host "    Certificate imported: $thumbprint" -ForegroundColor Green

# Step 3: Add HTTPS binding to IIS site
Write-Host "  [3/4] Adding HTTPS binding to IIS site..." -ForegroundColor Gray

# Remove existing HTTPS binding if any
$existingBinding = Get-WebBinding -Name $siteName -Protocol https -ErrorAction SilentlyContinue
if ($existingBinding) {
    Remove-WebBinding -Name $siteName -Protocol https
}

# Add new HTTPS binding
New-WebBinding -Name $siteName -Protocol https -Port 443 -IPAddress "*"

# Assign certificate to binding
$binding = Get-WebBinding -Name $siteName -Protocol https
$binding.AddSslCertificate($thumbprint, "My")

Write-Host "    HTTPS binding configured" -ForegroundColor Green

# Step 4: Restart IIS
Write-Host "  [4/4] Restarting IIS..." -ForegroundColor Gray
iisreset /restart

Write-Host ""
Write-Host "SSL Configuration Complete!" -ForegroundColor Green
Write-Host "Site is now accessible at: https://49.50.9.81" -ForegroundColor Cyan
