@php
  $page='Berita';
  $subpage='Berita';
@endphp
@extends('layouts.main')
@section('title')
  Berita dan Informasi |
@endsection
@section('css')
  <style>
  .content p{
    font-size: 15px;
    font-weight:300;
  }
  .list-berita{
    padding:20px 0px;
    border-bottom: 1px solid #e6e6e6;
    display: block
  }
  .list-berita:hover h6{
    color:#429d9c
  }
  .list-berita .produk-wrapper{
    margin:0px
  }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/news.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Berita dan Informasi</h4>
        <p class="text-muted m-0">Menampilkan data berita dan informasi yang diinput oleh pengurus untuk anggota</p>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h3 class="mb-3">{{$data['berita']->judul}}</h3>
          <img src="{{(!empty($data['berita']->gambar) ? asset('storage/'.$data['berita']->gambar) : asset('assets/images/produk-default.jpg')) }}" style="width:100%" />
          <div class="content mt-3">{!!$data['berita']->content!!}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <h5>Attachment Berita</h5>
      @foreach ($data['attachment'] as $key => $value)
      <a class="list-berita" href="{{asset('storage/'.$value->attachment)}}">
        <div class="media">
          <img src="{{asset('assets/images/file.png')}}" style="width:60px" class="mr-2">
          <div class="media-body align-self-center">
            <h6 style="font-size:14px;font-weight:400">{{$value->judul}}</h6>
          </div>
        </div>
      </a>
      @endforeach
    </div>
  </div>
</div>
@endsection
@section('js')
  <script>

  </script>
@endsection
