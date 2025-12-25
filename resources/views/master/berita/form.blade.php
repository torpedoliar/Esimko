@php
  $app='master';
  $page='Berita dan Informasi';
  $subpage='Berita dan Informasi';
@endphp
@extends('layouts.admin')
@section('title')
  Berita dan Informasi |
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/news.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Berita dan Informasi</h4>
        <p class="text-muted m-0">Form input data berita dan informasi untuk anggota koperasi</p>
      </div>
    </div>
  </div>
  <form action="{{url('master/berita/proses')}}" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="card card-table mg-t-50">
      <div class="card-header">
        <h5>{{($action=='add') ? 'Tambah' : 'Edit'}} Berita</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <div class="produk-wrapper" style="height:230px;width:100%; padding:5px" data-tippy-placement="bottom">
              <img src="{{(!empty($data['berita']->gambar) ? asset('storage/'.$data['berita']->gambar) : asset('assets/images/produk-default.jpg')) }}" alt="" />
              <div class="upload-button" onclick="changeImage('produk')"></div>
              <input class="file-upload" type="file" name="gambar" accept="image/*"/>
            </div>
            @if($action=='edit')
              <button class="btn btn-primary btn-block mt-3" type="button" onclick="add_attachment()">Tambah Attachment</button>
            @endif
            @if(count($data['attachment'])!=0)
            <h5 class="mt-3 mb-2">Attachment Berita</h5>
            <table class="table">
              <tbody>
                @foreach ($data['attachment'] as $key => $value)
                <tr>
                  <td>
                    <h6>{{$value->judul}}</h6>
                  </td>
                  <td style="white-space:nowrap;width:1px">
                    <a href="javascript:;" onclick="edit_attachment({{$value->id}})" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                    <a href="javascript:;" onclick="confirmDelete({{$value->id}})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @endif
          </div>
          <div class="col-md-8">
            <div class="form-group">
              <label>Judul Berita</label>
              <input type="text" class="form-control" name="judul" value="{{(!empty($data['berita']) ? $data['berita']->judul : '' )}}" >
            </div>
            <div class="form-group">
              <label>Content</label>
              <textarea class="form-control tinymce" name="content" style="height:200px" >{{(!empty($data['berita']) ? $data['berita']->content : '' )}}</textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <input type="hidden" name="id" value="{{$id}}">
        <div class="pull-right">
          <a class="btn btn-dark" href="{{url('master/berita')}}" >Kembali</a>
          <button class="btn btn-success" type="submit" name="action" value="{{$action}}">Simpan</button>
        </div>
      </div>
    </div>
  </form>
</div>
@foreach ($data['attachment'] as $key => $value)
<form action="{{url('master/berita/attachment/proses')}}" method="post" id="hapus{{$value->id}}">
  {{ csrf_field()}}
  <input type="hidden" name="id" value="{{$value->id}}">
  <input type="hidden" name="fid_berita" value="{{$id}}">
  <input type="hidden" name="action" value="delete">
</form>
@endforeach
<div class="modal fade" id="form-attachment">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="title"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{url('master/berita/attachment/proses')}}" id="proses_transkasi" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="modal-body">
          <div class="form-group">
            <label>Nama File</label>
            <input type="text" class="form-control" name="judul" id="judul" >
          </div>
          <div class="form-group">
            <label>Attachment</label>
            <input type="file" class="dropify" name="attachment" >
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="fid_berita" value="{{$id}}">
          <input type="hidden" name="id" id="id">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button class="btn btn-primary"  id="action" name="action">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
@section('js')
  <script>
  function add_attachment(){
    $('#judul').val('');
    $('#id').val(0);
    $('#action').val('add');
    $('#title').html('Tambah Attachment');
    $('#form-attachment').modal('show');
  }
  function edit_attachment(id){
    $.get("{{ url('api/find_attachment_berita') }}/"+id,function(result){
      $('#judul').val(result.judul);
      $('#id').val(id);
      $('#action').val('edit');
      $('#title').html('Edit Attachment');
      $('#form-attachment').modal('show');
    });
  }
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
