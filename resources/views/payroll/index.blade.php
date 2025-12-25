@php
  $app='laporan';
  $page='Laporan';
  $subpage='Cetak Payroll';
@endphp
@extends('layouts.admin')
@section('title')
  Cetak Payroll  |
@endsection
@section('content')
  <div class="container-fluid">
    <div class="content-breadcrumb mb-2">
      <div class="page-title-box">
        <div class="media">
          <img src="{{asset('assets/images/icon-page/pay-day.png')}}" class="avatar-md mr-3">
          <div class="media-body align-self-center">
            <h4 class="mb-0 font-size-18">Cetak Payroll</h4>
            <p class="text-muted m-0">Menampilkan data payroll angsuran pinjaman anggota yang sudah diproses oleh petugas</p>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-2">
          <form action="" method="get">
            <input type="text" name="bulan" class="form-control monthpicker" value="{{$bulan}}" onchange="javascript:submit()" autocomplete="off">
          </form>
        </div>
        <div class="col-md-7">
          <form action="" method="get">
            <input type="hidden" name="bulan" value="{{$bulan}}">
            <div class="input-group">
              <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Anggota">
              <div class="input-group-append">
                <button class="btn btn-dark" type="submit">Search</button>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-3">
          <a href="{{url('laporan/payroll?bulan='.$bulan.'&mode=cetak')}}" target="_blank" class="btn btn-primary btn-block" >Cetak Payroll</a>
        </div>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    @if(count($data['payroll'])==0)
      <div style="width:100%;text-align:center">
        <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
        <h4 class="mt-2">Data Payroll tidak Ditemukan</h4>
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-middle table-custom">
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
              <td style="text-align:right">{{number_format(\App\Helpers\GlobalHelper::pembulatan_nominal($value->simpanan),0,',','.')}}</td>
              <td style="text-align:right">{{number_format(\App\Helpers\GlobalHelper::pembulatan_nominal($value->angsuran_uang),0,',','.')}}</td>
              <td style="text-align:right">{{number_format(\App\Helpers\GlobalHelper::pembulatan_nominal($value->angsuran_barang),0,',','.')}}</td>
              <td style="text-align:right">{{number_format(\App\Helpers\GlobalHelper::pembulatan_nominal($value->pinjaman_toko),0,',','.')}}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      {{ $data['payroll']->links('include.pagination', ['pagination' => $data['payroll']] ) }}
    @endif
  </div>
@endsection
@section('js')
<script>

</script>
@endsection
