@php
  $page='Penarikan';
  $subpage='Penarikan';
@endphp
@extends('layouts.main')
@section('title')
Penarikan |
@endsection
@section('css')
  <style>

  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-flex align-items-center justify-content-between">
        <h4 class="mb-0 font-size-18">Penarikan Simpanan</h4>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <h5>Formulir Penarikan Simpanan</h5>
        </div>
        <div class="card-body">
          <form action="{{url('transaksi/proses')}}" method="post">
            {{ csrf_field() }}
            <div class="form-group">
              <label>Jumlah Setoran</label>
              <input type="text" style="text-align:right" class="form-control autonumeric" data-a-dec="." data-a-sep="," name="nominal" value="0">
            </div>
            <div class="form-group">
              <label>Keterangan</label>
              <textarea name="keterangan" class="form-control" style="height:110px"></textarea>
            </div>
            <input type="hidden" name="jenis_transaksi" value="6">
            <input type="hidden" name="modul" value="penarikan">
            <div class="pull-right">
              <button class="btn btn-primary" name="action" value="add">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <div class="center mb-5">
            <img src="{{asset('assets/images/'.$data['penarikan']->icon)}}" style="width:80px">
            <h4 class="mt-3">{{$data['keterangan']->label}}</h4>
            <p>{{$data['keterangan']->keterangan}}</p>
          </div>
          <h5 class="mb-3">Informasi Transaksi</h5>
          <table class="table table-informasi">
            <tr>
              <th width="180px">No. Anggota</th>
              <th width="10px">:</th>
              <td>{{$data['penarikan']->no_anggota}}</td>
            </tr>
            <tr>
              <th>Nama Lengkap</th>
              <th>:</th>
              <td>{{$data['penarikan']->nama_lengkap}}</td>
            </tr>
            <tr>
              <th>Jenis Transaksi</th>
              <th>:</th>
              <td>{{$data['penarikan']->jenis_transaksi}}</td>
            </tr>
            <tr>
              <th>Metode Transaksi</th>
              <th>:</th>
              <td>{{$data['penarikan']->metode_transaksi}}</td>
            </tr>
            <tr>
              <th>Jumlah Penarikan</th>
              <th>:</th>
              <td>Rp {{number_format(str_replace('-','',$data['penarikan']->nominal),0,',','.')}}</td>
            </tr>
            <tr>
              <th>Keterangan</th>
              <th>:</th>
              <td>{{$data['penarikan']->keterangan}}</td>
            </tr>
          </table>
          <h5 class="mb-3 mt-4">Riwayat Transaksi</h5>
          <ul class="verti-timeline list-unstyled">
            <li class="event-list">
              <div class="event-timeline-dot">
                <i class="bx bx-right-arrow-circle"></i>
              </div>
              <h6>{{\App\Helpers\GlobalHelper::tgl_indo($data['penarikan']->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($data['penarikan']->created_at,'H:i:s')}}</h6>
              <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500">{{$data['penarikan']->nama_petugas}}</span></p>
            </li>
            @foreach (\App\Helpers\GlobalHelper::get_verifikasi_transaksi($id,'transaksi') as $key => $value)
            <li class="event-list">
              <div class="event-timeline-dot">
                <i class="bx bx-right-arrow-circle"></i>
              </div>
              <h6>{{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')}}</h6>
              <p class="text-muted">{{$value->caption}} <span style="font-weight:500">{{$value->nama_lengkap}}</span></p>
            </li>
            @endforeach
          </ul>
        </div>
        <div class="card-footer">
          <a href="{{url('penarikan')}}" class="btn btn-dark pull-right">Kembali</a>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
@section('js')

@endsection
