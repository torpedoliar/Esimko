@php
  $page='Pinjaman';
  $subpage='Pinjaman';
@endphp
@extends('layouts.main')
@section('title')
  Pinjaman |
@endsection
@section('css')
  <link href="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
  <style>
  .list-anggota{
    padding-bottom:10px;
    border-bottom: 1px solid #f2f2f2;
    margin-top:10px;
    cursor: pointer;
  }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-flex align-items-center justify-content-between">
        <h4 class="mb-0 font-size-18">Pinjaman</h4>
      </div>
    </div>
  </div>
  <form action="{{url('pinjaman/proses')}}" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="card">
      <div class="card-header">
        <h5>Formulir {{$data['jenis-pinjaman']->jenis_pinjaman}}</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div style="border:#dfe4e9 dashed 2px ;padding:20px">
              <h4 class="mb-3"># Identitas Anggota</h4>
              <div class="row">
                <div class="col-auto">
                  <div class="avatar-wrapper">
                    <img src="{{asset('assets/images/user-avatar-placeholder.png')}}" alt="" />
                  </div>
                </div>
                <div class="col">
                  <div class="list-content">
                    <span>No. Anggota</span>
                    <div id="no_anggota" class="info-content"><hr></div>
                  </div>
                  <div class="list-content">
                    <span>Nama Lengkap</span>
                    <div id="nama_lengkap" class="info-content"><hr></div>
                  </div>
                </div>
              </div>
              <input type="hidden" name="no_anggota" id="fid_anggota">
              <button type="button" data-target="#modal-anggota" data-toggle="modal" class="btn btn-secondary btn-block">PILIH ANGGOTA</button>
            </div>
          </div>
          <div class="col-md-6">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tanggal</label>
                  <input type="text" name="tanggal" autocomplete="off" class="datepicker form-control">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Metode Pencairan</label>
                  <select name="metode_pencairan" class="form-control select2">
                    @foreach ($data['metode-pencairan'] as $key => $value)
                    <option value="{{$value->id}}">{{$value->metode_pembayaran}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Jumlah</label>
                  <input type="text" name="jumlah" class="form-control">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label">Tenor</label>
                  <input data-toggle="touchspin" name="tenor" type="text" value="60" data-bts-postfix="Bulan" data-max='80'>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Keterangan</label>
              <textarea name="keterangan" class="form-control" style="height:110px"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <input type="hidden" name="action" value="{{$action}}">
        <input type="hidden" name="id" value="{{$id}}">
        <input type="hidden" name="jenis" value="{{$data['jenis-pinjaman']->id}}">
        <div class="pull-right">
          <a class="btn btn-secondary" href="{{url('pinjaman')}}" >Kembali</a>
          <button class="btn btn-primary" type="submit">{{($action=='add' ? 'Tambah' : 'Simpan')}}</button>
        </div>
      </div>
    </div>
  </form>
</div>
<div id="modal-anggota" class="modal fade right">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Pilih Anggota</h5>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
          <input type="text" class="form-control" value="" id="search" name="search" placeholder="Cari Anggota">
          <div class="input-group-append">
            <button class="btn btn-dark" onclick="search_anggota()">Search</button>
          </div>
        </div>
        <div id="loading"><img src="{{asset('assets/images/loading.gif')}}" style="width:100px"></div>
        <div id="list-anggota" >
          @foreach ($data['anggota'] as $key => $value)
            <div class="list-anggota" onclick="pilih_anggota('{{$value->id}}')">
              <div class="media">
                <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" style="max-width:none" class="rounded-circle img-thumbnail avatar-sm mr-2">
                <div class="media-body align-self-center" >
                  <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                  <h5 class="text-truncate font-size-16">{{$value->nama_lengkap}}</h5>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')
  <script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
  <script src="{{asset('assets/js/pages/form-advanced.init.js')}}"></script>
  <script>
  function pilih_anggota(id){
    $.get("{{ url('api/find_anggota') }}/"+id,function(result){
      $('#nama_lengkap').html(result.nama_lengkap);
      $('#no_anggota').html(result.no_anggota);
      $('#fid_anggota').val(result.no_anggota);
      $('#modal-anggota').modal('hide');
    });
  }
  </script>
@endsection
