<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDitolakAtToTransaksiTable extends Migration
{
    public function up()
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dateTime('ditolak_at')->nullable()->after('updated_at');
            $table->index(['fid_status', 'ditolak_at'], 'transaksi_tolak_retensi_idx');
        });
    }

    public function down()
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropIndex('transaksi_tolak_retensi_idx');
            $table->dropColumn('ditolak_at');
        });
    }
}