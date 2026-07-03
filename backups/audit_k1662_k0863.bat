@echo off
echo === K 1662 (Limit: 604,400) === > C:\IIS\Esimko\k1662_k0863_audit.txt
mysql -u root -e "SELECT no_anggota, nama_lengkap FROM esimko.anggota WHERE no_anggota='K 1662';" >> C:\IIS\Esimko\k1662_k0863_audit.txt
echo PENJUALAN KREDIT K 1662: >> C:\IIS\Esimko\k1662_k0863_audit.txt
mysql -u root -e "SELECT id, no_transaksi, total_pembayaran, angsuran, tenor, fid_status, fid_metode_pembayaran FROM esimko.penjualan WHERE fid_anggota='K 1662' AND fid_status IN (2,4) AND id IN (SELECT fid_penjualan FROM esimko.angsuran_belanja WHERE fid_status=3);" >> C:\IIS\Esimko\k1662_k0863_audit.txt
echo. >> C:\IIS\Esimko\k1662_k0863_audit.txt

echo === K 0863 (Limit: 41,310) === >> C:\IIS\Esimko\k1662_k0863_audit.txt
mysql -u root -e "SELECT no_anggota, nama_lengkap FROM esimko.anggota WHERE no_anggota='K 0863';" >> C:\IIS\Esimko\k1662_k0863_audit.txt
echo PENJUALAN KREDIT K 0863: >> C:\IIS\Esimko\k1662_k0863_audit.txt
mysql -u root -e "SELECT id, no_transaksi, total_pembayaran, angsuran, tenor, fid_status, fid_metode_pembayaran FROM esimko.penjualan WHERE fid_anggota='K 0863' AND fid_status IN (2,4) AND id IN (SELECT fid_penjualan FROM esimko.angsuran_belanja WHERE fid_status=3);" >> C:\IIS\Esimko\k1662_k0863_audit.txt
echo. >> C:\IIS\Esimko\k1662_k0863_audit.txt

echo === ALL PENDING ANGSURAN K 1662 === >> C:\IIS\Esimko\k1662_k0863_audit.txt
mysql -u root -e "SELECT ab.id, ab.fid_penjualan, p.no_transaksi, p.fid_metode_pembayaran, ab.angsuran_ke, ab.total_angsuran FROM esimko.angsuran_belanja ab JOIN esimko.penjualan p ON p.id=ab.fid_penjualan WHERE p.fid_anggota='K 1662' AND ab.fid_status=3;" >> C:\IIS\Esimko\k1662_k0863_audit.txt

echo === ALL PENDING ANGSURAN K 0863 === >> C:\IIS\Esimko\k1662_k0863_audit.txt
mysql -u root -e "SELECT ab.id, ab.fid_penjualan, p.no_transaksi, p.fid_metode_pembayaran, ab.angsuran_ke, ab.total_angsuran FROM esimko.angsuran_belanja ab JOIN esimko.penjualan p ON p.id=ab.fid_penjualan WHERE p.fid_anggota='K 0863' AND ab.fid_status=3;" >> C:\IIS\Esimko\k1662_k0863_audit.txt

type C:\IIS\Esimko\k1662_k0863_audit.txt
