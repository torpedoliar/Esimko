# Save output to file for clean reading
$out = "C:\IIS\Esimko\debug_output.txt"

"=== MySQL sql_mode ===" | Out-File $out
& mysql -u root -N -e "SELECT @@sql_mode;" 2>&1 | Out-File $out -Append

"`n=== K 1215 (RENDRA ARY W) ===" | Out-File $out -Append
"Expected limit: 796110, Actual: 794110, Diff: 2000" | Out-File $out -Append
"--- SUM ALL angsuran (fid_status=3): ---" | Out-File $out -Append
& mysql -u root -N -e "SELECT COALESCE(SUM(ab.total_angsuran),0) FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1215' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2,4) AND ab.fid_status = 3;" 2>&1 | Out-File $out -Append
"--- Per penjualan detail: ---" | Out-File $out -Append
& mysql -u root -e "SELECT ab.fid_penjualan, p.no_transaksi, p.total_pembayaran, COUNT(*) as cnt, SUM(ab.total_angsuran) as sum_ang, MIN(ab.total_angsuran) as min_ang, MAX(ab.total_angsuran) as max_ang FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1215' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2,4) AND ab.fid_status = 3 GROUP BY ab.fid_penjualan, p.no_transaksi, p.total_pembayaran ORDER BY ab.fid_penjualan;" 2>&1 | Out-File $out -Append
"--- Retur for K 1215: ---" | Out-File $out -Append
& mysql -u root -e "SELECT rp.fid_penjualan, irp.jumlah, pr.harga_jual, (irp.jumlah * pr.harga_jual) as retur_val FROM esimko.retur_penjualan rp JOIN esimko.item_retur_penjualan irp ON irp.fid_retur_penjualan = rp.id JOIN esimko.produk pr ON pr.id = irp.fid_produk WHERE rp.fid_anggota = 'K 1215';" 2>&1 | Out-File $out -Append

"`n=== K 1741 (MYA AGUSTI) ===" | Out-File $out -Append
"Expected limit: 1155920, Actual: 1119090, Diff: 36830" | Out-File $out -Append
"--- SUM ALL angsuran (fid_status=3): ---" | Out-File $out -Append
& mysql -u root -N -e "SELECT COALESCE(SUM(ab.total_angsuran),0) FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1741' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2,4) AND ab.fid_status = 3;" 2>&1 | Out-File $out -Append
"--- Per penjualan detail: ---" | Out-File $out -Append
& mysql -u root -e "SELECT ab.fid_penjualan, p.no_transaksi, p.total_pembayaran, COUNT(*) as cnt, SUM(ab.total_angsuran) as sum_ang FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1741' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2,4) AND ab.fid_status = 3 GROUP BY ab.fid_penjualan, p.no_transaksi, p.total_pembayaran ORDER BY ab.fid_penjualan;" 2>&1 | Out-File $out -Append
"--- Retur for K 1741: ---" | Out-File $out -Append
& mysql -u root -e "SELECT rp.fid_penjualan, irp.jumlah, pr.harga_jual, (irp.jumlah * pr.harga_jual) as retur_val FROM esimko.retur_penjualan rp JOIN esimko.item_retur_penjualan irp ON irp.fid_retur_penjualan = rp.id JOIN esimko.produk pr ON pr.id = irp.fid_produk WHERE rp.fid_anggota = 'K 1741';" 2>&1 | Out-File $out -Append

"`n=== K 1454 (ADI PURNIAWAN) ===" | Out-File $out -Append
"Expected limit: 1500000, Actual: 1331000, Diff: 169000" | Out-File $out -Append
"--- SUM ALL angsuran (fid_status=3): ---" | Out-File $out -Append
& mysql -u root -N -e "SELECT COALESCE(SUM(ab.total_angsuran),0) FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1454' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2,4) AND ab.fid_status = 3;" 2>&1 | Out-File $out -Append
"--- Per penjualan detail: ---" | Out-File $out -Append
& mysql -u root -e "SELECT ab.fid_penjualan, p.no_transaksi, p.total_pembayaran, COUNT(*) as cnt, SUM(ab.total_angsuran) as sum_ang FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota = 'K 1454' AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2,4) AND ab.fid_status = 3 GROUP BY ab.fid_penjualan, p.no_transaksi, p.total_pembayaran ORDER BY ab.fid_penjualan;" 2>&1 | Out-File $out -Append
"--- Retur for K 1454: ---" | Out-File $out -Append
& mysql -u root -e "SELECT rp.fid_penjualan, irp.jumlah, pr.harga_jual, (irp.jumlah * pr.harga_jual) as retur_val FROM esimko.retur_penjualan rp JOIN esimko.item_retur_penjualan irp ON irp.fid_retur_penjualan = rp.id JOIN esimko.produk pr ON pr.id = irp.fid_produk WHERE rp.fid_anggota = 'K 1454';" 2>&1 | Out-File $out -Append

"`n=== ANGSURAN with DUPLICATE fid_penjualan (cnt > 1, status=3) ===" | Out-File $out -Append
& mysql -u root -e "SELECT ab.fid_penjualan, COUNT(*) as cnt, SUM(ab.total_angsuran) as sum_ang, MIN(ab.angsuran_ke) as min_ke, MAX(ab.angsuran_ke) as max_ke, GROUP_CONCAT(ab.total_angsuran ORDER BY ab.angsuran_ke) as amounts FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id = ab.fid_penjualan WHERE p.fid_anggota IN ('K 1215','K 1741','K 1454') AND p.fid_metode_pembayaran = 3 AND p.fid_status IN (2,4) AND ab.fid_status = 3 GROUP BY ab.fid_penjualan HAVING cnt > 1 ORDER BY ab.fid_penjualan;" 2>&1 | Out-File $out -Append

Write-Host "Output saved to $out"
Get-Content $out
