<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\Importable;

class SimpleImport implements ToArray
{
    use Importable;
    public function array(array $array)
    {
        return $array;
    }
}

try {
    echo "Reading Excel Deep Scan...\n";
    $sheets = Excel::toArray(new SimpleImport, 'C:/IIS/Esimko/storage/cek_limit.xlsx');
    
    echo "Total Sheets: " . count($sheets) . "\n";
    
    foreach($sheets as $sheetIndex => $rows) {
        echo "Sheet $sheetIndex has " . count($rows) . " rows.\n";
        $nonEmptyCount = 0;
        foreach($rows as $rowIndex => $row) {
            $hasData = false;
            foreach($row as $cell) {
                if(!is_null($cell) && trim($cell) !== '') {
                    $hasData = true;
                    break;
                }
            }
            
            if($hasData) {
                echo "Sheet $sheetIndex Row $rowIndex: " . json_encode(array_values($row)) . "\n";
                $nonEmptyCount++;
            }
            
            if($nonEmptyCount >= 5) break; // Only show first 5 non-empty rows
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
