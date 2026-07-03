<?php
use Illuminate\Support\Facades\Schema;

echo "--- COLUMNS OF ANGGOTA TABLE ---\n";
$columns = Schema::getColumnListing('anggota');
foreach ($columns as $c) {
    echo "- $c\n";
}
