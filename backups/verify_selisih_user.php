<?php
// VERIFY SELISIH USER
use App\Penjualan;
use App\AngsuranBelanja;

$targets = [
    'K 0558' => 83237,
    'K 2008' => 1179339,
    'K 1606' => 10500
];

echo "--- VERIFIKASI SELISIH USER ---\n";
echo str_pad("MEMBER", 10) . str_pad("USER CLAIM", 15) . str_pad("SYSTEM JNE (IGNORED)", 25) . "DIFF\n";
echo str_repeat("-", 60) . "\n";

foreach ($targets as $id => $claim) {
    // 1. Get Active sales that have Paid/Missing Angsuran
    $transaksi = Penjualan::where('fid_anggota', $id)
        ->whereIn('fid_status', [2, 4])
        ->get();
        
    $totalIgnored = 0;
    
    foreach($transaksi as $t) {
        $angsuran = AngsuranBelanja::where('fid_penjualan', $t->id)->first();
        // Condition for "Ignored by Payroll": Angsuran exists AND is 6 (Lunas) OR is NULL
        // Payroll only picks up Status 3.
        
        // However, user might be referring to "Limit Selisih".
        // Let's count the ones that SHOULD be cutoff but weren't.
        if (!$angsuran || $angsuran->fid_status == 6) {
           $totalIgnored += $t->total_pembayaran;
        }
    }
    
    $diff = $totalIgnored - $claim;
    echo str_pad($id, 10) . 
         str_pad(number_format($claim), 15) . 
         str_pad(number_format($totalIgnored), 25) . 
         number_format($diff) . "\n";
}
