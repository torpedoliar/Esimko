Import-Module WebAdministration

# Update Esimko site to point to public folder
Set-ItemProperty "IIS:\Sites\Esimko" -Name physicalPath -Value "C:\IIS\Esimko\public"

# Ensure PHP handler is working
$fastCgiPath = "C:\php\php-cgi.exe"
$fastCgiSection = Get-WebConfiguration -Filter /system.webServer/fastCgi
$phpHandler = $fastCgiSection.Collection | Where-Object { $_.fullPath -eq $fastCgiPath }
if (-not $phpHandler) {
    Add-WebConfiguration -Filter /system.webServer/fastCgi -Value @{
        fullPath            = $fastCgiPath
        arguments           = ""
        maxInstances        = 4
        instanceMaxRequests = 10000
        activityTimeout     = 600
        requestTimeout      = 600
    }
    Write-Host "FastCGI handler added"
}

# Restart IIS site
Stop-Website -Name "Esimko"
Start-Website -Name "Esimko"

Write-Host "IIS Esimko site updated to point to C:\IIS\Esimko\public"
Write-Host "Done!"
