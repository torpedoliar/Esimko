@echo off
echo === K1741 penjualan 86145 ALL numeric columns ===
mysql -u root -e "SELECT id, total_pembayaran, angsuran, tenor, diskon FROM esimko.penjualan WHERE id=86145;"
echo.
echo === K1741 penjualan 82250 ALL numeric columns ===
mysql -u root -e "SELECT id, total_pembayaran, angsuran, tenor, diskon FROM esimko.penjualan WHERE id=82250;"
echo.
echo === K1741 penjualan 92328 ALL numeric columns ===
mysql -u root -e "SELECT id, total_pembayaran, angsuran, tenor, diskon FROM esimko.penjualan WHERE id=92328;"
echo.
echo === K1215 penjualan 90133 ALL numeric columns ===
mysql -u root -e "SELECT id, total_pembayaran, angsuran, tenor, diskon FROM esimko.penjualan WHERE id=90133;"
echo.
echo === K1215 penjualan 92473 ALL numeric columns ===
mysql -u root -e "SELECT id, total_pembayaran, angsuran, tenor, diskon FROM esimko.penjualan WHERE id=92473;"
echo.
echo === K1215 penjualan 99248 ALL numeric columns ===
mysql -u root -e "SELECT id, total_pembayaran, angsuran, tenor, diskon FROM esimko.penjualan WHERE id=99248;"
