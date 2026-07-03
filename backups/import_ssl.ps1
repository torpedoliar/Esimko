# Import SSL Certificate and Configure IIS HTTPS
# Run as Administrator

Import-Module WebAdministration

$siteName = "Esimko"
$pfxPath = "C:\IIS\Esimko\esimko.pfx"
$password = "Esimko2026"

Write-Host "Importing SSL Certificate..." -ForegroundColor Yellow

# Import certificate to Windows store
$securePassword = ConvertTo-SecureString -String $password -Force -AsPlainText
$cert = Import-PfxCertificate -FilePath $pfxPath -CertStoreLocation Cert:\LocalMachine\My -Password $securePassword
$thumbprint = $cert.Thumbprint
Write-Host "  Certificate imported: $thumbprint" -ForegroundColor Green

# Add HTTPS binding
Write-Host "Adding HTTPS binding..." -ForegroundColor Yellow

# Remove existing HTTPS binding if any
try {
    Remove-WebBinding -Name $siteName -Protocol https -ErrorAction SilentlyContinue
} catch {}

# Add new HTTPS binding
New-WebBinding -Name $siteName -Protocol https -Port 443 -IPAddress "*"

# Assign certificate to binding using netsh (more reliable)
$appId = [guid]::NewGuid().ToString("B")
netsh http add sslcert ipport=0.0.0.0:443 certhash=$thumbprint appid=$appId

Write-Host "HTTPS binding configured" -ForegroundColor Green

# Restart IIS
Write-Host "Restarting IIS..." -ForegroundColor Yellow
iisreset /restart

Write-Host ""
Write-Host "SSL Configuration Complete!" -ForegroundColor Green
Write-Host "Site is now accessible at: https://49.50.9.81" -ForegroundColor Cyan
