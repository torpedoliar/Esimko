<?php

namespace Tests\Feature;

use App\Http\Controllers\BelanjaController;
use App\Http\Controllers\MobileController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PurchaseOwnershipTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('penjualan', function (Blueprint $t) {
            $t->increments('id'); $t->string('fid_anggota'); $t->string('jenis_belanja');
            $t->integer('fid_status'); $t->integer('fid_metode_pembayaran')->default(1);
            $t->string('no_transaksi')->nullable(); $t->double('total_pembayaran')->default(1000); $t->timestamps();
        });
        Schema::create('anggota', function (Blueprint $t) {
            $t->increments('id'); $t->string('no_anggota'); $t->string('nama_lengkap'); $t->string('avatar')->nullable();
        });
        Schema::create('rekening_pembayaran', function (Blueprint $t) {
            $t->increments('id'); $t->string('keterangan'); $t->integer('fid_metode_pembayaran')->default(1);
        });
        Schema::create('status_belanja', function (Blueprint $t) {
            $t->increments('id'); $t->string('icon')->default('');
        });
        Schema::create('angsuran_belanja', function (Blueprint $t) {
            $t->increments('id'); $t->integer('fid_penjualan'); $t->integer('fid_status')->default(6);
            $t->integer('fid_payroll')->nullable(); $t->integer('angsuran_ke')->default(1); $t->double('total_angsuran')->default(1000);
        });
        Schema::create('status_angsuran', function (Blueprint $t) {
            $t->increments('id'); $t->string('status_angsuran'); $t->string('color')->default('');
        });
        Schema::create('payroll_angsuran_belanja', function (Blueprint $t) {
            $t->increments('id'); $t->string('bulan')->nullable();
        });
        DB::table('anggota')->insert(['no_anggota' => 'B', 'nama_lengkap' => 'Other Member']);
        DB::table('rekening_pembayaran')->insert(['id' => 1, 'keterangan' => 'Tunai']);
        DB::table('status_belanja')->insert(['id' => 1, 'icon' => '']);
        DB::table('status_angsuran')->insert(['id' => 6, 'status_angsuran' => 'Dibayar']);
        DB::table('penjualan')->insert([
            ['id' => 1, 'fid_anggota' => 'B', 'jenis_belanja' => 'toko', 'fid_status' => 1],
            ['id' => 2, 'fid_anggota' => 'A', 'jenis_belanja' => 'toko', 'fid_status' => 2],
        ]);
        DB::table('angsuran_belanja')->insert(['fid_penjualan' => 1]);
        Route::get('/ownership/mobile/detail', function (Request $r) { return (new MobileController)->detail_belanja($r, 'toko'); });
        Route::get('/ownership/mobile/installments', function (Request $r) { return (new MobileController)->angsuran_belanja($r); });
        Route::post('/ownership/mobile/cancel', function (Request $r) { return (new MobileController)->batalkan_belanja($r); });
        Route::get('/ownership/web/detail', function (Request $r) { return (new BelanjaController)->detail($r, 'toko'); });
        Route::post('/ownership/web/cancel', function (Request $r) { return (new BelanjaController)->proses_pembatalan($r, 'toko'); });
    }

    public function test_mobile_detail_cannot_read_other_member_purchase()
    {
        $this->get('/ownership/mobile/detail?id=1&no_anggota=A')->assertStatus(404);
    }

    public function test_installment_id_filter_still_checks_owner()
    {
        $this->get('/ownership/mobile/installments?id=1&no_anggota=A')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_mobile_cancellation_cannot_modify_other_member()
    {
        $this->post('/ownership/mobile/cancel', ['id' => 1, 'no_anggota' => 'A'])->assertStatus(404);
        $this->assertSame(1, (int) DB::table('penjualan')->where('id', 1)->value('fid_status'));
    }

    public function test_mobile_cancellation_cannot_change_completed_purchase()
    {
        $this->post('/ownership/mobile/cancel', ['id' => 2, 'no_anggota' => 'A', 'jenis' => 'online'])->assertStatus(422);
        $this->assertSame(2, (int) DB::table('penjualan')->where('id', 2)->value('fid_status'));
    }

    public function test_web_detail_is_owner_scoped()
    {
        $this->withSession(['useractive' => (object) ['no_anggota' => 'A', 'hak_akses' => 2]])
            ->get('/ownership/web/detail?id=1')->assertRedirect('main/belanja');
    }

    public function test_web_cancellation_checks_owner_and_status()
    {
        $this->withSession(['useractive' => (object) ['no_anggota' => 'A', 'hak_akses' => 2]])
            ->post('/ownership/web/cancel', ['id' => 1])->assertStatus(404);
        $this->post('/ownership/web/cancel', ['id' => 2])->assertStatus(422);
    }
}
