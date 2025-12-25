@php
    $app='sinjam';
    $subpage='Buku Simpanan';
@endphp
@extends('layouts.admin')
@section('title')
    Buku Simpanan |
@endsection
@section('css')
    <style>
        .list-anggota{
            padding-bottom:10px;
            border-bottom: 1px solid #f2f2f2;
            margin-top:10px;
            cursor: pointer;
        }

    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="media">
                <img src="{{asset('assets/images/icon-page/printer.png')}}" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">Buku Simpanan</h4>
                    <p class="text-muted m-0">Halaman untuk mencetak buku simpanan anggota</p>
                </div>
            </div>
        </div>
        <div class="row mt-3 mb-5">
            <div class="col-auto">
                <form action="" method="get">
                    <div style="width:320px;background:#dcdde1;padding:20px;height:100%;position:relative">
                        <div class="media mb-3" style="cursor:pointer;" onclick="pilih_anggota('show')">
                            <div class="avatar-thumbnail avatar-sm rounded-circle mr-2" id="avatar" style="height:60px;width:60px">
                                <img src="{{asset('assets/images/user-avatar-placeholder.png')}}" alt="" class="rounded-circle" />
                            </div>
                            <div class="align-self-center media-body">
                                <div id="no_anggota" >
                                    <div style="height:15px;width:80%;background:rgb(255 255 255 / 69%)"></div>
                                </div>
                                <div id="nama_lengkap">
                                    <div style="height:20px;width:100%;background:rgb(255 255 255 / 69%)" class="mt-2"></div>
                                </div>
                                <input type="hidden" name="anggota" id="fid_anggota">
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label>Jenis Simpanan</label>
                            <select class="select2" style="width:100%" name="jenis_simpanan">
                                @foreach($jenis_simpanan as $key => $value)
                                    <option value="{{ $key }}" {{ $key == $jenis_selected ? 'selected' : '' }} >{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Awal</label>
                            <input type="text" class="form-control datepicker" name="tanggal_awal" value="{{($request->tanggal_awal != '' ? $request->tanggal_awal : '' )}}">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Akhir</label>
                            <input type="text" class="form-control datepicker" name="tanggal_akhir" value="{{($request->tanggal_akhir != '' ? $request->tanggal_akhir : '' )}}">
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nomor Awal</label>
                                    <input data-toggle="touchspin" type="text" class="center form-control" name="nomor_awal" value="{{ $nomor_awal }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Halaman</label>
                                    <input data-toggle="touchspin" type="text" class="center form-control" max="{{ $data['pagetotal'] }}" name="page" value="{{ $page }}">
                                </div>
                            </div>
{{--                            <div class="col-md-6">--}}
{{--                                <div class="form-group">--}}
{{--                                    <label>Spacing Row</label>--}}
{{--                                    <input data-toggle="touchspin" type="text" class="center form-control" name="spacing" value="{{($request->spacing != '' ? $request->spacing : 0 )}}">--}}
{{--                                </div>--}}
{{--                            </div>--}}
                        </div>
                        <button class="btn btn-primary btn-block">Tampilkan</button>
                        <a class="btn btn-secondary btn-block" href="{{ url('simpanan/buku_simpanan') . '?' . last(explode('?', $request->getRequestUri())) }}&action=cetak" target="_blank">Cetak Buku</a>
                        <hr>
                        <a class="btn btn-light btn-block" href="{{ url('simpanan/buku_simpanan/cover') . '?' . last(explode('?', $request->getRequestUri())) }}" target="_blank">Cetak Cover</a>
                    </div>
                </form>
            </div>
            <div class="col">
                <div class="card" style="height:100%">
                    <div class="card-body" style="height:100%">
                        <table class="table table-middle table-sm table-bordered">
                            <thead>
                            <tr>
                                <th class="center">No</th>
                                <th class="center">Tanggal</th>
                                <th class="center">Sandi</th>
                                <th class="center">Debet</th>
                                <th class="center">Kredit</th>
                                <th class="center">Saldo</th>
                                <th class="center">Operator</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(empty($request->page) || $request->page==1)
                                @for ($i=0; $i < $request->spacing; $i++)
                                    <tr>
                                        <td colspan="7" class="center">Row Spacing</td>
                                    </tr>
                                @endfor
                            @endif
                            @if($page == 1)
                                @for($i = 1; $i < ($nomor_awal % 27); $i++)
                                    <tr>
                                        <td colspan="7">&nbsp;</td>
                                    </tr>
                                @endfor
                            @endif
                            @foreach ($data['data'] as $key => $value)
                                <tr>
                                    <td class="center">{{ $value->nomor }}</td>
                                    <td class="center">{{ \App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y') }}</td>
                                    <td class="center">{{ $value->sandi }}</td>
                                    <td style="text-align:right">{{number_format($value->debit * -1,'0',',','.')}}</td>
                                    <td style="text-align:right">{{number_format($value->kredit,'0',',','.')}}</td>
                                    <td style="text-align:right">{{number_format($key == 0 ? $value->saldo : $value->saldo,'0',',','.')}}</td>
                                    <td class="center">{{$value->operator}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
{{--                        @php($pageurl = url('pegawai'))--}}
{{--                        @include('include.pagination_custom')--}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="modal-anggota" class="modal fade right">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Pilih Anggota</h5>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="" id="search" name="search" placeholder="Cari Anggota">
                        <div class="input-group-append">
                            <button class="btn btn-dark" id="btn-search" onclick="search_anggota()">Search</button>
                        </div>
                    </div>
                    <div id="loading"><img src="{{asset('assets/images/loading.gif')}}" style="width:100px"></div>
                    <div id="list-anggota" ></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
    <script src="{{asset('assets/js/pages/form-advanced.init.js')}}"></script>
    <script src="{{asset('assets/js/accounting.js')}}"></script>
    <script>
        function search_anggota(){
            var search = $('#search').val();
            if(search !== ''){ search = '/'+search }
            else{ search = '/all'}
            $('#loading').show();
            $('#list-anggota').hide();
            $.get("{{ url('api/get_anggota/aktif/') }}"+search,function (result) {
                $('#list-anggota').html('');
                $.each(result,function(i,value){
                    $('#list-anggota').append('<div class="list-anggota" onclick="pilih_anggota('+value.id+')">'+
                        '<div class="media">'+
                        '<div class="avatar-thumbnail avatar-sm rounded-circle mr-2">'+
                        '<img style="margin-right:10px;" src="'+value.avatar+'" alt="" style="max-width:none" class="rounded-circle">'+
                        '</div>'+
                        '<div class="media-body align-self-center" >'+
                        '<p class="text-muted mb-0">No. '+value.no_anggota+'</p>'+
                        '<h5 class="text-truncate font-size-16">'+value.nama_lengkap+'</h5>'+
                        '</div>'+
                        '</div>'+
                        '</div>');
                });
                $('#loading').hide();
                $('#list-anggota').show();
            });
        };

        @if(!empty($anggota->id))
        pilih_anggota('{{$anggota->id}}');
        @endif

        function pilih_anggota(id){
            if(id=='show'){
                search_anggota();
                $('#modal-anggota').modal('show');
            }
            else{
                $.get("{{ url('api/find_anggota') }}/"+id,function(result){
                    $('#anggota_id').val(id);
                    $('#avatar').html('<img src="'+result.avatar+'" alt="" class="rounded-circle" >');
                    $('#no_anggota').html('<span>No. '+result.no_anggota+'</span>');
                    $('#nama_lengkap').html('<h5>'+result.nama_lengkap+'</h5>');
                    $('#fid_anggota').val(result.no_anggota);
                    $('#modal-anggota').modal('hide');
                });
            }
        }
    </script>
@endsection
