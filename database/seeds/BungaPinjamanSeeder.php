<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BungaPinjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seed bunga pinjaman settings and menu item.
     * Run: php artisan db:seed --class=BungaPinjamanSeeder
     *
     * @return void
     */
    public function run()
    {
        // 1. Create pengaturan table if not exists
        if (!DB::getSchemaBuilder()->hasTable('pengaturan')) {
            DB::statement("CREATE TABLE pengaturan (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                kode VARCHAR(100) UNIQUE NOT NULL,
                nama VARCHAR(255) NOT NULL,
                nilai VARCHAR(255) NOT NULL,
                tipe VARCHAR(50) NOT NULL,
                keterangan TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )");
        }

        // 2. Create pengaturan_log table if not exists
        if (!DB::getSchemaBuilder()->hasTable('pengaturan_log')) {
            DB::statement("CREATE TABLE pengaturan_log (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                fid_pengaturan BIGINT UNSIGNED NOT NULL,
                nilai_lama VARCHAR(255) NOT NULL,
                nilai_baru VARCHAR(255) NOT NULL,
                created_by VARCHAR(20) NOT NULL,
                keterangan TEXT NULL,
                created_at TIMESTAMP NULL
            )");
        }

        // 3. Seed default bunga pinjaman
        DB::table('pengaturan')->updateOrInsert(
            ['kode' => 'bunga_pinjaman'],
            [
                'nama' => 'Bunga Pinjaman (Per Bulan)',
                'nilai' => '0.01',
                'tipe' => 'persen',
                'keterangan' => 'Persentase bunga pinjaman per bulan untuk semua jenis pinjaman',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 4. Insert menu item
        $maxId = DB::table('modul')->max('id');
        $newId = max($maxId + 1, 73);
        
        $exists = DB::table('modul')->where('url', 'pengaturan/bunga_pinjaman')->exists();
        if (!$exists) {
            DB::table('modul')->insert([
                'id' => $newId,
                'parent_id' => 11, // Pengaturan parent
                'nama_modul' => 'Bunga Pinjaman',
                'url' => 'pengaturan/bunga_pinjaman',
                'icon' => 'fa-percent',
                'is_active' => 1,
                'order' => 3
            ]);
            
            // 5. Insert permission for Administrator
            DB::table('otoritas_user')->updateOrInsert(
                ['fid_hak_akses' => 1, 'fid_modul' => $newId],
                [
                    'is_view' => 'Y',
                    'is_insert' => 'Y',
                    'is_update' => 'Y',
                    'is_delete' => 'N',
                    'is_print' => 'N',
                    'is_verified' => 'N'
                ]
            );
            
            echo "Menu 'Bunga Pinjaman' created with ID: {$newId}\n";
        } else {
            echo "Menu 'Bunga Pinjaman' already exists.\n";
        }

        echo "BungaPinjamanSeeder completed!\n";
    }
}
