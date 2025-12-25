@php
// header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
// header("Content-Disposition: attachment; filename=nilai_indikator.xls");
// header("Expires: 0");
// header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
// header("Cache-Control: private",false);
@endphp
@extends('layouts.report')
@section('css')
  <style>
  .tabl tr th{
    font-size: 25px;
    padding: 15px
  }
  </style>
@endsection
@section('content')
  <table class="tabl">
    <tbody>
      @foreach ($data as $key => $value)
        <tr>
          <th>{{$value->no_anggota}}</th>
          <th>{{$value->nama_lengkap}}</th>
          <th>{{$value->password}}</th>
        </tr>
      @endforeach
    </tbody>
  </table>
@endsection
