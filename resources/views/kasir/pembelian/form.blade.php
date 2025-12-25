@php
  $page='Kasir Toko';
  $subpage='Kulak Barang';
@endphp
@extends('layouts.admin')
@section('title')
  Kulak Barang |
@endsection
@section('css')
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
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-flex align-items-center justify-content-between">
        <h4 class="mb-0 font-size-18">Kulak Barang</h4>
      </div>
    </div>
  </div>
  <form action="{{url('kasir/kulakan/proses')}}" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="hidden" name="action" value="{{$action}}">
    <input type="hidden" name="id" value="{{$id}}">
    <div class="card">
      <div class="card-header">
        <h5>{{($action=='add' ? 'Tambah' : 'Edit')}} Transaksi Pembelian</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Tanggal</label>
              <input type="text" name="tanggal" value="{{(!empty($data['pembelian']) ? \App\Helpers\GlobalHelper::dateFormat($data['pembelian']->tanggal,'d-m-Y') : date('d-m-Y'))}}" autocomplete="off" class="datepicker form-control">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>No. Pembelian</label>
              <input type="text" name="no_pembelian" value="{{(!empty($data['pembelian']) ? $data['pembelian']->no_pembelian : '')}}" autocomplete="off" class="form-control">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Supplier</label>
              <select name="supplier" class="form-control select2">
                @foreach ($data['supplier'] as $key => $value)
                <option value="{{$value->id}}">{{$value->nama_supplier}}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <hr>
        <table class="table table-middle table-bordered table-hover">
          <thead class="thead-light">
            <tr>
              <th>No</th>
              <th style="width:120px">Kode</th>
              <th>Nama Produk</th>
              <th class="center" style="width:80px">Qty</th>
              <th class="center" style="width:120px">Satuan</th>
              <th style="text-align:right;width:150px">Harga</th>
              <th style="text-align:right;width:150px">Total</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#</td>
              <td>
                <input type="text" class="form-control" id="kode" name="kode" readonly onclick="modal_produk()" >
              </td>
              <td>
                <input type="text" class="form-control" id="nama_produk" name="nama_produk" readonly>
              </td>
              <td>
                <input type="text" class="form-control" id="jumlah" name="jumlah">
              </td>
              <td>
                <input type="text" class="form-control" id="satuan" name="satuan" readonly>
              </td>
              <td>
                <input type="text" class="form-control" id="harga" name="harga">
              </td>
              <td>
                <input type="text" class="form-control" id="total" name="total">
              </td>
              <td style="width:1px;white-space:nowrap">
                <input type="hidden" name="produk_id" id="produk_id">
                <button class="btn btn-primary btn-block" id="action">Tambah</button>
                <button type="button" class="btn btn-dark btn-block" id="cancel" onclick="add_items()">Cancel</button>
              </td>
            </tr>
            @foreach ($data['items'] as $key => $value)
            <tr>
              <td>{{$key+1}}</td>
              <td>{{$value->kode}}</td>
              <td>{{$value->nama_produk}}</td>
              <td class="center">{{$value->jumlah}}</td>
              <td class="center">{{$value->satuan}}</td>
              <td style="text-align:right">{{number_format($value->harga,0,',','.')}}</td>
              <td style="text-align:right">{{number_format($value->total,0,',','.')}}</td>
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
  </form>
</div>
@foreach ($data['items'] as $key => $value)
<form action="{{url('kasir/kulakan/proses')}}" method="post" id="hapus{{$value->id}}">
  {{ csrf_field()}}
  <input type="hidden" name="id" value="{{$value->id}}">
  <input type="hidden" name="action" value="delete">
</form>
@endforeach
<div id="modal-produk" class="modal fade right">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Pilih Produk</h5>
      </div>
      <div class="modal-body">
        <input class="form-control" type="text" id="search" placeholder="Cari Data Produk">
        <hr>
        @foreach ($data['produk'] as $key => $value)
          <div class="list-produk" onclick="pilih_produk('{{$value->id}}')">
            <div class="media">
              <div class="image-square avatar-md mr-2" style="max-width:none;background-image:url('{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/admin/img/image-default.jpg'))}}')"></div>
              <div class="media-body align-self-center" >
                <p class="text-muted mb-0">Kode. {{$value->kode}}</p>
                <h5 class="text-truncate font-size-16">{{$value->nama_produk}}</h5>
                <span class="text-muted">{{$value->nama_kategori}}</span>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')
<script>

  $(function () {
    $('#cancel').trigger('click');
  });

  function modal_produk(){
    $('#modal-produk').modal('show');
  }

  function pilih_produk(id){
    $.get("{{ url('api/find_produk') }}/"+id,function(result){
      $('#kode').val(result.kode);
      $('#nama_produk').val(result.nama_produk);
      $('#satuan').val(result.satuan);
      $('#produk_id').val(id);
      $('#modal-produk').modal('hide');
    });
  }

function add_items(){
    $('#produk_id').val('');
    $('#kode').val('');
    $('#nama_produk').val('');
    $('#jumlah').val('');
    $('#harga').val('');
    $('#satuan').val('');
    $('#total').val('');
    $('#action').html('Tambah');
    $('#cancel').hide();
  }

  function edit_items(id){
    $.get("{{ url('api/find_pembelian_produk') }}/"+id,function(result){
      $('#produk_id').val(result.fid_produk);
      $('#kode').val(result.kode);
      $('#nama_produk').val(result.nama_produk);
      $('#jumlah').val(result.jumlah);
      $('#harga').val(result.harga);
      $('#satuan').val(result.satuan);
      $('#total').val(result.total);
      $('#action').html('Edit');
      $('#cancel').show();
    });
  }

</script>
@endsection
