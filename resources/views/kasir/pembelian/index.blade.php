@php
  $page='Kasir Toko';
  $subpage='Kulak Barang';
@endphp
@extends('layouts.admin')
@section('title')
  Kulak Barang |
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
  <div class="card">
    <div class="card-header">
      <div class="row">
        <div class="col-md-9">
          <form action="" method="get">
            <div class="input-group">
              <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Transaksi">
              <div class="input-group-append">
                <button class="btn btn-dark" type="submit">Search</button>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-3">
          <a class="btn btn-primary btn-block" href="{{url('kasir/kulakan/form')}}">Tambah Transaksi</a>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-middle table-bordered table-hover">
          <thead class="thead-light">
            <tr>
              <th>No</th>
              <th>Tanggal</th>
              <th>No. Pembelian</th>
              <th>Supplier</th>
              <th class="center">Jumlah<br>Item</th>
              <th style="text-align:right">Total (Rp)</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($data['pembelian'] as $key => $value)
            <tr>
              <td>{{ $data['pembelian']->firstItem() + $key }}</td>
              <td>{{\App\Helpers\GlobalHelper::tgl_indo($value->tanggal)}}</td>
              <td>{{$value->no_pembelian}}</td>
              <td>{{$value->nama_supplier}}</td>
              <td class="center">{{$value->jumlah}}</td>
              <td style="text-align:right">{{number_format($value->total,0,',','.')}}</td>
              <td style="width:1px;white-space:nowrap">
                <div class="text-center">
                  <a href="{{url('kasir/kulakan/form?id='.$value->id)}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                  <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                  <form action="{{url('kasir/kulakan/proses')}}" method="post" id="hapus{{$value->id}}">
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
        {{ $data['pembelian']->links('include.pagination', ['pagination' => $data['pembelian']] ) }}
      </div>
    </div>
  </div>
</div>

@endsection
@section('js')
<script>

</script>
@endsection
