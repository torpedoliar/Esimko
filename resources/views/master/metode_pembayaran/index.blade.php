@php
  $app='master';
  $page='Metode Pembayaran';
  $subpage='Metode Pembayaran';
@endphp
@extends('layouts.admin')
@section('title')
  Metode Pembayaran |
@endsection
@section('content')
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/organization.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Metode Pembayaran</h4>
          <p class="text-muted m-0">Menampilkan data metode dan rekening pembayaran yang digunakan dalam melakukan semua transaksi </p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
        <form action="" method="get">
        <select class="select2 form-control" name="periode" onchange="javascript:submit()">
          @foreach ($data['metode-pembayaran'] as $key => $value)
          <option value="{{$value->id}}">{{$value->metode_pembayaran}}</option>
          @endforeach
        </select>
        </form>
      </div>
      <div class="col-md-6">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Pengurus">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <a href="{{url('master/metode_pembayaran/form')}}" class="btn btn-primary btn-block">Tambah Rekening</a>
      </div>
    </div>
  </div>
  @if(count($data['rekening-pembayaran'])==0)
  <div style="width:100%;text-align:center">
    <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
    <h4 class="mt-2">Data Metode Pembayaran tidak Ditemukan</h4>
  </div>
  @else
  <div class="table-responsive">
    <table class="table table-middle table-custom">
      <thead>
        <tr>
          <th>Keterangan</th>
          <th class="center">Metode Pembayaran</th>
          <th>Nomor Rekening</th>
          <th>Atas Nama</th>
          <th>Cart of Account</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['rekening-pembayaran'] as $key => $value)
          <tr>
            <td>{{$value->keterangan}}</td>
            <td class="center">{{$value->metode_pembayaran}}</td>
            <td class="center">{{$value->no_rekening}}</td>
            <td class="center">{{$value->atas_nama}}</td>
            <td class="center"></td>
            <td style="width:1px;white-space:nowrap">
              <div class="text-center">
                <a href="{{url('master/metode_pembayaran/form?id='.$value->id)}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                <a href="{{url('master/metode_pembayaran/form?id='.$value->id)}}" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endsection
@section('js')
  <script>

  </script>
@endsection
