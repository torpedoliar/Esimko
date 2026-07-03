@echo off
echo === DEBUGGING: K1215 penjualan dengan angsuran pending ===
mysql -u root -e "SELECT p.id, p.total_pembayaran, p.angsuran, p.tenor, p.fid_status, p.tanggal, (SELECT COUNT(*) FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3) as pending_count FROM esimko.penjualan p WHERE p.fid_anggota='K 1215' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND EXISTS (SELECT 1 FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3) ORDER BY p.id;"
echo.
echo === DEBUGGING: K1741 penjualan dengan angsuran pending ===
mysql -u root -e "SELECT p.id, p.total_pembayaran, p.angsuran, p.tenor, p.fid_status, p.tanggal, (SELECT COUNT(*) FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3) as pending_count FROM esimko.penjualan p WHERE p.fid_anggota='K 1741' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND EXISTS (SELECT 1 FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3) ORDER BY p.id;"
echo.
echo === SUM: K1215 ===
mysql -u root -e "SELECT SUM(p.total_pembayaran) as sum_total, SUM(p.angsuran) as sum_angsuran, COUNT(*) as cnt FROM esimko.penjualan p WHERE p.fid_anggota='K 1215' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND EXISTS (SELECT 1 FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3);"
echo.
echo === SUM: K1741 ===
mysql -u root -e "SELECT SUM(p.total_pembayaran) as sum_total, SUM(p.angsuran) as sum_angsuran, COUNT(*) as cnt FROM esimko.penjualan p WHERE p.fid_anggota='K 1741' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND EXISTS (SELECT 1 FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3);"
