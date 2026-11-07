<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow,noarchive"><meta name="referrer" content="no-referrer"><title>Cek Bukti eSIMKO</title>
<style>body{margin:0;background:#f3f7f4;color:#20352c;font:16px system-ui,sans-serif}.card{max-width:520px;margin:8vh auto;padding:28px;background:white;border-radius:16px;box-shadow:0 8px 30px #163b2010}h1{font-size:25px;color:#118334}dl{display:grid;grid-template-columns:1fr 1.5fr;gap:14px}dt{color:#617468}dd{margin:0;overflow-wrap:anywhere}.muted{color:#617468;font-size:13px}@media(max-width:580px){.card{margin:24px 12px;padding:22px}}</style></head>
<body><main class="card"><p>eSIMKO · Koperasi Satya Sejahtera</p>
@if($bukti)
<h1>Bukti terverifikasi</h1><p>Kode cocok dengan data transaksi saat ini.</p>
<dl><dt>Jenis</dt><dd>{{ $bukti['jenis'] }}</dd><dt>Nama tersamar</dt><dd>{{ $bukti['nama'] }}</dd><dt>Tanggal</dt><dd>{{ $bukti['tanggal'] }}</dd><dt>Nominal</dt><dd>{{ $bukti['nominal'] }}</dd><dt>Status</dt><dd>{{ $bukti['status'] }}</dd></dl>
@else
<h1>Bukti tidak valid</h1><p>Kode tidak cocok, transaksi sudah berubah, atau bukti tidak tersedia. Hubungi petugas koperasi untuk pengecekan.</p>
@endif
<p class="muted">Halaman ini hanya menampilkan informasi minimal. Untuk mengunduh dokumen lengkap, masuk ke akun yang berhak. Jangan membagikan QR kepada pihak yang tidak berkepentingan.</p>
</main></body></html>
