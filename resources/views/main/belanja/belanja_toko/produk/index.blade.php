@php
  $subpage='Belanja Toko';
@endphp
@extends('main.belanja.layout')
@section('content_belanja')
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
    <button class="btn btn-primary btn-block" >Filter Data</button>
  </div>
</div>
<div class="row mt-4">
  @foreach ($data['produk'] as $key => $value)
  <div class="col-xl-3 col-sm-4 col-6">
    <a href="{{url('belanja/produk/detail?id='.$value->kode)}}">
      <div class="card">
        <div class="produk">
          <img class="card-img-top img-fluid" src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}">
          <div class="card-body">
            <h6 class="title"><a href="" class="text-secondary">{{$value->nama_produk}}</a></h6>
            {{-- <div class="mt-3">
              <span class="discount">20%</span>
              <span class="text-muted font-size-10"><del>Rp 50.000</del></span>
            </div> --}}
            <h6 class="price mt-2">Rp {{number_format($value->harga_satuan,0,',','.')}}</h6>
          </div>
        </div>
      </div>
    </a>
  </div>
  @endforeach
</div>
@endsection
