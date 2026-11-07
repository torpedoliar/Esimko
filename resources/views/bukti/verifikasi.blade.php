<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow,noarchive"><meta name="referrer" content="no-referrer"><title>Cek Bukti eSIMKO</title>
<style>body{margin:0;background:#f3f7f4;color:#20352c;font:16px system-ui,sans-serif}.card{max-width:520px;margin:8vh auto;padding:28px;background:white;border-radius:16px;box-shadow:0 8px 30px #163b2010}h1{font-size:25px;color:#118334}.logo{width:220px;max-width:70%;margin-bottom:6px}.kop{margin-bottom:18px;border-bottom:3px solid #118334;padding-bottom:14px}.kop p{margin:6px 0 0;color:#617468;font-size:14px}dl{display:grid;grid-template-columns:1fr 1.5fr;gap:14px}dt{color:#617468}dd{margin:0;overflow-wrap:anywhere}.badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:13px;font-weight:600}.ok{background:#e3f5e8;color:#0d6b2b}.bad{background:#fdecec;color:#a11a1a}.muted{color:#617468;font-size:13px}@media(max-width:580px){.card{margin:24px 12px;padding:22px}}</style></head>
<body><main class="card">
<div class="kop">
<img class="logo" src="{{ asset(config('koperasi.logo')) }}" alt="eSIMKO">
<p>{{ config('koperasi.nama') }}<br>{{ config('koperasi.perusahaan') }} · {{ config('koperasi.alamat') }}</p>
</div>
@if($bukti)
<h1>Bukti terverifikasi <span class="badge ok">VALID</span></h1><p>Kode cocok dengan data transaksi saat ini.</p>
<dl><dt>Jenis</dt><dd>{{ $bukti['jenis'] }}</dd><dt>Nama tersamar</dt><dd>{{ $bukti['nama'] }}</dd><dt>Tanggal</dt><dd>{{ $bukti['tanggal'] }}</dd><dt>Nominal</dt><dd>{{ $bukti['nominal'] }}</dd><dt>Status</dt><dd>{{ $bukti['status'] }}</dd></dl>
@else
<h1>Bukti tidak valid <span class="badge bad">TIDAK VALID</span></h1><p>Kode tidak cocok, transaksi sudah berubah, atau bukti tidak tersedia. Hubungi petugas koperasi untuk pengecekan.</p>
@endif
<p class="muted">Halaman ini hanya menampilkan informasi minimal. Untuk mengunduh dokumen lengkap, masuk ke akun yang berhak. Jangan membagikan QR kepada pihak yang tidak berkepentingan.</p>
</main></body></html>
