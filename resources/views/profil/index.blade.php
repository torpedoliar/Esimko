@php
  $page='Dashboard';
  $subpage=$tab;
@endphp
@extends('layouts.main')
@section('title')
Profil |
@endsection
@section('css')
  <style>
  .menu{
    list-style-type: none;
    margin: 0;
    padding: 0;
    width: 100%;
  }
  .menu li a i{
    font-size:23px;
    margin-right:10px;
    font-weight: 400;
    align-items:center
  }
  .menu li a {
    display: flex;
    color: #000;
    padding: 8px 16px;
    text-decoration: none;
    align-items:center
  }
  .menu li:last-child a{
    border-bottom:none;
  }

  .menu li a:hover {
    background-color:#f2f2f5;
  }

  .menu li a.active {
    color: #429d9c;
    font-weight: 500;
  }

  .menu li a:hover:not(.active) {
    background-color: #f2f2f5;
  }
  .table-informasi  tr td,
  .table-informasi  tr th{
    vertical-align: middle;
  }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/profile.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Profil Anggota</h4>
        <p class="text-muted m-0">Menampilkan profil dari anggota koperasi</p>
      </div>
    </div>
  </div>
  <div class="row mb-2 mt-4">
    <div class="col-auto">
      <div style="position:sticky;top:180px;width:250px;z-index:0">
        <ul class="menu">
          <li>
            <a href="{{url('main/profil?tab=informasi')}}" class="{{($subpage == 'informasi' ? 'active' : '') }}">
              <i class="bx bxs-user" style="color:#16a085"></i>
              <div>Informasi Personal</div>
            </a>
          </li>
          <li>
            <a href="{{url('main/profil?tab=gaji_pokok')}}" class="{{($subpage == 'gaji_pokok' ? 'active' : '') }}">
              <i class="bx bxs-wallet" style="color:#2980b9"></i>
              <div>Riwayat Gaji</div>
            </a>
          </li>
          <li>
            <a href="{{url('main/profil?tab=ubah_password')}}" class="{{($subpage == 'ubah_password' ? 'active' : '') }}">
              <i class="bx bxs-lock-alt" style="color:#f39c12"></i>
              <div>Ubah Password</div>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <div class="col">
      @include('profil.'.$subpage)
    </div>
  </div>
</div>
@endsection
@section('js')
  <script>
  cancel_personal();
  function cancel_personal(){
    $('#personal .show').show();
    $('#personal  .form').hide();
  }
  function edit_personal(){
    $('#personal .show').hide();
    $('#personal .form').show();
  }

  cancel_kontak();
  function cancel_kontak(){
    $('#kontak .show').show();
    $('#kontak  .form').hide();
  }
  function edit_kontak(){
    $('#kontak .show').hide();
    $('#kontak .form').show();
  }
  </script>
@endsection
