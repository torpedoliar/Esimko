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
            // Read fresh permission values: cached permissions may have been revoked.
            $permission = DB::table('otoritas_user')->where('fid_hak_akses', $user->hak_akses)->where('fid_modul', $bukti['module'])->first();
            abort_unless($permission && $permission->is_view === 'Y' && $permission->is_print === 'Y', 404);
            abort_unless($permission->is_all_user === 'Y' || (string) $user->no_anggota === $bukti['created_by'], 404);
        }
        return $this->download($bukti);
    }

    public function mobile(Request $request, $jenis, $id)
    {
        $bukti = BuktiTransaksi::load($jenis, $id);
        abort_unless($bukti && (string) $request->no_anggota === $bukti['owner'], 404);
        return $this->download($bukti);
    }

    private function download(array $bukti)
    {
        return response(BuktiTransaksi::pdf($bukti), 200, $this->headers() + [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="bukti-' . $bukti['jenis'] . '-' . $bukti['id'] . '.pdf"',
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
