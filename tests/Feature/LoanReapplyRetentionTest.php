<?php

namespace Tests\Feature;

use App\Console\Commands\PurgeExpiredRejectedLoans;
use App\Http\Controllers\MobileController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoanReapplyRetentionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('transaksi', function (Blueprint $t) {
            $t->increments('id'); $t->string('fid_anggota'); $t->integer('fid_status');
            $t->integer('fid_jenis_transaksi'); $t->integer('tenor')->nullable();
            $t->decimal('nominal', 15, 2)->nullable(); $t->text('draft_pengajuan')->nullable();
            $t->dateTime('ditolak_at')->nullable(); $t->timestamps();
        });
        Schema::create('gaji_pokok', function (Blueprint $t) {
            $t->increments('id'); $t->string('attachment')->nullable();
        });
        Schema::create('verifikasi_transaksi', function (Blueprint $t) {
            $t->increments('id'); $t->integer('fid_transaksi'); $t->string('jenis');
            $t->string('caption'); $t->text('keterangan')->nullable();
            $t->dateTime('created_at')->nullable(); $t->string('created_by')->nullable();
        });
        Storage::fake();
    }

    public function test_purge_removes_expired_rejected_documents()
    {
        $draft = ['slip' => ['bulan_ini' => 'slip_gaji/a.pdf'], 'dokumen' => ['kk' => 'dokumen_pengajuan/kk.pdf'], 'consent' => ['versi' => 'v1']];
        DB::table('transaksi')->insert([
            'id' => 1, 'fid_anggota' => 'A', 'fid_status' => 2, 'fid_jenis_transaksi' => 10,
            'draft_pengajuan' => json_encode($draft), 'ditolak_at' => now()->subDays(8)->toDateTimeString(),
        ]);
        Storage::put('slip_gaji/a.pdf', 'x');
        Storage::put('dokumen_pengajuan/kk.pdf', 'x');

        $this->assertSame(1, PurgeExpiredRejectedLoans::purgeExpired());
        $saved = json_decode(DB::table('transaksi')->where('id', 1)->value('draft_pengajuan'), true);
        $this->assertArrayNotHasKey('slip', $saved);
        $this->assertArrayNotHasKey('dokumen', $saved);
        $this->assertFalse(Storage::exists('slip_gaji/a.pdf'));
        $this->assertFalse(Storage::exists('dokumen_pengajuan/kk.pdf'));
    }

    public function test_reapply_fetch_requires_ownership()
    {
        $draft = ['gaji' => 2000000, 'slip' => ['bulan_ini' => 'slip_gaji/a.pdf'], 'dokumen' => ['kk' => 'kk'], 'consent' => ['versi' => 'v1']];
        DB::table('transaksi')->insert([
            'id' => 10, 'fid_anggota' => 'A', 'fid_status' => 2, 'fid_jenis_transaksi' => 10,
            'nominal' => -2000000, 'tenor' => 12, 'draft_pengajuan' => json_encode($draft),
            'ditolak_at' => now()->toDateTimeString(),
        ]);

        $controller = new MobileController;
        $other = Request::create('/api/mobile/pinjaman/pengajuan', 'GET', ['id' => 10, 'no_anggota' => 'B']);
        $this->assertSame(404, $controller->pengajuan_pinjaman($other)->getStatusCode());

        $owner = Request::create('/api/mobile/pinjaman/pengajuan', 'GET', ['id' => 10, 'no_anggota' => 'A']);
        $resp = $controller->pengajuan_pinjaman($owner);
        $this->assertSame(200, $resp->getStatusCode());
        $data = json_decode($resp->getContent(), true)['data'];
        $this->assertSame(10, $data['jenis_pinjaman']);
        $this->assertContains('slip_bulan_ini', $data['dokumen_terlampir']);
        $this->assertNotNull($data['batas_ajukan_ulang']);
    }
}