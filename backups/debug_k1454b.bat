@echo off
echo === K1454 angsuran_belanja for ALL credit penjualan ===
mysql -u root -e "SELECT ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status, ab.fid_payroll FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (SELECT id FROM esimko.penjualan WHERE fid_anggota='K 1454' AND fid_metode_pembayaran=3) ORDER BY ab.fid_penjualan, ab.angsuran_ke;"
echo.
echo === K1454 penjualan detail ===
mysql -u root -e "SELECT p.id, p.no_transaksi, p.angsuran, p.total_pembayaran, p.tenor, p.fid_status, p.tanggal FROM esimko.penjualan p WHERE p.fid_anggota='K 1454' AND p.fid_metode_pembayaran=3 ORDER BY p.id;"
