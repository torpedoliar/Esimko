@php
    $app='manajemen_barang';
    $page='Data Barang';
    $subpage='Data Barang';
@endphp
@extends('layouts.admin')
@section('title')
    Data Barang |
@endsection
@section('css')
    <style>
        .table-informasi td,
        .table-informasi th {
            padding: .4rem .75rem;
            vertical-align: top;
            border-top: 1px solid rgb(0 0 0 / 7%);
        }
        .table-informasi tr:first-child td,
        .table-informasi tr:first-child th {
            border-top: none;
        }
        .verti-timeline {
            border-left: 2px dashed #e0e0e0;
            margin: 0 10px;
        }
        .nav-pills .nav-link.active,
        .nav-pills .nav-link.active:hover,
        .nav-pills .show>.nav-link {
            color: #fff;
            background-color: #45a086;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2" style="padding-bottom:10px">
            <div class="row">
                <div class="col-lg-6">
                    <div class="media">
                        <div class="rounded mr-4 produk-wrapper" style="height:120px;width:120px;border:6px solid #e7e7e9">
                            <img src="{{(!empty($data['produk']->foto) ? asset('storage/'.$data['produk']->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                        </div>
                        <div class="align-self-center media-body">
                            <span class="font-size-15">Kode. {{$data['produk']->kode}}</span>
                            <h4>{{$data['produk']->nama_produk}}</h4>
                            <div class="mt-3">
                                <a class="btn btn-sm btn-secondary" href="{{url('manajemen_stok/barang/form?id='.$id)}}">Edit Barang</a>
                                {{-- <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="btn btn-sm btn-secondary mt-3" >Hapus</a>
                                <form action="{{url('manajemen_stok/barang/proses')}}" method="post" id="hapus{{$value->id}}">
                                  {{ csrf_field()}}
                                  <input type="hidden" name="id" value="{{$value->id}}">
                                  <input type="hidden" name="action" value="delete">
                                </form> --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 align-self-center">
                    <div class="mt-4 mt-lg-0">
                        <div class="row">
                            <div class="col-3">
                                <div>
                                    <p class="text-muted text-truncate mb-1">Harga Beli </p>
                                    <h5 class="mb-0 font-size-15">Rp {{number_format($data['produk']->harga_beli,'0',',','.')}}</h5>
                                </div>
                            </div>
                            <div class="col-3">
                                <div>
                                    <p class="text-muted text-truncate mb-1">Margin </p>
                                    <h5 class="mb-0 font-size-15">Rp {{number_format($data['produk']->margin_nominal,'0',',','.')}}</h5>
                                </div>
                            </div>
                            <div class="col-3">
                                <div>
                                    <p class="text-muted text-truncate mb-1">Harga Jual </p>
                                    <h5 class="mb-0 font-size-15">Rp {{number_format($data['produk']->harga_jual,'0',',','.')}}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <ul class="nav nav-pills mb-2 mt-5">
                <li class="nav-item waves-effect waves-light pr-2">
                    <a class="nav-link {{($tab=='informasi' ? 'active' : '')}}" href="{{url('manajemen_stok/barang/detail?id='.$data['produk']->id.'&tab=informasi')}}">Informasi Barang</a>
                </li>
                <li class="nav-item waves-effect waves-light pr-2">
                    <a class="nav-link {{($tab=='mutasi' ? 'active' : '')}}" href="{{url('manajemen_stok/barang/detail?id='.$data['produk']->id.'&tab=mutasi')}}">Mutasi Produk</a>
                </li>
            </ul>
        </div>
        @include('manajemen_stok.barang.detail.'.$tab)
    </div>
@endsection
@section('js')
    <script>

    </script>
@endsection
