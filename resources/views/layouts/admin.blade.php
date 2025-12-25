@php
    use Illuminate\Support\Facades\Session;
    $app_title=array('main_dashboard'=>'Main Dashboard',
                     'master'=> 'Data Master dan Pengaturan',
                     'sinjam'=> 'Simpanan dan Pinjaman',
                     'manajemen_barang'=> 'Manajemen Barang',
                     'pos'=> 'Poin of Sales (POS)',
                     'laporan' => 'Laporan dan Keuangan');
@endphp
    <!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>@yield('title')eSIMKO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="" name="description" />
    <meta content="" name="author" />
    {{-- <link rel="shortcut icon" href="{{asset('assets/images/favicon.ico')}}"> --}}
    <link href="{{asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
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
        body{
            /* background:#eff4f9 */
        }
        .footer {
            color: #eff4f9;
            background-color: #5a656f;
        }
        .h1, .h2, .h3, .h4, .h5, .h6,
        h1, h2, h3, h4, h5, h6 {
            margin-bottom: 0px;
            font-weight: 500;
            line-height: 1.2;
        }

        .page-item.active .page-link {
            background-color: #45a086;
            border-color: #45a086;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected],
        .select2-container--default .select2-results__option[aria-selected=true]:hover {
            background-color: #dce4ec;
            color:#000;
        }
        #page-topbar {
            background-color: #329D9C;
        }

        .header-item:hover {
            color: #fff;
        }

        .navbar-header .dropdown.show .header-item {
            background-color: rgb(0 95 94 / 22%);
            color: #ffffff;
            width:100%;
            text-align: right
        }


        .nav-pills .nav-link.active,
        .nav-pills .nav-link.active:hover,
        .nav-pills .show>.nav-link {
            color: #fff;
            background-color: #45a086;
        }

        .nav-pills .nav-link:hover{
            background-color: #f2f2f2;
        }

        body[data-sidebar=colored] .vertical-menu {
            background-color: #205072;
        }
        body[data-sidebar=colored] .navbar-brand-box {
            background-color: #205072;
        }
        body[data-sidebar=colored] #sidebar-menu ul li a {
            color: #fff;
        }
        body[data-sidebar=colored] #sidebar-menu ul li a i {
            color: #fff;
        }
        body[data-sidebar=colored] #sidebar-menu ul li ul.sub-menu li a {
            color: #a6d4ff;
        }
        body[data-sidebar=colored] #sidebar-menu ul li ul.sub-menu li a:hover {
            color: #2ecc71;
            background: #205072;
        }
        body[data-sidebar=colored] #sidebar-menu ul li a:hover {
            color: #fff;
            background: #114163;
        }
        body[data-sidebar=colored] #sidebar-menu ul li a.active {
            color: #f8f8fb !important;
            background: #16a085;
        }
        .mm-active .active i {
            color: #f8f8fb!important;
        }

        body[data-sidebar=colored] .mm-active {
            color: inherit!important;
        }
        body[data-sidebar=colored] .mm-active .active,
        body[data-sidebar=colored] .mm-active>i {
            color: inherit!important;
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

        .card-header {
            padding: .75rem 1.25rem;
            margin-bottom: 0;
            background-color: #eaecef;
            border-bottom: 0 solid #f6f6f6;
        }
        .card-footer {
            background-color: #eaecef;
        }

        .header-item {
            color: #f8f8fb;
        }

        .noti-icon i {
            color: #f8f8fb;
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
            margin: 0 10px 0px 0;
            transition: all .3s ease;
            background: whitesmoke;
        }

        .avatar-thumbnail{
            position: relative;
            padding: 0.3rem;
            background-color: #f8f8fb;
        }

        .avatar-thumbnail img{
            height: 100%;
            width: 100%;
            object-fit: cover;
        }

        .avatar-wrapper img {
            height: 100%;
            width: 100%;
            transition: all .3s ease;
            object-fit: cover;
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


        .pull-right{
            float: right;
        }

        .table-middle tbody tr td{
            vertical-align: middle;
        }
        .avatar-sm {
            height: 3rem;
            width: 3rem;
        }
        .badge {
            padding: 0.5em;
            border-radius: 0px  ;
        }
        .center{
            text-align: center;
        }

        .noti-icon i {
            color: #f8f8fb;
        }
        .header-profile-user {
            height: 36px;
            width: 36px;
            background-color: rgba(255, 255, 255, 0.6);
            padding: 3px;
        }
        #loading{
            display: none;
            margin-top:100px;
            text-align:center;
        }

        body[data-sidebar=colored] #sidebar-menu ul li ul.sub-menu li a.active {
            color: #2ecc71 !important;
            background: #205072;
        }
        /* .btn-primary.focus, .btn-primary:focus {
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
        } */

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
        .table-informasi tr td{
            text-align: right;
        }
        .table-informasi tr:first-child td,
        .table-informasi tr:first-child th {
            border-top:none;
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

        .table.dataTable,
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

        .content-breadcrumb{
            background:rgb(90 101 111 / 5%);
            margin: -24px;
            padding: 25px;
        }
        .avatar-md {
            height: 3.5rem;
            width: 3.5rem;
        }
        .btn-dark {
            background-color: #205072;
            border-color: #205072;
        }
        .btn-dark:hover {
            background-color: #114163;
            border-color: #114163;
        }
        .list-status{
            display: flex;
            align-items: center;
            margin: 0px;
            padding: 0px
        }
        .list-status li{
            list-style: none;
            display: flex;
            align-items: center;
            margin-right: 15px;
            font-size:11px;
            letter-spacing: .5px;
        }
        .indikator-status{
            width:10px;
            height:10px;
            margin-right:5px;
            /* margin-top:2px; */
        }

        .alert-secondary {
            color: #3c3e49;
            background-color: #f5f5f8;
            border-color: #e8eaef;
        }
        .img-thumbnail {
            padding: .15rem;
            background-color: #f8f8fb;
            border: 1px solid #f6f6f6;
            border-radius: .25rem;
            max-width:100%;
            height: auto;
        }
        .verti-timeline .event-list {
            position: relative;
            padding: 0 0 0px 20px;
        }
        .autonumeric{
            text-align: right
        }
        .dropdown-icon-item img {
            height: 80px;
            margin-bottom: 10px
        }
        .dropdown-icon-item span{
            line-height: 17px
        }
        .line-xs{
            margin-top: 0.3rem;
            margin-bottom: .3rem;
        }
        .main-content{
            overflow: inherit;
        }
        /* .flex-container{
          display: flex;
      align-items: stretch;
        } */
        /* .dropdown-icon-item{
          border:2px solid #47947b;
          margin:5px
        } */
    </style>
    @yield('css')
</head>

<body data-sidebar="colored">
<div id="layout-wrapper">
    <header id="page-topbar">
        <div class="navbar-header">
            <div class="d-flex">
                <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                    <i class="fa fa-fw fa-bars"></i>
                </button>
                <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                    {{$app_title[$app]}}
                </button>
            </div>
            <div class="d-flex">
                <div class="dropdown d-inline-block">
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
                                    <a class="dropdown-icon-item" href="{{url('keuangan/bagan_akun')}}">
                                        <img src="{{asset('assets/images/accounting.png')}}">
                                        <span>Laporan dan<br>Akuntansi</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dropdown d-none d-lg-inline-block">
                    <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                        <i class="bx bx-fullscreen"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <div class="vertical-menu">
        <div data-simplebar class="h-100">
            <div id="sidebar-menu">
                <div style="padding:15px">
                    <img src="{{asset('assets/images/logo-esimko.png')}}" alt="" style="width:100%;" >
                </div>
                <ul class="metismenu list-unstyled" id="side-menu">
                    @php
                        $app_array=array('main_dashboard'=>1,'master'=> 2,'sinjam'=> 3,'manajemen_barang'=>4, 'pos' => 5, 'laporan' => 6);
                        $list_menu = \App\Helpers\GlobalHelper::get_modul($app_array[$app]);
                    @endphp
                    @foreach ($list_menu as $key => $value)
                        <li>
                            <a href="@if($value->link==NULL) javascript: void(0); @else {{url($value->link)}} @endif" class="@if(!empty($value->submodul) && count($value->submodul) !=0 ) has-arrow @endif waves-effect @if($page==$value->nama_modul) active @endif">
                                <i class="{{$value->icon}}"></i>
                                <span>{{$value->nama_modul}}</span>
                            </a>
                            @if(!empty($value->submodul) && count($value->submodul) !=0 )
                                <ul class="sub-menu @if($page==$value->nama_modul) mm-show @endif" aria-expanded="false">
                                    @foreach ($value->submodul as $key2 => $value2)
                                        <li><a href="{{url($value2->link)}}" class="@if($subpage==$value2->nama_modul) active @endif">{{$value2->nama_modul}}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">
        <div class="page-content">
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
<script src="{{asset('assets/libs/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}} "></script>
<script src="{{asset('assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js')}}"></script>
<script src="{{asset('assets/libs/highcharts/highcharts.js') }}" type="text/javascript" ></script>
<script src="{{asset('assets/libs/highcharts/highcharts-more.js') }}" type="text/javascript" ></script>
<script src="{{asset('assets/libs/dropify/dist/js/dropify.min.js')}}"></script>
<script src='{{asset('assets/libs/jstree/jstree.js') }}' type='text/javascript' ></script>
<script src="{{asset('assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>
<script src="{{asset('assets/js/autoNumeric.js')}} " type="text/javascript"></script>
<script src="{{asset('assets/js/pages/sweet-alerts.init.js')}}"></script>
<script src="{{asset('assets/js/app.js')}}"></script>
<script>
    $('input').attr('autocomplete', 'off');
    @if(\Illuminate\Support\Facades\Session::has('message'))
    Swal.fire({
        text: '{{ \Illuminate\Support\Facades\Session::get('message') }}',
        type: '{{ \Illuminate\Support\Facades\Session::get('message_type') }}',
        showCloseButton: false,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 1500
    });
    $('#kode').focus();
    @endif
    tinymce.init({
        selector: '.tinymce',
        height: 350,
        menubar: false,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor textcolor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table contextmenu paste code help'
        ],
        toolbar: 'undo redo |  formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist ',
    });
    $('.select2').select2();
    $('.autonumeric').autoNumeric({mDec: '2',aPad:false,vMin:'-9999999999999999999999999',vMax:'9999999999999999999999999'});
    $('.monthpicker').datepicker({
        autoclose: true,
        format: "mm-yyyy",
        startView: "months",
        minViewMode: "months"
    });

    $('.dropify').dropify({
        messages: {
            'default': 'Drag and drop a file here or click',
            'replace': 'Drag and drop or click to replace',
            'remove':  'Remove',
            'error':   'Ooops, something wrong happended.'
        }
    });

    $('.datepicker').datepicker({
        autoclose: true,
        format: "dd-mm-yyyy",
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


    // Konfirmasi Logout
    function confirmLogout(){
        Swal.fire({
            title: "Are you sure?",
            text: "Apakah anda yakin ingin keluar dari alikasi ini",
            type:"question",
            showCancelButton: true,
            confirmButtonColor: '#d63030',
            cancelButtonColor: '#cbcbcb',
            confirmButtonText: 'Logout'
        }).then((result) => {
            if (result.value == true) {
                $('#logout'+id).submit();
            }
        });
    }

    let get_form_data = ($form) => {
        let unindexed_array = $form.serializeArray();
        let indexed_array = {};
        $.map(unindexed_array, function(n, i){
            indexed_array[n['name']] = n['value'];
        });
        return indexed_array;
    }
    add_commas = (nStr) =>{
        nStr += '';
        let x = nStr.split('.');
        let x1 = x[0];
        let x2 = x.length > 1 ? '.' + x[1] : '';
        let rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + '.' + '$2');
        }
        return x1 + x2;
    }

    remove_commas = (nStr) => {
        nStr = nStr.replace(/\./g,'');
        nStr = nStr.replace(/\,/g,'.');
        return nStr;
    }

    let remove_space = (nStr) => {
        nStr = nStr.replace(/\ /g,'');
        return nStr;
    }

    let format_date = (value) => {
        if (value !== '' && value !== null) {
            let data = value.split('-');
            return [data[2], data[1], data[0]].join('-');
        }
        return '';
    }
</script>
@yield('js')
</body>
</html>
