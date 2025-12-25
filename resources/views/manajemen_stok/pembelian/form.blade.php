@php
    $app='manajemen_barang';
    $page='Pembelian Barang';
    $subpage='Pembelian Barang';
@endphp
@extends('layouts.kasir')
@section('title')
    Pembelian Barang |
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
        .table-form td{
            padding: .3rem;
            vertical-align: center;
            border-top: none;
        }

        .table-form .row{
            margin-right: -0.3rem;
            margin-left: -0.3rem;
        }

        .table-form .row .col-md-4,
        .table-form .row .col-md-8{
            padding-right: 0.3rem;
            padding-left: 0.3rem;
        }

        .pos-container{
            display: flex !important;
        }
        .pos-container .coloum-left{
            width:100%;
            background:#fff;
        }
        .pos-container .coloum-right{
            width:400px;
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
        <form action="{{url('manajemen_stok/pembelian/proses')}}" method="post" id="proses_pembelian">
            {{ csrf_field() }}
            <div class="pos-container" style="height:calc(100vh - 180px)">
                <div class="coloum-left" style="position:relative">
                    <div style="padding:20px;background:#fff">
                        <div class="input-group">
                            <input type="text" style="margin:0px" id="kode" name="kode" class="form-control" placeholder="Cari Data Barang">
                            <div class="input-group-append">
                                <button class="btn btn-secondary" type="submit" name="action" value="add_barang" >Tambahkan</button>
                            </div>
                        </div>
                    </div>
                    <div class="pos-header">
                        <table class="table table-middle table-hover m-0">
                            <thead class="thead-light">
                            <tr>
                                <th width="50px">No</th>
                                <th>Nama Barang</th>
                                <th class="center" style="width:140px">Jumlah<br>Barang</th>
                                <th class="center" style="width:140px">Harga Satuan <hr class="line-xs"> Total Harga</th>
                                <th class="center" style="width:250px">Margin Penjualan<hr class="line-xs">Harga Jual</th>
                                <th style="width:50px"></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                    <div style="height:calc(100vh - 430px);margin-bottom:20px;overflow:scroll;">
                        <table class="table table-middle table-hover">
                            <tbody>
                            @if(!empty($data['items']))
                                @foreach ($data['items'] as $key => $value)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>
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
                                        <td class="center" style="width:140px">
                                            <input data-toggle="touchspin" onchange="calc_items()" class="center" id="jumlah_{{$value->id}}" name="jumlah[{{$value->id}}]" type="text" value="{{$value->jumlah}}">
                                            <div class="mt-1" style="text-align:right;color:#444444">{{$value->satuan}}</div>
                                        </td>
                                        <td style="text-align:right;width:140px">
                                            <input type="text" style="text-align:right" onkeyup="calc_items()" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="harga_{{$value->id}}" name="harga[{{$value->id}}]" value="{{$value->harga}}">
                                            <div id="total_harga_{{$value->id}}" class="mt-1" style="text-align:right;color:#444444">Rp {{number_format($value->total,0,',','.')}}</div>
                                        </td>
                                        <td style="text-align:right;width:250px">
                                            <div style="display:flex">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Rp</span>
                                                    </div>
                                                    <input type="text" class="form-control autonumeric" name="margin_nominal[{{$value->id}}]" id="nominal_margin_{{$value->id}}" value="{{$value->margin_nominal}}" onkeyup="calc_items('nominal')" data-a-dec="," data-a-sep="." style="border-radius:0px"  >
                                                </div>
                                                <div class="input-group" style="width:130px" >
                                                    <input type="text"class="form-control" style="border-radius:0px;margin-left:-1px;text-align:center" onkeyup="calc_items('margin')" id="margin_{{$value->id}}" name="margin[{{$value->id}}]" value="{{$value->margin}}" required >
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><i class="mdi mdi-percent-outline"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="harga_jual_{{$value->id}}" class="mt-1" style="text-align:right;color:#444444">Rp {{number_format($value->harga_jual,0,',','.')}}</div>
                                        </td>
                                        <td style="width:1px;white-space:nowrap;">
                                            <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="pos-footer">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label>Sub Total</label>
                                    <input type="text" name="subtotal" value="{{(!empty($data['pembelian']) ? $data['pembelian']->subtotal : 0)}}" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="subtotal" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>Diskon </label>
                                    <div style="display:flex">
                                        <div class="input-group" style="width:150px" >
                                            <input type="text" name="diskon_persen" style="text-align:center;margin-right:-1px" onkeyup="calc_transaksi()" value="{{(!empty($data['pembelian']) ? $data['pembelian']->diskon_persen : 0 )}}" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="diskon_persen">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="mdi mdi-percent-outline"></i></span>
                                            </div>
                                        </div>
                                        <div class="input-group">
                                            <div class="input-group-append">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" name="diskon_nominal" style="margin-left:-1px" value="{{(!empty($data['pembelian']) ? $data['pembelian']->diskon_nominal : '')}}" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="diskon_nominal" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>PPN</label>
                                    <div style="display:flex">
                                        <div class="input-group" style="width:150px" >
                                            <input type="text" name="ppn_persen" style="text-align:center;margin-right:-1px" onkeyup="calc_transaksi()" value="{{(!empty($data['pembelian']) ? $data['pembelian']->ppn_persen : 0 )}}" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="ppn_persen">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="mdi mdi-percent-outline"></i></span>
                                            </div>
                                        </div>
                                        <div class="input-group">
                                            <div class="input-group-append">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" name="ppn_nominal" style="margin-left:-1px" value="{{(!empty($data['pembelian']) ? $data['pembelian']->ppn_nominal : '')}}" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="ppn_nominal" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label>Biaya Tambahan</label>
                                    <input type="text" name="biaya_tambahan" onkeyup="calc_transaksi()" value="{{(!empty($data['pembelian']) ? $data['pembelian']->biaya_tambahan : 0)}}" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="biaya_tambahan">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label>Total Pembayaran</label>
                                    <input type="text" name="total" value="{{(!empty($data['pembelian']) ? $data['pembelian']->total : 0)}}" class="form-control autonumeric" data-a-dec="," data-a-sep="." id="total" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="coloum-right" style="position:relative;">
                    <div style="padding:20px;height:calc(100vh - 300px);margin-bottom:20px;overflow:scroll;">
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="text" name="tanggal" value="{{(!empty($data['pembelian']) ? \App\Helpers\GlobalHelper::dateFormat($data['pembelian']->tanggal,'d-m-Y') : date('d-m-Y'))}}" autocomplete="off" class="datepicker form-control">
                        </div>
                        <div class="form-group">
                            <label>No. Pembelian</label>
                            <input type="text" name="no_pembelian" value="{{(!empty($data['pembelian']) ? $data['pembelian']->no_pembelian : '')}}" autocomplete="off" class="form-control" readonly >
                        </div>
                        <div class="form-group">
                            <label>Supplier</label>
                            <select name="supplier" class="form-control select2">
                                @foreach ($data['supplier'] as $key => $value)
                                    <option value="{{$value->id}}" {{(!empty($data['pembelian']) && $data['pembelian']->fid_supplier == $value->id ? 'selected' : '')}} >{{$value->nama_supplier}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea name="keterangan"  class="form-control" style="height:100px">{{(!empty($data['pembelian']) ? $data['pembelian']->keterangan : '')}}</textarea>
                        </div>
                    </div>
                    <div style="width:100%;position:absolute;bottom:0px;padding:20px">
                        <input type="hidden" name="id" value="{{$id}}">
                        <div class="row gutter-2">
                            <div class="col-md-6">
                                <a class="btn btn-secondary btn-block" href="{{url('manajemen_stok/pembelian')}}" >Kembali</a>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-primary btn-block" type="submit" name="action" value="simpan">Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @if(!empty($data['items']))
        @foreach ($data['items'] as $key => $value)
            <form action="{{url('manajemen_stok/pembelian/proses')}}" method="post" id="hapus{{$value->id}}">
                {{ csrf_field()}}
                <input type="hidden" name="id" value="{{$id}}">
                <input type="hidden" name="produk_id" value="{{$value->fid_produk}}">
                <input type="hidden" name="action" value="delete_items">
            </form>
        @endforeach
    @endif
    <div id="modal-produk" class="modal fade right">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Pilih Produk</h5>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="" id="search" name="search" placeholder="Cari Produk">
                        <div class="input-group-append">
                            <button class="btn btn-dark" id="btn-search" onclick="search_produk()">Search</button>
                        </div>
                    </div>
                    <div id="loading"><img src="{{asset('assets/images/loading.gif')}}" style="width:100px"></div>
                    <div id="list-produk" ></div>
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
        calc_items();
        function calc_items(jenis='all'){
            $.get("{{ url('api/get_items_pembelian/'.$id) }}",function (result) {
                subtotal=0;
                $.each(result,function(i,value){
                    jumlah=$('#jumlah_'+value.id).val();
                    harga=$('#harga_'+value.id).val();
                    harga=harga.split('.').join('');
                    total_harga=harga*jumlah;
                    $('#total_harga_'+value.id).html('Rp '+accounting.formatNumber(total_harga,0,'.',','));
                    margin=$('#margin'+value.id).val();
                    if(jenis == 'margin'){
                        margin=$('#margin_'+value.id).val();
                        nominal_margin=margin*harga/100;
                        $('#nominal_margin_'+value.id).val(accounting.formatNumber(nominal_margin,0,'.',','));
                    }
                    else{
                        nominal_margin=$('#nominal_margin_'+value.id).val();
                        nominal_margin = nominal_margin.split('.').join('');
                        margin = (nominal_margin/harga)*100;
                        $('#margin_'+value.id).val(accounting.formatNumber(margin,0,'.',','));
                    }
                    harga_jual=(harga*margin/100)+parseInt(harga);
                    $('#harga_jual_'+value.id).html('Rp '+accounting.formatNumber(harga_jual,0,'.',','));
                });
            });
        }

        calc_transaksi();
        function calc_transaksi(){
            subtotal=$('#subtotal').val();
            subtotal=subtotal.split('.').join('');
            diskon_nominal=subtotal*$('#diskon_persen').val()/100;
            ppn_nominal=(subtotal-diskon_nominal)*$('#ppn_persen').val()/100;
            biaya_tambahan=$('#biaya_tambahan').val();
            biaya_tambahan=biaya_tambahan.split('.').join('');
            biaya_tambahan=parseInt(biaya_tambahan);
            total=(subtotal-diskon_nominal)+ppn_nominal+biaya_tambahan;
            $('#diskon_nominal').val(accounting.formatNumber(diskon_nominal,0,'.',','));
            $('#ppn_nominal').val(accounting.formatNumber(ppn_nominal,0,'.',','));
            $('#total').val(accounting.formatNumber(total,0,'.',','));
        }
    </script>
@endsection
