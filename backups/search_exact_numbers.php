<?php
$content = file_get_contents('e:/Vibe/Esimko/diagnose_result_clean.txt');
$targets = ['10,500', '83,237', '1,179,339'];

echo "--- SEARCHING EXACT NUMBERS ---\n";

foreach ($targets as $t) {
    if (strpos($content, $t) !== false) {
        echo "FOUND: $t\n";
        // Get context
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (strpos($line, $t) !== false) {
                echo "Line " . ($i+1) . ": $line\n";
            }
        }
    } else {
        echo "NOT FOUND: $t\n";
    }
    echo "----------------\n";
}
