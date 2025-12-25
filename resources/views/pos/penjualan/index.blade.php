@php
    $app='pos';
    $page='Penjualan';
    $subpage='Penjualan';
@endphp
@extends('layouts.admin')
@section('title')
    Penjualan |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="{{asset('assets/images/icon-page/market.png')}}" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Data Penjualan</h4>
                        <p class="text-muted m-0">Menampilkan data penjualan barang toko ke pembeli atau anggota</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <form action="" method="get" id="status_form" >
                        <input type="hidden" value="{{$status}}" id="status_id" name="status" value="">
                        <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
                            <option value="#282828" data-id="all">Semua Status</option>
                            @foreach ($data['status'] as $key => $value)
                                <option value="{{$value->color}}" {{($status == $value->id ? 'selected' : '')}} data-id="{{ $value->id}}" >{{$value->status}}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-md-9">
                    <form action="" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Search Transaksi Penjualan">
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-2">
{{--                    <a href="{{url('pos/penjualan/form')}}" class="btn btn-primary btn-block" >Tambah</a>--}}
                </div>
            </div>
        </div>
        @if(count($data['penjualan'])==0)
            <div style="width:100%;text-align:center">
                <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
                <h4 class="mt-2">Data Penjualan Barang tidak Ditemukan</h4>
            </div>
        @else
            <div class="table-responsive mt-4 mb-4">
                <table class="table table-middle table-custom">
                    <thead>
                    <tr>
                        <th>No. Penjualan<hr class="line-xs">Waktu</th>
                        <th>Pembeli</th>
                        <th class="center">Jumlah<br>Barang</th>
                        <th class="center">Metode<br>Pembayaran</th>
                        <th style="text-align:right">Total<br>Pembayaran</th>
                        <th>Kasir</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data['penjualan'] as $key => $value)
                        <tr>
                            <td style="width:1px;white-space:nowrap;border-color:{{$value->color}}">
                                <h6>{{$value->no_transaksi}}</h6>
                                {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'d/m/Y, H:i:s')}}
                            </td>
                            <td>
                                <div class="media">
                                    <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                                        <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle">
                                    </div>
                                    <div class="media-body align-self-center">
                                        @if(!empty($value->no_anggota))
                                            <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                                            <h5 class="text-truncate font-size-13"><a href="{{url('anggota/detail?id='.$value->id)}}" class="text-dark">{{$value->nama_lengkap}}</a></h5>
                                        @else
                                            <p class="text-muted mb-0">No. 0000</p>
                                            <h5 class="text-truncate font-size-13">Bukan Anggota</h5>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            {{-- <td style="white-space:nowrap">
                              <div class="media">
                                <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px;border:1px solid #e4e4e4">
                                  <img src="{{(!empty($value->produk->foto) ? asset('storage/'.$value->produk->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                                </div>
                                <div class="align-self-center media-body">
                                  <h6>{{$value->produk->nama_produk}}</h6>
                                  {{$value->produk->jumlah}} {{$value->produk->satuan}} x Rp {{number_format($value->produk->harga,0,',','.')}}
                                </div>
                              </div>
                            </td> --}}
                            <td class="center">{{$value->jumlah}}</td>
                            <td class="center">{{$value->metode_pembayaran}}</td>
                            <td style="text-align:right;white-space:nowrap">Rp {{number_format($value->total_pembayaran,0,',','.')}}</td>
                            <td style="width:1px;white-space:nowrap">
                                @if($value->kasir==null)
                                    <span>Belum Diproses<br>oleh Kasir</span>
                                @else
                                    <span class="text-muted">No. {{$value->kasir}}</span>
                                    <h6>{{$value->nama_petugas}}</h6>
                                @endif
                            </td>
                            <td style="width:1px;white-space:nowrap">
                                <div class="text-center">
                                    @if($value->fid_status==5)
                                        <a href="{{url('pos/penjualan_baru?no_transaksi='.$value->no_transaksi)}}" class="text-dark"><i class="bx bx-search-alt h3 m-0"></i></a>
                                    @else
                                        <a href="{{url('pos/penjualan/detail?id='.$value->id)}}" class="text-dark"><i class="bx bx-search-alt h3 m-0"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mb-4">
                {{ $data['penjualan']->links('include.pagination', ['pagination' => $data['penjualan']] ) }}
            </div>
        @endif
    </div>
@endsection
@section('js')
    <script>
        function formatStatus(status) {
            var $status = $(
                '<span style="display:flex;align-items:center;"><div class="indikator-status mr-2" style="background:'+status.id+'"></div>'+status.text+'</span>'
            );
            return $status;
        };

        $(".select2-status").select2({
            templateResult: formatStatus
        });

        function pilih_status(){
            let id = $('#status_color').find('option:selected').attr('data-id');
            $('#status_id').val(id);
            $('#status_form').submit();
        }
    </script>
@endsection
