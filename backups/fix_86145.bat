@echo off
echo === BEFORE FIX: penjualan 86145 ===
mysql -u root -e "SELECT id, angsuran FROM esimko.penjualan WHERE id=86145;"
echo.
echo === BEFORE FIX: angsuran_belanja for 86145 ===
mysql -u root -e "SELECT id, angsuran_ke, total_angsuran, fid_status FROM esimko.angsuran_belanja WHERE fid_penjualan=86145 ORDER BY angsuran_ke;"
echo.
echo === FIXING penjualan.angsuran: 33660 to 16830 ===
mysql -u root -e "UPDATE esimko.penjualan SET angsuran=16830 WHERE id=86145;"
echo.
echo === FIXING angsuran_belanja.total_angsuran: 33660 to 16830 ===
mysql -u root -e "UPDATE esimko.angsuran_belanja SET total_angsuran=16830 WHERE fid_penjualan=86145;"
echo.
echo === AFTER FIX: penjualan 86145 ===
mysql -u root -e "SELECT id, angsuran FROM esimko.penjualan WHERE id=86145;"
echo.
echo === AFTER FIX: angsuran_belanja for 86145 ===
mysql -u root -e "SELECT id, angsuran_ke, total_angsuran, fid_status FROM esimko.angsuran_belanja WHERE fid_penjualan=86145 ORDER BY angsuran_ke;"
