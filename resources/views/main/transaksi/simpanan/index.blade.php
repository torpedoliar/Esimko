@php
    $page='Simpanan';
    $subpage='Simpanan';
@endphp
@extends('layouts.main')
@section('title')
    Simpanan |
@endsection
@section('content')
    <div class="content-breadcrumb mb-2">
        <div class="container-fluid">
            <div class="page-title-box pb-0">
                <div class="media">
                    <img src="{{asset('assets/images/icon-page/wallet.png')}}" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Simpanan</h4>
                        <p class="text-muted m-0">Menampilkan data simpanan yang sudah diinput oleh petugas atau anggota</p>
                    </div>
                </div>
            </div>
            <form action="{{url('main/transaksi/filter')}}" method="post" id="filter_transaksi" class="mt-5">
                @php($filter=Session::get('filter_transaksi'))
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-3">
                        <select name="jenis" class="form-control select2" onchange="javascript:submit()">
                            <option value="all">Semua Jenis Transaksi</option>
                            @foreach ($data['jenis-transaksi'] as $key => $value)
                                <option value="{{$value->id}}" {{(!empty($filter['simpanan']) ? ($filter['simpanan']['jenis']==$value->id ? 'selected' : '') : '' )}}  >{{$value->jenis_transaksi}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="hidden" id="status_id" name="status" value="{{(!empty($filter['simpanan']) ? $filter['simpanan']['status'] : 'all' )}}">
                        <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
                            <option value="#282828" data-id="all">Semua Status</option>
                            @foreach ($data['status-transaksi'] as $key => $value)
                                <option value="{{$value->color}}" {{(!empty($filter['simpanan']) ? ($filter['simpanan']['status']==$value->id ? 'selected' : '') : '' )}}  data-id="{{ $value->id}}" >{{$value->status}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div>
                            <div class="input-daterange input-group" data-date-format="dd-mm-yyyy" data-provide="datepicker">
                                <input type="text" class="form-control" value="{{(!empty($filter['simpanan']) ? $filter['simpanan']['from'] : '' )}}" autocomplete="off" id="from" onchange="javascript:submit()" name="from" placeholder="Dari Tanggal" />
                                <input type="text" class="form-control" value="{{(!empty($filter['simpanan']) ? $filter['simpanan']['to'] : '' )}}" autocomplete="off" id="to" onchange="javascript:submit()" name="to" placeholder="Sampai Tanggal" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="dropdown">
                            <button class="btn btn-primary btn-block dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" >Formulir</button>
                            <div class="dropdown-menu dropdown-menu-right" style="">
                                <a class="dropdown-item" href="javascript:;" onclick="form_transkasi('Setoran')">Setoran Simpanan</a>
                                <a class="dropdown-item" href="javascript:;" onclick="form_transkasi('Penarikan')">Penarikan Simpanan</a>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="modul" value="simpanan">
            </form>
        </div>
    </div>
    <div class="container-fluid mt-4 mb-5">
        <div class="row">
            <div class="col">
                <div style="height:100%">
                    @if(count($data['simpanan'])==0)
                        <div style="width:100%;text-align:center" class="mb-5">
                            <img src="{{asset('assets/images/not-found.png')}}" class="mt-3" style="width:180px">
                            <h5 class="mt-3">Data Simpanan Tidak Ditemukan</h5>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-middle table-custom">
                                <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis Transaksi</th>
                                    <th class="center">Metode<br>Pembayaran</th>
                                    <th style="text-align:right">Nominal</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($data['simpanan'] as $key => $value)
                                    <tr>
                                        <td class="center" style="width:1px;white-space:nowrap;border-color:{{$value->color}}">{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')}}</td>
                                        <td style="font-weight:500">{{$value->jenis_transaksi}}</td>
                                        <td class="center">{{$value->metode_transaksi}}</td>
                                        <td style="text-align:right;color:{{($value->operasi=='kredit' ? '#008305' : '#be0600')}}">{{($value->operasi=='kredit' ? '+' : '-')}} {{number_format(str_replace('-','',$value->nominal),0,',','.')}}</td>
                                        <td style="width:1px;white-space:nowrap">
                                            <div class="text-center">
                                                <a href="{{url('main/simpanan/detail?id='.$value->id)}}" class="text-dark"><i class="bx bx-search-alt h3 m-0"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mb-4">
                            {{ $data['simpanan']->links('include.pagination', ['pagination' => $data['simpanan']] ) }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-auto">
                <div style="border-left:1px solid #dedede;padding:20px 20px;height:100%;width:250px" class="mb-4">
                    <div class="form-group">
                        <label>Saldo Simpanan Pokok</label>
                        <div class="font-size-13">Rp {{number_format($data['saldo']->simpanan_pokok,0,',','.')}}</div>
                    </div>
                    <div class="form-group">
                        <label>Saldo Simpanan Wajib</label>
                        <div class="font-size-13">Rp {{number_format($data['saldo']->simpanan_wajib,0,',','.')}}</div>
                    </div>
                    <div class="form-group">
                        <label>Saldo Simpanan Sukarela</label>
                        <div class="font-size-13">Rp {{number_format($data['saldo']->simpanan_sukarela,0,',','.')}}</div>
                    </div>
                    <div class="form-group">
                        <label>Saldo Simpanan Hari Raya</label>
                        <div class="font-size-13">Rp {{number_format($data['saldo']->simpanan_hari_raya,0,',','.')}}</div>
                    </div>
                    <div class="form-group">
                        <label>Total Saldo Simpanan</label>
                        <div class="font-size-13">Rp {{number_format($data['saldo']->total_simpanan,0,',','.')}}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="form-transaksi">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{url('main/transaksi/proses')}}" id="proses_transkasi" method="post">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Jumlah <span id="jenis"></span></label>
                            <input type="text" style="text-align:right" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="nominal" value="0">
                            <div id="sisa_saldo" style="text-align: right;font-style: italic;color: #a7a7a7;margin-top: 5px;">
                                Sisa Saldo Rp {{number_format(\App\Helpers\GlobalHelper::saldo_tabungan(Session::get('useractive')->no_anggota,'Simpanan Sukarela'),0,',','.')}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea name="keterangan" class="form-control" style="height:110px"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="jenis_transaksi" id="jenis_transaksi">
                        <input type="hidden" name="modul" id="modul">
                        <input type="hidden" name="id" id="id">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary"  id="action" name="action">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        function form_transkasi(jenis){
            $('#id').val(0);
            $('#action').val('add');
            $('#title').html('Tambah '+jenis);
            $('#jenis').html(jenis);
            if(jenis=='Setoran'){
                $('#sisa_saldo').hide();
                $('#jenis_transaksi').val(4);
                $('#modul').val('simpanan');
            }
            else{
                $('#sisa_saldo').show();
                $('#jenis_transaksi').val(6);
                $('#modul').val('penarikan');
            }
            $('#form-transaksi').modal('show');
        }
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
            $('#filter_transaksi').submit();
        }

    </script>
@endsection
