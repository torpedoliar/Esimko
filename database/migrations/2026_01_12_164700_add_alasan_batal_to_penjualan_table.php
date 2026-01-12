<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlasanBatalToPenjualanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->text('alasan_batal')->nullable()->after('keterangan');
            $table->string('dibatalkan_oleh', 50)->nullable()->after('alasan_batal');
            $table->timestamp('tanggal_batal')->nullable()->after('dibatalkan_oleh');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn(['alasan_batal', 'dibatalkan_oleh', 'tanggal_batal']);
        });
    }
}
