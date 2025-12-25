@php
    $app='sinjam';
    $page='Bunga Simpanan';
    $subpage='Bunga Simpanan';
    $disabled = $data['status'];
@endphp
@extends('layouts.admin')
@section('title')
    Bunga Simpanan |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="{{asset('assets/images/icon-page/care.png')}}" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Bunga Simpanan</h4>
                        <p class="text-muted m-0">Menampilkan data bunga simpanan sukarela yang sudah diposting oleh petugas setiap hari</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10">
                    <form action="" method="get">
                        <div class="row">
                            <div class="col-lg-2">
                                <input type="text" name="tanggal" class="form-control datepicker" value="{{$tanggal}}"  autocomplete="off">
                            </div>
                            <div class="col-lg-10">
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Anggota">
                                    <div class="input-group-append">
                                        <button class="btn btn-dark" type="submit">Search</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-block" onclick="confirm_proses()"  >Posting Bunga</button>
                </div>
            </div>
        </div>
        @if(count($data['bunga-simpanan']) == 0)
            <div style="width:100%;text-align:center">
                <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
                <h4 class="mt-3">Bunga Simpanan belum Diposting</h4>
            </div>
        @else
            <div class="row mt-4 mb-4">
                <div class="col">
                    <div style="height::100%">
                        {{--            @if(count($data['bunga-simpanan']->data)==0)--}}
                        {{--              <div style="width:100%;text-align:center">--}}
                        {{--                <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">--}}
                        {{--                <h4 class="mt-3">Data Anggota tidak Ditemukan</h4>--}}
                        {{--              </div>--}}
                        {{--            @else--}}
                        <div class="table-responsive">
                            <table class="table table-middle table-custom">
                                <thead>
                                <tr>
                                    <th>No. Anggota</th>
                                    <th>Nama Lengkap</th>
                                    <th>Tanggal</th>
                                    <th style="text-align:right">Nominal</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($data['bunga-simpanan'] as $key => $value)
                                    <tr>
                                        <td>{{ $value->anggota->no_anggota }}</td>
                                        <td>{{ $value->anggota->nama_lengkap }}</td>
                                        <td>{{ format_date($value->tanggal) }}</td>
                                        <td style="text-align:right">Rp {{ format_number($value->nominal) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $data['bunga-simpanan']->links('include.pagination', ['pagination' => $data['bunga-simpanan']] ) }}
                        {{--            @endif--}}
                    </div>
                </div>
                {{--        <div class="col-auto">--}}
                {{--          <div style="border-left:1px solid #dedede;padding:20px 20px;height:100%;width:280px" class="mt-3 mb-3">--}}
                {{--            <div class="form-group">--}}
                {{--              <label>Waktu Posting</label>--}}
                {{--              <div class="font-size-13">{!!(!empty($data['bunga-simpanan']) ? \App\Helpers\GlobalHelper::tgl_indo($data['bunga-simpanan']->created_at).', '.\App\Helpers\GlobalHelper::dateFormat($data['bunga-simpanan']->created_at,'H:i:s') : '<hr>')!!}</div>--}}
                {{--            </div>--}}
                {{--            <div class="form-group">--}}
                {{--              <label>Diposting oleh</label>--}}
                {{--              <div class="font-size-13">{!!(!empty($data['bunga-simpanan']) ? '('.$data['bunga-simpanan']->created_by.') '.$data['bunga-simpanan']->nama_lengkap : '<hr>')!!}</div>--}}
                {{--            </div>--}}
                {{--            <div class="form-group">--}}
                {{--              <label>Total Anggota</label>--}}
                {{--              <div class="font-size-13">{!!(!empty($data['bunga-simpanan']) ? $data['bunga-simpanan']->jumlah_anggota.' orang' : '<hr>')!!}</div>--}}
                {{--            </div>--}}
                {{--            <div class="form-group">--}}
                {{--              <label>Persentase Bunga</label>--}}
                {{--              <div class="font-size-13">{!!(!empty($data['bunga-simpanan']) ? $data['bunga-simpanan']->bunga.'% dari Saldo Awal Bulan'  : '<hr>')!!}</div>--}}
                {{--            </div>--}}
                {{--            <div class="form-group">--}}
                {{--              <label>Total Bunga</label>--}}
                {{--              <div class="font-size-13">{!!(!empty($data['bunga-simpanan']) ? 'Rp '.number_format(str_replace('-','',$data['bunga-simpanan']->nominal),0,',','.') : '<hr>')!!}</div>--}}
                {{--            </div>--}}
                {{--          </div>--}}
                {{--        </div>--}}
            </div>
        @endif
    </div>
    <form action="{{url('simpanan/bunga/proses')}}" id="proses_bunga" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="tanggal" value="{{$tanggal}}">
    </form>
@endsection
@section('js')
    <script>
        function confirm_proses(){
            Swal.fire({
                title: "Are you sure?",
                text: "Apakah anda yakin ingin memproses bunga simpanan anggota untuk hari ini",
                type:"question",
                showCancelButton: true,
                confirmButtonColor: '#16a085',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Proses Simpanan'
            }).then((result) => {
                if (result.value == true) {
                    $('#proses_bunga').submit();
                }
            });
        }
    </script>
@endsection
