<?php

namespace Tests\Unit;

use App\Http\Controllers\MobileController;
use App\Support\WebLoanDocuments;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WebLoanDocumentsTest extends TestCase
{
    private function existing($extra = [])
    {
        return (object) ['draft_pengajuan' => json_encode(array_merge([
            'slip' => ['bulan_ini' => 'one', 'bulan_lalu' => 'two', 'dua_bulan_lalu' => 'three'],
            'dokumen' => ['kk' => 'kk', 'ktp1' => 'ktp', 'kartu_anggota' => 'card', 'id_card' => 'id', 'surat_hutang' => 'debt'],
        ], $extra))];
    }

    public function test_admin_evidence_records_uploader_without_fabricating_member_consent()
    {
        $request = Request::create('/', 'POST', ['gaji_pokok' => '1000000'], [], [
            'bukti_persetujuan' => UploadedFile::fake()->create('signed.pdf', 1, 'application/pdf'),
        ]);
        $draft = WebLoanDocuments::prepare($request, $this->existing(), 10, 1000000, true, true, 'STAFF');
        $this->assertSame('STAFF', $draft['bukti_persetujuan']['petugas']);
        $this->assertArrayNotHasKey('consent', $draft);
        $this->assertArrayNotHasKey('persetujuan_pinjaman', $draft);
    }

    public function test_admin_cannot_submit_without_signed_evidence()
    {
        $this->expectException(ValidationException::class);
        WebLoanDocuments::prepare(Request::create('/', 'POST'), $this->existing(), 10, 1000000, true, true, 'STAFF');
    }

    public function test_member_must_confirm_both_current_versions_for_new_rules()
    {
        $this->expectException(ValidationException::class);
        WebLoanDocuments::prepare(Request::create('/', 'POST', ['consent' => '1', 'consent_version' => 'old']), $this->existing(), 10, 1000000, true, false, 'A');
    }

    public function test_member_acceptance_is_versioned_and_timestamped()
    {
        $request = Request::create('/', 'POST', ['consent' => '1', 'consent_version' => MobileController::PERSETUJUAN_VERSI,
            'loan_consent' => '1', 'loan_consent_version' => MobileController::PERSETUJUAN_PINJAMAN_VERSI]);
        $draft = WebLoanDocuments::prepare($request, $this->existing(), 10, 1000000, true, false, 'A');
        $this->assertSame('web', $draft['consent']['kanal']);
        $this->assertSame(MobileController::PERSETUJUAN_PINJAMAN_VERSI, $draft['persetujuan_pinjaman']['versi']);
    }

    public function test_invalid_file_is_rejected()
    {
        $request = Request::create('/', 'POST', [], [], ['attachment' => UploadedFile::fake()->create('bad.txt', 1, 'text/plain')]);
        $this->expectException(ValidationException::class);
        WebLoanDocuments::prepare($request, $this->existing(), 10, 1000000, true, true, 'STAFF');
    }
}
