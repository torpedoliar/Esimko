@echo off
echo === K1215 SUM ===
mysql -u root -e "SELECT COALESCE(SUM(ab.total_angsuran),0) as sum_all, COUNT(*) as cnt, COUNT(DISTINCT ab.fid_penjualan) as penj FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id=ab.fid_penjualan WHERE p.fid_anggota='K 1215' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND ab.fid_status=3;"
echo.
echo === K1741 SUM ===
mysql -u root -e "SELECT COALESCE(SUM(ab.total_angsuran),0) as sum_all, COUNT(*) as cnt, COUNT(DISTINCT ab.fid_penjualan) as penj FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id=ab.fid_penjualan WHERE p.fid_anggota='K 1741' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND ab.fid_status=3;"
echo.
echo === K1454 SUM ===
mysql -u root -e "SELECT COALESCE(SUM(ab.total_angsuran),0) as sum_all, COUNT(*) as cnt, COUNT(DISTINCT ab.fid_penjualan) as penj FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id=ab.fid_penjualan WHERE p.fid_anggota='K 1454' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND ab.fid_status=3;"
echo.
echo === K1215 PER PENJUALAN ===
mysql -u root -e "SELECT ab.fid_penjualan, p.total_pembayaran, COUNT(*) as cnt, SUM(ab.total_angsuran) as sum_ang FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id=ab.fid_penjualan WHERE p.fid_anggota='K 1215' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND ab.fid_status=3 GROUP BY ab.fid_penjualan, p.total_pembayaran;"
echo.
echo === K1741 PER PENJUALAN ===
mysql -u root -e "SELECT ab.fid_penjualan, p.total_pembayaran, COUNT(*) as cnt, SUM(ab.total_angsuran) as sum_ang FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id=ab.fid_penjualan WHERE p.fid_anggota='K 1741' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND ab.fid_status=3 GROUP BY ab.fid_penjualan, p.total_pembayaran;"
echo.
echo === K1454 PER PENJUALAN ===
mysql -u root -e "SELECT ab.fid_penjualan, p.total_pembayaran, COUNT(*) as cnt, SUM(ab.total_angsuran) as sum_ang FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id=ab.fid_penjualan WHERE p.fid_anggota='K 1454' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND ab.fid_status=3 GROUP BY ab.fid_penjualan, p.total_pembayaran;"
echo.
echo === RETUR K1215 ===
mysql -u root -e "SELECT COUNT(*) as cnt FROM esimko.retur_penjualan WHERE fid_anggota='K 1215';"
echo === RETUR K1741 ===
mysql -u root -e "SELECT COUNT(*) as cnt FROM esimko.retur_penjualan WHERE fid_anggota='K 1741';"
echo === RETUR K1454 ===
mysql -u root -e "SELECT COUNT(*) as cnt FROM esimko.retur_penjualan WHERE fid_anggota='K 1454';"
echo.
echo === DUPLICATES (cnt>1) for all 3 users ===
mysql -u root -e "SELECT ab.fid_penjualan, p.fid_anggota, COUNT(*) as cnt, SUM(ab.total_angsuran) as sum_ang, MIN(ab.total_angsuran) as min_ang, MAX(ab.total_angsuran) as max_ang FROM esimko.angsuran_belanja ab INNER JOIN esimko.penjualan p ON p.id=ab.fid_penjualan WHERE p.fid_anggota IN ('K 1215','K 1741','K 1454') AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND ab.fid_status=3 GROUP BY ab.fid_penjualan, p.fid_anggota HAVING cnt>1;"
