<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoanTenorValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('transaksi', function (Blueprint $t) {
            $t->increments('id');
            $t->string('fid_anggota');
            $t->integer('fid_jenis_transaksi');
            $t->integer('fid_status')->default(1);
            $t->integer('tenor');
            $t->string('nominal')->nullable();
            $t->string('keterangan')->nullable();
            $t->date('tanggal')->nullable();
            $t->integer('fid_metode_transaksi')->nullable();
            $t->string('created_by')->nullable();
            $t->text('draft_pengajuan')->nullable();
            $t->timestamps();
        });
        Schema::create('gaji_pokok', function (Blueprint $t) {
            $t->increments('id');
            $t->string('fid_anggota')->nullable();
            $t->string('bulan')->nullable();
            $t->string('gaji_pokok')->nullable();
            $t->string('attachment')->nullable();
            $t->string('created_by')->nullable();
            $t->timestamps();
        });
        Schema::create('otoritas_user', function (Blueprint $t) {
            $t->string('fid_hak_akses');
            $t->integer('fid_modul');
            $t->string('is_view')->default('Y');
            $t->string('is_insert')->default('Y');
            $t->string('is_update')->default('Y');
            $t->string('is_delete')->default('Y');
            $t->string('is_all_user')->default('Y');
            $t->string('is_print')->default('Y');
            $t->string('is_verified')->default('Y');
        });
        DB::table('otoritas_user')->insert(['fid_hak_akses' => '1', 'fid_modul' => 15]);
        Route::middleware('web')->post('/test-admin-loan', '\\App\\Http\\Controllers\\PinjamanController@proses');
    }

    private function asAdmin($payload)
    {
        return $this->withSession(['useractive' => (object) ['no_anggota' => 'ADMIN', 'hak_akses' => '1']])
            ->from('/form')->post('/test-admin-loan', $payload);
    }

    public function test_admin_rejects_new_invalid_tenors_before_persisting()
    {
        foreach ([[9, 61], [10, 16], [11, 21], [9, 0], [9, '1.5'], [8, 1]] as $case) {
            $this->asAdmin(['action' => 'add', 'jenis' => $case[0], 'tenor' => $case[1], 'no_anggota' => 'A'])
                ->assertRedirect('/form')->assertSessionHas('message');
        }
        $this->assertSame(0, DB::table('transaksi')->count());
    }

    public function test_admin_edit_cannot_bypass_limit_by_posting_another_type()
    {
        DB::table('transaksi')->insert([
            'id' => 1, 'fid_anggota' => 'A', 'fid_jenis_transaksi' => 10,
            'fid_status' => 1, 'tenor' => 18, 'nominal' => '-10000000',
        ]);
        $this->asAdmin(['action' => 'edit', 'id' => 1, 'jenis' => 9, 'tenor' => 19, 'no_anggota' => 'A'])
            ->assertRedirect('/form')->assertSessionHas('message');
        $this->assertEquals(18, DB::table('transaksi')->where('id', 1)->value('tenor'));
    }

    public function test_admin_legacy_loan_without_draft_can_edit_without_new_documents()
    {
        DB::table('transaksi')->insert([
            'id' => 2, 'fid_anggota' => 'A', 'fid_jenis_transaksi' => 10, 'fid_status' => 1,
            'tenor' => 12, 'nominal' => '-1000000', 'keterangan' => 'lama',
            'tanggal' => '2026-01-01', 'fid_metode_transaksi' => 1, 'created_by' => 'ADMIN',
        ]);
        $this->asAdmin([
            'action' => 'edit', 'id' => 2, 'jenis' => 10, 'tenor' => 12, 'no_anggota' => 'A',
            'nominal' => '1000000', 'gaji_pokok' => '5000000', 'metode_transaksi' => 1,
            'tanggal' => '2026-01-01', 'keterangan' => 'baru',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('baru', DB::table('transaksi')->where('id', 2)->value('keterangan'));
    }

    public function test_admin_new_application_requires_signed_member_consent_proof()
    {
        $this->asAdmin([
            'action' => 'add', 'jenis' => 10, 'tenor' => 12, 'no_anggota' => 'A',
            'nominal' => '1000000', 'gaji_pokok' => '5000000', 'metode_transaksi' => 1,
            'tanggal' => '2026-01-01', 'keterangan' => 'baru',
        ])->assertSessionHasErrors('bukti_persetujuan');
        $this->assertSame(0, DB::table('transaksi')->count());
    }
}
