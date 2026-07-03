@echo off
echo === K0531 INFO === > C:\IIS\Esimko\k0531_result.txt
mysql -u root -e "SELECT no_anggota, nama_lengkap FROM esimko.anggota WHERE no_anggota='K 0531';" >> C:\IIS\Esimko\k0531_result.txt
echo. >> C:\IIS\Esimko\k0531_result.txt
echo === PENJUALAN KREDIT === >> C:\IIS\Esimko\k0531_result.txt
mysql -u root -e "SELECT id, no_transaksi, tanggal, total_pembayaran, angsuran, tenor, fid_status, jenis_belanja FROM esimko.penjualan WHERE fid_anggota='K 0531' AND fid_metode_pembayaran=3 ORDER BY id;" >> C:\IIS\Esimko\k0531_result.txt
echo. >> C:\IIS\Esimko\k0531_result.txt
echo === ANGSURAN PENDING (status=3) === >> C:\IIS\Esimko\k0531_result.txt
mysql -u root -e "SELECT ab.id, ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (SELECT id FROM esimko.penjualan WHERE fid_anggota='K 0531') AND ab.fid_status=3 ORDER BY ab.fid_penjualan, ab.angsuran_ke;" >> C:\IIS\Esimko\k0531_result.txt
echo. >> C:\IIS\Esimko\k0531_result.txt
echo === ALL ANGSURAN === >> C:\IIS\Esimko\k0531_result.txt
mysql -u root -e "SELECT ab.id, ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, ab.fid_status FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (SELECT id FROM esimko.penjualan WHERE fid_anggota='K 0531') ORDER BY ab.fid_penjualan, ab.angsuran_ke;" >> C:\IIS\Esimko\k0531_result.txt
echo. >> C:\IIS\Esimko\k0531_result.txt
echo === SUM PENDING === >> C:\IIS\Esimko\k0531_result.txt
mysql -u root -e "SELECT SUM(ab.total_angsuran) as total_pending FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (SELECT id FROM esimko.penjualan WHERE fid_anggota='K 0531') AND ab.fid_status=3;" >> C:\IIS\Esimko\k0531_result.txt
echo. >> C:\IIS\Esimko\k0531_result.txt
echo === LIMIT === >> C:\IIS\Esimko\k0531_result.txt
cd /d C:\IIS\Esimko
php artisan tinker --execute="echo 'K0531 Limit: ' . App\Helpers\GlobalHelper::limitKaryawan('K 0531') . PHP_EOL;" >> C:\IIS\Esimko\k0531_result.txt
type C:\IIS\Esimko\k0531_result.txt
