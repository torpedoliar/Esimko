<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "--- TABLE SCHEMA: ANGGOTA ---\n";
$columns = Schema::getColumnListing('anggota');
print_r($columns);

// Also peek at one row
$row = DB::table('anggota')->first();
echo "\n--- SAMPLE ROW ---\n";
print_r($row);
