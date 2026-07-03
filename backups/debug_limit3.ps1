# Focused debug - only essential data, one query per insight
Write-Host "=== MySQL sql_mode ==="
& mysql -u root -e "SELECT @@sql_mode;"

Write-Host "`n=== K 1215 (RENDRA ARY W) - Expected limit: 796.110 - Actual: 794.110 ==="
Write-Host "--- Total angsuran with fid_status=3: ---"
& mysql -u root -e "SELECT SUM(ab.total_angsuran) as sum_angsuran, COUNT(*) as rows_count, COUNT(DISTINCT ab.fid_penjualan) as distinct_penjualan FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1215' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4) AND ab.fid_status = 3;"
Write-Host "--- Correct limit = 1500000 - SUM: ---"
& mysql -u root -e "SELECT 1500000 - COALESCE(SUM(ab.total_angsuran), 0) as correct_limit FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1215' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4) AND ab.fid_status = 3;"

Write-Host "`n=== K 1741 (MYA AGUSTI) - Expected limit: 1.155.920 - Actual: 1.119.090 ==="
Write-Host "--- Total angsuran with fid_status=3: ---"
& mysql -u root -e "SELECT SUM(ab.total_angsuran) as sum_angsuran, COUNT(*) as rows_count, COUNT(DISTINCT ab.fid_penjualan) as distinct_penjualan FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1741' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4) AND ab.fid_status = 3;"
Write-Host "--- Correct limit = 1500000 - SUM: ---"
& mysql -u root -e "SELECT 1500000 - COALESCE(SUM(ab.total_angsuran), 0) as correct_limit FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1741' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4) AND ab.fid_status = 3;"

Write-Host "`n=== K 1454 (ADI PURNIAWAN) - Expected limit: 1.500.000 - Actual: 1.331.000 ==="
Write-Host "--- Total angsuran with fid_status=3: ---"
& mysql -u root -e "SELECT SUM(ab.total_angsuran) as sum_angsuran, COUNT(*) as rows_count, COUNT(DISTINCT ab.fid_penjualan) as distinct_penjualan FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1454' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4) AND ab.fid_status = 3;"
Write-Host "--- Correct limit = 1500000 - SUM: ---"
& mysql -u root -e "SELECT 1500000 - COALESCE(SUM(ab.total_angsuran), 0) as correct_limit FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1454' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2, 4) AND ab.fid_status = 3;"

Write-Host "`n=== K 1454 - Also check with status 2 ONLY ==="
& mysql -u root -e "SELECT SUM(ab.total_angsuran) as sum_angsuran, COUNT(*) as rows_count FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1454' AND p.fid_metode_pembayaran = 3 AND p.fid_status = 2 AND ab.fid_status = 3;"

Write-Host "`n=== Angsuran per penjualan for K 1215 ==="
& mysql -u root -e "SELECT ab.fid_penjualan, p.total_pembayaran, COUNT(*) cnt, SUM(ab.total_angsuran) sum_ang FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1215' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2,4) AND ab.fid_status = 3 GROUP BY ab.fid_penjualan, p.total_pembayaran;"
