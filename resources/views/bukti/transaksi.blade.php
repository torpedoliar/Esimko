<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>{{ $bukti['judul'] }}</title>
<style>
@page { margin: 30px 36px; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #20352c; }
h1 { font-size: 19px; margin: 12px 0 4px; } h2 { font-size: 13px; margin: 0; }
.header { border-bottom: 3px solid #118334; padding-bottom: 13px; }
.logo { width: 210px; margin-bottom: 8px; }
.muted { color: #66756e; } .number { font-size: 9px; margin-bottom: 17px; }
table { width: 100%; border-collapse: collapse; } td { padding: 7px 8px; vertical-align: top; }
.details td { border-bottom: 1px solid #e5ebe7; } .key { width: 31%; color: #5b6d62; }
.amount { background: #edf7f0; margin: 18px 0; padding: 15px; }
.amount strong { font-size: 23px; } .items th { text-align: left; padding: 7px; background: #edf7f0; }
.items td { border-bottom: 1px solid #e5ebe7; } .verify { margin-top: 20px; border-top: 1px solid #d9e1dc; padding-top: 12px; }
.code { font-size: 8px; word-wrap: break-word; } .footer { margin-top: 17px; font-size: 8px; }
</style></head>
<body>
<div class="header">
<img class="logo" src="{{ $bukti['logo'] }}" alt="eSIMKO">
<h2>{{ config('koperasi.nama') }}</h2>
<div>{{ config('koperasi.perusahaan') }} · {{ config('koperasi.alamat') }}</div>
</div>
<h1>{{ $bukti['judul'] }}</h1>
<div class="number">Nomor bukti: {{ $bukti['nomor'] }}</div>
<table class="details">
<tr><td class="key">Nama anggota</td><td>{{ $bukti['nama'] }}</td></tr>
<tr><td class="key">Nomor anggota</td><td>{{ $bukti['owner'] }}</td></tr>
<tr><td class="key">Jenis transaksi</td><td>{{ $bukti['label'] }}</td></tr>
<tr><td class="key">Tanggal transaksi</td><td>{{ $bukti['tanggal'] }}</td></tr>
<tr><td class="key">Referensi</td><td>{{ $bukti['referensi'] }}</td></tr>
<tr><td class="key">Status saat diterbitkan</td><td>{{ $bukti['status'] }}</td></tr>
<tr><td class="key">Metode tercatat</td><td>{{ $bukti['metode'] }}</td></tr>
@if($bukti['tenor'])<tr><td class="key">Tenor pinjaman</td><td>{{ $bukti['tenor'] }} kali</td></tr>@endif
@if($bukti['angsuran_ke'])
<tr><td class="key">Angsuran ke-{{ $bukti['angsuran_ke'] }}</td><td>Pokok {{ \App\Support\BuktiTransaksi::rupiah($bukti['angsuran_pokok']) }} + bunga {{ \App\Support\BuktiTransaksi::rupiah($bukti['angsuran_bunga']) }}</td></tr>
@endif
<tr><td class="key">Petugas / jejak verifikasi</td><td>{{ $bukti['petugas'] }}{{ $bukti['diverifikasi_at'] ? ' · '.$bukti['diverifikasi_at'] : '' }}</td></tr>
</table>
<div class="amount">
<div>Nominal {{ strpos($bukti['judul'], 'Persetujuan') !== false ? 'disetujui' : 'transaksi' }}</div>
<strong>{{ \App\Support\BuktiTransaksi::rupiah($bukti['nominal']) }}</strong>
<div>{{ ucfirst(\App\Support\BuktiTransaksi::terbilang($bukti['nominal'])) }} rupiah</div>
</div>
@if(count($bukti['items']))
<table class="items"><thead><tr><th>Barang</th><th>Jumlah</th><th>Total</th></tr></thead><tbody>
@foreach($bukti['items'] as $item)<tr><td>{{ $item['nama'] }}</td><td>{{ $item['jumlah'] }}</td><td>{{ \App\Support\BuktiTransaksi::rupiah($item['total']) }}</td></tr>@endforeach
</tbody></table>
@endif
@if(strpos($bukti['judul'], 'Persetujuan') !== false)
<p><strong>Dokumen ini merupakan bukti persetujuan pinjaman, bukan bukti pencairan uang.</strong></p>
@endif
<div class="verify"><table><tr>
<td style="width: 110px; padding-left: 0"><img src="{{ $qr }}" style="width: 105px; height: 105px" alt="QR verifikasi"></td>
<td><strong>Cek keaslian bukti</strong><p>Scan QR untuk mencocokkan bukti dengan status transaksi di server eSIMKO.</p>
<div class="code">Kode: {{ implode('-', str_split(strtoupper($bukti['kode']), 8)) }}</div>
<p class="muted">Bila transaksi berubah, kode bukti lama tidak lagi valid. Simpan dan bagikan dokumen ini hanya kepada pihak yang berwenang.</p></td>
</tr></table></div>
<div class="footer muted">Diunduh {{ date('d-m-Y H:i:s') }} · Dokumen elektronik eSIMKO berdasarkan data sistem. Cek QR untuk mencocokkan data. Bukan tanda tangan elektronik tersertifikasi.</div>
</body></html>
