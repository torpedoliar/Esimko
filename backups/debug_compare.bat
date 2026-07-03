@echo off
echo === VALID K1215 penjualan (user confirmed) = 90133,92473,99248 ===
echo === EXTRA K1215 penjualan = 100810 ===
echo.
echo --- K1215: penjualan 100810 status and date ---
mysql -u root -e "SELECT id, fid_status, total_pembayaran, tanggal FROM esimko.penjualan WHERE id=100810;"
echo.
echo --- K1215: penjualan 100810 angsuran ---
mysql -u root -e "SELECT id, angsuran_ke, total_angsuran, fid_status, fid_payroll FROM esimko.angsuran_belanja WHERE fid_penjualan=100810;"
echo.
echo --- K1215: valid penjualan fid_payroll check ---  
mysql -u root -e "SELECT ab.fid_penjualan, ab.angsuran_ke, ab.fid_payroll FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (90133,92473,99248) AND ab.fid_status=3 ORDER BY ab.fid_penjualan, ab.angsuran_ke;"
echo.
echo ==========================================
echo.
echo === VALID K1741 penjualan (user confirmed) = 82250,83768,84340,86145,92328 ===
echo === EXTRA K1741 penjualan = 100715,100755,100762,100996,101009 ===
echo.
echo --- K1741: extra penjualan status and dates ---
mysql -u root -e "SELECT id, fid_status, total_pembayaran, tanggal FROM esimko.penjualan WHERE id IN (100715,100755,100762,100996,101009);"
echo.
echo --- K1741: extra penjualan angsuran ---
mysql -u root -e "SELECT fid_penjualan, angsuran_ke, total_angsuran, fid_status, fid_payroll FROM esimko.angsuran_belanja WHERE fid_penjualan IN (100715,100755,100762,100996,101009) ORDER BY fid_penjualan;"
echo.
echo --- K1741: valid penjualan dates ---    
mysql -u root -e "SELECT id, fid_status, total_pembayaran, tanggal FROM esimko.penjualan WHERE id IN (82250,83768,84340,86145,92328);"
echo.
echo --- K1741: penjualan 86145 angsuran ALL ---
mysql -u root -e "SELECT angsuran_ke, total_angsuran, fid_status, fid_payroll FROM esimko.angsuran_belanja WHERE fid_penjualan=86145 ORDER BY angsuran_ke;"
