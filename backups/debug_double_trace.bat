@echo off
echo === CHECK: Penjualan 86145 duplicate items ===
mysql -u root -e "SELECT ip.id, ip.fid_penjualan, ip.fid_produk, ip.jumlah, ip.harga, COUNT(*) as cnt FROM esimko.item_penjualan ip WHERE ip.fid_penjualan=86145 GROUP BY ip.fid_produk, ip.harga;"
echo.
echo === CHECK: Penjualan 86145 ALL items ===
mysql -u root -e "SELECT ip.id, ip.fid_produk, ip.jumlah, ip.harga FROM esimko.item_penjualan ip WHERE ip.fid_penjualan=86145;"
echo.
echo === CHECK: K1454 ghost penjualan - ALL items ===
mysql -u root -e "SELECT p.id as penj_id, ip.id as item_id, ip.fid_produk, ip.jumlah, ip.harga, (SELECT COUNT(*) FROM esimko.item_penjualan ip2 WHERE ip2.fid_penjualan=p.id) as total_items FROM esimko.penjualan p LEFT JOIN esimko.item_penjualan ip ON ip.fid_penjualan=p.id WHERE p.fid_anggota='K 1454' AND p.fid_metode_pembayaran=3 AND p.fid_status IN (2,4) ORDER BY p.id, ip.id;"
echo.
echo === CHECK: K1454 angsuran_belanja duplicate check ===
mysql -u root -e "SELECT ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, COUNT(*) as cnt FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan IN (SELECT id FROM esimko.penjualan WHERE fid_anggota='K 1454' AND fid_metode_pembayaran=3) GROUP BY ab.fid_penjualan, ab.angsuran_ke HAVING cnt > 1;"
echo.
echo === CHECK: Penjualan 86145 angsuran_belanja duplicate check ===
mysql -u root -e "SELECT ab.fid_penjualan, ab.angsuran_ke, ab.total_angsuran, COUNT(*) as cnt FROM esimko.angsuran_belanja ab WHERE ab.fid_penjualan=86145 GROUP BY ab.angsuran_ke HAVING cnt > 1;"
