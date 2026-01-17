<?php
// Script untuk cek limit kredit anggota

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$no_anggota = $argv[1] ?? 'K 0863';

$anggota = \App\Anggota::where('no_anggota', 'like', '%' . str_replace('K ', '', $no_anggota) . '%')->first();

if (!$anggota) {
    echo "Anggota tidak ditemukan!\n";
    exit(1);
}

echo "========================================\n";
echo "ANALISA LIMIT KREDIT ANGGOTA\n";
echo "========================================\n";
echo "No Anggota: " . $anggota->no_anggota . "\n";
echo "Nama: " . $anggota->nama_lengkap . "\n";
echo "ID: " . $anggota->id . "\n";
echo "----------------------------------------\n";

// Hitung limit menggunakan function yang sama
$limit_tersedia = \App\Helpers\GlobalHelper::limitKaryawan($anggota->id);
echo "Limit Maksimal: Rp " . number_format(1500000) . "\n";
echo "Limit Tersedia: Rp " . number_format($limit_tersedia) . "\n";
echo "Limit Terpakai: Rp " . number_format(1500000 - $limit_tersedia) . "\n";
echo "----------------------------------------\n";

// Detail penjualan kredit
$penjualan = \App\Penjualan::where('fid_anggota', $anggota->id)
    ->where('fid_metode_pembayaran', 3) // kredit
    ->whereIn('fid_status', [2, 4]) // belum lunas
    ->orderBy('created_at', 'desc')
    ->get();

echo "\nPENJUALAN KREDIT BELUM LUNAS:\n";
echo "----------------------------------------\n";
$total = 0;
foreach ($penjualan as $p) {
    echo "- " . $p->no_transaksi . " | " . date('d/m/Y', strtotime($p->created_at)) . " | Rp " . number_format($p->grand_total) . " | Status: " . $p->fid_status . "\n";
    $total += $p->grand_total;
}
echo "Total Penjualan Kredit: Rp " . number_format($total) . "\n";

// Detail angsuran
$penjualan_ids = $penjualan->pluck('id')->toArray();
if (!empty($penjualan_ids)) {
    $angsuran = \App\AngsuranBelanja::whereIn('fid_penjualan', $penjualan_ids)
        ->where('fid_status', 3) // belum bayar
        ->get();
    
    echo "\nANGSURAN BELUM DIBAYAR:\n";
    echo "----------------------------------------\n";
    $total_angsuran = 0;
    foreach ($angsuran as $a) {
        $p = $penjualan->where('id', $a->fid_penjualan)->first();
        echo "- " . ($p ? $p->no_transaksi : 'N/A') . " | Angsuran ke-" . $a->angsuran_ke . " | Rp " . number_format($a->total_angsuran) . "\n";
        $total_angsuran += $a->total_angsuran;
    }
    echo "Total Angsuran Belum Bayar: Rp " . number_format($total_angsuran) . "\n";
}

echo "\n========================================\n";
