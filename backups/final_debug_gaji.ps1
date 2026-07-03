# Final debug - check exactly what's happening
Write-Host "=== User K 1568 data ==="
& mysql -u root -e "SELECT no_anggota, nama_lengkap FROM esimko.anggota WHERE no_anggota = 'K 1568';"

Write-Host "`n=== Gaji entries owned by K 1568 (fid_anggota = K 1568) ==="
& mysql -u root -e "SELECT id, fid_anggota, bulan, gaji_pokok, created_by, (SELECT nama_lengkap FROM esimko.anggota WHERE no_anggota = esimko.gaji_pokok.created_by) as creator_name FROM esimko.gaji_pokok WHERE fid_anggota = 'K 1568' ORDER BY id DESC LIMIT 10;"

Write-Host "`n=== Count of entries for K 1568 ==="
& mysql -u root -e "SELECT COUNT(*) as total FROM esimko.gaji_pokok WHERE fid_anggota = 'K 1568';"

Write-Host "`n=== Entries created by SYELVIA (checking if they're for different people) ==="
& mysql -u root -e "SELECT gp.id, gp.fid_anggota, a.nama_lengkap as owner_name, gp.bulan, gp.gaji_pokok, gp.created_by FROM esimko.gaji_pokok gp LEFT JOIN esimko.anggota a ON a.no_anggota = gp.fid_anggota WHERE gp.created_by IN (SELECT no_anggota FROM esimko.anggota WHERE nama_lengkap LIKE '%SYELVIA%') ORDER BY gp.id DESC LIMIT 10;"
