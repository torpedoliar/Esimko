<?php
// FIX FAILED CUTOFF (FEB 10)
// REVERT ANGSURAN STATUS FROM 6 (LUNAS) TO 3 (PENDING) FOR UNPAID HEADERS

use App\Penjualan;
use App\AngsuranBelanja;
use Illuminate\Support\Facades\DB;

$targets = ['K 0558', 'K 1031', 'K 1540', 'K 1154', 'K 1667', 'K 2008', 'K 1606'];

echo "--- FIXING FAILED CUTOFF MEMBERS ---\n";

DB::beginTransaction();
try {
    foreach ($targets as $id) {
        $transaksi = Penjualan::where('fid_anggota', $id)
            ->whereIn('fid_status', [2, 4]) // Active (2) or Partial (4)
            ->get();
            
        $count = 0;
        foreach($transaksi as $t) {
            $angsuran = AngsuranBelanja::where('fid_penjualan', $t->id)->first();
            
            if (!$angsuran) {
                // If missing, CREATE IT
                echo "$id : {$t->no_transaksi} -> Missing Angsuran -> CREATING St:3\n";
                $new = new AngsuranBelanja;
                $new->fid_penjualan = $t->id;
                $new->angsuran_ke = 1;
                $new->total_angsuran = $t->total_pembayaran;
                $new->fid_status = 3;
                $new->save();
                $count++;
            } elseif ($angsuran->fid_status != 3) {
                // If logic mismatch (Paid but Header Unpaid), REVERT TO 3
                echo "$id : {$t->no_transaksi} -> Angsuran St:{$angsuran->fid_status} -> UPDATING to 3\n";
                $angsuran->fid_status = 3;
                $angsuran->save();
                $count++;
            }
        }
        echo "=> $id : Fixed $count transactions.\n";
    }
    
    DB::commit();
    echo "SUCCESS: All fixes applied.\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
}
