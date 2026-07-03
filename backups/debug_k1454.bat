@echo off
echo === K1454 penjualan kredit dengan angsuran pending ===
mysql -u root -e "SELECT p.id, p.angsuran, p.total_pembayaran, p.fid_status, p.tanggal, (SELECT COUNT(*) FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3) as pending FROM esimko.penjualan p WHERE p.fid_anggota='K 1454' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND EXISTS (SELECT 1 FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3) ORDER BY p.id;"
echo.
echo === K1454 SUM ===
mysql -u root -e "SELECT SUM(angsuran) as sum_ang, COUNT(*) as cnt FROM esimko.penjualan p WHERE p.fid_anggota='K 1454' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND EXISTS (SELECT 1 FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3);"
echo.
echo === K1454 ALL penjualan kredit (termasuk non-pending) ===
mysql -u root -e "SELECT p.id, p.angsuran, p.total_pembayaran, p.fid_status, p.tanggal FROM esimko.penjualan p WHERE p.fid_anggota='K 1454' AND p.fid_metode_pembayaran=3 ORDER BY p.id;"
