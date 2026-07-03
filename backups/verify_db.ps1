# Verify database import
Write-Host "Verifying database..." -ForegroundColor Yellow
$result = & mysql -u root -e "USE esimko; SELECT COUNT(*) as tables_count FROM information_schema.tables WHERE table_schema = 'esimko';"
Write-Host $result
$result2 = & mysql -u root -e "USE esimko; SELECT COUNT(*) as users FROM users;"
Write-Host $result2
Write-Host "Done!" -ForegroundColor Green
