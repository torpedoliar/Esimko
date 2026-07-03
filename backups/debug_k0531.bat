@echo off
echo === K0531 Member Info ===
mysql -u root -e "SELECT no_anggota, nama_lengkap FROM esimko.anggota WHERE no_anggota='K 0531';"
echo.
echo === K0531 Penjualan Kredit ===
mysql -u root -e "SELECT id, no_transaksi, tanggal, total_pembayaran, angsuran, tenor, fid_status, fid_metode_pembayaran, jenis_belanja FROM esimko.penjualan WHERE fid_anggota='K 0531' AND fid_metode_pembayaran=3 ORDER BY id DESC;"
echo.
echo === K0531 Angsuran Belanja Pending ===
mysql -u root -e "SELECT ab.id, ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (SELECT id FROM esimko.penjualan WHERE fid_anggota='K 0531') AND ab.fid_status=3 ORDER BY ab.fid_penjualan, ab.angsuran_ke;"
echo.
echo === K0531 ALL Angsuran Belanja ===
mysql -u root -e "SELECT ab.id, ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (SELECT id FROM esimko.penjualan WHERE fid_anggota='K 0531') ORDER BY ab.fid_penjualan, ab.angsuran_ke;"
echo.
echo === K0531 Limit via Tinker ===
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K0531 Limit: ' . App\Helpers\GlobalHelper::limitKaryawan('K 0531') . PHP_EOL;"
