@php
    $app='manajemen_barang';
    $page='Manajemen Stok';
    $subpage='Pengembalian Barang';
@endphp
@extends('layouts.admin')
@section('title')
    Retur Barang |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="{{asset('assets/images/icon-page/return-box.png')}}" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Retur Barang</h4>
                        <p class="text-muted m-0">Menampilkan data retur barang ke supplier yang dibeli</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    <form action="" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Transaksi">
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-3">
                    <a class="btn btn-primary btn-block" href="{{url('manajemen_stok/return/form')}}">Tambah Retur</a>
                </div>
            </div>
        </div>
        @if(count($data['retur'])==0)
            <div style="width:100%;text-align:center">
                <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
                <h4 class="mt-2">RETUR BARANG TIDAK DITEMUKAN</h4>
            </div>
        @else
            <div class="table-responsive mt-4 mb-4">
                <table class="table table-middle table-custom">
                    <thead>
                    <tr>
                        <th class="center">Tanggal</th>
                        <th>No. Retur<hr class="line-xs">Nama Supplier</th>
                        <th>Nama Barang</th>
                        <th class="center" style="white-space:nowrap">Metode Retur<hr class="line-xs">Jumlah</th>
                        <th style="text-align:right;white-space:nowrap">Harga Beli<hr class="line-xs">Total</th>
                        <th>Created by</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data['retur'] as $key => $value)
                        <tr>
                            <td class="center" style="width:1px;white-space:nowrap">{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')}}</td>
                            <td style="width:1px;white-space:nowrap">
                                <div style="white-space:nowrap">{{$value->no_retur}}</div>
                                <div style="font-weight:500">{{$value->nama_supplier}}</div>
                            </td>
                            <td>
                                <div class="media">
                                    <div class="rounded mr-2 produk-wrapper" style="height:50px;width:50px">
                                        <img src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                                    </div>
                                    <div class="align-self-center media-body">
                                        <span>Kode. {{$value->kode}}</span>
                                        <h6>{{$value->nama_produk}}</h6>
                                    </div>
                                </div>
                            </td>
                            <td class="center">
                                <h6>{{$value->metode}}</h6>
                                {{$value->jumlah}} {{$value->satuan}}
                            </td>
                            <td style="text-align:right">
                                Rp {{number_format($value->harga,'0',',','.')}}
                                <h6>Rp {{number_format($value->total,'0',',','.')}}</h6>
                            </td>
                            <td style="width:1px;white-space:nowrap">
                                <h6>({{$value->created_by}}) {{$value->nama_lengkap}}</h6>
                                at {{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')}}
                            </td>
                            <td style="width:1px;white-space:nowrap">
                                <div class="text-center">
                                    <a href="{{url('manajemen_stok/return/form?id='.$value->fid_retur_pembelian)}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                                    <a href="javascript:;" onclick="confirmDelete({{ $value->fid_retur_pembelian }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                                    <form action="{{url('manajemen_stok/return/proses')}}" method="post" id="hapus{{$value->fid_retur_pembelian}}">
                                        {{ csrf_field()}}
                                        <input type="hidden" name="id" value="{{$value->fid_retur_pembelian}}">
                                        <input type="hidden" name="action" value="delete">
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mb-4">
                {{ $data['retur']->links('include.pagination', ['pagination' => $data['retur']] ) }}
            </div>
        @endif
    </div>

@endsection
@section('js')
    <script>

    </script>
@endsection
