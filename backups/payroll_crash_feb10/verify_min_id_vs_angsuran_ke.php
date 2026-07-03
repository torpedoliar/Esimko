<?php
use App\AngsuranBelanja;
use App\Penjualan;
use Illuminate\Support\Facades\DB;

echo "=== VERIFIKASI: MIN(id) vs ORDER BY angsuran_ke ASC ===\n\n";

// Get all active credit sales
$belanja = Penjualan::select('id')
    ->where(function ($a){
        $a->where(function ($i){
            $i->where('jenis_belanja','toko')
                ->Where('fid_status',2);
        })->orWhere(function ($i){
            $i->where('jenis_belanja','!=','toko')
                ->Where('fid_status',4);
        });
    })
    ->where('fid_metode_pembayaran',3)
    ->where('tanggal', '<=', date('Y-m-d'))
    ->pluck('id')
    ->toArray();

echo "Total Sales to check: " . count($belanja) . "\n";

$mismatches = 0;
$checked = 0;

// Check in chunks
foreach (array_chunk($belanja, 500) as $chunk) {
    // Method A: MIN(id) per sale
    $minIds = AngsuranBelanja::select(DB::raw('fid_penjualan, MIN(id) as min_id'))
        ->whereIn('fid_penjualan', $chunk)
        ->where('fid_status', 3)
        ->groupBy('fid_penjualan')
        ->pluck('min_id', 'fid_penjualan')
        ->toArray();

    // Method B: ORDER BY angsuran_ke ASC, first() per sale
    // Use raw SQL subquery for efficiency
    $minAngsuranKe = DB::select("
        SELECT ab1.fid_penjualan, ab1.id as correct_id
        FROM angsuran_belanja ab1
        INNER JOIN (
            SELECT fid_penjualan, MIN(angsuran_ke) as min_ke
            FROM angsuran_belanja
            WHERE fid_penjualan IN (" . implode(',', $chunk) . ")
            AND fid_status = 3
            GROUP BY fid_penjualan
        ) ab2 ON ab1.fid_penjualan = ab2.fid_penjualan AND ab1.angsuran_ke = ab2.min_ke
        WHERE ab1.fid_status = 3
    ");

    $correctMap = [];
    foreach ($minAngsuranKe as $r) {
        // If multiple rows with same min angsuran_ke, take the first one
        if (!isset($correctMap[$r->fid_penjualan])) {
            $correctMap[$r->fid_penjualan] = $r->correct_id;
        }
    }

    foreach ($minIds as $penjualanId => $minId) {
        $checked++;
        if (isset($correctMap[$penjualanId]) && $correctMap[$penjualanId] != $minId) {
            $mismatches++;
            if ($mismatches <= 10) {
                echo "MISMATCH: Sale $penjualanId => MIN(id)=$minId, MIN(angsuran_ke).id={$correctMap[$penjualanId]}\n";
            }
        }
    }
}

echo "\nTotal Checked: $checked\n";
echo "Total Mismatches: $mismatches\n";

if ($mismatches == 0) {
    echo "\n✅ SAFE: MIN(id) dan ORDER BY angsuran_ke ASC menghasilkan record yang SAMA.\n";
    echo "ID auto-increment berkorelasi sempurna dengan angsuran_ke.\n";
} else {
    echo "\n⚠️ WARNING: Ada $mismatches perbedaan! MIN(id) TIDAK selalu sama dengan angsuran_ke terkecil.\n";
    echo "Fix harus diubah ke MIN(angsuran_ke) + subquery.\n";
}
