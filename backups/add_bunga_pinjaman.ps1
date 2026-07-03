# Add Bunga Pinjaman menu and settings
Write-Host "Adding Bunga Pinjaman menu and settings..." -ForegroundColor Yellow

# 1. Check if menu already exists
$menuExists = & mysql -u root -N -e "SELECT COUNT(*) FROM esimko.modul WHERE link = 'pengaturan/bunga_pinjaman';"
Write-Host "Menu exists check: $menuExists"

if ($menuExists -eq 0) {
    # Add menu item (parent_id = 11 is Pengaturan/Data Master section)
    & mysql -u root -e "INSERT INTO esimko.modul (parent_id, nama_modul, icon, link, ``order``, is_active) VALUES (11, 'Bunga Pinjaman', 'mdi mdi-percent', 'pengaturan/bunga_pinjaman', 3, 1);"
    Write-Host "  OK - Menu 'Bunga Pinjaman' added" -ForegroundColor Green
    
    # Get the new menu ID
    $newMenuId = & mysql -u root -N -e "SELECT id FROM esimko.modul WHERE link = 'pengaturan/bunga_pinjaman';"
    Write-Host "  New menu ID: $newMenuId" -ForegroundColor Cyan
}
else {
    Write-Host "  SKIP - Menu already exists" -ForegroundColor Yellow
}

# 2. Check if pengaturan record exists
$settingExists = & mysql -u root -N -e "SELECT COUNT(*) FROM esimko.pengaturan WHERE kode = 'bunga_pinjaman';"
Write-Host ""
Write-Host "Setting exists check: $settingExists"

if ($settingExists -eq 0) {
    # Add default setting (1% = 0.01)
    & mysql -u root -e "INSERT INTO esimko.pengaturan (kode, nama, nilai, tipe, keterangan, created_at, updated_at) VALUES ('bunga_pinjaman', 'Bunga Pinjaman (Per Bulan)', '0.01', 'persen', 'Persentase bunga pinjaman per bulan', NOW(), NOW());"
    Write-Host "  OK - Setting 'bunga_pinjaman' added with default 1%" -ForegroundColor Green
}
else {
    # Show current value
    $currentValue = & mysql -u root -N -e "SELECT nilai FROM esimko.pengaturan WHERE kode = 'bunga_pinjaman';"
    $percentage = [float]$currentValue * 100
    Write-Host "  SKIP - Setting already exists with value: $percentage%" -ForegroundColor Yellow
}

# 3. Grant access to all hak_akses for the new menu
Write-Host ""
Write-Host "Granting menu access to all roles..." -ForegroundColor Yellow
$menuId = & mysql -u root -N -e "SELECT id FROM esimko.modul WHERE link = 'pengaturan/bunga_pinjaman';"
$hakAksesList = & mysql -u root -N -e "SELECT id FROM esimko.hak_akses;"

foreach ($hakAkses in $hakAksesList.Split("`n")) {
    if ($hakAkses -ne "") {
        $exists = & mysql -u root -N -e "SELECT COUNT(*) FROM esimko.otoritas_user WHERE fid_modul = $menuId AND fid_hak_akses = $hakAkses;"
        if ($exists -eq 0) {
            & mysql -u root -e "INSERT INTO esimko.otoritas_user (fid_hak_akses, fid_modul, is_view, is_insert, is_update, is_delete, is_print, is_verified) VALUES ($hakAkses, $menuId, 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');"
            Write-Host "  Granted access for hak_akses ID: $hakAkses" -ForegroundColor Green
        }
    }
}

# 4. Verify
Write-Host ""
Write-Host "Verification:" -ForegroundColor Yellow
& mysql -u root -e "SELECT id, nama_modul, link FROM esimko.modul WHERE link = 'pengaturan/bunga_pinjaman';"
& mysql -u root -e "SELECT kode, nama, nilai, tipe FROM esimko.pengaturan WHERE kode = 'bunga_pinjaman';"

Write-Host ""
Write-Host "Done! Bunga Pinjaman feature is now available." -ForegroundColor Green
