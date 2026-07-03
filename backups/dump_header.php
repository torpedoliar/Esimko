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
    echo "Reading Excel...\n";
    $data = Excel::toArray(new SimpleImport, 'C:/IIS/Esimko/storage/cek_limit.xlsx');
    
    if (isset($data[0][0])) {
        echo "HEADER ROW:\n";
        print_r($data[0][0]);
        
        echo "\nFIRST DATA ROW:\n";
        if (isset($data[0][1])) {
            print_r($data[0][1]);
        }
    } else {
        echo "File appears empty or unreadable.\n";
        print_r($data);
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
