<?php
// Script to generate Missed Cut-Off Report
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "esimko";

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

echo "# Laporan 150 Anggota: Missed Cut-Off (26 Jan - 10 Feb 2026)\n";
echo "Data transaksi berikut berstatus 'Belum Lunas' (3) dan TIDAK masuk Payroll bulan ini.\n\n";

$sql = "SELECT 
    a.no_anggota, 
    a.nama_lengkap, 
    p.no_transaksi,
    p.tanggal,
    ab.total_angsuran
FROM esimko.angsuran_belanja ab 
JOIN esimko.penjualan p ON ab.fid_penjualan = p.id 
JOIN esimko.anggota a ON p.fid_anggota = a.no_anggota 
WHERE ab.fid_status = 3 
AND ab.fid_payroll IS NULL 
AND p.tanggal BETWEEN '2026-01-26' AND '2026-02-10' 
ORDER BY a.nama_lengkap ASC, p.tanggal DESC";

$result = $mysqli->query($sql);

$current_member = "";
$grand_total = 0;

while ($row = $result->fetch_assoc()) {
    if ($current_member != $row['no_anggota']) {
        if ($current_member != "") echo "\n";
        echo "### " . $row['nama_lengkap'] . " (" . $row['no_anggota'] . ")\n";
        $current_member = $row['no_anggota'];
    }
    
    echo "- " . $row['no_transaksi'] . " (" . $row['tanggal'] . ") : Rp " . number_format($row['total_angsuran'], 0, ',', '.') . "\n";
    $grand_total += $row['total_angsuran'];
}

echo "\n---\n**GRAND TOTAL MISSED**: Rp " . number_format($grand_total, 0, ',', '.') . "\n";
$mysqli->close();
