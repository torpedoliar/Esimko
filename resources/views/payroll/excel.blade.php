@extends('layouts.report')
@section('title')
  Cetak Payroll
@endsection
@section('css')
  <style>

  </style>
@endsection
@section('content')
  <table class="tabl" style="width:100%">
    <thead>
      <tr>
        <th class="center">HIRS</th>
        <th>Nama Karyawan</th>
        <th class="center">Level Jabatan</th>
        <th class="center">Lokasi</th>
        <th style="text-align:right">Simpanan</th>
        <th style="text-align:right">Angsuran Uang</th>
        <th style="text-align:right">Angsuran Barang</th>
        <th style="text-align:right">Pinjaman Toko</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($data['payroll'] as $key => $value)
      <tr>
        <td class="center">{{$value->no_hirs}}</td>
        <td>{{$value->nama_lengkap}}</td>
        <td class="center">{{$value->level}}</td>
        <td class="center">{{$value->lokasi}}</td>
        <td style="text-align:right">{{(\App\Helpers\GlobalHelper::pembulatan_nominal($value->simpanan),0,',','.')}}</td>
        <td style="text-align:right">{{(\App\Helpers\GlobalHelper::pembulatan_nominal($value->angsuran_uang),0,',','.')}}</td>
        <td style="text-align:right">{{(\App\Helpers\GlobalHelper::pembulatan_nominal($value->angsuran_barang),0,',','.')}}</td>
        <td style="text-align:right">{{(\App\Helpers\GlobalHelper::pembulatan_nominal($value->pinjaman_toko),0,',','.')}}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
@endsection
