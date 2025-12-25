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
  <div class="card">
    <div class="card-header">
      <div class="row">
        <div class="col-md-9">
          <form action="" method="get">
            <div class="input-group">
              <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Produk">
              <div class="input-group-append">
                <button class="btn btn-dark" type="submit">Search</button>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-3">
          <a class="btn btn-primary btn-block" href="{{url('kasir/produk/form')}}">Tambah Produk</a>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-middle table-bordered table-hover">
          <thead class="thead-light">
            <tr>
              <th>No</th>
              <th>Kode</th>
              <th>Nama Produk</th>
              <th>Kategori</th>
              <th class="center">Stok</th>
              <th class="center">Satuan</th>
              <th style="text-align:right">Harga</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($data['produk'] as $key => $value)
            <tr>
              <td>{{ $data['produk']->firstItem() + $key }}</td>
              <td>{{$value->kode}}</td>
              <td>{{$value->nama_produk}}</td>
              <td>{{$value->nama_kategori}}</td>
              <td class="center">{{$value->stok}}</td>
              <td class="center">{{$value->satuan}}</td>
              <td style="text-align:right">{{number_format($value->harga_satuan,0,',','.')}}</td>
              <td style="width:1px;white-space:nowrap">
                <div class="text-center">
                  <a href="{{url('kasir/produk/form?id='.$value->id)}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                  <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                  <form action="{{url('kasir/produk/proses')}}" method="post" id="hapus{{$value->id}}">
                    {{ csrf_field()}}
                    <input type="hidden" name="id" value="{{$value->id}}">
                    <input type="hidden" name="action" value="delete">
                  </form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div style="margin-top:20px">
        {{ $data['produk']->links('include.pagination', ['pagination' => $data['produk']] ) }}
      </div>
    </div>
  </div>
</div>

@endsection
@section('js')
<script>

</script>
@endsection
