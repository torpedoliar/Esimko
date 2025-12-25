@php
  $app='master';
  $page='Pengaturan';
  $subpage='Metode Pembayaran';
@endphp
@extends('layouts.admin')
@section('title')
  Metode Pembayaran |
@endsection
@section('css')
  <style>
  .logo-wrapper {
    border:2px solid #e2e2e2 ;
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: none;
    margin: 0 10px 0px 0;
    transition: all .3s ease;
    background: whitesmoke;
    padding:5px
  }
  .logo-wrapper img {
    height: 100%;
    width: 100%;
    transition: all .3s ease;
    object-fit: contain;
  }

  .logo-wrapper .upload-button {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    cursor:pointer;
  }

  .logo-wrapper .file-upload{
    opacity: 0;
    pointer-events: none;
    position: absolute;
  }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/card-payment.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Metode Pembayaran</h4>
        <p class="text-muted m-0">Menampilkan metode pembayaran dari setiap transaksi</p>
      </div>
    </div>
  </div>
  <div class="card mt-3">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-middle table-custom">
          <thead>
            <tr>
              <th></th>
              <th>Metode<br>Pembayaran</th>
              <th>Nomor<br>Rekening</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($data['rekening-pembayaran'] as $key => $value)
              <tr>
                <td>
                  <div class="media">
                    <div class="logo-wrapper" style="width:50px;height:50px;background:transparent;border:none">
                      <img src="{{(!empty($value->logo) ? asset('storage/'.$value->logo) : asset('assets/images/image-default.png') )}}" alt="">
                    </div>
                    <div class="media-body align-self-center">
                      <h5 class="text-truncate font-size-14">{{$value->keterangan}}</h5>
                      <p class="text-muted mb-0 mt-1 font-size-12">{{$value->metode_pembayaran}}</p>
                    </div>
                  </div>
                </td>
                <td>
                  @if(!empty($value->no_rekening))
                  <h6>{{$value->no_rekening}}</h6>
                  <span>{{$value->atas_nama}}</span>
                  @else
                  <h6>-</h6>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')
  <script>

  </script>
@endsection
