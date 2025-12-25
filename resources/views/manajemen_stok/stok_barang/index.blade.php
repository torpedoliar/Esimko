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
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/product.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Data Barang</h4>
          <p class="text-muted m-0">Menampilkan data barang atau produk yang dijual ditoko secara online atau offline</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
        <form action="" method="get" >
          <select class="select2" name="kategori" style="width:100%" onchange="javascript:submit()">
            <option value="all" >Semua Kategori</option>
            @foreach ($data['kategori'] as $key => $value)
            <option value="{{$value->id}}" {{($kategori == $value->id ? 'selected' : '')}}>{{$value->nama_kategori}}</option>
            @endforeach
          </select>
        </form>
      </div>
      <div class="col-md-7">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Produk">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-2">
        <a class="btn btn-primary btn-block" href="{{url('manajemen_stok/barang/form')}}">Tambah Barang</a>
      </div>
    </div>
  </div>
  @if(count($data['produk'])==0)
  <div style="width:100%;text-align:center">
    <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
    <h4 class="mt-2">DATA BARANG TIDAK DITEMUKAN</h4>
  </div>
  @else
  <div class="table-responsive">
    <table class="table table-middle table-custom">
      <thead>
        <tr>
          <th>Produk</th>
          <th>Kategori</th>
          <th class="center">Stok</th>
          <th class="center">Retur</th>
          <th class="center">Terjual</th>
          <th class="center">Sisa</th>
          {{-- <th class="center">Satuan</th> --}}
          <th style="text-align:right">Harga Jual</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['produk'] as $key => $value)
        <tr>
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
          <td>{{$value->nama_kategori}}</td>
          <td class="center">{{$value->stok}}<br>{{$value->satuan}}</td>
          <td class="center">{{$value->retur}}<br>{{$value->satuan}}</td>
          <td class="center">{{$value->terjual}}<br>{{$value->satuan}}</td>
          <td class="center">{{$value->sisa}}<br>{{$value->satuan}}</td>
          {{-- <td class="center">{{$value->satuan}}</td> --}}
          <td style="text-align:right">{{number_format($value->harga_satuan,0,',','.')}}</td>
          <td style="width:1px;white-space:nowrap">
            <div class="text-center">
              <a href="{{url('manajemen_stok/barang/form?id='.$value->id)}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
              <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
              <form action="{{url('manajemen_stok/barang/proses')}}" method="post" id="hapus{{$value->id}}">
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
  <div class="mt-3 mb-3">
    {{ $data['produk']->links('include.pagination', ['pagination' => $data['produk']] ) }}
  </div>
  @endif
</div>

@endsection
@section('js')
<script>

</script>
@endsection
