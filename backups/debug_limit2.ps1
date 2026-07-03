# Simplified debug - avoid GROUP BY issues
$users = @("K 1215", "K 1741", "K 1454")

foreach ($user in $users) {
    Write-Host "=============================================="
    Write-Host "CASE: $user"
    Write-Host "=============================================="
    
    Write-Host "`n--- 1. User Info ---"
    & mysql -u root -e "SELECT no_anggota, nama_lengkap FROM esimko.anggota WHERE no_anggota = '$user';"

    Write-Host "`n--- 2. Credit Penjualan (metode=3, status IN 2,4) ---"
    & mysql -u root -e "SELECT id, no_transaksi, total_pembayaran, fid_status, tanggal FROM esimko.penjualan WHERE fid_anggota = '$user' AND fid_metode_pembayaran = 3 AND fid_status IN (2, 4) ORDER BY id;"

    Write-Host "`n--- 3. ALL angsuran for those penjualan (ALL statuses) ---"
    & mysql -u root -e "SELECT ab.id, ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = '$user' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4) ORDER BY ab.fid_penjualan, ab.angsuran_ke;"

    Write-Host "`n--- 4. SUM of angsuran with fid_status=3 (what code sums) ---"
    & mysql -u root -e "SELECT SUM(ab.total_angsuran) as total_used FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = '$user' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4) AND ab.fid_status = 3;"

    Write-Host "`n--- 5. SUM per penjualan (angsuran fid_status=3) ---"
    & mysql -u root -e "SELECT ab.fid_penjualan, p.no_transaksi, p.total_pembayaran, COUNT(*) as jumlah_angsuran, SUM(ab.total_angsuran) as total_angsuran_sum FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = '$user' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4) AND ab.fid_status = 3 GROUP BY ab.fid_penjualan, p.no_transaksi, p.total_pembayaran;"

    Write-Host "`n--- 6. Retur count ---"
    & mysql -u root -e "SELECT COUNT(*) as retur_count FROM esimko.retur_penjualan WHERE fid_anggota = '$user';"

    Write-Host "`n--- 7. MySQL sql_mode ---"
    & mysql -u root -e "SELECT @@sql_mode;"

    Write-Host "`n`n"
}
