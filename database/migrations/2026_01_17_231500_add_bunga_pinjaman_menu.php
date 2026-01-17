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
            
            // Insert menu
            $menuId = DB::table('modul')->insertGetId([
                'parent_id' => $parentId,
                'nama_modul' => 'Bunga Pinjaman',
                'icon' => 'fa fa-percent',
                'link' => 'pengaturan/bunga_pinjaman',
                'order' => 3,
                'is_active' => 1,
            ]);
            
            // Copy permissions from Otoritas Modul (or any existing Pengaturan submenu)
            $referenceMenuId = DB::table('modul')
                ->where('link', 'pengaturan/otoritas_user')
                ->value('id');
            
            if ($referenceMenuId) {
                $permissions = DB::table('otoritas_user')
                    ->where('fid_modul', $referenceMenuId)
                    ->get();
                
                foreach ($permissions as $perm) {
                    DB::table('otoritas_user')->insert([
                        'fid_hak_akses' => $perm->fid_hak_akses,
                        'fid_modul' => $menuId,
                        'is_view' => $perm->is_view,
                        'is_insert' => $perm->is_insert,
                        'is_update' => $perm->is_update,
                        'is_delete' => $perm->is_delete,
                        'is_all_user' => $perm->is_all_user,
                        'is_print' => $perm->is_print,
                        'is_verified' => $perm->is_verified,
                    ]);
                }
            }
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
