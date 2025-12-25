@php
  $app='master';
  $page='Data Master';
  $subpage='Rekening Pembayaran';
@endphp
@extends('layouts.admin')
@section('title')
  Rekening Pembayaran |
@endsection
@section('css')
  <style>
  .logo-wrapper {
    position: relative;
    width: 150px;
    height: 150px;
    border-radius: 0px;
    overflow: hidden;
    box-shadow: none;
    margin: 0 10px 0px 0;
    transition: all .3s ease;
    background: whitesmoke;
  }
  .logo-wrapper img {
    height: 100%;
    width: 100%;
    transition: all .3s ease;
    object-fit: contain;
  }

  .logo-wrapper .upload-button {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    cursor:pointer;
  }

  .logo-wrapper .file-upload{
    opacity: 0;
    pointer-events: none;
    position: absolute;
  }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/card-payment.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Rekening Pembayaran</h4>
        <p class="text-muted m-0">Formulir pengisian rekening pembayaran yang digunakan dalam melakukan semua transaksi</p>
      </div>
    </div>
  </div>
  <form action="{{url('master/metode_pembayaran/proses')}}" style="margin-top:30px" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="card">
      <div class="card-header">
        <h5>{{($action=='add' ? 'Tambah' : 'Edit')}} Rekening Pembayaran</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-auto">
            <div class="logo-wrapper" data-tippy-placement="bottom" title="Change Logo">
              <img src="{{(!empty($data['rekening']->logo) ? asset('storage/'.$data['rekening']->logo) : asset('assets/images/image-default.png') )}}" alt="" />
              <div class="upload-button" onclick="changeImage('logo')"></div>
              <input class="file-upload" type="file" name="logo" accept="image/*"/>
            </div>
          </div>
          <div class="col">
            <div class="row">
              <div class="col-md-8">
                <div class="form-group">
                  <label>Keterangan</label>
                  <input type="text" class="form-control" name="keterangan" value="{{(!empty($data['rekening']) ? $data['rekening']->keterangan : '')}}"  autocomplete="off" readonly  >
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Metode Pembayaran</label>
                  <select class="select2" name="metode"  style="width:100%">
                    @foreach ($data['metode'] as $key => $value)
                    <option value="{{$value->id}}" >{{$value->metode_pembayaran}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>No. Rekening</label>
                  <input type="text" class="form-control" name="no_rekening" value="{{(!empty($data['rekening']) ? $data['rekening']->no_rekening : '')}}" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Atas Nama</label>
                  <input type="text" class="form-control" name="an_rekening" value="{{(!empty($data['rekening']) ? $data['rekening']->atas_nama : '')}}" autocomplete="off" >
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Status Aktif</label>
                  <select class="select2" name="status_aktif" style="width:100%">
                    <option value="1" >Aktif</option>
                    <option value="0" >Tidak Aktif</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <input type="hidden" name="action" value="{{$action}}">
        <input type="hidden" name="id" value="{{$id}}">
        <div class="pull-right">
          <a class="btn btn-secondary" href="{{url('master/rekening_pembayaran')}}" >Kembali</a>
          <button class="btn btn-primary" type="submit">{{($action=='add' ? 'Tambah' : 'Simpan')}}</button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
@section('js')
  <script>
  function changeImage(target) {
    var readURL = function(input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
          $('.'+target+'-wrapper img').attr('src', e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
      }
    };

    $(".file-upload").on('change', function(){
      readURL(this);
    });
    $(".file-upload").click();
  }
  </script>
@endsection
