<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddBungaPinjamanMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if menu already exists
        $exists = DB::table('modul')
            ->where('link', 'pengaturan/bunga_pinjaman')
            ->exists();
        
        if (!$exists) {
            // Get Pengaturan parent menu ID
            $parentId = DB::table('modul')
                ->where('nama_modul', 'Pengaturan')
                ->whereNull('link')
                ->value('id');
            
            // Default to 11 if not found (common ID for Pengaturan)
            $parentId = $parentId ?: 11;
            
            DB::table('modul')->insert([
                'parent_id' => $parentId,
                'nama_modul' => 'Bunga Pinjaman',
                'icon' => 'fa fa-percent',
                'link' => 'pengaturan/bunga_pinjaman',
                'order' => 3,
                'is_active' => 1,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('modul')
            ->where('link', 'pengaturan/bunga_pinjaman')
            ->delete();
    }
}
