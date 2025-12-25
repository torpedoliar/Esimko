@php
  $page='Kasir Toko';
  $subpage='Produk';
@endphp
@extends('layouts.admin')
@section('title')
  Produk |
@endsection
@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-flex align-items-center justify-content-between">
        <h4 class="mb-0 font-size-18">Data Produk</h4>
      </div>
    </div>
  </div>
  <form action="{{url('kasir/produk/proses')}}" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="card">
      <div class="card-header">
        <h5>{{($action=='add' ? 'Tambah' : 'Edit')}} Produk</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-auto">
            <div class="avatar-wrapper" style="height:230px;width:330px" data-tippy-placement="bottom" title="Change Avatar">
              <img src="{{(!empty($data['produk']->foto) ? asset('storage/'.$data['produk']->foto) : asset('assets/images/image-default.jpg')) }}" alt="" />
              <div class="upload-button" onclick="changeImage('avatar')"></div>
              <input class="file-upload" type="file" name="foto" accept="image/*"/>
            </div>
          </div>
          <div class="col">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Kode</label>
                  <input type="text" class="form-control" name="kode" value="{{(!empty($data['produk']) ? $data['produk']->kode : '')}}"  autocomplete="off" required >
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <label>Nama Produk</label>
                  <input type="text" class="form-control" name="nama_produk" value="{{(!empty($data['produk']) ? $data['produk']->nama_produk : '')}}"  autocomplete="off" required >
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Kategori</label>
                  <select class="select2" style="width:100%" name="kategori">
                    @foreach ($data['kategori'] as $key => $value)
                    <option value="{{$value->id}}" {{(!empty($data['produk']) && $data['produk']->fid_kategori==$value->id ? 'selected' : '')}} >{{$value->nama_kategori}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Satuan</label>
                  <input type="text" class="form-control" name="satuan" value="{{(!empty($data['produk']) ? $data['produk']->satuan : '')}}" required >
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Harga Satuan</label>
                  <input type="text" class="form-control autonumeric" value="{{(!empty($data['produk']) ? $data['produk']->harga_satuan : '')}}" data-a-dec="." data-a-sep="," name="harga_satuan"  required >
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Deskripsi</label>
              <textarea class="form-control" name="deskripsi" style="height:100px" >{{(!empty($data['produk']) ? $data['produk']->deskripsi : '')}}</textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <input type="hidden" name="action" value="{{$action}}">
        <input type="hidden" name="id" value="{{$id}}">
        <div class="pull-right">
          <a class="btn btn-secondary" href="{{url('kasir/produk')}}" >Kembali</a>
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
