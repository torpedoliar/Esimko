@php
  $page='Dashboard';
  $subpage='Dashboard';
@endphp
@section('title')
Dashboard |
@endsection
@extends('layouts.landing_page')
@section('css')
  <style>
  body[data-layout=horizontal] .page-content {
    margin-top:0px;
    padding:0px
  }

  #page-topbar {
    background-color:transparent;
  }

  #page-topbar{
    box-shadow:none
  }

  .slider{
    height:550px;
    background-color: #d1f5ee;
    position: relative;
  }
  .title-fitur{
    position: relative;
    font-size:40px;
    font-weight:600
  }
  .title-fitur span {
    position: relative;
    z-index: 1;
  }
  .title-fitur span:before {
    content: '';
    display: block;
    position: absolute;
    left: 0;
    right: 0;
    bottom: 13px;
    height: 8px;
    background: #67e3a9;
    z-index: -1;
  }
  .desc-fitur{
    font-size:20px;
    font-weight:400;
    letter-spacing:0.8px
  }
  .list-fitur{
    margin: 0;
    padding: 0;
    list-style: none;
  }
  .list-fitur li {
    color: #111736;
    font-size:18px;
    font-weight:400;
    letter-spacing:0.8px
  }
  .list-fitur li i {
    font-size: 25px;
    margin-right: 10px;
    color: #16a085;
  }
  .shapes-container {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
  }
  .bg-shape {
    position: absolute;
    height: 190%;
    width: 100%;
    display: block;
    border-radius: 50px;
    background:#9deadb;
    bottom: 0;
    right: 0;
    -webkit-transform: translate(45%, -35%) rotate(119deg);
    transform: translate(45%, -35%) rotate(119deg);
    z-index: 0;
  }
  .desc-slider{
    font-size:30px;
    font-weight:500;
    letter-spacing:0.5px;
    line-height:35px
  }
  .desc-slider span {
    position: relative;
    z-index: 1;
  }
  .desc-slider span:before {
    content: '';
    display: block;
    position: absolute;
    left: 0;
    right: 0;
    bottom: 8px;
    height: 10px;
    background: #67e3a9;
    z-index: -1;
  }
  .footer {
    color: #ecf0f1;
    background-color: #205072;
  }
  .btn-success {
    color: #205072;
    background-color: rgb(205 247 229);
    border-color: #cdf7e5;
  }
  </style>
@endsection
@section('content')
<div class="slider">
  <div class="shapes-container">
    <div class="bg-shape"></div>
  </div>
  <div class="container-fluid" style="padding-top:130px">
    <div class="row">
      <div class="col-md-6" style="margin-top:100px">
        <div style="font-size:45px;font-weight:500">Selamat Datang</div>
        <div class="desc-slider">
          <span>di Elektronik Sistem Informasi</span><br><span>dan Manajemen Koperasi (esimko)</span>
        </div>
        <div style="font-size:15px;letter-spacing:0.8px;margin-top:10px">Platform digital yang mempermudah anggota dalam melakukan transaksi simpan pinjam, penjualan, dan kredit belanja</div>
      </div>
      <div class="col-md-6">
        <img src="{{asset('assets/images/finance.png')}}" style="width:100%;">
      </div>
    </div>
  </div>
