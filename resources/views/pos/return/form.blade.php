@php
    $app='pos';
    $page='Retur Penjualan';
    $subpage='Retur Penjualan';
@endphp
@extends('layouts.admin')
@section('title')
    Retur Penjualan |
@endsection
@section('css')
    <link href="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
    <style>
        .list-produk{
            padding-bottom:10px;
            border-bottom: 1px solid #f2f2f2;
            margin-top:10px;
            cursor: pointer;
        }
        .image-square{
            background-color:#ececec;
            position: relative; /* If you want text inside of it */
            background-position: 50% 50%;
            background-repeat: no-repeat;
            background-size: cover;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="media">
                <img src="{{asset('assets/images/icon-page/return-box.png')}}" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">Retur Barang</h4>
                    <p class="text-muted m-0">Formulir pengisian data retur penjualan barang toko dari pembeli atau anggota ke toko</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <form action="" method="get">
                    <div class="row">
                        <div class="col-md-3">
                            <select class="select2 form-control" name="jenis_pencarian" id="jenis_pencarian">
                                <option value="transaksi" {{($jenis_pencarian=='transaksi' ? 'selected' : '')}}>Nomor Transaksi</option>
                                <option value="anggota" {{($jenis_pencarian=='anggota' ? 'selected' : '')}}>Nomor Anggota</option>
                            </select>
                        </div>
                        <div class="col-md-9">
                            @if($id!=0)
                                <input type="hidden" name="id" value="{{$id}}">
                            @endif
                            <div class="input-group">
                                <input type="text" name="search" id="search" value="{{$search}}" class="form-control" >
                                <div class="input-group-append">
                                    <button class="btn btn-secondary" type="submit" id="btn_cari" ></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <form action="{{url('pos/return/proses')}}" method="post" id="proses_retur">
                    {{ csrf_field() }}
                    <input type="hidden" name="fid_penjualan" value="{{$fid_penjualan}}">
                    <input type="hidden" name="fid_anggota" value="{{(!empty($data['retur']) ? $data['retur']->no_anggota : null )}}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="media" style="cursor:pointer" onclick="pilih_anggota('show')">
                                <div class="rounded mr-3 produk-wrapper" style="height:70px;width:70px">
                                    <img src="{{asset('assets/images/user-avatar-placeholder.png')}}" alt="" />
                                </div>
                                <div class="align-self-center media-body">
                                    <div id="no_anggota" >
                                        @if(!empty($data['retur']->no_anggota))
                                            <span>No. {{$data['retur']->no_anggota}}</span>
                                        @else
                                            <div style="height:15px;width:80%;background:whitesmoke"></div>
                                        @endif
                                    </div>
                                    <div id="nama_lengkap">
                                        @if(!empty($data['retur']->nama_lengkap))
                                            <h5>{{$data['retur']->nama_lengkap}}</h5>
                                        @else
                                            <div style="height:20px;width:100%;background:whitesmoke" class="mt-2"></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="text" name="tanggal" value="{{(!empty($data['retur']) ? \App\Helpers\GlobalHelper::dateFormat($data['retur']->tanggal,'d-m-Y') : '')}}" autocomplete="off" class="form-control datepicker">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>No. Retur Penjualan</label>
                                <input type="text" value="{{(!empty($data['retur']) ? $data['retur']->no_retur : '')}}" autocomplete="off" class="form-control" readonly >
                            </div>
                        </div>
                    </div>
                    <table class="table table-middle table-bordered table-hover mt-3">
                        <thead class="thead-light">
                        <tr>
                            <th width="20px">No</th>
                            <th>Nama Barang</th>
                            <th class="center" width="130px">Jumlah Beli</th>
                            <th class="center" width="150px">Jumlah Tukar</th>
                            <th class="center" width="150px">Sub Total</th>
                            <th>Keterangan</th>
                        </tr>
                        </thead>
                        @if(!empty($data['items']))
                            <tbody>
                            @php($total = 0)
                            @foreach ($data['items'] as $key => $value)
                                <tr>
                                    <td width="20px">{{$key+1}}</td>
                                    <td>
                                        <div class="media">
                                            <div class="rounded mr-3 produk-wrapper" style="height:60px;width:60px">
                                                <img src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                                            </div>
                                            <div class="align-self-center media-body">
                                                <span>Kode. {{$value->kode}}</span>
                                                <h6>{{$value->nama_produk}}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="center">{{$value->jumlah}} {{$value->satuan}}</td>
                                    <td class="center">
                                        <input type="hidden" name="fid_produk[]" value="{{$value->fid_produk}}">
                                        <input data-toggle="touchspin" name="jumlah[{{$value->fid_produk}}]" value="{{$value->jumlah_retur}}" type="text" data-max="{{$value->jumlah}}">
                                    </td>
                                    <td class="center">{{ format_number($value->jumlah * $value->produk->harga_jual) }}</td>
                                    <td>
                                        <input class="form-control" name="keterangan[{{$value->fid_produk}}]" value="{{$value->keterangan}}" type="text" >
                                    </td>
                                </tr>
                                @php($total += $value->jumlah * $value->produk->harga_jual)
                            @endforeach
                            </tbody>
                        @endif
                        <tr>
                            <th colspan="4">Total</th>
                            <th class="text-center">{{ format_number($total) }}</th>
                        </tr>
                    </table>
                    <input type="hidden" name="id" value="{{$id}}">
                </form>
            </div>
            <div class="card-footer">
                <div class="pull-right">
                    <a class="btn btn-secondary" href="{{url('pos/return')}}" >Kembali</a>
                    <button class="btn btn-primary" type="button" onclick="$('#proses_retur').submit()"  >Simpan</button>
                    @if(!empty($data['retur']) && $id!=0)
                        <a class="btn btn-danger" href="javascript:;" onclick="confirmDelete({{$id}})" class="text-dark">Hapus</a>
                        <form action="{{url('pos/return/proses')}}" method="post" id="hapus{{$id}}">
                            {{ csrf_field()}}
                            <input type="hidden" name="id" value="{{$id}}">
                            <input type="hidden" name="action" value="delete">
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
    <script src="{{asset('assets/js/pages/form-advanced.init.js')}}"></script>
    <script>
        $(function () {
            $('#cancel').trigger('click');
            $('#jenis_pencarian').trigger('change');
        });
        $('#jenis_pencarian').on('change', function() {
            jenis=this.value;
            if(jenis=='transaksi'){
                $('#search').attr("placeholder", "Masukkan No. Transaksi Penjualan");
                $('#btn_cari').html('Cari Transaksi');
            }
            else{
                $('#search').attr("placeholder", "Masukkan No. Anggota");
                $('#btn_cari').html('Cari Anggota');
            }
        });
    </script>
@endsection
