@echo off
echo === K1741 INDIVIDUAL penjualan matching whereHas angsuran pending ===
mysql -u root -e "SELECT p.id, p.angsuran, p.fid_status, p.tanggal FROM esimko.penjualan p WHERE p.fid_anggota='K 1741' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND EXISTS (SELECT 1 FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3) ORDER BY p.id;"
echo.
echo === K1215 INDIVIDUAL penjualan matching whereHas angsuran pending ===
mysql -u root -e "SELECT p.id, p.angsuran, p.fid_status, p.tanggal FROM esimko.penjualan p WHERE p.fid_anggota='K 1215' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND EXISTS (SELECT 1 FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3) ORDER BY p.id;"
echo.
echo === K1741 extra penjualan angsuran values ===
mysql -u root -e "SELECT p.id, p.angsuran, p.fid_status, p.tanggal FROM esimko.penjualan p WHERE p.id IN (100715,100755,100762,100996,101009);"