</div>
<section style="background:#205072" class="pt-5 pb-5">
</section>
<section style="background:#ecf0f1" class="pt-5 pb-5">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-4">
        <img src="{{asset('assets/images/simpanan.png')}}" style="width:100%">
      </div>
      <div class="col-md-7">
        <h4 class="title-fitur mb-3 mt-5">
          <span>Simpanan Anggota</span>
        </h4>
        <p class="desc-fitur">Anggota dapat melakukan setoran dan penarikan simpanan secara online dan diverifikasi oleh petugas koperasi melalui fitur simpanan anggota</p>
        <div class="row mt-4">
          <div class="col-md-5">
            <ul class="list-fitur">
              <li><i class="bx bx-check-square"></i>Simpanan Pokok</li>
              <li><i class="bx bx-check-square"></i>Simpanan Wajib</li>
            </ul>
          </div>
          <div class="col-md-5">
            <ul class="list-fitur">
              <li><i class="bx bx-check-square"></i>Simpanan Hari Raya</li>
              <li><i class="bx bx-check-square"></i>Simpanan Sukarela</li>
            </ul>
          </div>
        </div>
        <a href="{{url('')}}" class="btn btn-primary mt-4">Pelajari Lebih Lanjut</a>
      </div>
    </div>
  </div>
</section>
<section class="pt-5 pb-5">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8">
        <h4 class="title-fitur mb-3 mt-4">
          <span>Pinjaman Anggota</span>
        </h4>
        <p class="desc-fitur">Anggota dapat melakukan pinjaman dan pembayaran angsuran secara online dan diverifikasi oleh petugas koperasi melalui fitur pinjaman anggota</p>
        <div class="row mt-4">
          <div class="col-md-8">
            <ul class="list-fitur">
              <li><i class="bx bx-check-square"></i>Pinjaman Jangka Panjang</li>
              <li><i class="bx bx-check-square"></i>Pinjaman Jangka Panjang</li>
              <li><i class="bx bx-check-square"></i>Pinjaman Barang</li>
            </ul>
          </div>
        </div>
        <a href="{{url('')}}" class="btn btn-primary mt-4">Pelajari Lebih Lanjut</a>
      </div>
      <div class="col-md-4">
        <img src="{{asset('assets/images/pinjaman.png')}}" style="width:100%">
      </div>
    </div>
  </div>
</section>
<section style="background:#ecf0f1;padding-bottom:100px" class="pt-5">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-4">
        <img src="{{asset('assets/images/shopping.png')}}" style="width:100%">
      </div>
      <div class="col-md-8">
        <h4 class="title-fitur mb-3 mt-5">
          <span>Poin of Sales</span>
        </h4>
        <p class="desc-fitur">Transaksi penjulan toko atau belanja anggota dapat dilakukan secara online dan dapat melakukan kredit belanja dengan mudah</p>
        <div class="row mt-4">
          <div class="col-md-8">
            <ul class="list-fitur">
              <li><i class="bx bx-check-square"></i>Belanja Barang Toko</li>
              <li><i class="bx bx-check-square"></i>Belanja Konsinyasi</li>
              <li><i class="bx bx-check-square"></i>Belanja Online</li>
            </ul>
          </div>
        </div>
        <a href="{{url('')}}" class="btn btn-primary mt-4">Pelajari Lebih Lanjut</a>
      </div>
    </div>
  </div>
</section>
@endsection
@section('js')
  <script>
  @if(empty(Session::get('useractive')))
  document.getElementById("masuk").classList.remove("btn-success");
  document.getElementById("masuk").classList.add("btn-primary");
  @endif
  $('#esimko-dark').show();
  $('#esimko-white').hide();
  window.onscroll = function() {scrollFunction()};
  function scrollFunction() {
    if (document.body.scrollTop > 80 || document.documentElement.scrollTop > 80) {
      document.getElementById("page-topbar").style.background ="#329D9C";
      @if(empty(Session::get('useractive')))
      document.getElementById("masuk").classList.remove("btn-primary");
      document.getElementById("masuk").classList.add("btn-success");
      @endif
      $('#esimko-dark').hide();
      $('#esimko-white').show();
    } else {
      document.getElementById("page-topbar").style.background = "transparent";
      @if(empty(Session::get('useractive')))
      document.getElementById("masuk").classList.remove("btn-success");
      document.getElementById("masuk").classList.add("btn-primary");
      @endif
      $('#esimko-dark').show();
      $('#esimko-white').hide();
    }
  }
  </script>
@endsection
