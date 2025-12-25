@php
    $app='pos';
    $page='Laporan';
    $subpage='Laporan Penjualan';
@endphp
@extends('layouts.admin')
@section('title')
    Laporan Penjualan |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="{{asset('assets/images/icon-page/market.png')}}" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Data Laporan Penjualan</h4>
                        <p class="text-muted m-0">Menampilkan laporan hasil penjualan</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('js')
    <script>

    </script>
@endsection
