@extends('layouts.auth')
@section('title')
  Login |
@endsection
@section('content')
<div class="inner d-flex flex-column justify-content-center" style="padding:1rem 3rem;">
  <img src="{{asset('assets/images/logo-esimko-login.png')}}" alt="" style="width:70%;" >
  <h4 class="mb-3 mt-5" style="font-size:25px;font-weight:500;letter-spacing: 0.8px;">Login User</h4>
  <form class="form-horizontal" action="{{url('auth/login/proses')}}" method="post">
    {{ csrf_field() }}
    <div class="form-group">
      <label for="username">Usernameasa</label>
      <input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
    </div>
    <div class="form-group">
      <label for="userpassword">Password</label>
      <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
    </div>
    <div class="mt-3">
      <button class="btn btn-primary btn-block waves-effect waves-light" type="submit">LOGIN</button>
    </div>
  </form>
</div>
@endsection
