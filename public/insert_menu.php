<?php

require '/var/www/html/vendor/autoload.php';
$app = require '/var/www/html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Insert menu using raw query
DB::statement("INSERT INTO modul (parent_id, nama_modul, icon, link, `order`, is_active) VALUES (11, 'Bunga Pinjaman', 'mdi mdi-percent', 'pengaturan/bunga_pinjaman', 3, 1)");

echo "Menu inserted!\n";

// Get the inserted ID
$menu = DB::table('modul')->where('link', 'pengaturan/bunga_pinjaman')->first();
if ($menu) {
    echo "Menu ID: " . $menu->id . "\n";
    
    // Insert permission
    DB::statement("INSERT INTO otoritas_user (fid_hak_akses, fid_modul, is_view, is_insert, is_update, is_delete, is_print, is_verified) VALUES (1, {$menu->id}, 'Y', 'Y', 'Y', 'N', 'N', 'N')");
    echo "Permission added for Administrator\n";
}

echo "Done!\n";

