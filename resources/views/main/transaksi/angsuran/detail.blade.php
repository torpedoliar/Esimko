@php
  $page='Angsuran';
  $subpage='Angsuran';
@endphp
@extends('layouts.main')
@section('title')
Angsuran |
@endsection
@section('css')
  <style>

  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box pb-0">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/wallet.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Data Angsuran</h4>
        <p class="text-muted m-0">Menampilkan data Angsuran Pinjaman yang sudah diinput oleh petugas atau anggota</p>
      </div>
    </div>
  </div>
  <div class="card mt-5">
    <div class="card-body">
      <div class="center mb-5">
        <img src="{{asset('assets/images/success.png')}}" style="width:80px">
        <h4 class="mt-3">Pembayaran Angsuran Pinjaman Berhasil</h4>
      </div>
      <h5 class="mb-3">Informasi Transaksi</h5>
      <table class="table table-informasi">
        <tr>
          <th width="180px">No. Anggota</th>
          <th width="10px">:</th>
          <td>{{$data['angsuran']->no_anggota}}</td>
        </tr>
        <tr>
          <th>Nama Lengkap</th>
          <th>:</th>
          <td>{{$data['angsuran']->nama_lengkap}}</td>
        </tr>
        <tr>
          <th>Jenis Pinjaman</th>
          <th>:</th>
          <td>{{$data['angsuran']->jenis_transaksi}}</td>
        </tr>
        <tr>
          <th>Angsuran Ke</th>
          <th>:</th>
          <td>{{$data['angsuran']->angsuran_ke}}</td>
        </tr>
        <tr>
          <th>Bulan</th>
          <th>:</th>
          <td>{{\App\Helpers\GlobalHelper::nama_bulan($data['angsuran']->bulan)}}</td>
        </tr>
        <tr>
          <th>Angsuran Pokok</th>
          <th>:</th>
          <td>Rp {{number_format($data['angsuran']->angsuran_pokok,0,',','.')}}</td>
        </tr>
        <tr>
          <th>Angsuran Bunga</th>
          <th>:</th>
          <td>Rp {{number_format($data['angsuran']->angsuran_bunga,0,',','.')}}</td>
        </tr>
        <tr>
          <th>Total Angsuran</th>
          <th>:</th>
          <td>Rp {{number_format($data['angsuran']->total_angsuran,0,',','.')}}</td>
        </tr>
        <tr>
          <th>Keterangan</th>
          <th>:</th>
          <td>{{(!empty($data['angsuran']->keterangan) ? $data['angsuran']->keterangan : 'Tidak ada keterangan')}}</td>
        </tr>
      </table>
    </div>
    <div class="card-footer">
      <a href="{{url('main/angsuran')}}" class="btn btn-dark pull-right">Kembali</a>
    </div>
  </div>
</div>
@endsection
@section('js')
<script>

</script>
@endsection
