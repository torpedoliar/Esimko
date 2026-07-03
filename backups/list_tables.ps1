# List all tables in esimko database
Write-Host "Listing all tables in esimko database..." -ForegroundColor Yellow
& mysql -u root -e "USE esimko; SHOW TABLES;"
