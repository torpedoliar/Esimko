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
    border:2px solid #e2e2e2 ;
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: none;
    margin: 0 10px 0px 0;
    transition: all .3s ease;
    background: whitesmoke;
    padding:5px
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
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/icon-page/bank.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Rekening Pembayaran</h4>
          <p class="text-muted m-0">Menampilkan Data Rekening Pembayaran yang digunakan dalam melakukan semua transaksi</p>
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
      <div class="col-md-9">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Pengurus">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="row mt-4">
    <div class="col-auto">
      <form action="{{url('master/rekening_pembayaran/proses')}}" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="card" style="width:350px;">
          <div class="card-body">
            <h5 id="title"></h5>
            <hr>
            <div class="logo-wrapper" style="width:100px;height:100px" data-tippy-placement="bottom" title="Change Logo">
              <img id="logo_rekening" src="{{asset('assets/images/image-default.png')}}" alt="" />
              <div class="upload-button" onclick="changeImage('logo')"></div>
              <input class="file-upload" type="file" name="logo" accept="image/*"/>
            </div>
            <div class="form-group mt-3">
              <label>keterangan</label>
              <input type="text" class="form-control mb-3 mt-2" name="keterangan" id="keterangan" autocomplete="off"  >
            </div>
            <div class="form-group">
              <label>Metode Pembayaran</label>
              <select class="select2" name="metode" id="metode" style="width:100%;margin-top:20px">
                @foreach ($data['metode-pembayaran'] as $key => $value)
                <option value="{{$value->id}}" >{{$value->metode_pembayaran}}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label>No. Rekening</label>
              <input type="text" class="form-control" name="no_rekening" id="no_rekening" autocomplete="off" >
            </div>
            <div class="form-group">
              <label>Atas Nama</label>
              <input type="text" class="form-control" name="atas_nama" id="atas_nama" autocomplete="off" >
            </div>
            {{-- <div class="form-group">
              <label>Status Aktif</label>
              <select class="select2" name="status_aktif" id="status_aktif" style="width:100%">
                <option value="1" >Aktif</option>
                <option value="0" >Tidak Aktif</option>
              </select>
            </div> --}}
          </div>
          <div class="card-footer">
            <input type="hidden" name="id" id="id">
            <div class="pull-right">
              <button type="submit" class="btn btn-primary" id="action"></button>
              <button type="button" class="btn btn-secondary" id="cancel" onclick="add_rekening()" >Cancel</button>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="col">
      @if(count($data['rekening-pembayaran'])==0)
      <div style="width:100%;text-align:center">
        <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
        <h4 class="mt-2">Data Rekening Pembayaran tidak Ditemukan</h4>
      </div>
      @else
      <div class="table-responsive">
        <table class="table table-middle table-custom">
          <thead>
            <tr>
              <th>Metode<br>Pembayaran</th>
              <th>Nomor<br>Rekening</th>
              <th>Bagan Akun</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($data['rekening-pembayaran'] as $key => $value)
              <tr>
                <td>
                  <div class="media">
                    <div class="logo-wrapper" style="width:50px;height:50px;background:transparent;border:none">
                      <img src="{{(!empty($value->logo) ? asset('storage/'.$value->logo) : asset('assets/images/image-default.png') )}}" alt="">
                    </div>
                    <div class="media-body align-self-center">
                      <h5 class="text-truncate font-size-14">{{$value->keterangan}}</h5>
                      <p class="text-muted mb-0 mt-1 font-size-12">{{$value->metode_pembayaran}}</p>
                    </div>
                  </div>
                </td>
                <td>
                  @if(!empty($value->no_rekening))
                  <h6>{{$value->no_rekening}}</h6>
                  <span>{{$value->atas_nama}}</span>
                  @else
                  <h6>-</h6>
                  @endif
                </td>
                <td><h6>-</h6></td>
                <td style="width:1px;white-space:nowrap">
                  <div class="text-center">
                    <a href="javascript:;" onclick="edit_rekening({{$value->id}})" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                    <a href="javascript:;" onclick="confirmDelete({{$value->id}})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                    <form action="{{url('master/rekening_pembayaran/proses')}}" method="post" id="hapus{{$value->id}}">
                      {{ csrf_field()}}
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="{{$value->id}}">
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
@section('js')
  <script>
  add_rekening();
  function add_rekening(){
    $('#keterangan').val('');
    $('#no_rekening').val('');
    $('#atas_nama').val('');

    $('#metode').val(1);
    $('#metode').select2();

    // $('#status_aktif').val(1);
    // $('#status_aktif').select2();

    $('#id').val(0);
    $('#action').val('add');
    $('#action').html('Tambah');
    $('#title').html('Tambah Rekening');
    $('#cancel').hide();
  }

  function edit_rekening(id){
    $.get("{{ url('api/find_metode_pembayaran') }}/"+id,function(result){
      $('#keterangan').val(result.keterangan);

      $('#metode').val(result.fid_metode_pembayaran);
      $('#metode').select2();

      // $('#status_aktif').val(result.is_active);
      // $('#status_aktif').select2();

      $('#no_rekening').val(result.no_rekening);
      $('#atas_nama').val(result.atas_nama);
      $('#id').val(id);
      $('#action').val('edit');
      $('#action').html('Simpan');
      $('#title').html('Edit Rekening');
      $('#cancel').show();
    });
  }

  function changeImage(target) {
    var readURL = function(input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
          $('#logo_rekening').attr('src', e.target.result);
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
