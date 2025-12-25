<!DOCTYPE html>
<html lang="en-US" ng-app="">
  <head>
  	<title>@yield('title')</title>
  	<meta charset="utf-8">
  	<meta content="IE=edge" http-equiv="x-ua-compatible">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
  	<meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="yes" name="apple-touch-fullscreen">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/report.css') }}">
    @yield('css')
  </head>
  <body>
    @yield('content')
    <script src="{{asset('assets/libs/jquery/jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/jquery.qrcode.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/qrcode.js')}}"></script>
    @yield('js')
  </body>
</html>
