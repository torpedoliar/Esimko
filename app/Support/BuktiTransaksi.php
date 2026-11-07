<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class BuktiTransaksi
{
    public static function eligible($jenis, $row)
    {
        if (!$row) return false;
        $r = (object) $row;
        $status = (int) ($r->fid_status ?? 0);
        if ($jenis === 'angsuran') return $status === 6;
        if ($jenis === 'penjualan') {
            return ($r->jenis_belanja ?? '') === 'toko'
                ? in_array($status, [2, 4], true)
                : in_array($r->jenis_belanja ?? '', ['konsinyasi', 'online'], true) && in_array($status, [4, 6], true);
        }
        if ($jenis !== 'transaksi') return false;
        $type = (int) ($r->fid_jenis_transaksi ?? 0);
        if (in_array($type, [9, 10, 11, 15], true)) return in_array($status, [3, 4, 6], true);
        return in_array($type, [1, 2, 3, 4, 6, 7, 8, 12, 13, 14], true) && $status === 4;
    }

    public static function load($jenis, $id)
    {
        if (!in_array($jenis, ['transaksi', 'angsuran', 'penjualan'], true) || !ctype_digit((string) $id) || (int) $id < 1) return null;
        $row = DB::table($jenis)->where('id', $id)->first();
        if (!static::eligible($jenis, $row)) return null;
        $loan = null;
        if ($jenis === 'angsuran') {
            $loan = DB::table('transaksi')->where('id', $row->fid_transaksi)->first();
            if (!$loan || !in_array((int) $loan->fid_status, [3, 4, 6], true)) return null;
        }
        $owner = $loan ? $loan->fid_anggota : ($row->fid_anggota ?? null);
        if (!$owner) return null;
        $member = DB::table('anggota')->where('no_anggota', $owner)->first();
        if (!$member) return null;
        $amount = $jenis === 'angsuran'
            ? (float) $row->angsuran_pokok + (float) $row->angsuran_bunga
            : (float) ($jenis === 'penjualan' ? $row->total_pembayaran : $row->nominal);
        $type = (int) ($row->fid_jenis_transaksi ?? 0);
        $title = 'Bukti Transaksi';
        $label = '';
        $status = 'Selesai';
        $module = 34;
        if ($jenis === 'transaksi') {
            $transactionType = DB::table('jenis_transaksi')->where('id', $type)->first();
            $label = $transactionType->jenis_transaksi ?? 'Transaksi';
            if (in_array($type, [9, 10, 11, 15], true)) {
                $module = 15;
                $title = (int) $row->fid_status === 3 ? 'Bukti Persetujuan Pinjaman' : ((int) $row->fid_status === 6 ? 'Bukti Status Pelunasan Pinjaman' : 'Bukti Penyelesaian Pinjaman');
                $status = (int) $row->fid_status === 3 ? 'Disetujui' : ((int) $row->fid_status === 6 ? 'Pinjaman Lunas' : 'Selesai');
            } elseif (in_array($type, [6, 7, 8], true)) {
                $module = [6 => 31, 7 => 32, 8 => 33][$type];
            } elseif (in_array($type, [12, 13, 14], true)) {
                $module = 17;
                $title = 'Bukti Pembayaran Angsuran';
            }
        } elseif ($jenis === 'angsuran') {
            $module = 17;
            $title = 'Bukti Pembayaran Angsuran';
            $label = 'Angsuran ke-' . $row->angsuran_ke;
            $status = 'Dibayar';
        } else {
            $module = ['toko' => 26, 'konsinyasi' => 28, 'online' => 29][$row->jenis_belanja];
            $title = 'Bukti Transaksi Belanja';
            $label = 'Belanja ' . ucfirst($row->jenis_belanja);
            $status = (($row->jenis_belanja === 'toko' && (int) $row->fid_status === 4) || (int) $row->fid_status === 6) ? 'Kredit Lunas' : 'Selesai';
        }
        $items = $jenis === 'penjualan'
            ? DB::table('item_penjualan')
                ->leftJoin('produk', 'produk.id', '=', 'item_penjualan.fid_produk')
                ->leftJoin('satuan_barang', 'satuan_barang.id', '=', 'produk.fid_satuan')
                ->where('item_penjualan.fid_penjualan', $id)
                ->orderBy('item_penjualan.id')
                ->get([
                    'item_penjualan.fid_produk', 'item_penjualan.nama_barang', 'item_penjualan.jumlah', 'item_penjualan.total',
                    'produk.nama_produk', 'produk.kode as kode_produk',
                    DB::raw('satuan_barang.satuan as satuan_ref'),
                ])->map(function ($item) {
                    $nama = trim((string) ($item->nama_produk ?? '')) !== ''
                        ? $item->nama_produk
                        : (trim((string) ($item->nama_barang ?? '')) !== '' ? $item->nama_barang : 'Barang #' . ($item->fid_produk ?? ''));
                    return [
                        'nama' => (string) $nama,
                        'satuan' => (string) ($item->satuan_ref ?? ''),
                        'jumlah' => rtrim(rtrim(number_format((float) $item->jumlah, 4, '.', ''), '0'), '.'),
                        'total' => number_format((float) $item->total, 2, '.', ''),
                    ];
                })->all()
            : [];
        $methodId = $jenis === 'penjualan' ? ($row->fid_metode_pembayaran ?? null) : (($loan ?: $row)->fid_metode_transaksi ?? null);
        $method = $methodId ? DB::table('rekening_pembayaran')->where('id', $methodId)->value('keterangan') : null;
        $auditKind = $jenis === 'penjualan' ? 'penjualan' : 'transaksi';
        $auditId = $loan ? $loan->id : $row->id;
        $audit = DB::table('verifikasi_transaksi')->where('jenis', $auditKind)->where('fid_transaksi', $auditId)->orderByDesc('id')->first();
        $processBy = (string) (($loan ?: $row)->created_by ?? '');
        $date = $row->tanggal ?? $row->created_at ?? '';
        if ($jenis === 'angsuran') {
            $payment = DB::table('transaksi')->where('fid_angsuran', $row->id)
                ->where('fid_anggota', $owner)->where('fid_status', 4)
                ->whereIn('fid_jenis_transaksi', [12, 13, 14])->orderBy('id')->first(['tanggal', 'created_by']);
            $date = ($payment->tanggal ?? null) ?: 'Tanggal pembayaran tidak tercatat';
            if (!empty($payment->created_by)) $processBy = (string) $payment->created_by;
        }
        // Petugas: utamakan nama dari jejak verifikasi; kalau tidak ada, pakai pembuat transaksi
        // (sama seperti yang ditampilkan aplikasi).
        $staffById = function ($no) { return $no ? DB::table('anggota')->where('no_anggota', $no)->value('nama_lengkap') : null; };
        $staff = ($audit && $audit->created_by ? $staffById($audit->created_by) : null) ?: $staffById($processBy);
        $data = [
            'jenis' => $jenis, 'id' => (int) $id, 'owner' => (string) $owner,
            'nama' => (string) $member->nama_lengkap, 'nominal' => number_format(abs($amount), 2, '.', ''),
            'tanggal' => (string) $date, 'status_id' => (int) $row->fid_status, 'status' => $status,
            'judul' => $title, 'label' => $label, 'module' => $module,
            'created_by' => $processBy,
            'metode' => (string) ($method ?: 'Tidak tercatat'), 'petugas' => (string) ($staff ?: 'Tidak tercatat'),
            'diverifikasi_at' => (string) ($audit->created_at ?? ''), 'items' => $items,
            'tenor' => (int) ($row->tenor ?? 0), 'angsuran_ke' => (int) ($row->angsuran_ke ?? 0),
            'angsuran_pokok' => number_format((float) ($row->angsuran_pokok ?? 0), 2, '.', ''),
            'angsuran_bunga' => number_format((float) ($row->angsuran_bunga ?? 0), 2, '.', ''),
            'keterangan' => (string) ($row->keterangan ?? ''),
            'referensi' => (string) ($row->no_transaksi ?? ($loan->id ?? $row->id)),
        ];
        $data['kode'] = static::code($data);
        $data['nomor'] = 'ESK-' . strtoupper($jenis) . '-' . $id . '-' . strtoupper(substr($data['kode'], 0, 12));
        return $data;
    }

    public static function code(array $data)
    {
        $key = (string) config('app.key');
        if (strpos($key, 'base64:') === 0) $key = base64_decode(substr($key, 7), true);
        if (!$key) throw new \RuntimeException('Kunci verifikasi belum tersedia');
        $signed = $data;
        unset($signed['kode'], $signed['nomor']);
        return hash_hmac('sha256', 'bukti-v1|' . json_encode($signed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), $key);
    }

    public static function verify($ref, $code)
    {
        if (!is_string($ref) || !preg_match('/^(transaksi|angsuran|penjualan):([1-9][0-9]*)$/D', $ref, $matches) || !is_string($code) || !preg_match('/^[a-f0-9]{64}$/D', $code)) return null;
        $data = static::load($matches[1], $matches[2]);
        return $data && hash_equals($data['kode'], $code) ? $data : null;
    }

    public static function pdf(array $data)
    {
        $url = url('/cek-bukti') . '?' . http_build_query(['ref' => $data['jenis'] . ':' . $data['id'], 'kode' => $data['kode']]);
        $qr = (new QRCode(new QROptions(['outputType' => QRCode::OUTPUT_IMAGE_PNG, 'scale' => 6, 'eccLevel' => QRCode::ECC_M])))->render($url);
        $dir = storage_path('framework/bukti-pdf');
        if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) throw new \RuntimeException('Penyimpanan PDF tidak tersedia');
        $data['logo'] = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path(config('koperasi.logo'))));
        $options = new Options();
        $options->setIsRemoteEnabled(false);
        $options->setIsPhpEnabled(false);
        $options->setIsJavascriptEnabled(false);
        $options->setChroot([public_path('assets/images')]);
        $options->setTempDir($dir);
        $options->setFontCache($dir);
        $options->setDefaultFont('DejaVu Sans');
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('bukti.transaksi', ['bukti' => $data, 'qr' => $qr, 'verifyUrl' => $url])->render());
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
        return $pdf->output();
    }

    public static function maskName($name)
    {
        return implode(' ', array_map(function ($part) { return mb_substr($part, 0, 2) . '***'; }, preg_split('/\s+/', trim($name))));
    }

    public static function rupiah($amount)
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }

    public static function terbilang($amount)
    {
        $n = (int) abs(round((float) $amount));
        $small = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        if ($n === 0) return 'nol';
        if ($n < 12) return $small[$n];
        if ($n < 20) return static::terbilang($n - 10) . ' belas';
        foreach ([[1000000000000, ' triliun'], [1000000000, ' miliar'], [1000000, ' juta'], [1000, ' ribu'], [100, ' ratus'], [10, ' puluh']] as $unit) {
            if ($n >= $unit[0]) {
                $count = intdiv($n, $unit[0]);
                $prefix = $count === 1 && $unit[0] === 1000 ? 'seribu' : ($count === 1 && $unit[0] === 100 ? 'seratus' : static::terbilang($count) . $unit[1]);
                return $prefix . ($n % $unit[0] ? ' ' . static::terbilang($n % $unit[0]) : '');
            }
        }
    }
}
