@echo off
echo === FOCUS: Extra penjualan for K1741 - are they in this month? ===
mysql -u root -e "SELECT id, total_pembayaran, tanggal, fid_status FROM esimko.penjualan WHERE id IN (100715,100755,100762,100996,101009) ORDER BY id;"
echo.
echo === FOCUS: Valid penjualan for K1741 - dates ===  
mysql -u root -e "SELECT id, total_pembayaran, tanggal, fid_status FROM esimko.penjualan WHERE id IN (82250,83768,84340,86145,92328) ORDER BY id;"
echo.
echo === FOCUS: Valid penjualan for K1215 - dates ===
mysql -u root -e "SELECT id, total_pembayaran, tanggal, fid_status FROM esimko.penjualan WHERE id IN (90133,92473,99248,100810) ORDER BY id;"
echo.
echo === FOCUS: angsuran fid_payroll for K1741 valid penjualan ===
mysql -u root -e "SELECT fid_penjualan, angsuran_ke, total_angsuran, fid_status, fid_payroll FROM esimko.angsuran_belanja WHERE fid_penjualan IN (82250,83768,84340,86145,92328) ORDER BY fid_penjualan, angsuran_ke;"
echo.
echo === FOCUS: angsuran fid_payroll for K1741 EXTRA penjualan ===
mysql -u root -e "SELECT fid_penjualan, angsuran_ke, total_angsuran, fid_status, fid_payroll FROM esimko.angsuran_belanja WHERE fid_penjualan IN (100715,100755,100762,100996,101009) ORDER BY fid_penjualan, angsuran_ke;"
echo.
echo === FOCUS: angsuran for K1215 penjualan 100810 ===
mysql -u root -e "SELECT fid_penjualan, angsuran_ke, total_angsuran, fid_status, fid_payroll FROM esimko.angsuran_belanja WHERE fid_penjualan=100810;"
echo.
echo === FOCUS: angsuran for K1215 valid penjualan - fid_payroll ===
mysql -u root -e "SELECT fid_penjualan, angsuran_ke, total_angsuran, fid_status, fid_payroll FROM esimko.angsuran_belanja WHERE fid_penjualan IN (90133,92473,99248) AND fid_status=3 ORDER BY fid_penjualan, angsuran_ke;"
