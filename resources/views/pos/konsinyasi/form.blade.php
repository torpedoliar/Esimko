@php
    $page='Poin of Sales';
    $subpage='Belanja Konsinyasi';
@endphp
@extends('layouts.admin')
@section('title')
    Belanja Konsinyasi |
@endsection
@section('css')
    <link href="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
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
                <img src="{{asset('assets/images/penarikan.png')}}" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">Belanja Konsinyasi</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Halaman Admin</a></li>
                        <li class="breadcrumb-item"><a href="#">Poin of Sales</a></li>
                        <li class="breadcrumb-item active">Belanja Konsinyasi</li>
                    </ol>
                </div>
            </div>
        </div>
        <form action="{{url('pos/belanja_konsinyasi/proses')}}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="card">
                <div class="card-header">
                    <h5>{{($action=='add' ? 'Tambah' : 'Edit')}} Belanja Konsinyasi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div style="border:#dfe4e9 dashed 2px ;padding:20px">
                                <h5 class="mb-3"># Identitas Anggota</h5>
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar-wrapper" style="height:100px;width:100px">
                                            <img src="{{asset('assets/images/user-avatar-placeholder.png')}}" alt="" />
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="list-content">
                                            <span>No. Anggota</span>
                                            <div id="no_anggota" class="info-content">{!!(!empty($data['belanja']) ? $data['belanja']->no_anggota :'<hr>')!!}</div>
                                        </div>
                                        <div class="list-content">
                                            <span>Nama Lengkap</span>
                                            <div id="nama_lengkap" class="info-content">{!!(!empty($data['belanja']) ? $data['belanja']->nama_lengkap :'<hr>')!!}</div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="fid_anggota" value="{{(!empty($data['belanja']) ? $data['belanja']->no_anggota : '')}}" id="fid_anggota">
                                <button type="button" onclick="pilih_anggota('show')" class="btn btn-secondary btn-block mt-3">PILIH ANGGOTA</button>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tanggal</label>
                                        <input type="text" value="{{(!empty($data['belanja']) ? \App\Helpers\GlobalHelper::dateFormat($data['belanja']->tanggal,'d-m-Y') : '')}}" class="form-control datepicker" name="tanggal" autocomplete="off" name="tanggal" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>No Transaksi</label>
                                        <input type="text" value="{{(!empty($data['belanja']) ? $data['belanja']->no_transaksi : '')}}" class="form-control" autocomplete="off" name="no_transaksi" >
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Nama Toko</label>
                                        <input type="text" value="{{(!empty($data['belanja']) ? $data['belanja']->nama_toko : '')}}" class="form-control" name="nama_toko" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Total Belanja</label>
                                        <input type="text" value="{{(!empty($data['belanja']) ? $data['belanja']->total_pembayaran : '0')}}" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="total_belanja" id="total_belanja" onkeyup="calc_belanja()" >
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Margin</label>
                                        <input type="text" data-toggle="touchspin" value="{{(!empty($data['belanja']->margin) ? $data['belanja']->margin : 10)}}" class="form-control" name="margin" id="margin" onkeyup="calc_belanja()">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tenor</label>
                                        <input type="text" data-toggle="touchspin" value="{{(!empty($data['belanja']->tenor) ? $data['belanja']->tenor : 1)}}" class="form-control" name="tenor" id="tenor" onkeyup="calc_belanja()" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Angsuran</label>
                                        <input type="text" value="{{(!empty($data['belanja']) ? $data['belanja']->angsuran : '')}}" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="angsuran" id="angsuran" onkeyup="calc_belanja()">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Upload Bukti Transaksi</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="customFile" name="attachment">
                                            <label class="custom-file-label" for="customFile">Choose file</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea class="form-control" name="keterangan" style="height:65px" value="">{{(!empty($data['belanja']) ? $data['belanja']->keterangan : '')}}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <input type="hidden" name="action" value="{{$action}}">
                    <input type="hidden" name="id" value="{{$id}}">
                    <div class="pull-right">
                        <a class="btn btn-secondary" href="{{url('pos/belanja_konsinyasi')}}" >Kembali</a>
                        <button class="btn btn-primary" type="submit">{{($action=='add' ? 'Tambah' : 'Simpan')}}</button>
                    </div>
                </div>
            </div>
        </form>
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
        $(function () {
            calc_belanja();
        });

        function calc_belanja(){
            total_belanja=$('#total_belanja').val();
            total_belanja=total_belanja.split('.').join('');
            tenor=$('#tenor').val();
            margin=$('#margin').val();
            total_pembayaran=(total_belanja*margin/100)+parseInt(total_belanja);
            angsuran=total_pembayaran/tenor;
            $('#angsuran').val(accounting.formatNumber(angsuran,0,'.',','));
        }

        function search_anggota(){
            var search = $('#search').val();
            if(search !== ''){ search = '/'+search }
            else{ search = '/all'}
            $('#loading').show();
            $('#list-anggota').hide();
            $.get("{{ url('api/get_anggota/all/') }}"+search,function (result) {
                $('#list-anggota').html('');
                $.each(result,function(i,value){
                    $('#list-anggota').append('<div class="list-anggota" onclick="pilih_anggota('+value.id+')">'+
                        '<div class="media">'+
                        '<img style="margin-right:10px;" src="'+value.avatar+'" alt="" style="max-width:none" class="rounded-circle img-thumbnail avatar-sm mr-2">'+
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

        function pilih_anggota(id){
            if(id=='show'){
                search_anggota();
                $('#modal-anggota').modal('show');
            }
            else{
                $.get("{{ url('api/find_anggota') }}/"+id,function(result){
                    $('#nama_lengkap').html(result.nama_lengkap);
                    $('#no_anggota').html(result.no_anggota);
                    $('#fid_anggota').val(result.no_anggota);
                    $('#modal-anggota').modal('hide');
                });
            }
        }

        @if(session()->has('error'))
            alert('{{ session('error') }}')
        @endif
    </script>
@endsection
