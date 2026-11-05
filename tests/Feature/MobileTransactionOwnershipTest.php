<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileTransactionOwnershipTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('transaksi', function (Blueprint $t) {
            $t->increments('id'); $t->string('fid_anggota'); $t->integer('fid_jenis_transaksi');
            $t->integer('fid_status'); $t->text('draft_pengajuan')->nullable();
            $t->string('bukti_transaksi')->nullable(); $t->timestamps();
        });
        Schema::create('verifikasi_transaksi', function (Blueprint $t) {
            $t->increments('id'); $t->integer('fid_transaksi'); $t->string('jenis');
            $t->string('caption'); $t->text('keterangan')->nullable();
            $t->dateTime('created_at')->nullable(); $t->string('created_by')->nullable();
        });
        Schema::create('gaji_pokok', function (Blueprint $t) {
            $t->increments('id'); $t->string('attachment')->nullable();
        });
        Storage::fake();
        Route::post('/test-mobile-upload-doc', function (Request $request) {
            return (new \App\Http\Controllers\MobileController)->upload_dokumen($request);
        });
        Route::post('/test-mobile-cancel', function (Request $request) {
            return (new \App\Http\Controllers\MobileController)->batalkan_transaksi($request);
        });
        DB::table('transaksi')->insert([
            ['id' => 1, 'fid_anggota' => 'A', 'fid_jenis_transaksi' => 10, 'fid_status' => 1, 'draft_pengajuan' => json_encode(['dokumen' => ['kk' => 'dokumen_pengajuan/old.pdf']])],
            ['id' => 2, 'fid_anggota' => 'B', 'fid_jenis_transaksi' => 10, 'fid_status' => 1, 'draft_pengajuan' => '{}'],
            ['id' => 3, 'fid_anggota' => 'A', 'fid_jenis_transaksi' => 10, 'fid_status' => 3, 'draft_pengajuan' => '{}'],
        ]);
        Storage::put('dokumen_pengajuan/old.pdf', 'old');
    }

    public function test_member_cannot_upload_to_another_members_loan()
    {
        $this->post('/test-mobile-upload-doc', [
            'id' => 1, 'no_anggota' => 'B', 'jenis_dok' => 'kk',
            'attachment' => UploadedFile::fake()->create('kk.pdf', 1, 'application/pdf'),
        ])->assertStatus(404);
        $this->assertCount(1, Storage::allFiles());
    }

    public function test_member_cannot_modify_approved_loan_documents()
    {
        $this->post('/test-mobile-upload-doc', [
            'id' => 3, 'no_anggota' => 'A', 'jenis_dok' => 'kk',
            'attachment' => UploadedFile::fake()->create('kk.pdf', 1, 'application/pdf'),
        ])->assertStatus(422);
        $this->assertCount(1, Storage::allFiles());
    }

    public function test_owner_can_replace_document_and_unreferenced_old_file_is_removed()
    {
        $this->post('/test-mobile-upload-doc', [
            'id' => 1, 'no_anggota' => 'A', 'jenis_dok' => 'kk',
            'attachment' => UploadedFile::fake()->create('kk.pdf', 1, 'application/pdf'),
        ])->assertOk();
        $draft = json_decode(DB::table('transaksi')->where('id', 1)->value('draft_pengajuan'), true);
        $this->assertTrue(Storage::exists($draft['dokumen']['kk']));
        $this->assertFalse(Storage::exists('dokumen_pengajuan/old.pdf'));
    }

    public function test_member_cannot_cancel_another_members_transaction()
    {
        $this->post('/test-mobile-cancel', ['id' => 2, 'no_anggota' => 'A'])->assertStatus(404);
        $this->assertSame(1, (int) DB::table('transaksi')->where('id', 2)->value('fid_status'));
    }

    public function test_member_cannot_cancel_processed_transaction()
    {
        $this->post('/test-mobile-cancel', ['id' => 3, 'no_anggota' => 'A'])->assertStatus(422);
        $this->assertSame(3, (int) DB::table('transaksi')->where('id', 3)->value('fid_status'));
    }

    public function test_owner_can_cancel_pending_transaction()
    {
        $this->post('/test-mobile-cancel', ['id' => 1, 'no_anggota' => 'A'])->assertOk();
        $this->assertSame(5, (int) DB::table('transaksi')->where('id', 1)->value('fid_status'));
    }
}
