<?php
$file = 'e:\Vibe\Esimko\docs\mass_fix_strategy\missed_cutoff_users_report.md';
$content = file_get_contents($file);
$lines = explode("\n", $content);

$users = [];
$currentUser = '';
$currentTotal = 0;

foreach ($lines as $line) {
    if (strpos($line, '### ') === 0) {
        if ($currentUser) {
            $users[] = ['name' => $currentUser, 'total' => $currentTotal];
        }
        $currentUser = trim(str_replace('### ', '', $line));
        $currentTotal = 0;
    } elseif (strpos($line, '- ') === 0) {
        // Extract amount: "... : Rp 123.456"
        if (preg_match('/Rp ([\d\.]+)/', $line, $matches)) {
            $amount = (int)str_replace('.', '', $matches[1]);
            $currentTotal += $amount;
        }
    }
}
if ($currentUser) {
    $users[] = ['name' => $currentUser, 'total' => $currentTotal];
}

// Generate Markdown Table
$output = "# Ringkasan 150 Anggota Missed Cut-Off\n\n";
$output .= "| No | Nama Anggota (ID) | Total Tertunda |\n";
$output .= "|----|-------------------|----------------|\n";

$grandTotal = 0;
foreach ($users as $index => $user) {
    $grandTotal += $user['total'];
    $formattedTotal = number_format($user['total'], 0, ',', '.');
    $no = $index + 1;
    $output .= "| $no | {$user['name']} | Rp $formattedTotal |\n";
}

$formattedGrandTotal = number_format($grandTotal, 0, ',', '.');
$output .= "\n**GRAND TOTAL: Rp $formattedGrandTotal**\n";

file_put_contents('e:\Vibe\Esimko\docs\missed_cutoff_summary_table.md', $output);
echo "Summary generated with " . count($users) . " users. Total: Rp " . $formattedGrandTotal;
