{{--
  Tombol unduh bukti transaksi (PDF). Parameter:
  $jenis : transaksi|angsuran|penjualan
  $id    : id baris tabel yang bersangkutan
  $row   : baris transaksi (objek/array dengan fid_status, fid_jenis_transaksi, jenis_belanja)
  $otoritas (opsional): array otoritas_modul petugas; tombol disembunyikan bila tanpa izin cetak.
--}}
@php
  $__otoritas = $otoritas ?? null;
  $__boleh = ($__otoritas === null) || ($__otoritas['view'] === 'Y' && $__otoritas['print'] === 'Y');
  $__label = 'Unduh Bukti PDF';
  if ($jenis === 'transaksi' && in_array((int) ($row->fid_jenis_transaksi ?? 0), [9, 10, 11, 15]) && (int) $row->fid_status === 3) {
      $__label = 'Unduh Bukti Persetujuan';
  }
@endphp
@if($__boleh && \App\Support\BuktiTransaksi::eligible($jenis, $row))
  <a href="{{ url('bukti-transaksi/'.$jenis.'/'.$id) }}" class="btn btn-success" target="_blank" rel="noopener">
    <i class="bx bx-download"></i> {{ $__label }}
  </a>
@endif
