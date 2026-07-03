<?php
use App\Penjualan;
use App\AngsuranBelanja;
use Illuminate\Support\Facades\DB;

$baseLimit = 1500000;

$members = [
    'K 1154' => ['target' => 414314, 'app_shows' => 553300],
    'K 1667' => ['target' => 750890, 'app_shows' => 821100],
];

echo "=== DIAGNOSA LIMIT K 1154 & K 1667 ===\n\n";

foreach ($members as $id => $info) {
    echo "--- $id ---\n";
    echo "Target Limit : " . number_format($info['target']) . "\n";
    echo "App Shows    : " . number_format($info['app_shows']) . "\n";
    echo "Base Limit   : " . number_format($baseLimit) . "\n";
    
    // Current debt from app perspective
    $currentDebt = $baseLimit - $info['app_shows'];
    $targetDebt  = $baseLimit - $info['target'];
    $moreDebtNeeded = $targetDebt - $currentDebt;
    
    echo "Current Debt : " . number_format($currentDebt) . " (app limit implies this)\n";
    echo "Target Debt  : " . number_format($targetDebt) . "\n";
    echo "More Debt    : " . number_format($moreDebtNeeded) . " (need to activate this much more)\n";
    
    // Check actual status breakdown
    $pIds = Penjualan::where('fid_anggota', $id)->whereIn('fid_status', [2, 4])->pluck('id');
    
    $status3 = DB::table('angsuran_belanja')
        ->whereIn('fid_penjualan', $pIds)
        ->where('fid_status', 3)
        ->sum('total_angsuran');
    $count3 = DB::table('angsuran_belanja')
        ->whereIn('fid_penjualan', $pIds)
        ->where('fid_status', 3)
        ->count();
        
    $status6_nopayroll = DB::table('angsuran_belanja')
        ->whereIn('fid_penjualan', $pIds)
        ->where('fid_status', 6)
        ->whereNull('fid_payroll')
        ->sum('total_angsuran');
    $count6np = DB::table('angsuran_belanja')
        ->whereIn('fid_penjualan', $pIds)
        ->where('fid_status', 6)
        ->whereNull('fid_payroll')
        ->count();
        
    $status6_withpayroll = DB::table('angsuran_belanja')
        ->whereIn('fid_penjualan', $pIds)
        ->where('fid_status', 6)
        ->whereNotNull('fid_payroll')
        ->sum('total_angsuran');
    $count6wp = DB::table('angsuran_belanja')
        ->whereIn('fid_penjualan', $pIds)
        ->where('fid_status', 6)
        ->whereNotNull('fid_payroll')
        ->count();
    
    echo "\nDB Status Breakdown:\n";
    echo "  Status 3 (Pending/Active Debt): " . number_format($status3) . " ($count3 records)\n";
    echo "  Status 6 (No Payroll - Free) : " . number_format($status6_nopayroll) . " ($count6np records)\n";
    echo "  Status 6 (With Payroll - Cut) : " . number_format($status6_withpayroll) . " ($count6wp records)\n";
    
    // Available records to activate (Status 6 without payroll)
    echo "\nAvailable Status 6 (no payroll) records to activate:\n";
    $available = DB::table('angsuran_belanja')
        ->join('penjualan', 'penjualan.id', '=', 'angsuran_belanja.fid_penjualan')
        ->where('penjualan.fid_anggota', $id)
        ->whereIn('penjualan.fid_status', [2, 4])
        ->where('angsuran_belanja.fid_status', 6)
        ->whereNull('angsuran_belanja.fid_payroll')
        ->select('angsuran_belanja.id', 'angsuran_belanja.total_angsuran', 'angsuran_belanja.angsuran_ke')
        ->orderBy('angsuran_belanja.total_angsuran', 'ASC')
        ->get();
    
    foreach ($available as $a) {
        echo "  ID={$a->id}, Ke={$a->angsuran_ke}, Amount=" . number_format($a->total_angsuran) . "\n";
    }
    
    // Try to find subset that matches moreDebtNeeded
    echo "\nFinding subset to match gap of " . number_format($moreDebtNeeded) . ":\n";
    $fillSum = 0;
    $fillIds = [];
    foreach ($available as $a) {
        if ($fillSum + $a->total_angsuran <= $moreDebtNeeded + 500) {
            $fillSum += $a->total_angsuran;
            $fillIds[] = $a->id;
        }
    }
    echo "  Best match: " . number_format($fillSum) . " (" . count($fillIds) . " records)\n";
    echo "  IDs: " . implode(', ', $fillIds) . "\n";
    echo "  Remaining gap: " . number_format($moreDebtNeeded - $fillSum) . "\n";
    
    $projectedLimit = $baseLimit - ($currentDebt + $fillSum);
    echo "  Projected Limit: " . number_format($projectedLimit) . "\n\n";
}
