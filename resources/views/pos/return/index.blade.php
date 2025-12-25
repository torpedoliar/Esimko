@php
    $app='pos';
    $page='Retur Penjualan';
    $subpage='Retur Penjualan';
@endphp
@extends('layouts.admin')
@section('title')
    Retur Penjualan |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="{{asset('assets/images/icon-page/return-box.png')}}" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Retur Penjualan</h4>
                        <p class="text-muted m-0">Menampilkan data retur penjualan barang toko dari pembeli atau anggota ke toko</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    <form action="" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Kode Produk / Nama Produk / Nama Anggota / No Anggota / No Retur">
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-3">
                    <a class="btn btn-primary btn-block" href="{{url('pos/return/form')}}">Tambah Retur</a>
                </div>
            </div>
        </div>
        @if(count($data['retur'])==0)
            <div style="width:100%;text-align:center">
                <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
                <h4 class="mt-2">Data Retur Barang tidak Ditemukan</h4>
            </div>
        @else
            <div class="table-responsive mt-4 mb-4">
                <table class="table table-middle table-custom">
                    <thead>
                    <tr>
                        <th>No. Retur Penjualan<hr class="line-xs">Waktu</th>
                        <th>Pembeli</th>
                        <th>Nama Barang</th>
                        <th class="center">Jumlah</th>
                        <th>Petugas</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data['retur'] as $key => $value)
                        <tr>
                            <td style="width:1px;white-space:nowrap">
                                <h6>No. {{$value->no_retur}}</h6>
                                {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'d/m/Y, H:i:s')}}
                            </td>
                            <td style="width:1px;white-space:nowrap">
                                <div class="media">
                                    <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                                        <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle">
                                    </div>
                                    <div class="media-body align-self-center">
                                        <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                                        <h5 class="text-truncate font-size-13"><a href="{{url('anggota/detail?id='.$value->id)}}" class="text-dark">{{$value->nama_lengkap}}</a></h5>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="media">
                                    <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px">
                                        <img src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                                    </div>
                                    <div class="align-self-center media-body">
                                        <span>Kode. {{$value->kode}}</span>
                                        <h6>{{$value->nama_produk}}</h6>
                                    </div>
                                </div>
                            </td>
                            <td class="center">{{$value->jumlah}}<br>{{$value->satuan}}</td>
                            <td style="width:1px;white-space:nowrap">
                                <span class="text-muted">No. {{$value->created_by}}</span>
                                <h6>{{$value->nama_petugas}}</h6>
                            </td>
                            <td style="width:1px;white-space:nowrap">
                                <div class="text-center">
                                    <a href="{{url('pos/return/form?id='.$value->fid_retur_penjualan)}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                                    <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                                    <form action="{{url('pos/return/items/delete')}}" method="post" id="hapus{{$value->id}}">
                                        {{ csrf_field()}}
                                        <input type="hidden" name="id" value="{{$value->id}}">
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:20px">
                {{ $data['retur']->links('include.pagination', ['pagination' => $data['retur']] ) }}
            </div>
        @endif
    </div>

@endsection
@section('js')
    <script>

    </script>
@endsection
