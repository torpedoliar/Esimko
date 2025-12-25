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
    .h1, .h2, .h3, .h4, .h5, .h6,
    h1, h2, h3, h4, h5, h6 {
      margin-bottom: 0px;
      font-weight: 500;
      line-height: 1.2;
    }
    #page-topbar {
      background-color: #329D9C;
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
      padding: calc(55px + 24px) calc(24px / 2) 60px calc(24px / 2);
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
    .navbar-header .dropdown.show .header-item {
      background-color: #4cb597;
      color: #2c3e4f;
    }
    .header-item:hover {
      color: #2c3e4f;
    }
    .noti-icon i {
      color: #343a40;
    }
    .page-item.active .page-link {
      background-color: #2ecc71;
      border-color: #2ecc71;
    }
    .btn-success {
      color: #fff;
      background-color: #469c49;
      border-color: #469c49;
    }
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
    .avatar-wrapper .upload-button {
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      cursor:pointer;
    }
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
      background-color:#007a62;
      border-color:#007a62;
    }
    .btn-primary.disabled,
    .btn-primary:disabled {
      color: #fff;
      background-color: #2ecc71;
      border-color: #2ecc71;
    }

    .btn-primary.focus, .btn-primary:focus {
      color: #fff;
      background-color: #2ecc71;
      border-color: #2ecc71;
      -webkit-box-shadow: none;
      box-shadow: none;
    }
    .btn-primary:not(:disabled):not(.disabled).active,
    .btn-primary:not(:disabled):not(.disabled):active,
    .show>.btn-primary.dropdown-toggle {
      color: #fff;
      background-color: #2ecc71;
      border-color: #2ecc71;
    }

    .btn-success.focus, .btn-success:focus {
      color: #fff;
      background-color: rgb(0 0 0 / 50%);
      border-color: transparent;
      -webkit-box-shadow: none;
      box-shadow: none;
    }
    .btn-success:not(:disabled):not(.disabled).active,
    .btn-success:not(:disabled):not(.disabled):active,
    .show>.btn-primary.dropdown-toggle {
      color: #fff;
      background-color: rgb(0 0 0 / 50%);
      border-color: transparent;
    }
    .btn-success {
      color: #ffffff;
      background-color: rgb(0 0 0 / 28%);
      border-color: transparent;
    }
    .btn-success:hover {
      color: #fff;
      background-color: rgb(0 0 0 / 50%);
      border-color: transparent;
    }
    .header-item {
      color: #f4fdfa;
      height:100px
    }
    .dropdown-menu {
      border-radius: 0px 0px .2rem .2rem;
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
    .dropdown-icon-item img {
      height: 80px;
      margin-bottom: 10px
    }
    .dropdown-icon-item span{
      line-height: 17px
    }
    .noti-icon i {
      color: #ffffff;
    }

    /* ============ Whatsapp Popoup ================= */

    @keyframes pulse {
      0% {
          transform: scale(1, 1);
      }
      50% {
          opacity: 0.3;
      }
      100% {
          transform: scale(1.3);
          opacity: 0;
      }
    }

    .whatsapp-area {
        display: flex;
        flex-direction: row;
        justify-content: flex-end;
        align-content: flex-end;
        height: auto;
        position: fixed;
        z-index: 1;
        bottom: 30px;
        right: 30px;
        padding: 0;
        margin: 0px;
    }

    .chat-button-area {
        display: block;
        position: absolute;
        bottom: 0;
    }
    .whatsapp-btn {
      display: block;
      justify-content: center;
      align-content: center;
      width: 60px;
      height: 60px;
      font-size: 35px;
      text-align: center;
      background: #62d468;
      color: #fff;
      z-index: 8;
      transition: .3s;
      margin: 10px;
      padding: 7px;
      border: none;
      outline: none;
      cursor: default;
      border-radius: 50%;
      -webkit-box-shadow: 0px 5px 35px 0px rgba(0, 0, 0, 0.25);
      -moz-box-shadow: 0px 5px 35px 0px rgba(0, 0, 0, 0.25);
      box-shadow: 0px 5px 35px 0px rgba(0, 0, 0, 0.25);
      position: relative;
    }
    .circle-animation {
      display: flex;
      top: 0;
      right: 0;
      left: 0;
      position: absolute;
      justify-content: center;
      align-content: center;
      width: 60px;
      height: 60px;
      margin: 10px 0px 0px 10px;
      border-radius: 50%;
      transition: .3s;
      background-color: #62d468;
      animation: pulse 1.2s 4.0s ease 100000000;
    }

    .whatsapp-area .header {
      position: relative;
    }

    .whatsapp-popup {
      display: none;
      position: absolute;
      flex-direction: column;
      justify-content: flex-start;
      align-items: flex-start;
      width: 290px;
      padding: 20px;
      bottom: 110px;
      right: 5px;
      background-color: white;
      -webkit-box-shadow: 0px 5px 35px 0px rgba(0, 0, 0, 0.15);
      -moz-box-shadow: 0px 5px 35px 0px rgba(0, 0, 0, 0.15);
      box-shadow: 0px 5px 35px 0px rgba(0, 0, 0, 0.15);
      -webkit-animation-duration: 1s;
      animation-duration: 1s;
      -webkit-animation-fill-mode: both;
      animation-fill-mode: both;
    }

    .whatsapp-popup::after {
      content: '';
      width: 0;
      height: 0;
      border-left: 25px solid transparent;
      border-right: 0px solid transparent;
      border-top: 20px solid #fff;
      position: absolute;
      bottom: -20px;
      right: 30px;
      -webkit-box-shadow: 2px 0px 0px 0px rgba(0, 0, 0, 0.01);
      -moz-box-shadow: 2px 0px 0px 0px rgba(0, 0, 0, 0.01);
      box-shadow: 2px 0px 0px 0px rgba(0, 0, 0, 0.01);
    }

    .whatsapp-popup h2 {
      font-size: 24px;
      font-weight: 700;
    }

    .whatsapp-popup p {
      font-weight: 400;
    }

    .close-popup {
      width: 30px;
      height: 30px;
      text-align: center;
      background-color: transparent;
      border: none;
      outline: none;
      cursor: pointer;
      position: absolute;
      right: -5px;
      top: -10px;
      opacity: 0.2;
      font-size: 20px;
    }

    .close-popup:hover {
      opacity: 0.5;
    }

    .form-area {
      position: relative;
    }

    .form-area .send-btn {
      border-radius: 50%;
      height: 30px;
      padding: 5px 0px;
      top: 5px;
      right: 5px;
      position: absolute;
      width: 30px;
      border: 0;
      outline: none;
      cursor: pointer;
      background-color: #3fbaff;
      color: #ffffff;
      text-align: center;
    }

    .form-area .send-btn:hover {
      background-color: #505050;
      transition: .3s;
    }

    .whatsapp-popup input[type=text] {
      width: 100%;
      height: 40px;
      box-sizing: border-box;
      border-radius: 20px;
      font-size: 14px;
      background-color: #f9f9f9;
      padding: 0px 0px 0px 15px;
      -webkit-transition: width 0.3s ease-in-out;
      transition: width 0.3s ease-in-out;
      outline: none;
      transition: .3s;
      border: 1px solid #f6f7fd;
    }

    .whatsapp-popup input[type=text]:focus {
      border: 1px solid #e3e3e3;
    }

    .whatsapp-popup input::placeholder {
      color: rgba(68, 68, 68, 0.705);
      opacity: 1;
    }

    .chat-area {
      overflow: hidden;
      position: relative;
      margin-bottom: 25px;
      -webkit-animation-duration: 1s;
      animation-duration: 1s;
      -webkit-animation-fill-mode: both;
      animation-fill-mode: both;
      visibility: block;
    }

    .chat-area p {
      margin: 0;
      background: #f6f7fd;
      padding: 10px;
      border-radius: 0px 8px 8px 8px;
      display: inline-block;
      position: relative;
    }

    .chat-area p::before {
      content: '';
      width: 0;
      height: 0;
      border-left: 12px solid transparent;
      border-right: 0px solid transparent;
      border-top: 12px solid #f6f7fd;
      position: absolute;
      top: 0px;
      left: -12px;
    }

    .chat-area .img-item {
      display: inline-block;
      width: 45px;
      height: 45px;
      border-radius: 50%;
      margin-right: 10px;
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
                <img src="{{asset('assets/images/logo-esimko-landing-page.png')}}" id="esimko-white" style="height:70px;" >
                <img src="{{asset('assets/images/logo-esimko-dark.png')}}" id="esimko-dark" style="height:70px;" >
              </a>
            </div>
          </div>
          <div class="d-flex">
            @if(!empty(Session::get('useractive')))
            <div class="d-flex">
              <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <div class="d-none d-xl-inline-block" style="text-align:right"><span style="font-weight:400;font-size:18px">{{Session::get('useractive')->nama_lengkap}}</span><br>No. {{Session::get('useractive')->no_anggota}}</div>
                  <img class="rounded-circle header-profile-user"  src="{{asset('assets/images/user-avatar-placeholder.png')}}" style="margin-left: 5px;vertical-align:top;height:50px;width:50px">
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                  <a class="dropdown-item" href="{{url((Session::get('useractive')->hak_akses==2 ? 'main/dashboard' : 'dashboard'))}}"><i class="bx bx-laptop font-size-16 align-middle mr-1"></i>Masuk Aplikasi</a>
                  <a class="dropdown-item" href="{{url('main/profil')}}"><i class="bx bx-user font-size-16 align-middle mr-1"></i>Profil Anggota</a>
                  <a class="dropdown-item" href="{{url('main/profil?tab=ubah_password')}}"><i class="bx bx-lock-alt font-size-16 align-middle mr-1"></i>Ganti Password</a>
                  @if(count(\App\Helpers\GlobalHelper::get_user_akses(Session::get('useractive')->id)) > 1)
                  <a class="dropdown-item" href="#user_akses" data-toggle="modal"><i class="bx bx-wrench font-size-16 align-middle mr-1"></i>User Akses</a>
                  @endif
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" href="{{url('auth/logout')}}"><i class="bx bx-power-off font-size-16 align-middle mr-1 text-danger"></i> Logout</a>
                </div>
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
            @else
            <div class="dropdown d-none d-lg-inline-block ml-1">
              {{-- <button type="button" class="btn btn-dark mr-2" onclick="location.href='{{url('auth/register')}}';">DAFTAR ANGGOTA</button> --}}
              <button type="button" id="masuk" class="btn btn-success" onclick="location.href='{{url('auth/login')}}';">LOGIN</button>
            </div>
            @endif
          </div>
        </div>
      </header>
      <div class="main-content">
        <div class="page-content" >
          @yield('content')
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
    </div>
    <div class="whatsapp-area">
      <div class="whatsapp-popup">
        <div class="header">
          <button type="button" class="close-popup"><span class="bx bx-x"></span></button>
          <h2>Need Help ?</h2>
          <p>Contact us via Whatsapp</p>
        </div>
        <div class="chat-area">
          <img class="img-item" src="{{asset('assets/images/logo-koperasi-pure.png')}}" alt="" />
          <p>Anything I can help?</p>
        </div>
        <form class="form-area" id="form-area">
          <input class="whats-input" type="text" id="whats-in" autocomplete="off" Placeholder="Write your message" />
          <button type="submit" class="send-btn"><span class="bx bxs-send"></span></button>
        </form>
      </div>
      <div class="chat-button-area">
        <button type="button" class="whatsapp-btn"> <span class="bx bxl-whatsapp"></span> </button>
        <div class="circle-animation"></div>
      </div>
    </div>
    @if(!empty(Session::get('useractive')))
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
    @endif
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
      $('#esimko-dark').hide();

      $('.whatsapp-btn').click(function() {
        $('.whatsapp-popup').css('display', 'block');
      });

      $('.close-popup').on("click", function () {
          $('.whatsapp-popup').css('display','none');
      });


      $('#form-area').submit(function( event ) {
        var msg = document.getElementById('whats-in').value;
        var relmsg = msg.replace(/ /g, "%20");
        window.open('https://wa.me/+6281xxx?text=' + relmsg, '_blank');
        event.preventDefault();
      });
		</script>
		@yield('js')
  </body>
</html>
