# Check gaji_pokok data for user K 1568
Write-Host "Checking gaji_pokok for user K 1568..."
& mysql -u root -e "SELECT id, fid_anggota, bulan, gaji_pokok, created_by FROM esimko.gaji_pokok WHERE fid_anggota = 'K 1568' LIMIT 10;"

Write-Host ""
Write-Host "Checking table structure..."
& mysql -u root -e "DESCRIBE esimko.gaji_pokok;"

Write-Host ""
Write-Host "Checking if K 1568 exists in anggota table..."
& mysql -u root -e "SELECT no_anggota, nama_lengkap FROM esimko.anggota WHERE no_anggota = 'K 1568' LIMIT 1;"

Write-Host ""
Write-Host "Checking who created gaji for K 1568 (SYELVIA AINIE)..."
& mysql -u root -e "SELECT a.no_anggota, a.nama_lengkap FROM esimko.anggota a WHERE a.nama_lengkap LIKE '%SYELVIA%' LIMIT 5;"

Write-Host ""
Write-Host "Checking gaji_pokok entries by SYELVIA (created_by)..."
& mysql -u root -e "SELECT id, fid_anggota, bulan, gaji_pokok, created_by FROM esimko.gaji_pokok ORDER BY id DESC LIMIT 20;"
