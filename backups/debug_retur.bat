@echo off
echo === K1741 retur data ===
mysql -u root -e "SELECT rp.id, rp.fid_penjualan, rp.fid_anggota, rp.tanggal FROM esimko.retur_penjualan rp WHERE rp.fid_anggota='K 1741';"
echo.
echo === K1741 item retur ===
mysql -u root -e "SELECT irp.*, p.harga_jual FROM esimko.item_retur_penjualan irp JOIN esimko.retur_penjualan rp ON rp.id=irp.fid_retur_penjualan JOIN esimko.produk p ON p.id=irp.fid_produk WHERE rp.fid_anggota='K 1741';"
echo.
echo === K1741 retur for valid penjualan ===
mysql -u root -e "SELECT rp.fid_penjualan, irp.jumlah, p.harga_jual, (irp.jumlah * p.harga_jual) as total_retur FROM esimko.item_retur_penjualan irp JOIN esimko.retur_penjualan rp ON rp.id=irp.fid_retur_penjualan JOIN esimko.produk p ON p.id=irp.fid_produk WHERE rp.fid_anggota='K 1741' AND rp.fid_penjualan IN (82250,83768,84340,86145,92328);"
echo.
echo === K1454 penjualan kredit ===
mysql -u root -e "SELECT p.id, p.angsuran, p.total_pembayaran, p.fid_status, p.tanggal FROM esimko.penjualan p WHERE p.fid_anggota='K 1454' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4);"
