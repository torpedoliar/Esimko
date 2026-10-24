<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRilisApkTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('rilis_apk')) {
            Schema::create('rilis_apk', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('versi', 20);            // versionName, mis. 1.0.2
                $table->integer('version_code');        // versionCode Android, naik tiap rilis
                $table->text('changelog')->nullable();
                $table->string('nama_file');            // nama file di storage/app/apk/
                $table->bigInteger('ukuran');           // bytes
                $table->string('sha256', 64);           // integritas — diverifikasi app saat self-update
                $table->bigInteger('unduhan')->default(0);
                $table->string('dibuat_oleh')->nullable();
                $table->timestamps();
            });
        }

        // Menu admin (pola sama dgn migrasi bunga_pinjaman_menu): sub-menu Pengaturan
        // + salin otoritas dari modul referensi supaya hak akses ikut terbentuk.
        $exists = DB::table('modul')->where('link', 'pengaturan/rilis_apk')->exists();
        if (!$exists) {
            $parentId = DB::table('modul')
                ->where('nama_modul', 'Pengaturan')
                ->whereNull('link')
                ->value('id');
            $parentId = $parentId ?: 11;

            $menuId = DB::table('modul')->insertGetId([
                'parent_id' => $parentId,
                'nama_modul' => 'Rilis Aplikasi',
                'icon' => 'fa fa-android',
                'link' => 'pengaturan/rilis_apk',
                'order' => 9,
                'is_active' => 1,
            ]);

            $referenceMenuId = DB::table('modul')
                ->where('link', 'pengaturan/otoritas_user')
                ->value('id');
            if ($referenceMenuId) {
                $permissions = DB::table('otoritas_user')->where('fid_modul', $referenceMenuId)->get();
                foreach ($permissions as $perm) {
                    DB::table('otoritas_user')->insert([
                        'fid_hak_akses' => $perm->fid_hak_akses,
                        'fid_modul' => $menuId,
                        'is_view' => $perm->is_view,
                        'is_insert' => $perm->is_insert,
                        'is_update' => $perm->is_update,
                        'is_delete' => $perm->is_delete,
                        'is_all_user' => $perm->is_all_user,
                        'is_print' => $perm->is_print ?? 1,
                        'is_verified' => $perm->is_verified ?? 1,
                    ]);
                }
            }
        }
    }

    public function down()
    {
        $menuId = DB::table('modul')->where('link', 'pengaturan/rilis_apk')->value('id');
        if ($menuId) {
            DB::table('otoritas_user')->where('fid_modul', $menuId)->delete();
            DB::table('modul')->where('id', $menuId)->delete();
        }
        Schema::dropIfExists('rilis_apk');
    }
}
