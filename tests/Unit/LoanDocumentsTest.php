<?php

namespace Tests\Unit;

use App\Http\Controllers\MobileController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class LoanDocumentsTest extends TestCase
{
    private function loan($type, array $docs, $slip = null)
    {
        return (object) [
            'fid_jenis_transaksi' => $type,
            'draft_pengajuan' => json_encode([
                'dokumen' => $docs,
                'slip' => $slip ?: ['bulan_ini' => 'a', 'bulan_lalu' => 'b', 'dua_bulan_lalu' => 'c'],
                'consent' => ['versi' => 'v1'],
                'persetujuan_pinjaman' => ['versi' => 'v1'],
            ]),
        ];
    }

    public function test_new_documents_are_separate_and_required_by_type()
    {
        $controller = new MobileController;
        $base = ['kk' => 'a', 'ktp1' => 'b', 'ktp2' => 'c', 'kartu_anggota' => 'd', 'id_card' => 'e'];
        foreach ([9, 10, 11] as $type) {
            $this->assertStringContainsString('Surat Hutang', $controller->cek_dokumen_pengajuan($this->loan($type, $base), 10000001));
            $docs = $base + ['surat_hutang' => 'f'];
            if ($type === 9) {
                $this->assertStringContainsString('KTP Penjamin', $controller->cek_dokumen_pengajuan($this->loan($type, $docs), 10000001));
                $docs['ktp_penjamin'] = 'g';
            }
            $this->assertTrue($controller->cek_dokumen_pengajuan($this->loan($type, $docs), 10000001));
        }
        $withoutSpouse = $base + ['surat_hutang' => 'f', 'ktp_penjamin' => 'g'];
        unset($withoutSpouse['ktp2']);
        $this->assertStringContainsString('KTP Suami/Istri', $controller->cek_dokumen_pengajuan($this->loan(9, $withoutSpouse), 10000001));
        $this->assertTrue($controller->cek_dokumen_pengajuan($this->loan(9, $withoutSpouse), 10000000));
    }

    public function test_legacy_requirements_and_slip_are_preserved()
    {
        $controller = new MobileController;
        $base = ['kk' => 'a', 'ktp1' => 'b', 'kartu_anggota' => 'd', 'id_card' => 'e'];
        $this->assertTrue($controller->cek_dokumen_pengajuan($this->loan(10, $base), 1, false));
        $this->assertStringContainsString('Surat Hutang', $controller->cek_dokumen_pengajuan($this->loan(9, $base), 1, false));
        // Pengajuan lama hanya punya satu slot slip; tidak dipaksa melengkapi 2 bulan lain.
        $this->assertTrue($controller->cek_dokumen_pengajuan($this->loan(10, $base, 'legacy-slip'), 1, false));
    }

    public function test_guarantor_upload_is_validated_like_other_documents()
    {
        $controller = new MobileController;
        $method = new \ReflectionMethod($controller, 'validasi_files_pengajuan');
        $method->setAccessible(true);
        $path = tempnam(sys_get_temp_dir(), 'loan');
        file_put_contents($path, 'not an image or pdf');
        try {
            $file = new UploadedFile($path, 'ktp.txt', 'text/plain', null, true);
            $request = new Request([], [], [], [], ['dokumen_ktp_penjamin' => $file]);
            $this->assertStringContainsString('KTP Penjamin', $method->invoke($controller, $request));
        } finally {
            @unlink($path);
        }
    }
}
