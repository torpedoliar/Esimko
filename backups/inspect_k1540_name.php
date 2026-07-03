<?php
use Illuminate\Support\Facades\DB;

$id = 'K 1540';
echo "--- RAW INSPECTION FOR $id ---\n";

$anggota = DB::table('anggota')->where('no_anggota', $id)->first();

if ($anggota) {
    echo "NAME: " . $anggota->nama . "\n";
    echo "PLAFON: " . number_format($anggota->plafon) . "\n";
} else {
    echo "MEMBER NOT FOUND IN DB.\n";
    // Try without space?
    $id2 = str_replace(' ', '', $id);
    $anggota2 = DB::table('anggota')->where('no_anggota', $id2)->first();
    if ($anggota2) {
        echo "FOUND WITH ID '$id2': " . $anggota2->nama . " | Plafon: " . number_format($anggota2->plafon) . "\n";
    }
}
