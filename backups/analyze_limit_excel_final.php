<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\Importable;

class SimpleImport implements ToArray {
    use Importable;
    public function array(array $array) { return $array; }
}

function clean_currency($str) {
    if (trim($str) === '-' || trim($str) === '') return 0;
    // Remove "Rp " and thousand separators
    // Case: "Rp 83.237" -> 83237
    // Case: "1.179.339" -> 1179339
    
    // Be careful with decimals if any? Usually Rp is integer.
    $clean = str_replace(['Rp', '.', ' ', ','], '', $str);
    return (int)$clean;
}

try {
    $sheets = Excel::toArray(new SimpleImport, 'C:/IIS/Esimko/storage/cek_limit.xlsx');
    $issues = [];
    
    foreach($sheets as $rows) {
        foreach($rows as $row) {
            if (!isset($row[1])) continue;
            $line = trim($row[1]);
            
            if (strpos($line, '|') === false) continue;
            if (strpos($line, '|---') !== false) continue;
            if (strpos($line, 'Nama (ID)') !== false) continue;
            
            $cols = explode('|', $line);
            if (count($cols) < 5) continue;
            
            // Logic to find Selisih column (usually last)
            $selisihRaw = trim($cols[count($cols)-1]);
            if ($selisihRaw === '') $selisihRaw = trim($cols[count($cols)-2]);

            $nameId = trim($cols[2]); 
            $selisih = clean_currency($selisihRaw);
            
            if ($selisih != 0) {
                // Extract ID
                $id = 'UNKNOWN';
                if (preg_match('/\((K\s*\d+)\)/', $nameId, $matches)) {
                    $id = $matches[1];
                }
                $issues[] = [
                    'id' => $id,
                    'name' => $nameId,
                    'selisih' => $selisihRaw, // Keep raw for display
                    'int_selisih' => $selisih
                ];
            }
        }
    }
    
    // Output Markdown Table
    echo "| MEMBER ID | NAMA LENGKAP | SELISIH (EXCEL) |\n";
    echo "|---|---|---|\n";
    foreach ($issues as $issue) {
        echo "| **{$issue['id']}** | {$issue['name']} | {$issue['selisih']} |\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
