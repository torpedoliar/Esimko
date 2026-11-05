<?php

namespace App\Support;

use App\Http\Controllers\MobileController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/** Web adapter for the existing mobile loan draft format. */
class WebLoanDocuments
{
    public static function parts()
    {
        return [
            'attachment' => ['slip', 'bulan_ini'],
            'slip_bulan_lalu' => ['slip', 'bulan_lalu'],
            'slip_dua_bulan_lalu' => ['slip', 'dua_bulan_lalu'],
            'dokumen_kk' => ['dokumen', 'kk'], 'dokumen_ktp1' => ['dokumen', 'ktp1'],
            'dokumen_ktp2' => ['dokumen', 'ktp2'], 'dokumen_ktp_penjamin' => ['dokumen', 'ktp_penjamin'],
            'dokumen_kartu_anggota' => ['dokumen', 'kartu_anggota'], 'dokumen_id_card' => ['dokumen', 'id_card'],
            'dokumen_surat_hutang' => ['dokumen', 'surat_hutang'],
            'bukti_persetujuan' => ['dokumen', 'bukti_persetujuan'],
        ];
    }

    public static function prepare($request, $existing, $type, $nominal, $newRules, $admin, $actor, $skipCompleteness = false)
    {
        $draft = $existing ? json_decode($existing->draft_pengajuan, true) : [];
        $draft = is_array($draft) ? $draft : [];
        if (!empty($draft['slip']) && !is_array($draft['slip'])) $draft['slip'] = ['bulan_ini' => $draft['slip']];
        $draft['gaji'] = (float) str_replace('.', '', $request->input('gaji_pokok', '0'));
        foreach (self::parts() as $part => $target) {
            $file = $request->file($part);
            if (!$admin && $part === 'bukti_persetujuan' && $file) {
                throw ValidationException::withMessages([$part => 'Bukti persetujuan hanya dapat diunggah petugas.']);
            }
            if (!$file) continue;
            if (!$file->isValid() || !in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'application/pdf'], true) || $file->getSize() > 5120 * 1024) {
                throw ValidationException::withMessages([$part => 'File harus JPG, PNG, atau PDF dan maksimal 5 MB.']);
            }
            $draft[$target[0]][$target[1]] = '__pending_upload__';
        }
        if ($admin) {
            $hasMemberConsent = !empty($draft['consent']['versi']) && !empty($draft['persetujuan_pinjaman']['versi']);
            if ($newRules && !$request->hasFile('bukti_persetujuan')) {
                throw ValidationException::withMessages(['bukti_persetujuan' => 'Perubahan pengajuan memerlukan bukti persetujuan anggota yang ditandatangani untuk pengajuan ini.']);
            }
            if ($request->hasFile('bukti_persetujuan')) {
                if ($newRules) {
                    // Earlier electronic consent describes an earlier application, not
                    // the newly revised terms. Preserve it as history, not current consent.
                    if (!empty($draft['consent']) || !empty($draft['persetujuan_pinjaman'])) {
                        $draft['riwayat_persetujuan'][] = ['consent' => $draft['consent'] ?? null, 'persetujuan_pinjaman' => $draft['persetujuan_pinjaman'] ?? null];
                    }
                    unset($draft['consent'], $draft['persetujuan_pinjaman']);
                }
                $draft['bukti_persetujuan'] = ['petugas' => $actor, 'waktu' => date('Y-m-d H:i:s')];
            }
            $hasSignedEvidence = !empty($draft['dokumen']['bukti_persetujuan']) && !empty($draft['bukti_persetujuan']['petugas']);
            if (!$skipCompleteness && !$hasSignedEvidence && !$hasMemberConsent) {
                throw ValidationException::withMessages(['bukti_persetujuan' => 'Bukti persetujuan anggota yang ditandatangani wajib diunggah petugas.']);
            }
            // Placeholders are validation-only, never persisted as member checkboxes.
            $check = $draft;
            if ($hasSignedEvidence) {
                $check['consent'] = ['bukti' => true];
                $check['persetujuan_pinjaman'] = ['bukti' => true];
            }
        } else {
            if ($newRules && !empty($draft['dokumen']['bukti_persetujuan'])) {
                unset($draft['dokumen']['bukti_persetujuan'], $draft['bukti_persetujuan']);
            }
            if ($request->input('consent') === '1' && $request->input('consent_version') === MobileController::PERSETUJUAN_VERSI) {
                $draft['consent'] = ['versi' => MobileController::PERSETUJUAN_VERSI, 'waktu' => date('Y-m-d H:i:s'), 'kanal' => 'web'];
            }
            if ($request->input('loan_consent') === '1' && $request->input('loan_consent_version') === MobileController::PERSETUJUAN_PINJAMAN_VERSI) {
                $draft['persetujuan_pinjaman'] = ['versi' => MobileController::PERSETUJUAN_PINJAMAN_VERSI, 'waktu' => date('Y-m-d H:i:s'), 'kanal' => 'web'];
            }
            if (!$skipCompleteness && $newRules && ($request->input('consent') !== '1' || $request->input('consent_version') !== MobileController::PERSETUJUAN_VERSI || $request->input('loan_consent') !== '1' || $request->input('loan_consent_version') !== MobileController::PERSETUJUAN_PINJAMAN_VERSI)) {
                throw ValidationException::withMessages(['consent' => 'Kedua persetujuan terbaru wajib dicentang.']);
            }
            $check = $draft;
            // A signed paper uploaded by a staff member is separate evidence.
            if (!empty($draft['dokumen']['bukti_persetujuan']) && !empty($draft['bukti_persetujuan']['petugas'])) {
                $check['consent'] = ['bukti' => true]; $check['persetujuan_pinjaman'] = ['bukti' => true];
            }
        }
        if ($skipCompleteness) return $draft;
        $candidate = (object) ['fid_jenis_transaksi' => $type, 'draft_pengajuan' => json_encode($check)];
        $error = (new MobileController)->cek_dokumen_pengajuan($candidate, $nominal, $newRules);
        if ($error !== true) throw ValidationException::withMessages(['dokumen' => $error]);
        return $draft;
    }

    public static function store($request, &$draft, &$newPaths)
    {
        foreach (self::parts() as $part => $target) {
            if (!$request->hasFile($part)) continue;
            $path = $request->file($part)->store($target[0] === 'slip' ? 'slip_gaji' : 'dokumen_pengajuan');
            if (!$path) throw new \RuntimeException('Gagal menyimpan dokumen.');
            $newPaths[] = $path;
            $draft[$target[0]][$target[1]] = $path;
        }
    }

    public static function cleanupReplaced($oldDraft, $draft)
    {
        foreach (self::replacedPaths($oldDraft, $draft) as $path) {
            try { self::deleteIfUnreferenced($path); }
            catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Pembersihan dokumen pengajuan tertunda', ['error' => $e->getMessage()]); }
        }
    }

    public static function deleteIfUnreferenced($path)
    {
        if (!is_string($path) || (!\Illuminate\Support\Str::startsWith($path, ['slip_gaji/', 'dokumen_pengajuan/'])) || strpos($path, '..') !== false) return;
        // Conservative substring scan also covers legacy string slips and JSON escaping.
        $filename = basename($path);
        if ($filename === '' || $filename === '.' || $filename === '..') return;
        if (\Illuminate\Support\Facades\DB::table('gaji_pokok')->where('attachment', $path)->exists() ||
            \Illuminate\Support\Facades\DB::table('transaksi')->where('draft_pengajuan', 'like', '%'.$filename.'%')->exists()) return;
        Storage::delete($path);
    }

    public static function replacedPaths($oldDraft, $newDraft)
    {
        $old = is_string($oldDraft) ? json_decode($oldDraft, true) : $oldDraft;
        if (!is_array($old)) return [];
        if (!empty($old['slip']) && !is_array($old['slip'])) $old['slip'] = ['bulan_ini' => $old['slip']];
        $paths = [];
        foreach (self::parts() as $target) {
            $path = $old[$target[0]][$target[1]] ?? null;
            if ($path && $path !== ($newDraft[$target[0]][$target[1]] ?? null)) $paths[] = $path;
        }
        return array_unique($paths);
    }

    public static function rollbackFiles($paths)
    {
        foreach ($paths as $path) Storage::delete($path);
    }
}
