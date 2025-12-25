@php
  $app='manajemen_barang';
  $page='Manajemen Stok';
  $subpage='Pembelian Barang';
@endphp
@extends('layouts.kasir')
@section('title')
  Pembelian Barang |
@endsection
@section('css')
  <link href="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
  <style>
  .list-produk{
    padding-bottom:10px;
    border-bottom: 1px solid #f2f2f2;
    margin-top:10px;
    cursor: pointer;
  }
  .image-square{
    background-color:#ececec;
    position: relative; /* If you want text inside of it */
    background-position: 50% 50%;
    background-repeat: no-repeat;
    background-size: cover;
  }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="card" style="position:relative;margin-top:-25px">
    <div style="display: flex;position:absolute;top:20px;z-index:1000;width:100%">
      <div style="width:100%;padding:20px">
        <div class="row">
          <div class="col-md-2">
            <button class="btn btn-primary btn-block" onclick="pilih_produk('show')">Pilih Produk</button>
          </div>
          <div class="col-md-10">
            <form action="{{url('pos/penjualan/proses_items')}}" method="post" id="add_items">
              {{ csrf_field() }}
              <input type="hidden" name="id" value="{{$id}}">
              <div class="input-group">
                <input type="text" id="kode" name="kode" class="form-control" placeholder="Masukkan Kode Barang">
                <div class="input-group-append">
                  <button class="btn btn-secondary" type="submit" >Tambahkan</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div style="width:450px"></div>
    </div>
  </div>
  <form action="{{url('pos/penjualan/proses')}}" method="post" id="proses_penjualan">
    {{ csrf_field() }}
    <div class="card" >
      <div class="card-body" style="padding:0px;">
        <div style="display: flex !important;height:calc(100vh - 180px)">
          <div style="width:100%;position:relative">
            <div style="padding:20px">
              <table class="table table-middle table-hover mb-0" style="margin-top:50px">
                <thead class="thead-light">
                  <tr>
                    <th width="50px">No</th>
                    <th>Nama Barang</th>
                    <th class="center" style="width:150px">Jumlah<br>Barang</th>
                    <th class="center" style="width:135px">Harga Satuan <hr style="margin-top: 0.5rem;margin-bottom: 0.5rem;"> Total Harga</th>
                    <th class="center" style="width:150px">Margin (%)<hr style="margin-top: 0.5rem;margin-bottom: 0.5rem;">Harga Jual</th>
                    <th></th>
                  </tr>
                </thead>
              </table>
              <div style="height:calc(100vh - 450px);margin-bottom:20px;overflow: scroll;">
                <table class="table table-middle table-hover">
                  <tbody>
                    @foreach ($data['items'] as $key => $value)
                    <tr>
                      <td>{{$key+1}}</td>
                      <td>
                        <div class="media">
                          <div class="rounded mr-3 produk-wrapper" style="height:60px;width:60px">
                            <img src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                          </div>
                          <div class="align-self-center media-body">
                            <span>Kode. {{$value->kode}}</span>
                            <h6>{{$value->nama_produk}}</h6>
                          </div>
                        </div>
                      </td>
                      <td style="white-space:nowrap">
                        <input data-toggle="touchspin" id="jumlah" name="jumlah" value="" type="text" value="0">
                        <div id="satuaan" class="mt-1" style="text-align:right;color:#444444">Pcs</div>
                      </td>
                      <td>
                        <input type="text" style="text-align:right" class="form-control autonumeric" data-a-dec="." data-a-sep="," id="harga" name="harga">
                        <div id="harga_jual" class="mt-1" style="text-align:right;color:#444444">Rp 20.000</div>
                      </td>
                      <td>
                        <input type="text" class="form-control center" id="margin" name="margin">
                        <div id="harga_jual" class="mt-1" style="text-align:right;color:#444444">Rp 20.000</div>
                      </td>
                      <td style="width:1px;white-space:nowrap">
                        <div class="text-center">
                          <a href="javascript:;" onclick="edit_items({{ $value->id }})" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                          <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                        </div>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            <div style="background:#f8f9fa;padding:20px;width:100%;position:absolute;bottom:0px;border-top:3px solid #eff2f7">
              <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-3">
                  <div class="form-group mb-0" style="text-align:right">
                    <label>Sub Total (Rp)</label>
                    <input type="text" class="form-control autonumeric" id="subtotal" readonly style="text-align:right" value="{{(!empty($data['penjualan']) ? $data['penjualan']->subtotal : 0 )}}" data-a-dec="," data-a-sep=".">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group mb-0" style="text-align:right">
                    <label>Diskon (%)</label>
                    <input type="text" class="form-control" onchange="calc_items()" id="diskon" style="text-align:right" name="diskon" value="{{(!empty($data['penjualan']) ? $data['penjualan']->diskon : 0 )}}">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group mb-0" style="text-align:right">
                    <label>PPN (%)</label>
                    <input type="text" class="form-control" onchange="calc_items()" id="diskon" style="text-align:right" name="diskon" value="{{(!empty($data['penjualan']) ? $data['penjualan']->diskon : 0 )}}">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group mb-0" style="text-align:right">
                    <label>Total (Rp)</label>
                    <input type="text" class="form-control autonumeric" name="total_pembayaran" id="total" readonly style="text-align:right" value="{{(!empty($data['penjualan']) ? $data['penjualan']->total : 0 )}}" data-a-dec="," data-a-sep="." >
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div style="width:450px;background:#eaecef;position:relative">
            <div style="padding:20px;">
              <div class="form-group">
                <label>Tanggal</label>
                <input type="text" class="form-control datepicker" name="no_transaksi" value="{{(!empty($data['penjualan']) ? $data['penjualan']->tanggal : null)}}">
              </div>
              <div class="form-group">
                <label>No. Transaksi</label>
                <input type="text" class="form-control" name="no_transaksi" value="{{(!empty($data['penjualan']) ? $data['penjualan']->no_pembelian : null)}}">
              </div>
              <div class="form-group">
                <label>Supplier</label>
                <select name="supplier" class="form-control select2">
                  @foreach ($data['supplier'] as $key => $value)
                  <option value="{{$value->id}}">{{$value->nama_supplier}}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group">
                <label>Keterangan</label>
                <textarea name="no_pembelian" style="height:100px" class="form-control">{{(!empty($data['pembelian']) ? $data['pembelian']->keterangan : '')}}</textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <input type="hidden" name="id" value="{{$id}}">
  </form>
