@php
  $app='manajemen_barang';
  $page='Data Barang';
  $subpage='Data Barang';
@endphp
@extends('layouts.admin')
@section('title')
  Data Barang |
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/product.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Data Barang</h4>
        <p class="text-muted m-0">Formulir pengisian data barang atau produk yang dijual ditoko secara online atau offline</p>
      </div>
    </div>
  </div>
  <form action="{{url('manajemen_stok/barang/proses')}}" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="card">
      <div class="card-header">
        <h5>{{($action=='add' ? 'Tambah' : 'Edit')}} Barang</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-auto">
            <div class="produk-wrapper" style="height:225px;width:225px;padding:5px" data-tippy-placement="bottom">
              <img src="{{(!empty($data['produk']->foto) ? asset('storage/'.$data['produk']->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
              <div class="upload-button" onclick="changeImage('produk')"></div>
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
              <div class="col-md-8">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Stok Awal</label>
                      <input data-toggle="touchspin" type="text" class="form-control center" name="stok" value="{{(!empty($data['produk']) ? $data['produk']->stok : '')}}" required >
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
                      <label>Harga Beli (HPP)</label>
                      <input type="text" class="form-control autonumeric" onkeyup="calc_harga('margin')" id="harga_beli" value="{{(!empty($data['produk']) ? $data['produk']->harga_beli : 0 )}}" data-a-dec="," data-a-sep="." name="harga_beli"  required >
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Margin Penjualan</label>
                  <div style="display:flex">
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp</span>
                      </div>
                      <input type="text" class="form-control autonumeric" id="nominal_margin" onkeyup="calc_harga('nominal')" data-a-dec="," data-a-sep="." style="border-radius:0px"  >
                    </div>
                    <div class="input-group" style="width:150px" >
                      <input type="text"class="form-control" style="border-radius:0px;margin-left:-1px;text-align:center" onkeyup="calc_harga('margin')" id="margin" name="margin" value="{{(!empty($data['produk']) ? $data['produk']->margin : 0 )}}"  required >
                      <div class="input-group-append">
                        <span class="input-group-text"><i class="mdi mdi-percent-outline"></i></span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label>Harga Jual</label>
                  <input type="text" class="form-control autonumeric" id="harga_jual" name="harga_satuan" readonly >
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <label>Deskripsi</label>
                  <textarea class="form-control" name="deskripsi" style="height:117px" >{{(!empty($data['produk']) ? $data['produk']->deskripsi : '')}}</textarea>
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
          <a class="btn btn-secondary" href="{{url('manajemen_stok/barang')}}" >Kembali</a>
          <button class="btn btn-primary" type="submit">{{($action=='add' ? 'Tambah' : 'Simpan')}}</button>
        </div>
      </div>
    </div>
  </form>
</div>

@endsection
@section('js')
  <script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
  <script src="{{asset('assets/js/pages/form-advanced.init.js')}}"></script>
  <script src="{{asset('assets/js/accounting.js')}}"></script>
  <script>
  calc_harga();
  function calc_harga(jenis='all'){
    harga_beli=$('#harga_beli').val();
    harga_beli = harga_beli.split('.').join('');

    if(jenis == 'nominal'){
      nominal_margin=$('#nominal_margin').val();
      nominal_margin = nominal_margin.split('.').join('');
      margin = (nominal_margin/harga_beli)*100,2;
      $('#margin').val(accounting.formatNumber(margin,0,'.',','));
    }
    else{
      margin=$('#margin').val();
      nominal_margin=margin*harga_beli/100,2;
      $('#nominal_margin').val(accounting.formatNumber(nominal_margin,0,'.',','));
    }

    harga_jual=parseInt(harga_beli)+parseInt(nominal_margin);
    $('#harga_jual').val(accounting.formatNumber(harga_jual,0,'.',','));

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
