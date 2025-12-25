@php
    $app='sinjam';
    $page='Monitoring Anggota';
    $subpage='Saldo Simpanan';
@endphp
@extends('layouts.admin')
@section('title')
    Monitoring Saldo Simpanan |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="{{asset('assets/images/icon-page/wallet.png')}}" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Monitoring Saldo Simpanan</h4>
                        <p class="text-muted m-0">Menampilkan data saldo simpanan anggota</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <form action="" method="get" id="status_form" >
                        <input type="hidden" value="{{$status ?? ''}}" id="status_id" name="status" value="">
                        <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
                            <option value="#282828" data-id="all">Semua Status</option>
                            @foreach ($data['status'] as $key => $value)
                                <option value="{{$value->color}}" {{($status == $value->id ? 'selected' : '')}} data-id="{{ $value->id}}" >{{$value->status_anggota}}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-md-6">
                    <form action="" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Anggota">
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-1">
                    <a href="{{ url('monitoring/saldo_simpanan/detail') }}" class="btn btn-block btn-success">Detail</a>
                </div>
                <div class="col-lg-1">
                    <button class="btn btn-block btn-primary" onclick="cetak()">Cetak</button>
                </div>
            </div>
        </div>
        @if($data['saldo']==null || count($data['saldo']) == 0)
            <div style="width:100%;text-align:center">
                <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
                <h4 class="mt-3">Anggota tidak Ditemukan</h4>
            </div>
        @else
            <div class="table-responsive mt-4 mb-4">
                <table class="table table-middle table-custom">
                    <thead>
                    <tr>
                        <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
                        <th style="text-align:right;width:150px">Simpanan<br>Pokok</th>
                        <th style="text-align:right;width:150px">Simpanan<br>Wajib</th>
                        <th style="text-align:right;width:150px">Simpanan<br>Sukarela</th>
                        <th style="text-align:right;width:150px">Simpanan<br>Hari Raya</th>
                        <th style="text-align:right;width:150px">Total Saldo</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data['saldo'] as $key => $value)
                        <tr onclick="window.location.href = '{{ url('monitoring/saldo_simpanan/detail?no_anggota=' . $value->no_anggota) }}'">
                            <td style="border-color:{{$value->color}}">
                                <div class="media">
                                    <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                                        <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle">
                                    </div>
                                    <div class="media-body align-self-center">
                                        <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                                        <h5 class="text-truncate font-size-15"><span class="text-dark">{{$value->nama_lengkap}}</span></h5>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align:right">Rp {{number_format($value->simpanan_pokok,'0',',','.')}}</td>
                            <td style="text-align:right">Rp {{number_format($value->simpanan_wajib,'0',',','.')}}</td>
                            <td style="text-align:right">Rp {{number_format($value->simpanan_sukarela,'0',',','.')}}</td>
                            <td style="text-align:right">Rp {{number_format($value->simpanan_hari_raya,'0',',','.')}}</td>
                            <td style="text-align:right">Rp {{number_format($value->total_simpanan,'0',',','.')}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mb-4 mt-3">
                {{ $data['saldo']->links('include.pagination', ['pagination' => $data['saldo']] ) }}
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

        cetak = () => {
            window.open("{{ url('monitoring/saldo_simpanan/cetak') }}", '_blank');
        }
    </script>
@endsection
