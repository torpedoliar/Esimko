# Fix IIS Static Files - Complete Solution
# Run as Administrator

Import-Module WebAdministration

$siteName = "Esimko"
$physicalPath = "C:\IIS\Esimko\public"

Write-Host "Fixing IIS Static File Serving..." -ForegroundColor Yellow

# 1. Ensure Static Content feature is installed
Write-Host "  [1/6] Checking Windows features..." -ForegroundColor Gray
$staticContent = Get-WindowsFeature -Name Web-Static-Content
if (-not $staticContent.Installed) {
    Write-Host "    Installing Static Content feature..." -ForegroundColor Gray
    Install-WindowsFeature -Name Web-Static-Content
}

# 2. Update site physical path
Write-Host "  [2/6] Setting site physical path..." -ForegroundColor Gray
Set-ItemProperty "IIS:\Sites\$siteName" -Name physicalPath -Value $physicalPath

# 3. Configure MIME types
Write-Host "  [3/6] Configuring MIME types..." -ForegroundColor Gray
$mimeTypes = @{
    ".css"   = "text/css"
    ".js"    = "application/javascript"
    ".json"  = "application/json"
    ".woff"  = "application/font-woff"
    ".woff2" = "font/woff2"
    ".ttf"   = "application/x-font-ttf"
    ".eot"   = "application/vnd.ms-fontobject"
    ".svg"   = "image/svg+xml"
    ".png"   = "image/png"
    ".jpg"   = "image/jpeg"
    ".jpeg"  = "image/jpeg"
    ".gif"   = "image/gif"
    ".ico"   = "image/x-icon"
    ".webp"  = "image/webp"
}

foreach ($ext in $mimeTypes.Keys) {
    try {
        Remove-WebConfigurationProperty -PSPath "IIS:\Sites\$siteName" -Filter "system.webServer/staticContent" -Name "." -AtElement @{fileExtension = $ext } -ErrorAction SilentlyContinue
    }
    catch {}
    try {
        Add-WebConfigurationProperty -PSPath "IIS:\Sites\$siteName" -Filter "system.webServer/staticContent" -Name "." -Value @{fileExtension = $ext; mimeType = $mimeTypes[$ext] }
    }
    catch {}
}

# 4. Update web.config to allow static files
Write-Host "  [4/6] Updating web.config..." -ForegroundColor Gray
$webConfig = @"
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <handlers>
            <remove name="PHP_via_FastCGI" />
            <add name="PHP_via_FastCGI" path="*.php" verb="*" modules="FastCgiModule" scriptProcessor="C:\php\php-cgi.exe" resourceType="Either" requireAccess="Script" />
        </handlers>
        <rewrite>
            <rules>
                <rule name="Laravel" stopProcessing="true">
                    <match url="^(.*)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php/{R:1}" />
                </rule>
            </rules>
        </rewrite>
        <staticContent>
            <remove fileExtension=".woff" />
            <remove fileExtension=".woff2" />
            <mimeMap fileExtension=".woff" mimeType="application/font-woff" />
            <mimeMap fileExtension=".woff2" mimeType="font/woff2" />
        </staticContent>
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
$webConfig | Set-Content "$physicalPath\web.config" -Encoding UTF8

# 5. Set permissions
Write-Host "  [5/6] Setting permissions..." -ForegroundColor Gray
icacls $physicalPath /grant "IIS_IUSRS:(OI)(CI)RX" /Q
icacls "$physicalPath\assets" /grant "IIS_IUSRS:(OI)(CI)RX" /Q
icacls "$physicalPath\storage" /grant "IIS_IUSRS:(OI)(CI)RX" /Q

# 6. Restart IIS
Write-Host "  [6/6] Restarting IIS..." -ForegroundColor Gray
iisreset /restart

Write-Host ""
Write-Host "Done! Static files should now be served correctly." -ForegroundColor Green
Write-Host "Test at: http://49.50.9.81" -ForegroundColor Cyan
