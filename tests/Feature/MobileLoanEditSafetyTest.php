<?php

namespace Tests\Feature;

use App\Http\Controllers\MobileController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileLoanEditTestController extends MobileController
{
    public function validasi_transaksi($request, $jenis) { return 'success'; }
    public function proses_angsuran($id, $request)
    {
        DB::table('angsuran')->where('fid_transaksi', $id)->delete();
        if ($request->input('fail_schedule')) throw new \RuntimeException('schedule failure');
        DB::table('angsuran')->insert(['fid_transaksi' => $id, 'marker' => 'new']);
    }
}

class MobileLoanEditSafetyTest extends TestCase
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
            $t->dateTime('ditolak_at')->nullable();
        });
        Schema::create('angsuran', function (Blueprint $t) {
            $t->increments('id'); $t->integer('fid_transaksi'); $t->string('marker');
        });
        Schema::create('gaji_pokok', function (Blueprint $t) {
            $t->increments('id'); $t->string('attachment')->nullable();
        });
        Storage::fake();
        Route::post('/test-mobile-loan-edit', function (Request $request) {
            return (new MobileLoanEditTestController)->proses_transaksi($request, 'pinjaman');
        });
        $draft = ['gaji' => 20000000, 'slip' => ['bulan_ini' => 'slip_gaji/old.pdf', 'bulan_lalu' => 'old2', 'dua_bulan_lalu' => 'old3'],
            'dokumen' => ['kk' => 'kk', 'ktp1' => 'ktp', 'kartu_anggota' => 'ka', 'id_card' => 'id'],
            'consent' => ['versi' => 'old'], 'persetujuan_pinjaman' => ['versi' => 'old'], 'other' => 'preserved'];
        DB::table('transaksi')->insert(['id' => 1, 'fid_anggota' => 'A', 'fid_jenis_transaksi' => 10,
            'fid_status' => 1, 'tenor' => 18, 'nominal' => -1000000, 'draft_pengajuan' => json_encode($draft), 'keterangan' => 'original']);
        DB::table('angsuran')->insert(['fid_transaksi' => 1, 'marker' => 'old']);
        Storage::put('slip_gaji/old.pdf', 'original file');
    }

    private function payload(array $override = [])
    {
        return array_merge(['action' => 'edit', 'id' => 1, 'no_anggota' => 'A', 'jenis_pinjaman' => 10,
            'tenor' => 18, 'nominal' => '1000000', 'gaji_pokok' => '20000000', 'keterangan' => 'new',
            'persetujuan_versi' => MobileController::PERSETUJUAN_VERSI,
            'persetujuan_pinjaman_versi' => MobileController::PERSETUJUAN_PINJAMAN_VERSI], $override);
    }

    public function test_type_change_missing_documents_does_not_save_or_store_uploads()
    {
        $before = DB::table('transaksi')->where('id', 1)->first();
        $response = $this->post('/test-mobile-loan-edit', $this->payload(['jenis_pinjaman' => 9, 'tenor' => 12,
            'attachment' => UploadedFile::fake()->create('new.pdf', 1, 'application/pdf')]));
        $response->assertStatus(422)->assertSee('KTP Penjamin')->assertSee('Surat Hutang');
        $this->assertEquals($before, DB::table('transaksi')->where('id', 1)->first());
        $this->assertSame('old', DB::table('angsuran')->value('marker'));
        $this->assertSame(['slip_gaji/old.pdf'], Storage::allFiles());
        $this->assertSame('original file', Storage::get('slip_gaji/old.pdf'));
    }

    public function test_change_to_goods_requires_debt_letter()
    {
        $before = DB::table('transaksi')->where('id', 1)->first();
        $this->post('/test-mobile-loan-edit', $this->payload(['jenis_pinjaman' => 11, 'tenor' => 12]))
            ->assertStatus(422)->assertSee('Surat Hutang');
        $this->assertEquals($before, DB::table('transaksi')->where('id', 1)->first());
        $this->assertSame('old', DB::table('angsuran')->value('marker'));
        $this->assertSame(['slip_gaji/old.pdf'], Storage::allFiles());
    }

    public function test_nominal_change_requires_new_documents_without_changing_data_or_files()
    {
        $before = DB::table('transaksi')->where('id', 1)->first();
        $this->post('/test-mobile-loan-edit', $this->payload(['nominal' => '1.500.000',
            'attachment' => UploadedFile::fake()->create('new.pdf', 1, 'application/pdf')]))
            ->assertStatus(422)->assertSee('Surat Hutang');
        $this->assertEquals($before, DB::table('transaksi')->where('id', 1)->first());
        $this->assertSame('old', DB::table('angsuran')->value('marker'));
        $this->assertSame(['slip_gaji/old.pdf'], Storage::allFiles());
        $this->assertSame('original file', Storage::get('slip_gaji/old.pdf'));
    }

    public function test_nominal_change_with_required_documents_rebuilds_schedule()
    {
        $this->post('/test-mobile-loan-edit', $this->payload(['nominal' => '1.500.000',
            'dokumen_surat_hutang' => UploadedFile::fake()->create('debt.pdf', 1, 'application/pdf')]))->assertOk();
        $loan = DB::table('transaksi')->where('id', 1)->first();
        $this->assertEquals(-1500000, $loan->nominal);
        $this->assertSame('new', DB::table('angsuran')->value('marker'));
        $this->assertTrue(Storage::exists(json_decode($loan->draft_pengajuan, true)['dokumen']['surat_hutang']));
    }

    public function test_same_formatted_nominal_preserves_legacy_exception()
    {
        $this->post('/test-mobile-loan-edit', $this->payload(['nominal' => '1.000.000']))->assertOk();
        $this->assertSame('old', DB::table('angsuran')->value('marker'));
    }

    public function test_invalid_file_does_not_save_or_change_files()
    {
        $before = DB::table('transaksi')->where('id', 1)->first();
        $response = $this->post('/test-mobile-loan-edit', $this->payload([
            'dokumen_ktp_penjamin' => UploadedFile::fake()->createWithContent('bad.txt', 'not an image')]));
        $response->assertStatus(422)->assertSee('KTP Penjamin');
        $this->assertEquals($before, DB::table('transaksi')->where('id', 1)->first());
        $this->assertSame('old', DB::table('angsuran')->value('marker'));
        $this->assertSame(['slip_gaji/old.pdf'], Storage::allFiles());
    }

    public function test_failed_schedule_rolls_back_record_schedule_and_new_files()
    {
        $before = DB::table('transaksi')->where('id', 1)->first();
        $response = $this->post('/test-mobile-loan-edit', $this->payload(['tenor' => 15, 'fail_schedule' => 1,
            'attachment' => UploadedFile::fake()->create('new.pdf', 1, 'application/pdf')]));
        $response->assertStatus(500);
        $this->assertEquals($before, DB::table('transaksi')->where('id', 1)->first());
        $this->assertSame('old', DB::table('angsuran')->value('marker'));
        $this->assertSame(['slip_gaji/old.pdf'], Storage::allFiles());
        $this->assertSame('original file', Storage::get('slip_gaji/old.pdf'));
    }

    public function test_changed_type_with_required_documents_rebuilds_schedule()
    {
        $response = $this->post('/test-mobile-loan-edit', $this->payload(['jenis_pinjaman' => 9, 'tenor' => 12,
            'dokumen_ktp_penjamin' => UploadedFile::fake()->create('guarantor.pdf', 1, 'application/pdf'),
            'dokumen_surat_hutang' => UploadedFile::fake()->create('debt.pdf', 1, 'application/pdf')]));
        $response->assertOk();
        $loan = DB::table('transaksi')->where('id', 1)->first();
        $this->assertEquals(9, $loan->fid_jenis_transaksi);
        $this->assertSame('new', DB::table('angsuran')->value('marker'));
        $draft = json_decode($loan->draft_pengajuan, true);
        $this->assertTrue(Storage::exists($draft['dokumen']['ktp_penjamin']));
        $this->assertTrue(Storage::exists($draft['dokumen']['surat_hutang']));
        $this->assertTrue(Storage::exists('slip_gaji/old.pdf'));
    }

    public function test_legacy_unchanged_edit_preserves_schedule_and_old_documents()
    {
        $response = $this->post('/test-mobile-loan-edit', $this->payload());
        $response->assertOk();
        $loan = DB::table('transaksi')->where('id', 1)->first();
        $this->assertEquals(18, $loan->tenor);
        $this->assertSame('old', DB::table('angsuran')->value('marker'));
        $this->assertSame('preserved', json_decode($loan->draft_pengajuan, true)['other']);
        $this->assertSame('original file', Storage::get('slip_gaji/old.pdf'));
    }

    public function test_new_rules_require_current_consent_versions()
    {
        $before = DB::table('transaksi')->where('id', 1)->first();
        $this->post('/test-mobile-loan-edit', $this->payload([
            'jenis_pinjaman' => 9, 'tenor' => 12,
            'persetujuan_versi' => 'v1-lama',
            'persetujuan_pinjaman_versi' => 'v1-lama',
        ]))->assertStatus(422)->assertSee('versi terbaru');
        $this->assertEquals($before, DB::table('transaksi')->where('id', 1)->first());
        $this->assertSame('old', DB::table('angsuran')->value('marker'));
    }

    public function test_replaced_slip_is_cleaned_up_after_successful_edit()
    {
        $this->post('/test-mobile-loan-edit', $this->payload([
            'attachment' => UploadedFile::fake()->create('new.pdf', 1, 'application/pdf'),
        ]))->assertOk();
        $this->assertFalse(Storage::exists('slip_gaji/old.pdf'));
        $draft = json_decode(DB::table('transaksi')->where('id', 1)->value('draft_pengajuan'), true);
        $this->assertTrue(Storage::exists($draft['slip']['bulan_ini']));
    }

    public function test_legacy_single_slip_application_can_still_be_edited()
    {
        $legacy = ['gaji' => 20000000, 'slip' => 'slip_gaji/old.pdf',
            'dokumen' => ['kk' => 'kk', 'ktp1' => 'ktp', 'kartu_anggota' => 'ka', 'id_card' => 'id'],
            'consent' => ['versi' => 'lama'], 'persetujuan_pinjaman' => ['versi' => 'lama']];
        DB::table('transaksi')->insert(['id' => 2, 'fid_anggota' => 'A', 'fid_jenis_transaksi' => 10,
            'fid_status' => 1, 'tenor' => 18, 'nominal' => -1000000,
            'draft_pengajuan' => json_encode($legacy), 'keterangan' => 'lama']);
        $this->post('/test-mobile-loan-edit', $this->payload(['id' => 2, 'keterangan' => 'baru']))
            ->assertOk();
        $this->assertSame('baru', DB::table('transaksi')->where('id', 2)->value('keterangan'));
        $this->assertSame('old', DB::table('angsuran')->where('fid_transaksi', 1)->value('marker'));
    }
}
