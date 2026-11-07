<?php

namespace Tests\Feature;

use App\Http\Controllers\MobileController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MobileSavingsDepositTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('transaksi', function (Blueprint $t) {
            $t->increments('id');
            $t->string('fid_anggota')->nullable();
            $t->date('tanggal')->nullable();
            $t->double('nominal')->nullable();
            $t->integer('tenor')->nullable();
            $t->integer('fid_jenis_transaksi')->nullable();
            $t->integer('fid_metode_transaksi')->nullable();
            $t->integer('fid_status')->nullable();
            $t->text('keterangan')->nullable();
            $t->string('created_by')->nullable();
            $t->dateTime('created_at')->nullable();
        });
        Route::post('/savings-type/{jenis}', function (Request $r, $jenis) {
            return (new MobileController)->proses_transaksi($r, $jenis);
        });
    }

    public function test_mobile_deposit_goes_to_sukarela_not_hari_raya()
    {
        $this->post('/savings-type/setoran', [
            'action' => 'add',
            'no_anggota' => 'A',
            'nominal' => '250.000',
            'keterangan' => 'Setoran dari aplikasi',
        ])->assertOk();

        $row = DB::table('transaksi')->where('fid_anggota', 'A')->first();
        $this->assertNotNull($row);
        // Simpanan Sukarela = 3. Simpanan Hari Raya = 4 (khusus petugas web).
        $this->assertSame(3, (int) $row->fid_jenis_transaksi);
        $this->assertSame(1, (int) $row->fid_status);
        $this->assertSame(250000.0, (float) $row->nominal);
    }

    public function test_mobile_withdrawal_still_uses_sukarela_withdrawal_type()
    {
        DB::table('transaksi')->insert([
            'fid_anggota' => 'A', 'tanggal' => '2026-11-01', 'nominal' => 500000,
            'fid_jenis_transaksi' => 3, 'fid_status' => 4, 'created_at' => '2026-11-01 08:00:00',
        ]);
        $this->post('/savings-type/penarikan', [
            'action' => 'add',
            'no_anggota' => 'A',
            'nominal' => '100.000',
            'keterangan' => 'Penarikan dari aplikasi',
        ])->assertOk();

        $row = DB::table('transaksi')->where('keterangan', 'Penarikan dari aplikasi')->first();
        $this->assertNotNull($row);
        $this->assertSame(6, (int) $row->fid_jenis_transaksi);
        $this->assertSame(-100000.0, (float) $row->nominal);
    }
}
