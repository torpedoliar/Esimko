<?php

namespace Tests\Feature;

use App\Http\Controllers\BuktiTransaksiController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LampiranBuktiTest extends TestCase
{
    protected $file;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('transaksi', function (Blueprint $t) {
            $t->increments('id'); $t->string('fid_anggota'); $t->integer('fid_jenis_transaksi')->default(3);
            $t->integer('fid_status')->default(4); $t->string('bukti_transaksi')->nullable(); $t->timestamps();
        });
        Schema::create('otoritas_user', function (Blueprint $t) {
            $t->increments('id'); $t->integer('fid_hak_akses'); $t->integer('fid_modul');
            $t->string('is_view'); $t->string('is_print'); $t->string('is_all_user');
        });
        $dir = storage_path('app/bukti_transaksi');
        if (!is_dir($dir)) mkdir($dir, 0700, true);
        $this->file = $dir . '/lampiran-test.jpg';
        file_put_contents($this->file, 'test-image');
        DB::table('transaksi')->insert([
            ['id' => 1, 'fid_anggota' => 'A', 'fid_jenis_transaksi' => 3, 'fid_status' => 4, 'bukti_transaksi' => 'bukti_transaksi/lampiran-test.jpg'],
            ['id' => 2, 'fid_anggota' => 'B', 'fid_jenis_transaksi' => 3, 'fid_status' => 4, 'bukti_transaksi' => 'bukti_transaksi/lampiran-test.jpg'],
        ]);
        Route::get('/lampiran-test/{id}', function (Request $r, $id) {
            return (new BuktiTransaksiController)->lampiran($r, $id);
        });
    }

    protected function tearDown(): void
    {
        if ($this->file && is_file($this->file)) unlink($this->file);
        parent::tearDown();
    }

    public function test_owner_can_view_their_attachment()
    {
        $this->withSession(['useractive' => (object) ['no_anggota' => 'A', 'hak_akses' => 2]])
            ->get('/lampiran-test/1')->assertOk()->assertHeader('Cache-Control', 'max-age=0, no-store, private');
    }

    public function test_member_cannot_view_other_member_attachment()
    {
        $this->withSession(['useractive' => (object) ['no_anggota' => 'A', 'hak_akses' => 2]])
            ->get('/lampiran-test/2')->assertStatus(404);
    }

    public function test_staff_requires_view_permission_on_module()
    {
        $this->withSession(['useractive' => (object) ['no_anggota' => 'STAFF', 'hak_akses' => 1]])
            ->get('/lampiran-test/1')->assertStatus(404);
        DB::table('otoritas_user')->insert(['fid_hak_akses' => 1, 'fid_modul' => 34, 'is_view' => 'Y', 'is_print' => 'Y', 'is_all_user' => '']);
        $this->withSession(['useractive' => (object) ['no_anggota' => 'STAFF', 'hak_akses' => 1]])
            ->get('/lampiran-test/1')->assertOk();
    }

    public function test_missing_file_or_record_is_not_found()
    {
        $this->withSession(['useractive' => (object) ['no_anggota' => 'A', 'hak_akses' => 2]])
            ->get('/lampiran-test/999')->assertStatus(404);
    }
}