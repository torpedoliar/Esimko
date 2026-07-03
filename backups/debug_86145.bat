@echo off
echo === Penjualan 86145 detail ===
mysql -u root -e "SELECT id, no_transaksi, total_pembayaran, angsuran, tenor, fid_status, tanggal FROM esimko.penjualan WHERE id=86145;"
echo.
echo === Penjualan 86145 ITEMS (produk yang dibeli) ===
mysql -u root -e "SELECT ip.id, ip.fid_produk, ip.jumlah, ip.harga, p.nama_produk FROM esimko.item_penjualan ip JOIN esimko.produk p ON p.id=ip.fid_produk WHERE ip.fid_penjualan=86145;"
echo.
echo === Penjualan 86145 ANGSURAN entries ===
mysql -u root -e "SELECT id, angsuran_ke, total_angsuran, fid_status, fid_payroll FROM esimko.angsuran_belanja WHERE fid_penjualan=86145 ORDER BY angsuran_ke;"
echo.
echo === Check for duplicate penjualan around same date for K1741 ===
mysql -u root -e "SELECT id, no_transaksi, total_pembayaran, angsuran, tenor, fid_status, tanggal FROM esimko.penjualan WHERE fid_anggota='K 1741' AND fid_metode_pembayaran=3 AND tanggal BETWEEN '2025-07-01' AND '2025-07-31' ORDER BY id;"
