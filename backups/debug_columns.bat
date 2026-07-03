@echo off
echo === K1215 penjualan 90133 ALL columns ===
mysql -u root -e "SELECT id, total_pembayaran, angsuran, tenor, diskon, tanggal FROM esimko.penjualan WHERE id=90133;"
echo.
echo === K1215 penjualan 92473 ALL columns ===
mysql -u root -e "SELECT id, total_pembayaran, angsuran, tenor, diskon, tanggal FROM esimko.penjualan WHERE id=92473;"
echo.
echo === K1215 penjualan 99248 ALL columns ===
mysql -u root -e "SELECT id, total_pembayaran, angsuran, tenor, diskon, tanggal FROM esimko.penjualan WHERE id=99248;"
echo.
echo === K1741 penjualan 86145 - the problem one ===
mysql -u root -e "SELECT id, total_pembayaran, angsuran, tenor, diskon, tanggal FROM esimko.penjualan WHERE id=86145;"
echo.
echo === K1741 first angsuran for each valid penjualan ===
mysql -u root -e "SELECT ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (82250,83768,84340,86145,92328) AND ab.fid_status=3 ORDER BY ab.fid_penjualan, ab.angsuran_ke LIMIT 20;"
