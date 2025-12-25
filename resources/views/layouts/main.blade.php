@php
  use Illuminate\Support\Facades\Session;
@endphp
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>@yield('title')eSIMKO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="" name="description" />
    <meta content="" name="author" />
    <link rel="shortcut icon" href="{{asset('assets/images/favicon.ico')}}">
		<link href="{{asset('assets/libs/sweetalert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/libs/jstree/themes/default/style.min.css') }}" rel="stylesheet" />
    <link href="{{asset('assets/libs/dropify/dist/css/dropify.min.css')}}" rel="stylesheet" >
		<link href="{{asset('assets/libs/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/css/bootstrap.min.css')}} " id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/app.min.css')}} " id="app-style" rel="stylesheet" type="text/css" />
		<style>
    .bootstrap-touchspin .btn-primary {
      color: #495057 !important;
      background-color: #eff2f7 !important;
      border-color: #ced4da !important;
    }
    .bootstrap-touchspin .form-control{
      border-color:#ced4da !important;
    }

    .h1, .h2, .h3, .h4, .h5, .h6,
    h1, h2, h3, h4, h5, h6 {
      margin-bottom: 0px;
      font-weight: 500;
      line-height: 1.2;
    }
    #page-topbar {
      background-color: #329D9C;
    }
    .info-content{
      font-size: 16px;
      font-weight:400
    }
    .list-content{
      margin-bottom: .5rem
    }
    .list-content span{
      color:#7b7b7b
    }
    .card-header{
      padding: 1rem !important;
    }
    .navbar-header {
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      -ms-flex-pack: justify;
      -webkit-box-pack: justify;
      justify-content: space-between;
      -webkit-box-align: center;
      -ms-flex-align: center;
      align-items: center;
      margin: 0 auto;
      height: 100px;
      padding: 0 calc(24px / 2) 0 0;
    }
    .topnav {
      background: #205072;
      padding: 0 calc(24px / 2);
      -webkit-box-shadow: 0 0.75rem 1.5rem rgb(18 38 63 / 3%);
      box-shadow: 0 0.75rem 1.5rem rgb(18 38 63 / 3%);
      margin-top: 100px;
      position: fixed;
      left: 0;
      right: 0;
      z-index: 100;
    }

    body[data-layout=horizontal] .page-content {
      margin-top: 0px;
      padding: calc(55px + 24px) calc(24px / 2) 60px calc(24px / 2);
    }

    .navbar-header .dropdown.show .header-item {
      background-color: rgb(0 95 94 / 22%);
      color: #ffffff;
      width:100%;
      text-align: right
    }

    .topnav .navbar-nav .nav-item .nav-link.active {
      color: #ade9cd;
    }

    .topnav .navbar-nav .nav-link {
      font-size: 17px;
      position: relative;
      padding: 1rem 1.3rem;
      color: #ffffff;
    }

    .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
      color: #fff;
      background-color: #205072;
    }

    .topnav .navbar-nav .nav-link i {
      font-size: 17px;
    }

    .topnav .navbar-nav .nav-link:focus,
    .topnav .navbar-nav .nav-link:hover {
      color: #ade9cd;
      background-color: transparent;
    }

    .card-header {
      padding: .75rem 1.25rem;
      margin-bottom: 0;
      background-color: #eaecef;
      border-bottom: 0 solid #f6f6f6;
    }
    .card-footer {
      background-color: #eaecef;
    }
    .card {
      margin-bottom: 20px;
    }

    .header-item:hover {
      color: #2c3e4f;
    }
    .noti-icon i {
      color: #ecfbf6;
    }
    .page-item.active .page-link {
      background-color: #45a086;
      border-color: #45a086;
    }
    .btn-success {
      color: #fff;
      background-color: #469c49;
      border-color: #469c49;
    }

    .produk-wrapper,
    .avatar-wrapper {
      position: relative;
      width: 150px;
      height: 150px;
      border-radius: 0px;
      overflow: hidden;
      box-shadow: none;
      margin: 0 10px 30px 0;
      transition: all .3s ease;
      background: whitesmoke;
    }

    .avatar-wrapper img {
      height: 100%;
      width: 100%;
      transition: all .3s ease;
      object-fit: scale-down;
    }
    .produk-wrapper img {
      height: 100%;
      width: 100%;
      transition: all .3s ease;
      object-fit:cover;
    }

    .produk-wrapper .upload-button,
    .avatar-wrapper .upload-button {
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      cursor:pointer;
    }
    .produk-wrapper .file-upload,
    .avatar-wrapper .file-upload {
      opacity: 0;
      pointer-events: none;
      position: absolute;
    }
    .datepicker {
      border: 1px solid #ced4da;
      padding: 8px;
      z-index: 9999!important;
    }

    .jstree-default .jstree-wholerow-clicked {
      background: #b0ffa4;
      background: -webkit-linear-gradient(top,#b0ffa4 0,#b0ffa4 100%);
      background: linear-gradient(to bottom,#b0ffa4 0,#b0ffa4 100%);
    }

    .jstree-default .jstree-search {
      font-style: italic;
      color: #199020;
      font-weight: 500;
    }

    /* Modal CSS Custom */
    .modal.right .modal-dialog {
      position: fixed;
      margin: auto;
      width: 400px;
      height: 100%;
      -webkit-transform: translate3d(0%, 0, 0);
          -ms-transform: translate3d(0%, 0, 0);
           -o-transform: translate3d(0%, 0, 0);
              transform: translate3d(0%, 0, 0);
    }
    .modal.right .modal-content {
      height: 100%;
      overflow-y: auto;
      border-radius:0px;
    }

    .modal.right .modal-body {
      padding: 15px;
    }

    .modal.right.fade .modal-dialog{
      right:-400px;
      -webkit-transition: opacity 0.3s linear, right 0.3s ease-out;
         -moz-transition: opacity 0.3s linear, right 0.3s ease-out;
           -o-transition: opacity 0.3s linear, right 0.3s ease-out;
              transition: opacity 0.3s linear, right 0.3s ease-out;
    }

    .modal.right.fade.show .modal-dialog{
      right: 0;
    }

    .modal-content {
      border: none;
      border-radius:0px;
    }

    .btn-dark {
      color: #fff;
      background-color: #205072;
      border-color: #205072;
    }

    .pull-right{
      float: right;
    }

    .table-middle tbody tr td{
      vertical-align: middle;
    }
    .avatar-sm {
      height: 3.8rem;
      width: 3.8rem;
    }
    .badge {
      padding: 0.5em;
      border-radius: 0px  ;
    }
    .center{
      text-align: center;
    }
    .mm-active .active i {
      color: #fff!important;
    }

    .btn-primary {
      color: #fff;
      background-color: #16a085;
      border-color: #16a085;
    }
    .btn-primary:hover {
      color: #fff;
      background-color: #429d9c;
      border-color: #429d9c;
    }
    .btn-primary.disabled,
    .btn-primary:disabled {
      color: #fff;
      background-color: #16a085;
      border-color: #16a085;
    }

    .btn-primary.focus, .btn-primary:focus {
      color: #fff;
      background-color: #429d9c;
      border-color: #429d9c;
      -webkit-box-shadow: none;
      box-shadow: none;
    }
    .btn-primary:not(:disabled):not(.disabled).active:focus,
    .btn-primary:not(:disabled):not(.disabled):active:focus,
    .show>.btn-primary.dropdown-toggle:focus,
    .btn-primary:not(:disabled):not(.disabled).active,
    .btn-primary:not(:disabled):not(.disabled):active,
    .show>.btn-primary.dropdown-toggle {
      color: #fff;
      background-color: #429d9c;
      border-color: #429d9c;
      -webkit-box-shadow: none;
      box-shadow: none;
    }

    .btn-success.focus, .btn-success:focus {
      color: #fff;
      background-color: #84b936;
      border-color: #84b936;
      -webkit-box-shadow: none;
      box-shadow: none;
    }


    .btn-success {
      color: #ffffff;
      background-color: #a6d65e;
      border-color: #a5d55e;
    }
    .btn-success:hover {
      color: #fff;
      background-color: #84b936;
      border-color: #84b936;
    }
    .header-item {
      color: #f4fdfa;
      height:100px
    }
    .dropdown-menu {
      border-radius: 0px 0px .2rem .2rem;
    }
    .dropdown-icon-item img {
      height: 80px;
      margin-bottom: 10px
    }
    .dropdown-icon-item span{
      line-height: 17px
    }
    .table-informasi tr td{
      text-align: right;
    }
    .table-informasi td, .table-informasi th {
      padding: .4rem .75rem;
      vertical-align: top;
      border-top: 1px solid #eff2f7;
    }
    .table-informasi th {
      font-weight: 500;
      letter-spacing: 0.2px;
      color:#3e3e3e
    }

    .table-custom{
      border-spacing: 0 7px;
      border-collapse: separate;
    }

    .table-custom thead tr {
      background-color:#fff;
    }
    .table-custom thead th {
      vertical-align: middle !important;
      border-bottom:none;
      font-weight: 600;
      letter-spacing: .5px;
      color:#429d9c
    }

    .table-custom th:first-child {
      border-top-left-radius: .5rem;
      border-bottom-left-radius: .5rem;
    }

    .table-custom td:first-child {
      border-top-left-radius: .5rem;
      border-bottom-left-radius: .5rem;
      border-left:5px solid #ced4da;
    }
    .table-custom td:first-child,
    .table-custom th:first-child{
      padding-left: 1.8rem !important
    }

    .table-custom td:last-child,
    .table-custom thead th:last-child {
      border-top-right-radius: .5rem;
      border-bottom-right-radius: .5rem;
    }
    .table-custom tbody tr {
      background-color:#fff;
    }
    .table-custom tbody tr:hover{
      background-color:rgb(52 73 95 / 8%);
      cursor: pointer;
    }
    .table-custom td, .table-custom th{
      border-top:none !important;
      padding-top: .5rem !important;
      padding-bottom: .5rem !important;
      padding-left: 1rem !important;
      padding-right: 1rem !important;
    }
    .indikator-status{
      width:10px;
      height:10px;
      margin-right:5px;
    }
    .alert {
      position: relative;
      padding: 1rem;
      margin-bottom: 1rem;
      border: 1px solid transparent;
      border-radius:0
    }
    .verti-timeline .event-list {
      position: relative;
      padding: 0 0 0px 20px;
    }
    .content-breadcrumb{
      position: relative;
      background:rgb(255 255 255 / 60%);
      margin:-25px -12px;
      padding:25px 12px;
    }
    .avatar-md {
      height: 3.5rem;
      width: 3.5rem;
    }
		</style>
		@yield('css')
  </head>

  <body data-layout="horizontal" >
    <div id="layout-wrapper">
      <header id="page-topbar">
        <div class="navbar-header">
          <div class="d-flex">
            <div class="navbar-brand-box">
              <a href="{{url('')}}">
                <img src="{{asset('assets/images/logo-esimko-landing-page.png')}}" alt="" style="height:70px;" >
              </a>
            </div>
          </div>
          <div class="d-flex">
            @if(!empty(Session::get('useractive')))
            <div class="d-flex">
              <div class="dropdown d-inline-block" style="min-width:250px;text-align:right">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <div class="d-none d-xl-inline-block" style="text-align:right"><span style="font-weight:400;font-size:18px">{{Session::get('useractive')->nama_lengkap}}</span><br>No. {{Session::get('useractive')->no_anggota}}</div>
                  <img class="rounded-circle header-profile-user"  src="{{asset('assets/images/user-avatar-placeholder.png')}}" style="margin-left: 5px;vertical-align:top;height:50px;width:50px">
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                  <a class="dropdown-item" href="{{url('/')}}"><i class="bx bx-grid-alt font-size-16 align-middle mr-1"></i> Landing Page</a>
                  <a class="dropdown-item" href="{{url('main/profil')}}"><i class="bx bx-user font-size-16 align-middle mr-1"></i> Ubah Profil</a>
                  <a class="dropdown-item" href="{{url('main/profil?tab=ubah_password')}}"><i class="bx bx-lock-alt font-size-16 align-middle mr-1"></i> Ubah Password</a>
                  @if(count(\App\Helpers\GlobalHelper::get_user_akses(Session::get('useractive')->id)) > 1)
                  <a class="dropdown-item" href="#user_akses" data-toggle="modal"><i class="bx bx-wrench font-size-16 align-middle mr-1"></i>User Akses</a>
                  @endif
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" href="{{url('auth/logout')}}"><i class="bx bx-power-off font-size-16 align-middle mr-1 text-danger"></i> Logout</a>
                </div>
              </div>
              @if(Session::get('useractive')->hak_akses!=2)
              <div class="dropdown d-lg-inline-block ml-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="bx bx-customize"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" style="width:400px">
                  <div class="px-lg-2">
                    <div class="row no-gutters">
                      <div class="col">
                        <a class="dropdown-icon-item" href="{{url('dashboard')}}">
                          <img src="{{asset('assets/images/analytics.png')}}">
                          <span>Main<br>Dashboard</span>
                        </a>
                      </div>
                      <div class="col">
                        <a class="dropdown-icon-item" href="{{url('anggota')}}">
                          <img src="{{asset('assets/images/folder.png')}}">
                          <span>Data Master<br>dan Pengaturan</span>
                        </a>
                      </div>
                      <div class="col">
                        <a class="dropdown-icon-item" href="{{url('simpanan/sukarela')}}">
                          <img src="{{asset('assets/images/simpan-pinjam.png')}}">
                          <span>Simpanan<br>dan Pinjaman</span>
                        </a>
                      </div>
                    </div>
                    <div class="row no-gutters">
                      <div class="col">
                        <a class="dropdown-icon-item" href="{{url('manajemen_stok/barang')}}">
                          <img src="{{asset('assets/images/warehouse.png')}}">
                          <span>Manajemen<br>Barang</span>
                        </a>
                      </div>
                      <div class="col">
                        <a class="dropdown-icon-item" href="{{url('pos/penjualan')}}">
                          <img src="{{asset('assets/images/cashier.png')}}">
                          <span>Poin of Sales<br>(POS)</span>
                        </a>
                      </div>
                      <div class="col">
                        <a class="dropdown-icon-item" href="{{url('keuangan/akun')}}">
                          <img src="{{asset('assets/images/accounting.png')}}">
                          <span>Laporan dan<br>Akuntansi</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endif
              <div class="dropdown d-none d-lg-inline-block">
                <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                  <i class="bx bx-fullscreen"></i>
                </button>
              </div>
              {{-- <div class="dropdown d-none d-lg-inline-block">
                <button type="button" class="btn header-item noti-icon waves-effect" onclick="">
                  <i class="bx bx-cart-alt"></i>
                </button>
              </div>
              <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="bx bx-bell bx-tada"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0" aria-labelledby="page-header-notifications-dropdown">
                  <div class="p-3">
                    <div class="row align-items-center">
                      <div class="col">
                        <h6 class="m-0">Notifications</h6>
                      </div>
                      <div class="col-auto">
                        <a href="{{url('')}}" class="small"> View All</a>
                      </div>
                    </div>
                  </div>
                  <div data-simplebar style="max-height:230px;">
                    <a href="" class="text-reset notification-item">
                      <div class="media">
                        <div class="avatar-xs mr-3">
                          <span class="avatar-title bg-primary rounded-circle font-size-16">
                            <i class="bx bx-cart"></i>
                          </span>
                        </div>
                        <div class="media-body">
                          <h6 class="mt-0 mb-1">Your order is placed</h6>
                          <div class="font-size-12 text-muted">
                            <p class="mb-1">If several languages coalesce the grammar</p>
                            <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 3 min ago</p>
                          </div>
                        </div>
                      </div>
                    </a>
                  </div>
                </div>
              </div> --}}
            </div>
            @else
            <div class="dropdown d-none d-lg-inline-block ml-1">
              <button type="button" class="btn btn-dark" onclick="location.href='{{url('auth/register')}}';">DAFTAR ANGGOTA</button>
              <button type="button" class="btn btn-success" onclick="location.href='{{url('auth/login')}}';">MASUK</button>
            </div>
            @endif
          </div>
        </div>
      </header>
      <div class="topnav">
        <div class="container-fluid">
          <nav class="navbar navbar-light navbar-expand-lg topnav-menu">
            <div class="collapse navbar-collapse" id="topnav-menu-content">
              <ul class="navbar-nav active">
                <li class="nav-item">
                  <a class="nav-link {{($page=='Dashboard' ? 'active' : '')}}" href="{{url('main/dashboard')}}" ><i class="bx bx-grid-alt mr-2"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link  {{($page=='Simpanan' ? 'active' : '')}}" href="{{url('main/simpanan')}}"><i class="bx bx-wallet mr-2"></i>Simpanan</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{($page=='Pinjaman' ? 'active' : '')}}" href="{{url('main/pinjaman')}}" ><i class="bx bx-credit-card mr-2"></i>Pinjaman</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{($page=='Angsuran' ? 'active' : '')}}" href="{{url('main/angsuran')}}" ><i class="bx bx-calendar mr-2"></i>Angsuran</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{($page=='Belanja' ? 'active' : '')}}" href="{{url('main/belanja')}}" ><i class="bx bx-cart-alt mr-2"></i>Belanja</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{($page=='Berita' ? 'active' : '')}}" href="{{url('main/berita')}}" ><i class="bx bx-news mr-2"></i>Berita</a>
                </li>
              </ul>
            </div>
          </nav>
        </div>
      </div>
      <div style="padding-top:100px">
        <div class="page-content">
          @yield('content')
        </div>
      </div>
      <footer class="footer">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-6">
              2021 © eSIMKO
            </div>
            <div class="col-sm-6">
              <div class="text-sm-right d-none d-sm-block">KOPKAR SATYA SEJAHTERA</div>
            </div>
          </div>
        </div>
      </footer>
    </div>
    <div class="modal fade" id="user_akses">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="title">Ubah User Akses</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="{{url('auth/user_akses/proses')}}" method="post">
            {{ csrf_field() }}
            <div class="modal-body">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>User Akses</th>
                  </tr>
                </thead>
                @foreach (\App\Helpers\GlobalHelper::get_user_akses(Session::get('useractive')->id) as $key => $value)
                <tr>
                  <td style="width:1px;white-space:nowrap">
                    <input type="radio" name="hak_akses" value="{{$value->id}}" {{(Session::get('useractive')->hak_akses==$value->id ? "checked" : "")}}>
                  </td>
                  <td>{{$value->hak_akses}}</td>
                </tr>
                @endforeach
              </table>
            </div>
            <div class="modal-footer">
              <input type="hidden" name="anggota" value="{{Session::get('useractive')->id}}">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Change</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script src="{{asset('assets/libs/jquery/jquery.min.js')}}"></script>
    <script src="{{asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/libs/metismenu/metisMenu.min.js')}}"></script>
    <script src="{{asset('assets/libs/simplebar/simplebar.min.js')}}"></script>
    <script src="{{asset('assets/libs/node-waves/waves.min.js')}}"></script>
		<script src="{{asset('assets/libs/select2/js/select2.min.js')}}"></script>
    <script src="{{asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}} "></script>
    <script src="{{asset('assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')}}"></script>
    <script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
    <script src="{{asset('assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js')}}"></script>
    <script src="{{asset('assets/libs/highcharts/highcharts.js') }}" type="text/javascript" ></script>
    <script src="{{asset('assets/libs/highcharts/highcharts-more.js') }}" type="text/javascript" ></script>
    <script src="{{asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
    <script src="{{asset('assets/libs/dropify/dist/js/dropify.min.js')}}"></script>
    <script src="{{asset('assets/js/autoNumeric.js')}} " type="text/javascript"></script>
    <script src='{{asset('assets/libs/jstree/jstree.js') }}' type='text/javascript' ></script>
		<script src="{{asset('assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>
		<script src="{{asset('assets/js/pages/sweet-alerts.init.js')}}"></script>
    <script src="{{asset('assets/js/app.js')}}"></script>
		<script>
			@if(\Illuminate\Support\Facades\Session::has('message'))
				Swal.fire({
					text: '{{ \Illuminate\Support\Facades\Session::get('message') }}',
					type: '{{ \Illuminate\Support\Facades\Session::get('message_type') }}',
					showCloseButton: false,
	  			showCancelButton: false,
					showConfirmButton: false,
					timer: 1500
				});
			@endif

			$('.select2').select2();
      $('.autonumeric').autoNumeric({mDec: '2',aPad:false,vMin:'-9999999999999999999999999',vMax:'9999999999999999999999999'});

			$('.monthpicker').datepicker({
				autoclose: true,
				format: "mm-yyyy",
				startView: "months",
				minViewMode: "months"
			});

			$('.datepicker').datepicker({
				autoclose: true,
				format: "dd-mm-yyyy",
			});

      $('.dropify').dropify({
        messages: {
          'default': 'Drag and drop a file here or click',
          'replace': 'Drag and drop or click to replace',
          'remove':  'Remove',
          'error':   'Ooops, something wrong happended.'
        }
      });

      function confirmDelete(id){
		    Swal.fire({
		      title: "Are you sure?",
		      text: "Apakah anda yakin ingin menghapus data ini",
		      type:"question",
          showCancelButton: true,
          confirmButtonColor: '#d63030',
          cancelButtonColor: '#cbcbcb',
          confirmButtonText: 'Yes, delete it!'
		    }).then((result) => {
          if (result.value == true) {
            $('#hapus'+id).submit();
          }
        });
		  }
		</script>
		@yield('js')
  </body>
</html>
