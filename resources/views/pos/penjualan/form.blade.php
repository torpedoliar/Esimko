@php
    $app='pos';
    $page='Penjualan';
    $subpage='Penjualan';
    $readonly=(!empty($data['penjualan']) ? ($data['penjualan']->fid_status != 2  ? '' : 'readonly' ) : '');

@endphp
@extends('layouts.kasir')
@section('title')
    Penjualan |
@endsection
@section('css')
    <link href="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
    <style>
        body{
            background: #f5f6fa;
        }

        /* .btn-primary {
          color: #fff;
          background-color: #58c596;
          border-color: #58c596;
        }

        .btn-primary:hover {
          color: #fff;
          background-color: #3ac187;
          border-color: #3ac187;
        } */

        .list-anggota{
            padding-bottom:10px;
            border-bottom: 1px solid #f2f2f2;
            margin-top:10px;
            cursor: pointer;
        }

        .pos-container{
            display: flex !important;
        }
        .pos-container .coloum-left{
            width:100%;
            background:#fff;
        }
        .pos-container .coloum-right{
            width:440px;
            background:#dcdde1;
        }

        .pos-header{
            position: relative;
        }

        .pos-header::before{
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            height: 1.25rem;
            width: 100%;
            background: linear-gradient(rgba(0,0,0,0.06),transparent);
        }

        .pos-footer{
            background:#718093;
            color:#fff;
            padding:20px;
            width:100%;
            position:absolute;
            bottom:0px;
        }

        .pos-footer::before{
            content: '';
            position: absolute;
            top: -20px;
            left: 0;
            height: 1.25rem;
            width: 100%;
            background: linear-gradient(transparent, rgba(0,0,0,0.06));
        }

        .gutter-2{
            margin-right: -5px;
            margin-left: -5px;
        }

        .gutter-2 .col-md-6{
            padding-right: 5px !important;
            padding-left: 5px !important;
        }

    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <form action="{{url('pos/penjualan/proses')}}" method="post" id="proses_penjualan">
            {{ csrf_field() }}
            <div class="pos-container" style="height:calc(100vh - 180px)">
                <div class="coloum-left" style="position:relative">
                    <div style="padding:20px;background:#fff">
                        <div class="d-flex">
                            <div class="align-self-center" style="width:100%;margin-right:20px">
                                <div class="input-group">
                                    <input type="text" style="margin:0px" id="kode" name="kode" class="form-control" {{($readonly=='readonly' ? 'disabled' : '')}} placeholder="Cari Data Barang" autofocus>
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary" type="submit" name="action" value="add_barang" >Tambahkan</button>
                                        <button class="btn btn-primary" type="button" onclick="cari_barang()">Cari Barang</button>
                                    </div>
                                </div>
                            </div>
                            <div class="media" style="cursor:pointer;width:450px" @if($readonly!='readonly') onclick="pilih_anggota('show')" @endif>
                                <div class="avatar-thumbnail avatar-sm rounded-circle mr-2" id="avatar" style="height:60px;width:60px">
                                    <img src="{{(!empty($data['setoran-berkala']->avatar) ? asset('storage/'.$data['setoran-berkala']->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle" />
                                </div>
                                <div class="align-self-center media-body">
                                    <div id="no_anggota" >
                                        @if(!empty($data['penjualan']->no_anggota))
                                            <span>No. {{$data['penjualan']->no_anggota}}</span>
                                        @else
                                            <div style="height:15px;width:80%;background:whitesmoke"></div>
                                        @endif
                                    </div>
                                    <div id="nama_lengkap">
                                        @if(!empty($data['penjualan']->nama_lengkap))
                                            <h5>{{$data['penjualan']->nama_lengkap}}</h5>
                                        @else
                                            <div style="height:20px;width:100%;background:whitesmoke" class="mt-2"></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pos-header">
                        <table class="table table-middle table-hover m-0">
                            <thead>
                            <tr style="background:#eff2f7">
                                <th width="20px">No</th>
                                <th>Nama Barang</th>
                                <th class="center" style="width:150px">Jumlah</th>
                                <th style="text-align:right;width:120px">Harga</th>
                                <th class="center" style="width:90px">Diskon</th>
                                <th style="text-align:right;width:120px">Sub Total</th>
                                <th style="width:50px"></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                    <div style="height:calc(100vh - 430px);margin-bottom:20px;overflow:scroll;">
                        <table class="table table-middle table-hover">
                            <tbody>
                            @foreach ($data['items'] as $key => $value)
                                <tr>
                                    <td width="20px">{{$key+1}}</td>
                                    <td>
                                        <input type="hidden" name="items[]" value="{{$value->id}}" >
                                        <div class="media">
                                            <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px;border:1px solid #e4e4e4">
                                                <img src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                                            </div>
                                            <div class="align-self-center media-body">
                                                <span>Kode. {{$value->kode}}</span>
                                                <h6>{{$value->nama_produk}}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="width:150px">
                                        <input id="jumlah_{{$value->id}}" {{($readonly=='readonly' ? 'disabled' : '')}} onkeyup="calc_items()" name="jumlah[{{$value->id}}]" value="{{$value->jumlah}}" data-toggle="touchspin" class="center"  type="text" data-max="{{$value->sisa}}" >
                                    </td>
                                    <td style="text-align:right;width:120px"><input type="text" style="text-align:right;" class="form-control" value="{{number_format($value->harga,0,',','.')}}" readonly ></td>
                                    <td class="center" style="width:90px">
                                        <input class="center form-control"  type="text" id="diskon_{{$value->id}}" {{($readonly=='readonly' ? 'disabled' : '')}} onchange="calc_items()" name="diskon_item[{{$value->id}}]" value="{{$value->diskon}}" >
                                    </td>
                                    <td style="text-align:right;width:120px">
                                        <h6 style="font-weight:500;font-size:16px" id="total_{{$value->id}}">{{number_format($value->total,0,',','.')}}</h6>
                                    </td>
                                    <td style="width:50px">
                                        <div class="text-center">
                                            <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pos-footer">
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-3">
                                <div class="form-group mb-0" style="text-align:right">
                                    <label>Sub Total (Rp)</label>
                                    <input type="text" class="form-control autonumeric" id="subtotal" readonly style="text-align:right" value="{{(!empty($data['penjualan']) ? $data['penjualan']->subtotal : 0 )}}" data-a-dec="," data-a-sep=".">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0" style="text-align:right">
                                    <label>Diskon (%)</label>
                                    <input type="text" class="form-control" onkeyup="calc_items()" {{$readonly}} id="diskon" style="text-align:right" name="diskon" value="{{(!empty($data['penjualan']) ? $data['penjualan']->diskon : 0 )}}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0" style="text-align:right">
                                    <label>Total (Rp)</label>
                                    <input type="text" class="form-control autonumeric" name="total_pembayaran" id="total" readonly style="text-align:right" value="{{(!empty($data['penjualan']) ? $data['penjualan']->total : 0 )}}" data-a-dec="," data-a-sep="." >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="coloum-right" style="position:relative;">
                    <div style="padding:20px;height:calc(100vh - 300px);margin-bottom:20px;overflow:scroll;">
                        <div class="form-group">
                            <label>No. Transaksi</label>
                            <input type="text" class="form-control" name="no_transaksi" value="{{(!empty($data['penjualan']) ? $data['penjualan']->no_transaksi : null)}}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Metode Pembayaran</label>
                            <select class="form-control select2" name="metode_pembayaran" {{($readonly=='readonly' ? 'disabled' : '')}} id="metode_pembayaran" >
                                @foreach ($data['metode-pembayaran'] as $key => $value)
                                    <option value="{{$value->id}}" {{(!empty($data['penjualan']) && $data['penjualan']->fid_metode_pembayaran == $value->id ? 'selected' : '' )}} >{{$value->keterangan}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="voucher_belanja">
                            <div class="form-group">
                                <label>Voucher Belanja</label>
                                <div class="row gutter-2">
                                    <div class="col-md-6">
                                        <select class="select2 form-control" id="voucher_type" {{($readonly=='readonly' ? 'disabled' : '')}} name="voucher_type" >
                                            <option value="nominal" {{(!empty($data['penjualan']) && $data['penjualan']->tipe_voucher == 'nominal' ? 'selected' : '' )}} >Nominal (Rp)</option>
                                            <option value="persen" {{(!empty($data['penjualan']) && $data['penjualan']->tipe_voucher == 'persen' ? 'selected' : '' )}}>Persen (%)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group" id="voucher_nominal">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" class="form-control autonumeric" id="voucher_nominal_input" {{$readonly}} name="voucher_nominal" value="{{(!empty($data['penjualan']->voucher_nominal) ? $data['penjualan']->voucher_nominal : 0 )}}" onkeyup="calc_items()" data-a-dec="," data-a-sep="." >
                                        </div>
                                        <div class="input-group" id="voucher_persen">
                                            <input type="text" class="form-control" id="voucher_persen_input" {{$readonly}} name="voucher_persen" onkeyup="calc_items()" value="{{(!empty($data['penjualan']->voucher_persen) ? $data['penjualan']->voucher_persen : 0 )}}" >
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Kode Voucher</label>
                                <input type="text" class="form-control" name="kode_voucher" {{$readonly}} value="{{(!empty($data['penjualan']) ? $data['penjualan']->kode_voucher : '' )}}" >
                            </div>
                        </div>
                        <div id="form_1" class="form-payment">
                            <div class="row gutter-2">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tunai (Rp)</label>
                                        <input type="text" class="form-control autonumeric" {{$readonly}} onkeyup="calc_items()" autocomplete="off" id="tunai" name="tunai" value="{{(!empty($data['penjualan']->tunai) ? $data['penjualan']->tunai : 0 )}}" data-a-dec="," data-a-sep="." >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kembali (Rp)</label>
                                        <input type="text" class="form-control autonumeric" id="kembali" name="kembali" value="{{(!empty($data['penjualan']) ? $data['penjualan']->kembali : 0 )}}" data-a-dec="," data-a-sep="." readonly >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="form_3" class="form-payment">
                            <h5>Sisa Limit Kredit/Angsuran</h5>
                            <h4 id="limit_anggota"></h4>
{{--                            <input type="hidden" name="bulan"  id="bulan">--}}
{{--                            <div class="form-group">--}}
{{--                                <label>Update Gaji Pokok (Rp)</label>--}}
{{--                                <input type="text" class="form-control autonumeric" {{$readonly}} onkeyup="calc_items()" autocomplete="off" id="gaji_pokok" name="gaji_pokok"  data-a-dec="," data-a-sep="." >--}}
{{--                                <div id="bulan_gaji" style="text-align:right;margin-top:5px;font-size:12px"></div>--}}
{{--                            </div>--}}
                        </div>
                        <div id="form_5" class="form-payment">
                            <div class="form-group">
                                <label>Nomor Debet Card</label>
                                <input type="text" class="form-control" {{$readonly}} name="no_debit_card" value="{{(!empty($data['penjualan']) ? $data['penjualan']->no_debit_card : 0)}}" >
                            </div>
                        </div>
                        <div id="form_7" class="form-payment">
                            <div class="form-group">
                                <label>Nomor Akun <span id="payment_online_name"></span></label>
                                <input type="text" class="form-control" {{$readonly}} name="account_number" value="{{(!empty($data['penjualan']) ? $data['penjualan']->account_number : 0)}}" >
                            </div>
                        </div>
                        <div id="alert"></div>
                    </div>
                    @if($action=='edit')
                        <div style="width:100%;position:absolute;bottom:0px;padding:20px">
                            @if($data['penjualan']->fid_status==3)
                                <div class="row gutter-2">
                                    <div class="col-md-6">
                                        <a href="{{url('pos/penjualan')}}" class="btn btn-dark btn-block">Kembali</a>
                                    </div>
                                    <div class="col-md-6">
                                        <button class="btn btn-info btn-block" type="button" onclick="batalkan('open')" >Buka Pembatalan</button>
                                    </div>
                                </div>
                            @else
                                <div class="row gutter-2">
                                    <div class="col-md-6">
                                        <button class="btn btn-danger btn-block" type="button" onclick="batalkan('close')">Batalkan</button>
                                    </div>
                                    <div class="col-md-6">
                                        <button class="btn btn-primary btn-block" name="action" value="simpan" {{($readonly=='readonly' ? 'disabled' : '')}} >Simpan</button>
                                    </div>
                                </div>
                                <div class="row gutter-2" style="margin-top:10px">
                                    <div class="col-md-6">
                                        <a href="{{url('pos/penjualan')}}" class="btn btn-dark btn-block">Kembali</a>
                                    </div>
                                    <div class="col-md-6">
                                        @if($data['penjualan']->fid_status==3)
                                            <button class="btn btn-info btn-block" type="button" onclick="cetak()">Cetak Struk</button>
                                        @else
                                            <button class="btn btn-info btn-block" name="action" value="bayar" {{($readonly=='readonly' ? 'disabled' : '')}} >Bayar</button>
                                        @endif
                                    </div>
                                </div>
                                <button class="btn btn-warning btn-block mt-3" name="action" value="hold" {{($readonly=='readonly' ? 'disabled' : '')}} >HOLD</button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <input type="hidden" name="id" value="{{$id}}">
            <input type="hidden" name="fid_anggota" value="{{(!empty($data['penjualan']->no_anggota) ? $data['penjualan']->no_anggota : null)}}" id="fid_anggota">
            <input type="hidden" id="anggota_id" value="{{(!empty($data['penjualan']) ? $data['penjualan']->anggota_id : '')}}" >
        </form>
    </div>
    @foreach ($data['items'] as $key => $value)
        <form action="{{url('pos/penjualan/delete_items')}}" method="post" id="hapus{{$value->id}}">
            {{ csrf_field()}}
            <input type="hidden" name="id" value="{{$value->id}}">
        </form>
    @endforeach
    <form action="{{url('pos/penjualan/proses_pembatalan')}}" method="post" id="proses_pembatalan">
        {{ csrf_field()}}
        <input type="hidden" name="id" value="{{$id}}">
        <input type="hidden" name="status" id="status">
    </form>
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
    <div id="modal-barang" class="modal fade right">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Cari Barang</h5>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="" id="search_barang" name="search" placeholder="Cari Barang">
                        <div class="input-group-append">
                            <button class="btn btn-dark" id="btn-search" onclick="search_barang()">Search</button>
                        </div>
                    </div>
                    <div id="loading-barang"><img src="{{asset('assets/images/loading.gif')}}" style="width:100px"></div>
                    <div id="list-barang" ></div>
                </div>
            </div>
        </div>
    </div>
    <div id="detail_angsuran" class="modal fade right">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Detail Angsuran</h5>
                </div>
                <div class="modal-body">
                    <div class="media mb-4">
                        <div class="avatar-wrapper" id="avatar" style="height:60px;width:60px">
                            <img src="{{asset('assets/images/user-avatar-placeholder.png')}}" alt="" />
                        </div>
                        <div class="align-self-center media-body">
                            <div id="no_anggota" >
                                <div style="height:15px;width:80%;background:whitesmoke"></div>
                            </div>
                            <div id="nama_lengkap">
                                <div style="height:20px;width:100%;background:whitesmoke" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="list-content">
                        <span>Angsuran Simpanan</span>
                        <div id="angsuran_simpanan" class="info-content"></div>
                    </div>
                    <div class="list-content">
                        <span>Setoran Berkala</span>
                        <div id="setoran_berkala" class="info-content"></div>
                    </div>
                    <div class="list-content">
                        <span>Angsuran Pinjaman</span>
                        <div id="angsuran_pinjaman" class="info-content"></div>
                    </div>
                    <div class="list-content">
                        <span>Angsuran Belanja</span>
                        <div id="angsuran_belanja" class="info-content"></div>
                    </div>
                    <div class="list-content">
                        <span>Total Angsuran</span>
                        <div id="total_angsuran" class="info-content"></div>
                    </div>
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
            $('#metode_pembayaran').trigger('change');
            $('#voucher_type').trigger('change');
            calc_items();
        });

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

        function search_barang(){
            var search = $('#search_barang').val();
            if(search !== ''){ search = '/'+search }
            else{ search = '/all'}
            $('#loading-barang').show();
            $('#list-barang').hide();
            $.get("{{ url('api/get_produk2/') }}"+search,function (result) {
                $('#list-barang').html('');
                $.each(result,function(i,value){
                    $('#list-barang').append(`<div class="list-barang" onclick="pilih_barang('`+value.kode+`')">`+
                        '<div class="media">'+
                        '<div class="avatar-thumbnail avatar-sm rounded-circle mr-2">'+
                        '<img style="margin-right:10px;" src="'+value.foto+'" alt="" style="max-width:none" class="rounded-circle">'+
                        '</div>'+
                        '<div class="media-body align-self-center" >'+
                        '<p class="text-muted mb-0">Kode : '+value.kode+'</p>'+
                        '<h5 class="text-truncate font-size-16">'+value.nama_produk+'</h5>'+
                        '</div>'+
                        '</div>'+
                        '</div>');
                });
                $('#loading-barang').hide();
                $('#list-barang').show();
            });
        };

        function batalkan(jenis){
            if(jenis == 'open'){
                msg = 'Apakah anda yakin ingin membuka pembatalan transaksi ini';
                status = 1;
            }
            else{
                msg = 'Apakah anda yakin ingin mebatalkan transaksi ini';
                status = 4;
            }
            Swal.fire({
                title: "Are you sure?",
                text: msg,
                type:"question",
                showCancelButton: true,
                confirmButtonColor: '#d63030',
                cancelButtonColor: '#cbcbcb',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.value == true) {
                    $('#status').val(status);
                    $('#proses_pembatalan').submit();
                }
            });
        }

        @if(!empty($data['penjualan']))
        pilih_anggota('{{$data['penjualan']->anggota_id}}');
        @endif

        function cari_barang() {
            search_barang();
            $('#modal-barang').modal('show');
        }

        function pilih_barang(kode) {
            $('#kode').val(kode);
            $('#modal-barang').modal('hide');
        }

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
                    $('#gaji_pokok').val(accounting.formatNumber(result.gaji_pokok,0,'.',','));
                    $('#bulan').val(result.bulan);
                    $('#bulan_gaji').html('Bulan '+result.bulan_tampil);
                    calc_items();
                    $('#modal-anggota').modal('hide');
                });
            }
        }

        add_commas = (nStr) => {
            nStr += '';
            let x = nStr.split('.');
            let x1 = x[0];
            let x2 = x.length > 1 ? '.' + x[1] : '';
            let rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + '.' + '$2');
            }
            return x1 + x2;
        }
        remove_commas = (nStr) => {
            nStr = nStr.replace(/\./g,'');
            return nStr;
        }

        function check_limit(id) {
            {{--$.get("{{ url('pos/penjualan/check_limit') }}?id=" + id, function (result) {--}}
            {{--    console.log(result);--}}
            {{--    $('#limit_anggota').html(add_commas(result));--}}
            {{--});--}}
        }

        function detail_angsuran(id){
            $.get("{{ url('api/find_anggota') }}/"+id,function(result){
                $('#detail_angsuran #no_anggota').html('<span>No. '+result.no_anggota+'</span>');
                $('#detail_angsuran #nama_lengkap').html('<h5>'+result.nama_lengkap+'</h5>');
                $('#detail_angsuran #avatar').html('<img src="'+result.avatar+'" alt="" >');
                $('#detail_angsuran #angsuran_simpanan').html('Rp 350.000');
                $('#detail_angsuran #setoran_berkala').html('Rp '+accounting.formatNumber(result.setoran_berkala,0,'.',','));
                total_angsuran_pinjaman=result.angsuran_jangka_panjang+result.angsuran_jangka_pendek+result.angsuran_barang;
                $('#detail_angsuran #angsuran_pinjaman').html('Rp '+accounting.formatNumber(total_angsuran_pinjaman,0,'.',','));
                total_angsuran_belanja=result.angsuran_belanja_toko+result.angsuran_belanja_online+result.angsuran_belanja_konsinyasi;
                $('#detail_angsuran #angsuran_belanja').html('Rp '+accounting.formatNumber(total_angsuran_belanja,0,'.',','));
                total_angsuran=total_angsuran_pinjaman+total_angsuran_belanja+result.setoran_berkala+350000;
                $('#detail_angsuran #total_angsuran').html('Rp '+accounting.formatNumber(total_angsuran,0,'.',','));
                $('#detail_angsuran').modal('show');
            });
        }

        function calc_items(){
            $.get("{{ url('api/get_items_penjualan/'.$id) }}",function (result) {
                subtotal=0;

                $.each(result,function(i,value){
                    jumlah=$('#jumlah_'+value.id).val();
                    diskon=$('#diskon_'+value.id).val();
                    harga_diskon=value.harga_jual - (value.harga_jual*diskon/100);
                    harga=harga_diskon*jumlah;
                    $('#total_'+value.id).html(accounting.formatNumber(harga,0,'.',','));
                    subtotal=subtotal+harga;
                });

                $('#subtotal').val(accounting.formatNumber(subtotal,0,'.',','));
                diskon=$('#diskon').val()*subtotal/100;
                total=subtotal-diskon;
                $('#total').val(accounting.formatNumber(total,0,'.',','));

                // gaji_pokok =$('#gaji_pokok').val();
                // gaji_pokok = gaji_pokok.split('.').join('');
                gaji_pokok = '';

                metode_pembayaran=$('#metode_pembayaran').val();

                $.get("{{ url('api/find_metode_pembayaran') }}/"+metode_pembayaran,function(result){

                    voucher_type=$('#voucher_type').val();
                    if(voucher_type=='persen'){
                        voucher_persen = $('#voucher_persen_input').val();
                        nominal_voucher = voucher_persen*total/100;
                    }
                    else{
                        nominal_voucher = $('#voucher_nominal_input').val();
                        nominal_voucher = nominal_voucher.split('.').join('');
                    }

                    if(result.fid_metode_pembayaran==1){
                        tunai=$('#tunai').val();
                        tunai=parseInt(tunai.split('.').join(''));

                        if(nominal_voucher === ""){
                            kembali=tunai-total;
                        }
                        else{
                            kembali=(parseInt(tunai)+parseInt(nominal_voucher))-parseInt(total);
                        }
                        $('#kembali').val(accounting.formatNumber(kembali,0,'.',','));
                        $('#alert').hide();
                    }
                    else if(result.fid_metode_pembayaran==3){
                        $('#alert').show();
                        anggota_id = $('#anggota_id').val();
                        check_limit(anggota_id);
                        if(anggota_id != 0 ){
                            $.get("{{ url('api/find_anggota') }}/"+anggota_id,function(result2){
                                // total_angsuran_pinjaman=result2.angsuran_jangka_panjang+result2.angsuran_jangka_pendek+result2.angsuran_barang;
                                // total_angsuran_belanja=result2.angsuran_belanja_toko+result2.angsuran_belanja_online+result2.angsuran_belanja_konsinyasi+total;
                                // total_angsuran=total_angsuran_pinjaman+total_angsuran_belanja+350000+result2.setoran_berkala;
                                // if(total_angsuran > gaji_pokok){
                                //     color='danger';
                                //     note='Belum bisa belanja dengan pembayaran secara kredit sebesar Rp '+accounting.formatNumber(total,0,'.',',')+', karena total angsuran per bulan melebihi gaji pokok sebesar Rp '+accounting.formatNumber(gaji_pokok,0,'.',',');
                                //     link='<div class="mt-2"><a href="javascript:;" style="color:#b80000" onclick="detail_angsuran('+anggota_id+')">Lihat Detail Angsuran</a></div>';
                                //     $("[name='action']").prop('disabled', true);
                                // }
                                // else{
                                //     if(total_angsuran_belanja > 1500000){
                                //         color='danger';
                                //         note='Belum bisa belanja dengan pembayaran secara kredit sebesar Rp '+accounting.formatNumber(total,0,'.',',')+', karena angsuran kredit belanja melebihi limit Rp 1.500.000';
                                //         link='<div class="mt-2"><a href="javascript:;" style="color:#b80000" onclick="detail_angsuran('+anggota_id+')">Lihat Detail Angsuran</a></div>';
                                //         $("[name='action']").prop('disabled', true);
                                //     }
                                //     else{
                                //         color='success';
                                //         note='Silahkan lanjutkan belanja dengan pembayaran secara kredit sebesar Rp '+accounting.formatNumber(total,0,'.',',');
                                //         link='';
                                //         $("[name='action']").prop('disabled', false);
                                //     }
                                // }
                                // alert='<div class="alert alert-'+color+'" role="alert">'+
                                //     note+' '+link
                                // '</div>';
                                // $('#alert').html(alert);
                            });
                        }
                    }
                    else{
                        $('#alert').hide();
                    }
                });
            });
        }

        $('#metode_pembayaran').on('change', function() {
            let id = this.value;
            $.get("{{ url('api/find_metode_pembayaran') }}/"+id,function(result){
                $('.form-payment').hide();
                $('#form_'+result.fid_metode_pembayaran).show();
                if(result.fid_metode_pembayaran==7){
                    $('#payment_online_name').html(result.keterangan);
                }

                if(result.fid_metode_pembayaran==3){
                    $('#voucher_belanja').hide();
                    check_limit_anggota();
                }
                else{
                    $('#voucher_belanja').show();
                }
                calc_items();
            });
        });

        $('#voucher_type').on('change', function() {
            id=this.value;
            if(id=='persen'){
                $('#voucher_persen').show();
                $('#voucher_nominal').hide();
            }
            else{
                $('#voucher_persen').hide();
                $('#voucher_nominal').show();
            }
        });

        let check_limit_anggota = () => {
            let  anggota_id = $('#fid_anggota').val();
            if (anggota_id === '') {
                swal.fire('Pilih anggota dahulu !');
            } else {
                $.get("{{ url('pos/penjualan/check_limit') }}?fid_anggota=" + anggota_id, (result) => {
                    console.log(result);
                    limit = result;
                    $('#limit_anggota').html(add_commas(limit));
                });
            }
        }
    </script>
@endsection
