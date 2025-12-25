@php
  $page='Belanja';
  $subpage='Belanja';
@endphp
@extends('layouts.main')
@section('title')
Belanja |
@endsection
@section('css')
  <style>
  .produk .card-body{
    padding: 0.7rem !important
  }

  .produk h6{
    font-weight: 500;
    line-height: 18px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    text-overflow: ellipsis;
    white-space: normal;
    -webkit-line-clamp: 2;
  }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-flex align-items-center justify-content-between">
        <h4 class="mb-0 font-size-18">Belanja</h4>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-3">
      <div class="card">
        <div class="card-body">
        </div>
      </div>
    </div>
    <div class="col-lg-9">
      <div class="row">
        @for ($i=0; $i < 10; $i++)
        <div class="col-xl-3 col-sm-4 col-6">
          <div class="card">
            <div class="produk">
              <img class="card-img-top img-fluid" src="{{asset('assets/images/produk-default.jpg')}}">
              <div class="card-body">
                <h6><a href="" class="text-secondary">Lorem Ipsum is simply dummy text of the printing and typesetting industry</a></h6>
                <p class="text-muted mt-2">
                  <i class="bx bx-star text-warning"></i>
                  <i class="bx bx-star text-warning"></i>
                  <i class="bx bx-star text-warning"></i>
                  <i class="bx bx-star text-warning"></i>
                  <i class="bx bx-star text-warning"></i>
                </p>
              </div>
            </div>
          </div>
        </div>
        @endfor
      </div>
    </div>
  </div>
</div>
@endsection
