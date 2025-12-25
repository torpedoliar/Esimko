@php
  $subpage='Belanja '.ucfirst($jenis);
  $keterangan='Halaman riwayat belanja '.$jenis.' anggota';
@endphp
@extends('main.belanja.layout')
@section('content_belanja')
<div class="card">
  <div class="card-body">

  </div>
</div>
@endsection
@section('add_js')
<script type="text/javascript" src="{{asset('assets/js/jquery.qrcode.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/qrcode.js')}}"></script>
<script>

</script>
@endsection
