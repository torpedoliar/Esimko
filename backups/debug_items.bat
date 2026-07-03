@echo off
echo Q1:
mysql -u root -e "SELECT id, total_pembayaran, angsuran, tenor FROM esimko.penjualan WHERE id=86145;"
echo.
echo Q2:
mysql -u root -e "SELECT ip.fid_produk, ip.jumlah, ip.harga FROM esimko.item_penjualan ip WHERE ip.fid_penjualan=86145;"
echo.
echo Q3:
mysql -u root -e "SELECT COUNT(*) as cnt_items, SUM(jumlah*harga) as total_items FROM esimko.item_penjualan WHERE fid_penjualan=86145;"
