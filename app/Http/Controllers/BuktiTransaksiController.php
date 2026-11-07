<?php

namespace App\Http\Controllers;

use App\Support\BuktiTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BuktiTransaksiController extends Controller
{
    public function web(Request $request, $jenis, $id)
    {
        $bukti = BuktiTransaksi::load($jenis, $id);
        $user = Session::get('useractive');
        abort_if(!$bukti || !$user, 404);
        if ((int) $user->hak_akses === 2) {
            abort_unless((string) $user->no_anggota === $bukti['owner'], 404);
        } else {
            // Petugas: sama seperti halaman detail modul — butuh izin lihat + cetak pada modul
            // terkait (diambil segar, karena izin bisa dicabut). Aplikasi tidak memakai
            // is_all_user/created_by untuk membatasi akses, jadi jangan ditambahkan di sini.
            $permission = DB::table('otoritas_user')->where('fid_hak_akses', $user->hak_akses)->where('fid_modul', $bukti['module'])->first();
            abort_unless($permission && $permission->is_view === 'Y' && $permission->is_print === 'Y', 404);
        }
        if ($request->query('inline') === '1') {
            return $this->download($bukti, true);
        }
        return $this->download($bukti);
    }

    /**
     * Lampiran bukti yang DIUNGGAH anggota (foto transfer) — bukan bukti PDF yang dibuat sistem.
     * Disajikan lewat rute ber-login dari penyimpanan privat, supaya bisa dibuka dari web
     * tanpa bergantung pada folder public/storage (yang bisa tidak sinkron).
     */
    public function lampiran(Request $request, $id)
    {
        $row = DB::table('transaksi')->where('id', $id)->first();
        $user = Session::get('useractive');
        abort_unless($row && $user && !empty($row->bukti_transaksi), 404);

        if ((int) $user->hak_akses === 2) {
            abort_unless((string) $user->no_anggota === (string) $row->fid_anggota, 404);
        } else {
            $module = BuktiTransaksi::moduleTransaksi($row->fid_jenis_transaksi);
            $permission = DB::table('otoritas_user')
                ->where('fid_hak_akses', $user->hak_akses)->where('fid_modul', $module)->first();
            abort_unless($permission && $permission->is_view === 'Y', 404);
        }
        return $this->streamLampiran($row->bukti_transaksi);
    }

    /** Lampiran yang sama untuk aplikasi mobile (autentikasi token, hanya pemilik). */
    public function lampiranMobile(Request $request, $id)
    {
        $row = DB::table('transaksi')->where('id', $id)->first();
        abort_unless($row && !empty($row->bukti_transaksi), 404);
        abort_unless((string) $request->no_anggota === (string) $row->fid_anggota, 404);
        return $this->streamLampiran($row->bukti_transaksi);
    }

    /** Aliran berkas lampiran dari penyimpanan privat (tanpa membuffer ke memori). */
    private function streamLampiran($relative)
    {
        $root = realpath(storage_path('app'));
        $real = realpath(storage_path('app/' . $relative));
        abort_unless($root && $real && is_file($real) && strpos($real, $root . DIRECTORY_SEPARATOR) === 0, 404);
        $mime = mime_content_type($real) ?: 'application/octet-stream';
        return response()->stream(function () use ($real) {
            $out = fopen('php://output', 'wb');
            $in = fopen($real, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in); fclose($out);
        }, 200, $this->headers() + ['Content-Type' => $mime, 'Content-Length' => filesize($real)]);
    }

    public function mobile(Request $request, $jenis, $id)
    {
        $bukti = BuktiTransaksi::load($jenis, $id);
        abort_unless($bukti && (string) $request->no_anggota === $bukti['owner'], 404);
        return $this->download($bukti);
    }

    private function download(array $bukti, $inline = false)
    {
        return response(BuktiTransaksi::pdf($bukti), 200, $this->headers() + [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="bukti-' . $bukti['jenis'] . '-' . $bukti['id'] . '.pdf"',
        ]);
    }

    public function verify(Request $request)
    {
        $bukti = BuktiTransaksi::verify($request->query('ref'), $request->query('kode'));
        $public = $bukti ? [
            'nama' => BuktiTransaksi::maskName($bukti['nama']), 'jenis' => $bukti['judul'],
            'tanggal' => $bukti['tanggal'], 'nominal' => BuktiTransaksi::rupiah($bukti['nominal']), 'status' => $bukti['status'],
        ] : null;
        return response()->view('bukti.verifikasi', ['bukti' => $public], $public ? 200 : 404)->withHeaders($this->headers());
    }

    private function headers()
    {
        return [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            'Referrer-Policy' => 'no-referrer',
        ];
    }
}
