@php
    $page='Pinjaman';
    $subpage='Pinjaman';
@endphp
@extends('layouts.main')
@section('title')
    Pinjaman |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 font-size-18">Pinjaman</h4>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-9">
                        <form action="" method="get">
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Pinjaman">
                                <div class="input-group-append">
                                    <button class="btn btn-dark" type="submit">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <div class="dropdown">
                            <button class="btn btn-primary btn-block dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Formulir Pinjaman</button>
                            <div class="dropdown-menu" style="width:220px;border-radius:0px">
                                @foreach ($data['jenis'] as $key => $value)
                                    <a class="dropdown-item" href="{{url('pinjaman/form?type='.$value->id)}}">{{$value->jenis_pinjaman}}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(count($data['pinjaman'])==0)
                    <div style="width:100%;text-align:center">
                        <img src="{{asset('assets/images/employees-not-found.png')}}" class="mt-3" style="width:250px">
                        <h4 class="mt-2">DATA PINJAMAN TIDAK DITEMUKAN</h4>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-middle table-bordered table-hover">
                            <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th class="center">Tanggal</th>
                                <th scope="col">No. Anggota<hr style="margin-top: 0.5rem;margin-bottom: 0.5rem;">Nama Lengkap</th>
                                <th class="center" width="130px">Jenis<br>Pinjaman</th>
                                <th style="text-align:right">Jumlah<br>Pinjaman</th>
                                <th class="center">Tenor<br>(Bulan)</th>
                                <th class="center" width="120px">Metode<br>Pencairan</th>
                                <th class="center">Status</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($data['pinjaman'] as $key => $value)
                                <tr>
                                    <td>{{ $data['pinjaman']->firstItem() + $key }}</td>
                                    <td class="center">{{\App\Helpers\GlobalHelper::tgl_indo($value->tanggal)}}</td>
                                    <td>
                                        <div class="media">
                                            <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle img-thumbnail avatar-sm mr-2">
                                            <div class="media-body align-self-center">
                                                <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                                                <h5 class="text-truncate font-size-15"><a href="{{url('anggota/detail?id='.$value->id)}}" class="text-dark">{{$value->nama_lengkap}}</a></h5>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="center">{{$value->jenis_pinjaman}}</td>
                                    <td style="text-align:right">{{number_format($value->nominal,0,',','.')}}</td>
                                    <td class="center">{{$value->tenor}}<br>Bulan</td>
                                    <td class="center">{{$value->metode_pembayaran}}</td>
                                    <td class="center">
                                        <span style="background:{{$value->color}};padding:3px 6px;color:#fff;font-size:11px">{{$value->status_pinjaman}}</span>
                                    </td>
                                    <td style="width:1px;white-space:nowrap">
                                        <div class="text-center">
                                            <a href="{{url('pinjaman/form?id='.$value->id)}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                                            <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                                            <form action="{{url('pinjaman/proses')}}" method="post" id="hapus{{$value->id}}">
                                                {{ csrf_field()}}
                                                <input type="hidden" name="id" value="{{$value->id}}">
                                                <input type="hidden" name="action" value="delete">
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top:20px">
                        {{ $data['pinjaman']->links('include.pagination', ['pagination' => $data['pinjaman']] ) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
