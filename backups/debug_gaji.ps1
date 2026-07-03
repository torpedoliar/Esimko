# Specific debug for K 1568
Write-Host "=== 1. User K 1568 Profile ==="
& mysql -u root -e "SELECT no_anggota, nama_lengkap FROM esimko.anggota WHERE no_anggota = 'K 1568';"

Write-Host "`n=== 2. Gaji data where fid_anggota = K 1568 ==="
& mysql -u root -e "SELECT COUNT(*) as count FROM esimko.gaji_pokok WHERE fid_anggota = 'K 1568';"

Write-Host "`n=== 3. Sample of recent gaji entries (showing fid_anggota) ==="
& mysql -u root -e "SELECT id, fid_anggota, bulan, gaji_pokok, created_by FROM esimko.gaji_pokok ORDER BY id DESC LIMIT 5;"

Write-Host "`n=== 4. SYELVIA AINIE user info ==="
& mysql -u root -e "SELECT no_anggota, nama_lengkap FROM esimko.anggota WHERE nama_lengkap LIKE '%SYELVIA%';"

Write-Host "`n=== 5. All gaji entries where SYELVIA is the fid_anggota (NOT created_by) ==="
$syelvia_no = & mysql -u root -N -e "SELECT no_anggota FROM esimko.anggota WHERE nama_lengkap LIKE '%SYELVIA%' LIMIT 1;"
if ($syelvia_no) {
    Write-Host "SYELVIA no_anggota: $syelvia_no"
    & mysql -u root -e "SELECT id, fid_anggota, bulan, gaji_pokok FROM esimko.gaji_pokok WHERE fid_anggota = '$syelvia_no' ORDER BY id DESC LIMIT 5;"
}
