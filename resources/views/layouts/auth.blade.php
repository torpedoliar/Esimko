<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>@yield('title')eSIMKO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="" name="description" />
    <meta content="" name="author" />
    {{-- <link rel="shortcut icon" href="{{asset('assets/images/favicon.ico')}}"> --}}
		<link href="{{asset('assets/libs/sweetalert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/bootstrap.min.css')}} " id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/app.min.css')}} " id="app-style" rel="stylesheet" type="text/css" />
		<style>
    body {
      margin: 0;
      font-family: "Roboto", "Helvetica Neue", "Helvetica", sans-serif;
      font-size: 0.8125rem;
      font-weight: 400;
      line-height: 1.5;
      color: #494c57;
      text-align: left;
      background-color: #329D9C !important;
    }

    .signin-wrapper {
      position: relative;
      height: 100vh;
      width: 100%;
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

    @media (min-width: 1280px){
      .signin-wrapper .content-left {
        width: 65%;
        height: 100vh;
        position: fixed;
        max-height: 100vh;
        overflow: hidden;
      }
      .signin-wrapper .content-right {
        margin-left: 65%;
      }
    }
    .signin-wrapper .content-left{
      /* background-image:url('{{asset('assets/images/bg-top.png')}}'),url('{{asset('assets/images/bg-bottom.png')}}');
      background-size: 100%,100%;
      background-position: top,bottom;
      background-repeat: no-repeat,no-repeat; */

    }

    label {
      font-weight: 400;
      font-size: 15px;
      letter-spacing: 0.8px;
    }
    .card-signin h4{
      color:#fff
    }
    .card-signin {
      border: none;
      height: 100%;
      background-color:#205072;
      color: #fff;
    }
    .signin-wrapper .inner {
      padding: 3rem;
      height: 100% !important;
    }
    .justify-content-center {
      -ms-flex-pack: center!important;
      justify-content: center!important;
    }
    .flex-column {
      -ms-flex-direction: column!important;
      flex-direction: column!important;
    }
    .d-flex {
      display: -ms-flexbox!important;
      display: flex!important;
    }
    .divider-text {
      position: relative;
      display: flex;
      align-items: center;
      text-transform: uppercase;
      color: #00ffb6;
      font-size: 10px;
      font-weight: 500;
      letter-spacing: .5px;
      margin: 15px 0;
      width: 100%;
    }
    .divider-text::before, .divider-text::after {
      content: '';
      display: block;
      flex: 1;
      height: 1px;
      background-color: #e5e9f2;
    }
		</style>
		@yield('css')
  </head>

  <body>
    <div class="signin-wrapper">
      <div class="content-left">
        <div class="inner center d-flex flex-column justify-content-center">
          <div style="text-align:center">
            <img src="{{asset('assets/images/logo-login.png')}}" style="width:350px">
          </div>
        </div>
      </div>
      <div class="content-right card-signin">
        <div style="height:100%;overflow: hidden scroll;">
          @yield('content')
        </div>
      </div>
    </div>
    <script src="{{asset('assets/libs/jquery/jquery.min.js')}}"></script>
    <script src="{{asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/libs/simplebar/simplebar.min.js')}}"></script>
    <script src="{{asset('assets/libs/node-waves/waves.min.js')}}"></script>
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

			// Konfirmasi Hapus
		  function confirmDelete(id){
		    Swal.fire({
		      title: "Are you sure?",
		      text: "Apakah anda yakin ingin menghapus data ini",
		      type:"question",
					confirmButtonColor:"#556ee6",
		      buttons: true,
		      dangerMode: true,
		    })
		    .then((willDelete) => {
		      if (willDelete) {
		          $('#hapus'+id).submit();
		      } else {

		      }
		    });
		  }
		</script>
		@yield('js')
  </body>
</html>
