@echo off
echo === QUERY1: Extra K1215 penjualan 100810 ===
mysql -u root -e "SELECT id, total_pembayaran, tanggal, fid_status FROM esimko.penjualan WHERE id=100810;"
echo ---
mysql -u root -e "SELECT angsuran_ke, total_angsuran, fid_status, fid_payroll FROM esimko.angsuran_belanja WHERE fid_penjualan=100810;"
echo.
echo === QUERY2: Extra K1741 penjualan dates and status ===
mysql -u root -e "SELECT id, total_pembayaran, tanggal, fid_status FROM esimko.penjualan WHERE id IN (100715,100755,100762,100996,101009) ORDER BY id;"
echo.
echo === QUERY3: Extra K1741 angsuran for 100715 ===
mysql -u root -e "SELECT angsuran_ke, total_angsuran, fid_status, fid_payroll FROM esimko.angsuran_belanja WHERE fid_penjualan=100715;"
echo.
echo === QUERY4: K1741 86145 all angsuran ===
mysql -u root -e "SELECT angsuran_ke, total_angsuran, fid_status, fid_payroll FROM esimko.angsuran_belanja WHERE fid_penjualan=86145 ORDER BY angsuran_ke;"
echo.
echo === QUERY5: Valid K1741 penjualan dates ===
mysql -u root -e "SELECT id, total_pembayaran, tanggal, fid_status FROM esimko.penjualan WHERE id IN (82250,83768,84340,86145,92328) ORDER BY id;"
