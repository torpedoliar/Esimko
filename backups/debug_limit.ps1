# Deep debug for POS Limit calculation
# Analyzing 3 cases: K 1215, K 1741, K 1454

$users = @("K 1215", "K 1741", "K 1454")

foreach ($user in $users) {
    Write-Host "=============================================="
    Write-Host "CASE: $user"
    Write-Host "=============================================="
    
    Write-Host "`n--- 1. User Info ---"
    & mysql -u root -e "SELECT no_anggota, nama_lengkap FROM esimko.anggota WHERE no_anggota = '$user';"

    Write-Host "`n--- 2. All Penjualan (Credit: metode=3, status IN 2,4) ---"
    & mysql -u root -e "SELECT p.id, p.no_transaksi, p.fid_anggota, p.fid_metode_pembayaran, p.fid_status, p.total_pembayaran, p.tanggal FROM esimko.penjualan p WHERE p.fid_anggota = '$user' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4) ORDER BY p.id;"

    Write-Host "`n--- 3. Angsuran Belanja for those Penjualan (status=3) ---"
    & mysql -u root -e "SELECT ab.id, ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (SELECT p.id FROM esimko.penjualan p WHERE p.fid_anggota = '$user' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4)) AND ab.fid_status = 3 ORDER BY ab.fid_penjualan, ab.angsuran_ke;"

    Write-Host "`n--- 4. First angsuran per penjualan (what code actually uses - GROUP BY trick) ---"
    & mysql -u root -e "SELECT a.* FROM (SELECT * FROM esimko.angsuran_belanja WHERE fid_status = 3 ORDER BY angsuran_ke ASC) a WHERE a.fid_penjualan IN (SELECT p.id FROM esimko.penjualan p WHERE p.fid_anggota = '$user' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4)) GROUP BY a.fid_penjualan;"

    Write-Host "`n--- 5. Retur items ---"
    & mysql -u root -e "SELECT rp.id as retur_id, rp.fid_anggota, rp.fid_penjualan, irp.id as item_id, irp.fid_produk, irp.jumlah, p.harga_jual, (irp.jumlah * p.harga_jual) as nilai_retur FROM esimko.retur_penjualan rp JOIN esimko.item_retur_penjualan irp ON irp.fid_retur_penjualan = rp.id JOIN esimko.produk p ON p.id = irp.fid_produk WHERE rp.fid_anggota = '$user' AND rp.fid_penjualan IN (SELECT a.fid_penjualan FROM (SELECT * FROM esimko.angsuran_belanja WHERE fid_status = 3 ORDER BY angsuran_ke ASC) a WHERE a.fid_penjualan IN (SELECT p2.id FROM esimko.penjualan p2 WHERE p2.fid_anggota = '$user' AND p2.fid_metode_pembayaran = 3 AND p2.fid_status IN (2, 4)) GROUP BY a.fid_penjualan);"

    Write-Host "`n--- 6. Calculated Limit ---"
    & mysql -u root -e "SELECT 1500000 - COALESCE((SELECT SUM(a.total_angsuran) FROM (SELECT * FROM esimko.angsuran_belanja WHERE fid_status = 3 ORDER BY angsuran_ke ASC) a WHERE a.fid_penjualan IN (SELECT p.id FROM esimko.penjualan p WHERE p.fid_anggota = '$user' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4)) GROUP BY a.fid_penjualan), 0) as calculated_limit;"

    Write-Host "`n`n"
}
