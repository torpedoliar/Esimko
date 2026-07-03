<?php
// Standalone script to generate K 1662 report
// Database credentials for IIS Server (based on working CLI access)
$host = "127.0.0.1";
$user = "root";
$pass = ""; // Try empty first as CLI worked without -p
$db   = "esimko";

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

$member_id = 'K 1662';
echo "# Laporan Verifikasi Transaksi K 1662 (Eri Firmansyah)\n\n";
echo "Berikut adalah daftar transaksi yang tercatat memiliki **Tenor Kosong** (Sehingga limit terpotong full).\n";
echo "Mohon dicek apakah transaksi ini Valid (Pernah belanja barang ini?).\n\n";

$sql = "SELECT p.id, p.no_transaksi, p.tanggal, p.total_pembayaran 
        FROM penjualan p 
        JOIN anggota a ON p.fid_anggota = a.no_anggota 
        WHERE (a.no_anggota = '$member_id' OR a.nama_lengkap LIKE '%Eri Firmansyah%')
        AND p.fid_metode_pembayaran = 3 
        AND (p.angsuran IS NULL OR p.angsuran = 0) 
        AND p.fid_status IN (2, 4) 
        ORDER BY p.tanggal DESC";

$result = $mysqli->query($sql);

if (!$result) {
    die("Query Error: " . $mysqli->error);
}

if ($result->num_rows == 0) {
    echo "Tidak ditemukan transaksi bermasalah untuk K 1662.\n";
}

while ($row = $result->fetch_assoc()) {
    echo "### Transaksi: " . $row['no_transaksi'] . "\n";
    echo "- **Tanggal**: " . $row['tanggal'] . "\n";
    echo "- **Total**: Rp " . number_format($row['total_pembayaran'], 0, ',', '.') . "\n";
    echo "- **Detail Barang**:\n";

    $items_sql = "SELECT nama_barang, jumlah, harga, total FROM item_penjualan WHERE fid_penjualan = " . $row['id'];
    $items = $mysqli->query($items_sql);
    
    if ($items && $items->num_rows > 0) {
        while ($item = $items->fetch_assoc()) {
            echo "  - " . $item['nama_barang'] . " (" . $item['jumlah'] . " x " . number_format($item['harga'], 0, ',', '.') . ") = Rp " . number_format($item['total'], 0, ',', '.') . "\n";
        }
    } else {
        echo "  - (Tidak ada item - GHOST TRANSACTION)\n";
    }
    echo "\n---\n";
}

$mysqli->close();