</div>
@foreach ($data['items'] as $key => $value)
<form action="{{url('manajemen_stok/pembelian/proses')}}" method="post" id="hapus{{$value->id}}">
  {{ csrf_field()}}
  <input type="hidden" name="id" value="{{$id}}">
  <input type="hidden" name="produk_id" value="{{$value->fid_produk}}">
  <input type="hidden" name="action" value="delete_items">
</form>
@endforeach
<div id="modal-produk" class="modal fade right">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Pilih Produk</h5>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
          <input type="text" class="form-control" value="" id="search" name="search" placeholder="Cari Produk">
          <div class="input-group-append">
            <button class="btn btn-dark" id="btn-search" onclick="search_produk()">Search</button>
          </div>
        </div>
        <div id="loading"><img src="{{asset('assets/images/loading.gif')}}" style="width:100px"></div>
        <div id="list-produk" ></div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')
<script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
<script src="{{asset('assets/js/pages/form-advanced.init.js')}}"></script>
<script>
  $(function () {
    $('#cancel').trigger('click');
  });

  function search_produk(){
    var search = $('#search').val();
    if(search !== ''){ search = '/'+search }
    else{ search = '/all'}

    $('#loading').show();
    $('#list-produk').hide();
    $.get("{{ url('api/get_produk/all/') }}"+search,function (result) {
      $('#list-produk').html('');
      $.each(result,function(i,value){
      $('#list-produk').append('<div class="list-produk" onclick="pilih_produk('+value.id+')">'+
        '<div class="media">'+
          '<div class="image-square avatar-md mr-2" style="max-width:none;background-image:url('+value.foto+')"></div>'+
          '<div class="media-body align-self-center" >'+
            '<p class="text-muted mb-0">Kode. '+value.kode+'</p>'+
            '<h5 class="font-size-16">'+value.nama_produk+'</h5>'+
            '<span>'+value.nama_kategori+'</span>'+
          '</div>'+
        '</div>'+
      '</div>');
      });
      $('#loading').hide();
      $('#list-produk').show();
    });
  };

  function pilih_produk(id){
    if(id=='show'){
      search_produk();
      $('#modal-produk').modal('show');
    }
    else{
      $.get("{{ url('api/find_produk') }}/"+id,function(result){
        $('#kode').html('<span>'+result.kode+'</span>');
        $('#nama_produk').html('<h6>'+result.nama_produk+'</h6>');
        $('#satuan').html('satuan : '+result.satuan);
        $('#produk_id').val(id);
        $('#modal-produk').modal('hide');
      });
    }
  }

function add_items(){
    $('#produk_id').val('');
    $('#kode').html('<div style="height:15px;width:150px;background:whitesmoke"></div>');
    $('#nama_produk').html('<div style="height:20px;width:250px;background:whitesmoke" class="mt-2"></div>');
    $('#jumlah').val('');
    $('#harga').val('');
    $('#satuan').html('');
    $('#total').val('');
    $('#action').html('Tambah');
    $('#cancel').hide();
  }

  function edit_items(id){
    $.get("{{ url('api/find_items_pembelian') }}/"+id,function(result){
      $('#produk_id').val(result.fid_produk);
      $('#kode').html('<span>'+result.kode+'</span>');
      $('#nama_produk').html('<h6>'+result.nama_produk+'</h6>');
      $('#jumlah').val(result.jumlah);
      $('#harga').val(result.harga);
      $('#satuan').html('satuan : '+result.satuan);
      $('#total').val(result.total);
      $('#action').html('Edit');
      $('#cancel').show();
    });
  }

</script>
@endsection
