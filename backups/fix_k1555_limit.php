<?php
use App\Penjualan;
use App\AngsuranBelanja;
use Illuminate\Support\Facades\DB;

echo "=== FIX K 1555: Lunaskan Sale #100188 ===\n\n";

DB::beginTransaction();
try {
    $updated = AngsuranBelanja::where('fid_penjualan', 100188)
        ->where('fid_status', 3)
        ->update(['fid_status' => 6]);
    echo "Updated $updated angsuran records to Status 6 (LUNAS).\n";

    // Verify
    $debt = Penjualan::where('fid_anggota', 'K 1555')
        ->where('fid_metode_pembayaran', 3)
        ->whereIn('fid_status', [2, 4])
        ->whereHas('angsuran_belanja', function ($q) { $q->where('fid_status', 3); })
        ->get();
    $total = 0;
    foreach ($debt as $p) {
        $val = $p->angsuran ?? ($p->total_pembayaran / max($p->tenor, 1));
        $total += $val;
    }
    $new_limit = 1500000 - $total;
    echo "New Limit: " . number_format($new_limit) . " (expected 1,377,500)\n";

    if (abs($new_limit - 1377500) < 1000) {
        echo "✅ SUCCESS\n";
        DB::commit();
    } else {
        echo "⚠️ Limit $new_limit not matching. Rolling back.\n";
        DB::rollback();
    }
} catch (Exception $e) {
    DB::rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
}
