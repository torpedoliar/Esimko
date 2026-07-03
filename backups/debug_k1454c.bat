@echo off
echo === K1454 ALL penjualan kredit detail ===
mysql -u root -e "SELECT p.id, p.no_transaksi, p.angsuran, p.total_pembayaran, p.tenor, p.fid_status, p.tanggal FROM esimko.penjualan p WHERE p.fid_anggota='K 1454' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) ORDER BY p.id;"
echo.
echo === K1454 angsuran_belanja pending ===
mysql -u root -e "SELECT ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (SELECT id FROM esimko.penjualan WHERE fid_anggota='K 1454' AND fid_metode_pembayaran=3 AND fid_status IN (2,4)) AND ab.fid_status=3 ORDER BY ab.fid_penjualan, ab.angsuran_ke;"
echo.
echo === K1454 LIMIT CALCULATION DEBUG ===
mysql -u root -e "SELECT p.id, IFNULL(p.angsuran,0) as ang FROM esimko.penjualan p WHERE p.fid_anggota='K 1454' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) AND EXISTS (SELECT 1 FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=p.id AND ab.fid_status=3);"
echo.
echo === K1454 retur data ===
mysql -u root -e "SELECT rp.id, rp.fid_penjualan, irp.jumlah, p.harga_jual, (irp.jumlah * p.harga_jual) as total FROM esimko.item_retur_penjualan irp JOIN esimko.retur_penjualan rp ON rp.id=irp.fid_retur_penjualan JOIN esimko.produk p ON p.id=irp.fid_produk WHERE rp.fid_anggota='K 1454';"
