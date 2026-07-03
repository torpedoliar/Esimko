@echo off
echo === Check Name K 1695 ===
mysql -u root -e "SELECT no_anggota, nama_lengkap FROM esimko.anggota WHERE no_anggota='K 1695';"
echo.
echo === Check Transactions Again ===
mysql -u root -e "SELECT p.id, p.no_transaksi, p.tanggal, p.total_pembayaran, p.fid_anggota FROM esimko.penjualan p WHERE p.fid_anggota='K 1695' AND p.fid_status IN (2,3,4) ORDER BY p.id DESC LIMIT 5;"
