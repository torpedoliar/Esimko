<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\RilisApk;

class RilisApkController extends Controller
{
    // Batas ukuran APK disimpan di sini (KB) supaya sinkron dgn maxAllowedContentLength
    // web.config (60 MB) dan kebutuhan riil APK (~50 MB).
    const MAKS_KB = 60000;

    public function index()
    {
        $data['rilis'] = RilisApk::orderByDesc('version_code')->get();
        $data['terbaru'] = RilisApk::terbaru();
        $app = 'master';
        $page = 'Pengaturan';
        $subpage = 'Rilis Aplikasi';
        return view('pengaturan.rilis_apk.index', $data, compact('app', 'page', 'subpage'));
    }

    public function proses(Request $request)
    {
        $request->validate([
            'versi' => 'required|regex:/^\d+\.\d+\.\d+$/',
            'version_code' => 'required|integer|min:1',
            'changelog' => 'nullable|string|max:2000',
        ], [
            'versi.regex' => 'Format versi harus x.y.z, contoh 1.0.3',
            'version_code.min' => 'Version code minimal 1 dan harus naik tiap rilis',
        ]);

        $file = $request->file('apk');
        if (empty($file) || !$file->isValid()) {
            return redirect()->back()->with('message', 'File APK wajib dipilih')->with('message_type', 'danger');
        }
        // Browser sering mengirim apk sebagai application/octet-stream — validasi ekstensi.
        if (strtolower($file->getClientOriginalExtension()) !== 'apk') {
            return redirect()->back()->with('message', 'File harus berupa .apk')->with('message_type', 'danger');
        }
        if ($file->getSize() > self::MAKS_KB * 1024) {
            return redirect()->back()->with('message', 'Ukuran APK melebihi ' . (self::MAKS_KB / 1000) . ' MB')->with('message_type', 'danger');
        }
        if (RilisApk::where('version_code', $request->version_code)->exists()) {
            return redirect()->back()->with('message', 'Version code ' . $request->version_code . ' sudah pernah dipakai — naikkan angkanya')->with('message_type', 'danger');
        }
        $terbaru = RilisApk::terbaru();
        if ($terbaru && $request->version_code <= $terbaru->version_code) {
            return redirect()->back()->with('message', 'Version code harus lebih besar dari rilis terbaru (' . $terbaru->version_code . ')')->with('message_type', 'danger');
        }

        $nama = 'esimko-mobile-v' . $request->versi . '.apk';
        $path = storage_path('app/apk/' . $nama);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }
        // Rilis dgn nama sama (jarang, versi beda code) tidak menimpa diam-diam.
        if (file_exists($path)) {
            unlink($path);
        }
        $file->move(dirname($path), $nama);

        $rilis = new RilisApk;
        $rilis->versi = $request->versi;
        $rilis->version_code = $request->version_code;
        $rilis->changelog = $request->changelog;
        $rilis->nama_file = $nama;
        $rilis->ukuran = filesize($path);
        $rilis->sha256 = hash_file('sha256', $path);
        $rilis->dibuat_oleh = Session::get('useractive')->no_anggota;
        $rilis->save();

        // "Wajib update" = naikkan min_mobile_version ke versi ini (force update di app).
        $this->tulis_version_json($rilis, !empty($request->wajib_update));

        return redirect('pengaturan/rilis_apk')
            ->with('message', 'Rilis v' . $request->versi . ' tersimpan dan menjadi versi terbaru')
            ->with('message_type', 'success');
    }

    public function hapus(Request $request)
    {
        $rilis = RilisApk::find($request->id);
        if (empty($rilis)) {
            return redirect('pengaturan/rilis_apk')->with('message', 'Rilis tidak ditemukan')->with('message_type', 'danger');
        }
        // File hanya dihapus bila tak dipakai rilis lain (nama file unik per versi — praktis selalu terhapus).
        $dipakaiLain = RilisApk::where('nama_file', $rilis->nama_file)->where('id', '!=', $rilis->id)->exists();
        if (!$dipakaiLain) {
            $path = storage_path('app/apk/' . $rilis->nama_file);
            if (file_exists($path)) {
                unlink($path);
            }
        }
        $rilis->delete();
        $this->tulis_version_json(RilisApk::terbaru(), false);
        return redirect('pengaturan/rilis_apk')
            ->with('message', 'Rilis v' . $rilis->versi . ' dihapus')
            ->with('message_type', 'success');
    }

    // Sinkronkan version.json dari rilis terbaru. Field web (version, changelog, dll)
    // dipertahankan — hanya bagian mobile yang ditulis ulang.
    public function tulis_version_json($rilis, $wajibUpdate)
    {
        $file = base_path('version.json');
        $json = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        if (!is_array($json)) {
            $json = [];
        }
        if (!empty($rilis)) {
            $json['mobile_version'] = $rilis->versi;
            $json['mobile_version_code'] = $rilis->version_code;
            $json['mobile_release_date'] = substr($rilis->created_at, 0, 10);
            $json['apk_url'] = url('apk/unduh');
            $json['apk_ukuran'] = (int) $rilis->ukuran;
            $json['apk_sha256'] = $rilis->sha256;
            if ($wajibUpdate) {
                $json['min_mobile_version'] = $rilis->versi;
            }
        }
        file_put_contents($file, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    // Halaman publik unduh aplikasi (tanpa login) — sumber: rilis terbaru.
    public function publik()
    {
        $data['rilis'] = RilisApk::terbaru();
        return view('apk', $data);
    }

    // Unduh APK terbaru (atau ?id= untuk versi tertentu). BinaryFileResponse =
    // streamed oleh Symfony, aman utk 50 MB. Counter untuk statistik admin.
    public function unduh(Request $request)
    {
        $rilis = !empty($request->id) ? RilisApk::find($request->id) : RilisApk::terbaru();
        if (empty($rilis)) {
            abort(404);
        }
        $path = storage_path('app/apk/' . $rilis->nama_file);
        if (!file_exists($path)) {
            abort(404);
        }
        $rilis->increment('unduhan');
        return response()->download($path, $rilis->nama_file, [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
