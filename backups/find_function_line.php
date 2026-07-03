<?php
$file = 'C:\IIS\Esimko\app\Helpers\GlobalHelper.php';
if (!file_exists($file)) {
    echo "File not found at $file\n";
    exit;
}
$lines = file($file);

foreach ($lines as $i => $line) {
    if (strpos($line, 'function sisa_kredit_belanja') !== false) {
        $docLine = $i + 1;
        echo "Line $docLine: " . trim($line) . "\n";
    }
}
