<?php
// Standalone script to generate Mass Duplicate Report
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "esimko";

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// Stats Summary
$summary_sql = "SELECT 
    COUNT(DISTINCT p.fid_anggota) as total_users,
    COUNT(DISTINCT p.id) as total_transactions,
    SUM(ab.total_angsuran) as total_ghost_debt
FROM esimko.angsuran_belanja ab 
JOIN esimko.penjualan p ON ab.fid_penjualan = p.id 
WHERE ab.fid_status = 3 
AND EXISTS (SELECT 1 FROM esimko.angsuran_belanja ab2 WHERE ab2.fid_penjualan = ab.fid_penjualan AND ab2.fid_status = 6)";

$res = $mysqli->query($summary_sql);
$row = $res->fetch_assoc();

echo "# Laporan Detail: 524 Anggota Terdampak Duplikat (Ghost Debt)\n\n";
echo "### Statistik Global\n";
echo "- **Total Anggota**: " . number_format($row['total_users']) . "\n";
echo "- **Total Transaksi Error**: " . number_format($row['total_transactions']) . "\n";
echo "- **Total Hutang Hantu**: Rp " . number_format($row['total_ghost_debt'], 0, ',', '.') . "\n\n";
echo "---\n\n";

// Detail Query
$sql = "SELECT
    a.no_anggota,
    a.nama_lengkap,
    p.no_transaksi,
    DATE(p.tanggal) as tgl,
    ab.total_angsuran,
    COUNT(ab.id) as ghost_count
FROM esimko.angsuran_belanja ab
JOIN esimko.penjualan p ON ab.fid_penjualan = p.id
JOIN esimko.anggota a ON p.fid_anggota = a.no_anggota
WHERE ab.fid_status = 3
  AND EXISTS (
      SELECT 1 FROM esimko.angsuran_belanja ab2
      WHERE ab2.fid_penjualan = ab.fid_penjualan
      AND ab2.fid_status = 6
  )
GROUP BY a.no_anggota, a.nama_lengkap, p.no_transaksi, tgl, ab.total_angsuran
ORDER BY a.nama_lengkap ASC, tgl DESC";

$result = $mysqli->query($sql);

$current_member = "";

while ($row = $result->fetch_assoc()) {
    if ($current_member != $row['no_anggota']) {
        if ($current_member != "") echo "\n";
        echo "### " . $row['nama_lengkap'] . " (" . $row['no_anggota'] . ")\n";
        $current_member = $row['no_anggota'];
    }
    
    echo "- **" . $row['no_transaksi'] . "** (" . $row['tgl'] . ") : Rp " . number_format($row['total_angsuran'], 0, ',', '.') . 
         " [Duplikat Hantu: " . $row['ghost_count'] . " baris]\n";
}

$mysqli->close();
