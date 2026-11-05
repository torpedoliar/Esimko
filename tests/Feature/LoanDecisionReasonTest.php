<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoanDecisionReasonTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('anggota', function (Blueprint $t) {
            $t->increments('id'); $t->string('no_anggota'); $t->string('nama_lengkap');
            $t->string('token')->nullable(); $t->dateTime('login_at')->nullable();
        });
        Schema::create('transaksi', function (Blueprint $t) {
            $t->increments('id'); $t->string('fid_anggota'); $t->integer('fid_status');
            $t->integer('fid_jenis_transaksi')->default(9);
            $t->text('draft_pengajuan')->nullable(); $t->string('created_by')->nullable();
            $t->dateTime('created_at')->nullable(); $t->date('tanggal')->nullable();
        });
        Schema::create('penjualan', function (Blueprint $t) {
            $t->increments('id'); $t->string('fid_anggota');
            $t->string('created_by')->nullable(); $t->dateTime('created_at')->nullable();
        });
        Schema::create('angsuran', function (Blueprint $t) {
            $t->increments('id'); $t->integer('fid_transaksi'); $t->integer('fid_status');
        });
        Schema::create('gaji_pokok', function (Blueprint $t) {
            $t->increments('id'); $t->string('attachment')->nullable();
        });
        Schema::create('status_transaksi', function (Blueprint $t) {
            $t->increments('id'); $t->string('caption');
        });
        Schema::create('otoritas_user', function (Blueprint $t) {
            $t->string('fid_hak_akses'); $t->integer('fid_modul');
            $t->string('is_view')->default('Y'); $t->string('is_insert')->default('Y');
            $t->string('is_update')->default('Y'); $t->string('is_delete')->default('Y');
            $t->string('is_all_user')->default('Y'); $t->string('is_print')->default('Y');
            $t->string('is_verified')->default('Y');
        });
        DB::table('otoritas_user')->insert(['fid_hak_akses' => '1', 'fid_modul' => 15]);
        Schema::create('verifikasi_transaksi', function (Blueprint $t) {
            $t->increments('id'); $t->integer('fid_transaksi'); $t->string('jenis');
            $t->string('caption'); $t->text('keterangan')->nullable();
            $t->dateTime('created_at')->nullable(); $t->string('created_by')->nullable();
        });
        DB::table('anggota')->insert([
            ['no_anggota' => 'A', 'nama_lengkap' => 'Anggota A', 'token' => 'token-a'],
            ['no_anggota' => 'B', 'nama_lengkap' => 'Anggota B', 'token' => 'token-b'],
        ]);
        DB::table('status_transaksi')->insert([
            ['id' => 2, 'caption' => 'Ditolak oleh'], ['id' => 3, 'caption' => 'Disetujui oleh'],
        ]);
        Route::middleware('web')->post('/test-loan-decision', '\\App\\Http\\Controllers\\PinjamanController@verifikasi');
    }

    private function loan($id, $draft = null)
    {
        DB::table('transaksi')->insert([
            'id' => $id, 'fid_anggota' => 'A', 'fid_status' => 1,
            'draft_pengajuan' => $draft, 'created_by' => 'A', 'created_at' => '2026-01-01 00:00:00',
        ]);
    }

    private function decision($id, $status, $reason = null)
    {
        return $this->withSession(['useractive' => (object) ['no_anggota' => 'A', 'hak_akses' => '1']])
            ->from('/test-loan')->post('/test-loan-decision', ['id' => $id, 'status' => $status, 'alasan' => $reason]);
    }

    public function test_rejection_requires_nonblank_reason_before_status_and_document_changes()
    {
        $path = 'dokumen_pengajuan/loan-reason-test-'.uniqid().'.txt';
        $file = storage_path('app/'.$path);
        if (!is_dir(dirname($file))) mkdir(dirname($file), 0777, true);
        file_put_contents($file, 'document');
        try {
            $this->loan(1, json_encode(['slip' => $path]));
            foreach ([null, '', " \t "] as $reason) {
                $this->decision(1, 2, $reason)->assertSessionHasErrors('alasan');
                $this->assertSame(1, (int) DB::table('transaksi')->where('id', 1)->value('fid_status'));
                $this->assertFileExists($file);
                $this->assertSame(0, DB::table('verifikasi_transaksi')->count());
            }
            $this->decision(1, 2, '  Dokumen tidak sesuai  ')->assertSessionHasNoErrors();
            $this->assertFileDoesNotExist($file);
            $this->assertSame(2, (int) DB::table('transaksi')->where('id', 1)->value('fid_status'));
            $this->assertSame(\App\Helpers\GlobalHelper::loan_decision_reason_for_log('Dokumen tidak sesuai'), DB::table('verifikasi_transaksi')->value('keterangan'));
        } finally {
            if (file_exists($file)) unlink($file);
        }
    }

    public function test_approval_reason_is_optional_and_recorded_when_supplied()
    {
        $this->loan(1); $this->loan(2);
        $this->decision(1, 3)->assertSessionHasNoErrors();
        $this->decision(2, 3, '  Memenuhi syarat  ')->assertSessionHasNoErrors();
        $this->assertNull(DB::table('verifikasi_transaksi')->where('fid_transaksi', 1)->value('keterangan'));
        $this->assertSame(\App\Helpers\GlobalHelper::loan_decision_reason_for_log('Memenuhi syarat'), DB::table('verifikasi_transaksi')->where('fid_transaksi', 2)->value('keterangan'));
    }

    public function test_mobile_history_requires_ownership_for_each_supported_type()
    {
        $this->loan(1);
        $this->loan(3);
        DB::table('transaksi')->where('id', 3)->update(['fid_jenis_transaksi' => 1]);
        DB::table('penjualan')->insert(['id' => 2, 'fid_anggota' => 'A', 'created_by' => 'A']);
        DB::table('verifikasi_transaksi')->insert([
            ['fid_transaksi' => 1, 'jenis' => 'transaksi', 'caption' => 'Ditolak', 'keterangan' => \App\Helpers\GlobalHelper::loan_decision_reason_for_log('Alasan pribadi'), 'created_by' => 'A'],
            ['fid_transaksi' => 1, 'jenis' => 'transaksi', 'caption' => 'Riwayat lama', 'keterangan' => 'Catatan internal', 'created_by' => 'A'],
            ['fid_transaksi' => 2, 'jenis' => 'penjualan', 'caption' => 'Diproses', 'keterangan' => 'Catatan penjualan internal', 'created_by' => 'A'],
            ['fid_transaksi' => 2, 'jenis' => 'penjualan', 'caption' => 'Lainnya', 'keterangan' => \App\Helpers\GlobalHelper::loan_decision_reason_for_log('Bukan pinjaman'), 'created_by' => 'A'],
            ['fid_transaksi' => 3, 'jenis' => 'transaksi', 'caption' => 'Simpanan', 'keterangan' => \App\Helpers\GlobalHelper::loan_decision_reason_for_log('Bukan alasan pinjaman'), 'created_by' => 'A'],
        ]);
        $this->getJson('/api/mobile/riwayat_transaksi?jenis=transaksi&id=1', ['Authorization' => 'Bearer token-a'])
            ->assertOk()->assertJsonFragment(['keterangan' => 'Alasan pribadi'])
            ->assertDontSee('Catatan internal')->assertDontSee(\App\Helpers\GlobalHelper::LOAN_DECISION_REASON_PREFIX);
        $this->getJson('/api/mobile/riwayat_transaksi?jenis=penjualan&id=2', ['Authorization' => 'Bearer token-a'])
            ->assertOk()->assertDontSee('Catatan penjualan internal')->assertDontSee('Bukan pinjaman');
        $this->getJson('/api/mobile/riwayat_transaksi?jenis=transaksi&id=3', ['Authorization' => 'Bearer token-a'])
            ->assertOk()->assertDontSee('Bukan alasan pinjaman');
        foreach (['transaksi&id=1', 'penjualan&id=2', 'lain&id=1', 'transaksi&id=999'] as $query) {
            $this->getJson('/api/mobile/riwayat_transaksi?jenis='.$query.'&no_anggota=A', ['Authorization' => 'Bearer token-b'])->assertStatus(404);
        }
        $this->getJson('/api/mobile/riwayat_transaksi?jenis=transaksi&id=1')->assertStatus(401);
    }
}
