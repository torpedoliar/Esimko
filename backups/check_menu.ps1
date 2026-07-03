# Check if Bunga Pinjaman menu exists
Write-Host "Checking menu..." -ForegroundColor Yellow
& mysql -u root -e "USE esimko; SELECT * FROM modul WHERE nama_modul LIKE '%bunga%' OR link LIKE '%bunga%';"
Write-Host ""
Write-Host "All menu items under Data Master (parent_id=11):" -ForegroundColor Yellow
& mysql -u root -e "USE esimko; SELECT id, nama_modul, link FROM modul WHERE parent_id = 11;"
