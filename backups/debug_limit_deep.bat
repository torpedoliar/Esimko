@echo off
echo === K1215 ALL credit penjualan detail ===
mysql -u root -e "SELECT p.id, p.no_transaksi, p.total_pembayaran, p.fid_status, p.tanggal, (SELECT COUNT(*) FROM esimko.angsuran_belanja WHERE fid_penjualan=p.id AND fid_status=3) as angsuran_pending, (SELECT COUNT(*) FROM esimko.angsuran_belanja WHERE fid_penjualan=p.id) as angsuran_total, (SELECT GROUP_CONCAT(fid_payroll ORDER BY angsuran_ke) FROM esimko.angsuran_belanja WHERE fid_penjualan=p.id AND fid_status=3) as payroll_ids FROM esimko.penjualan p WHERE p.fid_anggota='K 1215' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) ORDER BY p.id;"
echo.
echo === K1741 ALL credit penjualan detail ===
mysql -u root -e "SELECT p.id, p.no_transaksi, p.total_pembayaran, p.fid_status, p.tanggal, (SELECT COUNT(*) FROM esimko.angsuran_belanja WHERE fid_penjualan=p.id AND fid_status=3) as angsuran_pending, (SELECT COUNT(*) FROM esimko.angsuran_belanja WHERE fid_penjualan=p.id) as angsuran_total, (SELECT GROUP_CONCAT(fid_payroll ORDER BY angsuran_ke) FROM esimko.angsuran_belanja WHERE fid_penjualan=p.id AND fid_status=3) as payroll_ids FROM esimko.penjualan p WHERE p.fid_anggota='K 1741' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) ORDER BY p.id;"
echo.
echo === K1215 penjualan 100810 angsuran detail ===
mysql -u root -e "SELECT * FROM esimko.angsuran_belanja WHERE fid_penjualan=100810;"
echo.
echo === K1741 extra penjualan angsuran detail ===
mysql -u root -e "SELECT ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status, ab.fid_payroll FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (100715, 100755, 100762, 100996, 101009) ORDER BY ab.fid_penjualan, ab.angsuran_ke;"
echo.
echo === K1741 penjualan 86145 angsuran detail (wrong amount) ===
mysql -u root -e "SELECT ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status, ab.fid_payroll FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=86145 ORDER BY ab.angsuran_ke;"
echo.
echo === Angsuran status reference ===
mysql -u root -e "SELECT DISTINCT fid_status, COUNT(*) as cnt FROM esimko.angsuran_belanja GROUP BY fid_status;"
echo.
echo === Check fid_payroll for valid penjualan ===
mysql -u root -e "SELECT ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status, ab.fid_payroll FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (90133, 92473, 99248) AND ab.fid_status=3 ORDER BY ab.fid_penjualan, ab.angsuran_ke;"
