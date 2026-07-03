@echo off
echo === K1215: penjualan with pending angsuran - total_pembayaran values ===
mysql -u root -e "SELECT p.id, p.total_pembayaran, p.angsuran, p.tenor, p.tanggal FROM esimko.penjualan p WHERE p.fid_anggota='K 1215' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND EXISTS (SELECT 1 FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3) ORDER BY p.id;"
echo.
echo === K1741: penjualan with pending angsuran - total_pembayaran values ===
mysql -u root -e "SELECT p.id, p.total_pembayaran, p.angsuran, p.tenor, p.tanggal FROM esimko.penjualan p WHERE p.fid_anggota='K 1741' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND EXISTS (SELECT 1 FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3) ORDER BY p.id;"
echo.
echo === K1215 penjualan columns ===
mysql -u root -e "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='esimko' AND TABLE_NAME='penjualan' AND COLUMN_NAME LIKE '%%total%%' OR COLUMN_NAME LIKE '%%angsuran%%' OR COLUMN_NAME LIKE '%%harga%%';"
