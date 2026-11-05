<?php

namespace Tests\Feature;

use App\Http\Controllers\TransaksiController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebLoanSafetyController extends TransaksiController
{
    public function validasi_transaksi($request, $jenis) { return 'success'; }
    public function proses_angsuran($id, $request) { throw new \RuntimeException('schedule failed'); }
}

class WebLoanSafetyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('transaksi', function (Blueprint $t) {
            $t->increments('id'); $t->string('fid_anggota'); $t->integer('fid_jenis_transaksi');
            $t->integer('fid_status'); $t->integer('tenor'); $t->decimal('nominal', 15, 2);
            $t->text('draft_pengajuan')->nullable(); $t->string('keterangan')->nullable();
            $t->date('tanggal')->nullable(); $t->integer('fid_metode_transaksi')->nullable();
            $t->string('created_by')->nullable(); $t->timestamps();
            $t->string('bukti_transaksi')->nullable();
        });
        Schema::create('verifikasi_transaksi', function (Blueprint $t) {
            $t->increments('id'); $t->integer('fid_transaksi'); $t->string('jenis');
            $t->string('caption'); $t->text('keterangan')->nullable();
            $t->dateTime('created_at')->nullable(); $t->string('created_by')->nullable();
        });
        Schema::create('gaji_pokok', function (Blueprint $t) { $t->increments('id'); $t->string('attachment')->nullable(); });
        Schema::create('pengaturan', function (Blueprint $t) {
            $t->increments('id'); $t->string('kode'); $t->string('nama')->nullable();
            $t->string('nilai')->nullable(); $t->string('tipe')->nullable(); $t->string('keterangan')->nullable();
        });
        DB::table('pengaturan')->insert(['kode' => 'bunga_pinjaman', 'nilai' => '0.008']);
        Storage::fake();
        Route::post('/web-loan-test', function (Request $request) { return (new WebLoanSafetyController)->proses_transaksi($request); })->middleware('web');
        Route::post('/web-cancel-test', function (Request $request) { return (new \App\Http\Controllers\TransaksiController)->proses_pembatalan($request); })->middleware('web');
        Route::post('/web-proof-test', function (Request $request) { return (new \App\Http\Controllers\TransaksiController)->upload_bukti_transaksi($request); })->middleware('web');
        $draft = ['gaji' => 10000000, 'slip' => ['bulan_ini' => 'slip_gaji/old.pdf', 'bulan_lalu' => 'two', 'dua_bulan_lalu' => 'three'],
            'dokumen' => ['kk' => 'kk', 'ktp1' => 'ktp', 'kartu_anggota' => 'card', 'id_card' => 'id', 'surat_hutang' => 'debt'],
            'consent' => ['versi' => 'old'], 'persetujuan_pinjaman' => ['versi' => 'old']];
        DB::table('transaksi')->insert(['id' => 1, 'fid_anggota' => 'A', 'fid_jenis_transaksi' => 10, 'fid_status' => 1,
            'tenor' => 12, 'nominal' => -1000000, 'draft_pengajuan' => json_encode($draft), 'keterangan' => 'old']);
        Storage::put('slip_gaji/old.pdf', 'old');
    }

    private function payload($override = [])
    {
        return array_merge(['modul' => 'pinjaman', 'action' => 'edit', 'id' => 1, 'jenis_transaksi' => 10,
            'nominal' => '1000000', 'tenor' => 12, 'gaji_pokok' => '10000000', 'keterangan' => 'new'], $override);
    }

    public function test_document_only_edit_preserves_existing_schedule_and_file_on_no_replacement()
    {
        $this->withSession(['useractive' => (object)['no_anggota' => 'A']])->post('/web-loan-test', $this->payload())->assertRedirect();
        $this->assertSame('new', DB::table('transaksi')->where('id', 1)->value('keterangan'));
        $this->assertTrue(Storage::exists('slip_gaji/old.pdf'));
    }

    public function test_replacement_removes_unreferenced_old_file_after_commit()
    {
        $this->withSession(['useractive' => (object)['no_anggota' => 'A']])->post('/web-loan-test', $this->payload([
            'attachment' => UploadedFile::fake()->create('new.pdf', 1, 'application/pdf')]))->assertRedirect();
        $this->assertFalse(Storage::exists('slip_gaji/old.pdf'));
        $draft = json_decode(DB::table('transaksi')->where('id', 1)->value('draft_pengajuan'), true);
        $this->assertTrue(Storage::exists($draft['slip']['bulan_ini']));
    }

    public function test_replacement_keeps_file_referenced_by_salary()
    {
        DB::table('gaji_pokok')->insert(['attachment' => 'slip_gaji/old.pdf']);
        $this->withSession(['useractive' => (object)['no_anggota' => 'A']])->post('/web-loan-test', $this->payload([
            'attachment' => UploadedFile::fake()->create('new.pdf', 1, 'application/pdf')]))->assertRedirect();
        $this->assertTrue(Storage::exists('slip_gaji/old.pdf'));
    }

    public function test_member_cannot_edit_another_members_loan()
    {
        $this->withSession(['useractive' => (object)['no_anggota' => 'B']])->post('/web-loan-test', $this->payload())->assertNotFound();
        $this->assertSame('old', DB::table('transaksi')->where('id', 1)->value('keterangan'));
    }

    public function test_verified_loan_cannot_be_edited()
    {
        DB::table('transaksi')->where('id', 1)->update(['fid_status' => 3]);
        $this->withSession(['useractive' => (object)['no_anggota' => 'A']])->post('/web-loan-test', $this->payload())->assertForbidden();
    }

    public function test_legacy_web_loan_without_draft_can_edit_without_uploading_documents()
    {
        DB::table('transaksi')->insert(['id' => 2, 'fid_anggota' => 'A', 'fid_jenis_transaksi' => 10,
            'fid_status' => 1, 'tenor' => 12, 'nominal' => -1000000, 'draft_pengajuan' => null,
            'keterangan' => 'lama']);
        $this->withSession(['useractive' => (object)['no_anggota' => 'A']])
            ->post('/web-loan-test', $this->payload(['id' => 2, 'keterangan' => 'baru']))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('baru', DB::table('transaksi')->where('id', 2)->value('keterangan'));
    }

    public function test_new_web_application_requires_both_consents_and_documents()
    {
        $this->withSession(['useractive' => (object)['no_anggota' => 'A']])
            ->post('/web-loan-test', $this->payload(['action' => 'add', 'id' => null]))
            ->assertSessionHasErrors('consent');

        $this->withSession(['useractive' => (object)['no_anggota' => 'A']])
            ->post('/web-loan-test', $this->payload([
                'action' => 'add', 'id' => null,
                'consent' => '1', 'consent_version' => \App\Http\Controllers\MobileController::PERSETUJUAN_VERSI,
                'loan_consent' => '1', 'loan_consent_version' => \App\Http\Controllers\MobileController::PERSETUJUAN_PINJAMAN_VERSI,
            ]))->assertSessionHasErrors('dokumen');

        $this->assertSame(1, DB::table('transaksi')->count());
    }

    public function test_member_cannot_cancel_another_members_transaction()
    {
        DB::table('transaksi')->insert(['id' => 9, 'fid_anggota' => 'A', 'fid_jenis_transaksi' => 4,
            'fid_status' => 1, 'tenor' => 0, 'nominal' => 100000]);
        $this->withSession(['useractive' => (object)['no_anggota' => 'B']])
            ->post('/web-cancel-test', ['id' => 9, 'modul' => 'simpanan'])
            ->assertRedirect();
        $this->assertSame(1, (int) DB::table('transaksi')->where('id', 9)->value('fid_status'));
    }

    public function test_member_cannot_cancel_approved_transaction()
    {
        DB::table('transaksi')->where('id', 1)->update(['fid_status' => 3]);
        $this->withSession(['useractive' => (object)['no_anggota' => 'A']])
            ->post('/web-cancel-test', ['id' => 1, 'modul' => 'pinjaman'])
            ->assertRedirect();
        $this->assertSame(3, (int) DB::table('transaksi')->where('id', 1)->value('fid_status'));
    }

    public function test_member_cannot_upload_proof_to_another_members_transaction()
    {
        $this->withSession(['useractive' => (object)['no_anggota' => 'B']])
            ->post('/web-proof-test', ['id' => 1, 'bukti_transaksi' => UploadedFile::fake()->create('proof.jpg', 1, 'image/jpeg')])
            ->assertRedirect();
        $this->assertNull(DB::table('transaksi')->where('id', 1)->value('bukti_transaksi'));
    }
}
