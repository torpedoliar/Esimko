@php
    $app='pos';
    $page='Belanja '.ucfirst($jenis);
    $subpage='Belanja '.ucfirst($jenis);
    $readonly=(!empty($data['belanja']) ? ($data['belanja']->fid_status == 1  ? '' : 'readonly' ) : '');
@endphp
@extends('layouts.kasir')
@section('title')
    {{$page}} |
@endsection
@section('css')
    <link href="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
    <style>
        body{
            background: #f5f6fa;
        }
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
            width:380px;
            background:#ecf0f1;
            /* color:#fff */
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
        <form action="{{url('pos/belanja/'.$jenis.'/proses')}}" method="post" id="proses">
            {{ csrf_field() }}
            <div class="pos-container" style="height:calc(100vh - 180px)">
                <div class="coloum-left" style="position:relative">
                    <div style="padding:20px">
                        <div class="row">
                            <div class="col-md-3">
                                <button class="btn btn-primary btn-block" type="button" onclick="add_barang()" >Tambah Barang</button>
                            </div>
                            <div class="col-md-2">
                                <input type="text" id="tanggal" value="{{(!empty($data['belanja']) ? \App\Helpers\GlobalHelper::dateFormat($data['belanja']->tanggal,'d-m-Y') : date('d-m-Y'))}}" class="form-control datepicker" name="tanggal" autocomplete="off" name="tanggal" >
                            </div>
                            <div class="col-md-3">
                                <input type="text" value="{{(!empty($data['belanja']) ? $data['belanja']->no_transaksi : '')}}" class="form-control" placeholder="No. Transaksi" autocomplete="off" name="no_transaksi" {{($jenis=='konsinyasi' ? 'readonly' : '')}} >
                            </div>
                            @if($jenis=='online')
                                <div class="col-md-3">
                                    <input type="text" value="{{(!empty($data['belanja']) ? $data['belanja']->marketplace : '')}}" class="form-control" placeholder="Platform Marketplace" autocomplete="off" name="marketplace" >
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="height:calc(100vh - 430px);margin-bottom:20px;overflow:scroll;">
                        <table class="table table-middle table-hover">
                            <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Barang<hr class="line-xs">Nama Supplier</th>
                                <th class="center" style="width:1px;white-space:nowrap">Jumlah<br>Barang</th>
                                <th style="text-align:right;width:130px;white-space:nowrap">Harga Beli</th>
                                <th style="text-align:center;width:50px;white-space:nowrap">Margin</th>
                                <th style="text-align:right;width:130px;white-space:nowrap">Harga Jual</th>
                                <th style="text-align:right;width:130px;white-space:nowrap">Total Harga</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($data['items'] as $key => $value)
                                <tr>
                                    <td style="width:1px;white-space:nowrap">{{$key+1}}</td>
                                    <td>
                                        <h6>{{$value->nama_barang}}</h6>
                                        <span>{{$value->nama_supplier}}</span>
                                    </td>
                                    <td class="center">{{$value->jumlah}}<br>{{$value->satuan}}</td>
                                    <td style="text-align:right;white-space:nowrap">Rp {{number_format(($value->harga_beli ?? 0),0,',','.')}}</td>
                                    <td style="text-align:center;white-space:nowrap">{{$value->margin}}%</td>
                                    <td style="text-align:right;white-space:nowrap">Rp {{number_format($value->harga,0,',','.')}}</td>
                                    <td style="text-align:right;white-space:nowrap">Rp {{number_format($value->total,0,',','.')}}</td>
                                    <td style="width:1px;white-space:nowrap">
                                        <div class="text-center">
                                            <a href="javascript:;" onclick="edit_barang({{$value->id}})" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                                            <a href="javascript:;" onclick="confirmDelete({{$value->id}})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pos-footer">
                        <div class="row">
{{--                            <div class="col-md-2">--}}
{{--                                <div class="form-group mb-0">--}}
{{--                                    <label>Gaji Pokok</label>--}}
{{--                                    <input type="text" id="gaji_pokok" onkeyup="calc_belanja()" style="text-align:right" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="gaji_pokok" >--}}
{{--                                    <input type="hidden" name="bulan"  id="bulan">--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="col-md-4">--}}
{{--                                <div class="form-group mb-0">--}}
{{--                                    <label>Upload Slip Gaji</label>--}}
{{--                                    <div class="custom-file">--}}
{{--                                        <input type="file" class="custom-file-input" id="customFile" name="attachment">--}}
{{--                                        <label class="custom-file-label" for="customFile">Choose file</label>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="col-md-6">
                                <h5 class="text-white">Sisa Limit Kredit/Angsuran</h5>
                                <h4 class="text-white" id="limit_anggota"></h4>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label>Total</label>
                                    <input type="text" class="form-control autonumeric" name="total_pembayaran" id="total_pembayaran" readonly style="text-align:right" value="{{(!empty($data['belanja']) ? $data['belanja']->total_pembayaran : 0 )}}" data-a-dec="," data-a-sep=".">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label>Tenor</label>
                                    <input type="text" style="text-align:center" data-toggle="touchspin" value="{{(!empty($data['belanja']->tenor) ? $data['belanja']->tenor : 1)}}" class="form-control" name="tenor" id="tenor" onchange="calc_belanja()" >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label>Angsuran</label>
                                    <input type="text" style="text-align:right" value="{{(!empty($data['belanja']) ? $data['belanja']->angsuran : '')}}" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="angsuran" id="angsuran" readonly >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="coloum-right" style="position:relative;">
                    <div style="padding:20px;height:calc(100vh - 300px);margin-bottom:20px;overflow:scroll;">
                        <div class="media" style="cursor:pointer;" onclick="pilih_anggota('show')" >
                            <div class="avatar-thumbnail avatar-sm rounded-circle mr-2" id="avatar" style="height:60px;width:60px">
                                <img src="{{(!empty($data['setoran-berkala']->avatar) ? asset('storage/'.$data['setoran-berkala']->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle" />
                            </div>
                            <div class="align-self-center media-body">
                                <div id="no_anggota" >
                                    @if(!empty($data['belanja']->no_anggota))
                                        <span>No. {{$data['belanja']->no_anggota}}</span>
                                    @else
                                        <div style="height:15px;width:80%;background:whitesmoke"></div>
                                    @endif
                                </div>
                                <div id="nama_lengkap">
                                    @if(!empty($data['belanja']->nama_lengkap))
                                        <h5>{{$data['belanja']->nama_lengkap}}</h5>
                                    @else
                                        <div style="height:20px;width:100%;background:whitesmoke" class="mt-2"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="list-content">
                            <span>Angsuran Simpanan</span>
                            <div id="angsuran_simpanan" class="info-content">Rp 0</div>
                        </div>
                        <div class="list-content">
                            <span>Angsuran Pinjaman</span>
                            <div id="angsuran_pinjaman" class="info-content">Rp 0</div>
                        </div>
                        <div class="list-content">
                            <span>Setoran Berkala</span>
                            <div id="setoran_berkala" class="info-content">Rp 0</div>
                        </div>
                        <div class="list-content">
                            <span>Angsuran Belanja</span>
                            <div id="total_angsuran_belanja" class="info-content">Rp 0</div>
                        </div>
                        <div class="list-content">
                            <span>Total Angsuran</span>
                            <div id="total_angsuran" class="info-content">Rp 0</div>
                        </div>
                        <hr>
                        <div id="alert"></div>
                    </div>
                    @if($action=='edit')
                        <div style="width:100%;position:absolute;bottom:0px;padding:20px">
                            @if($data['belanja']->fid_status==3)
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
                                        <button class="btn btn-primary btn-block" name="action" value="simpan" >Simpan</button>
                                    </div>
                                </div>
                                <a href="{{url('pos/belanja/konsinyasi')}}" class="btn btn-dark btn-block mt-2">Kembali</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <input type="hidden" name="id" value="{{$id}}">
            <input type="hidden" name="fid_anggota" value="{{(!empty($data['belanja']->no_anggota) ? $data['belanja']->no_anggota : null)}}" id="fid_anggota">
            <input type="hidden" id="anggota_id" value="{{(!empty($data['belanja']) ? $data['belanja']->anggota_id : '')}}" >
        </form>
    </div>
    @foreach ($data['items'] as $key => $value)
        <form action="{{url('pos/belanja/'.$jenis.'/items/proses')}}" method="post" id="hapus{{$value->id}}">
            {{ csrf_field()}}
            <input type="hidden" name="id" value="{{$value->id}}">
        </form>
    @endforeach
    <form action="{{url('pos/belanja/'.$jenis.'/proses_pembatalan')}}" method="post" id="proses_pembatalan">
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
    <div class="modal fade" id="form-barang">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{url('pos/belanja/'.$jenis.'/items/proses')}}" method="post" id="form_belanja">
                    {{ csrf_field()}}
                    <input type="hidden" class="form-control" name="fid_anggota" id="fid_anggota2" >
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Barang</label>
                            <input type="text" class="form-control" name="nama_barang" id="nama_barang" required >
                        </div>
                        <div class="form-group">
                            <label>Nama Supplier</label>
                            <input type="text" class="form-control" name="nama_supplier" id="nama_supplier" required >
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Jumlah Barang</label>
                                    <input data-toggle="touchspin" style="text-align:center" type="text" onchange="calc_items()" class="form-control" name="jumlah" id="jumlah" >
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label>Satuan</label>
                                    <input type="text" class="form-control" name="satuan" id="satuan" >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Harga Beli (Rp)</label>
                                    <input type="text" class="form-control autonumeric" data-a-dec="," data-a-sep="." onkeyup="calc_items()" name="harga_beli" id="harga_beli" >
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label>Margin Penjualan</label>
                                    <div style="display:flex">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" class="form-control autonumeric" onkeyup="calc_items('nominal')" data-a-dec="," data-a-sep="." name="margin_nominal" id="nominal_margin"  style="border-radius:0px"  >
                                        </div>
                                        <div class="input-group" style="width:130px" >
                                            <input type="text"class="form-control" style="border-radius:0px;margin-left:-1px;text-align:center" onkeyup="calc_items('margin')" id="margin" name="margin" required >
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="mdi mdi-percent-outline"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Harga Jual (Rp)</label>
                                    <input type="text" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="harga_jual" id="harga_jual" >
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label>Total (Rp)</label>
                                    <input type="text" class="form-control autonumeric" data-a-dec="," data-a-sep="." name="total_harga" id="total_harga" >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="id" id="id">
                        <input type="hidden" name="penjualan_id" value="{{$id}}">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
    <script src="{{asset('assets/js/pages/form-advanced.init.js')}}"></script>
    <script src="{{asset('assets/js/accounting.js')}}"></script>
    <script>
        boleh_simpan = true;
        $(function () {
            $('#tanggal').trigger('change');
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
                    $('#list-anggota').append(`<div class="list-anggota" onclick="pilih_anggota(`+value.id+`, '`+ value.no_anggota +`')">`+
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

        @if(!empty($data['belanja']))
        pilih_anggota('{{$data['belanja']->anggota_id}}');
        @endif

        function pilih_anggota(id, no_anggota = ''){
            if(id=='show'){
                search_anggota();
                $('#modal-anggota').modal('show');
            }
            else{
                $.get("{{ url('api/find_anggota') }}/"+id,function(result){
                    $('#fid_anggota2').val(no_anggota);
                    $('#anggota_id').val(id);
                    $('#avatar').html('<img src="'+result.avatar+'" alt="" class="rounded-circle" >');
                    $('#no_anggota').html('<span>No. '+result.no_anggota+'</span>');
                    $('#nama_lengkap').html('<h5>'+result.nama_lengkap+'</h5>');
                    $('#fid_anggota').val(result.no_anggota);

                    check_limit_anggota();

                    // $('#angsuran_belanja_toko').html('Rp '+accounting.formatNumber(result.angsuran_belanja_toko,0,'.',','));
                    // $('#angsuran_belanja_konsinyasi').html('Rp '+accounting.formatNumber(result.angsuran_belanja_konsinyasi,0,'.',','));
                    // $('#angsuran_belanja_online').html('Rp '+accounting.formatNumber(result.angsuran_belanja_online,0,'.',','));
                    // total_angsuran_belanja=result.angsuran_belanja_toko+result.angsuran_belanja_konsinyasi+result.angsuran_belanja_online;
                    // $('#total_angsuran_belanja').html('Rp '+accounting.formatNumber(total_angsuran_belanja,0,'.',','));
                    // $('#angsuran_simpanan').html('Rp 350.000');
                    // total_angsuran_berkala=result.setoran_berkala;
                    // $('#setoran_berkala').html('Rp '+accounting.formatNumber(total_angsuran_berkala,0,'.',','));
                    // total_angsuran_pinjaman=result.angsuran_jangka_pendek+result.angsuran_jangka_panjang+result.angsuran_barang;
                    // $('#angsuran_pinjaman').html('Rp '+accounting.formatNumber(total_angsuran_pinjaman,0,'.',','));
                    // total_angsuran=total_angsuran_belanja+total_angsuran_berkala+total_angsuran_pinjaman+350000;
                    // $('#total_angsuran').html('Rp '+accounting.formatNumber(total_angsuran,0,'.',','));
                    // $('#fid_anggota').val(result.no_anggota);
                    // $('#gaji_pokok').val(accounting.formatNumber(result.gaji_pokok,0,'.',','));
                    // $('#bulan').val(result.bulan);
                    // $('#bulan_gaji').html('bulan '+result.bulan_tampil);
                    // calc_belanja();
                    $('#modal-anggota').modal('hide');
                });
            }
        }

        $("#tanggal").change(function(){
            anggota_id = $('#anggota_id').val();
            tanggal=$('#tanggal').val();
            bulan_sekarang=tanggal.substr(-7);
            console.log(anggota_id);
            if(anggota_id != 0 ){
                $.get("{{ url('api/find_anggota') }}/"+anggota_id+"/"+bulan_sekarang,function(result){
                    $('#gaji_pokok').val(accounting.formatNumber(result.gaji_pokok,0,'.',','));
                    $('#bulan').val(result.bulan);
                    $('#bulan_gaji').html('Bulan '+result.bulan_tampil);
                });
            }
        });

        function calc_items(jenis='all'){
            jumlah=$('#jumlah').val();
            harga_beli=$('#harga_beli').val();
            harga_beli = harga_beli.split('.').join('');

            margin=$('#margin').val();

            if(jenis == 'nominal'){
                nominal_margin=$('#nominal_margin').val();
                nominal_margin = nominal_margin.split('.').join('');
                margin = (nominal_margin/harga_beli)*100;
                $('#margin').val(accounting.formatNumber(margin,0,'.',','));
            }
            else{
                margin=$('#margin').val();
                nominal_margin=margin*harga_beli/100;
                $('#nominal_margin').val(accounting.formatNumber(nominal_margin,0,'.',','));
            }

            harga_jual=parseInt(nominal_margin)+parseInt(harga_beli);
            total=harga_jual*jumlah;

            $('#harga_jual').val(accounting.formatNumber(harga_jual,0,'.',','));
            $('#total_harga').val(accounting.formatNumber(total,0,'.',','));
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

        function calc_belanja(){

            total_pembayaran=$('#total_pembayaran').val();
            total_pembayaran=total_pembayaran.split('.').join('');

            tenor=$('#tenor').val();

            angsuran=total_pembayaran/tenor;
            $('#angsuran').val(accounting.formatNumber(angsuran,0,'.',','));

            anggota_id = $('#anggota_id').val();
            tanggal=$('#tanggal').val();
            bulan_sekarang=tanggal.substr(-7);



            // if(anggota_id != 0 ){
            //   $.get("{{ url('api/find_anggota') }}/"+anggota_id+"/"+bulan_sekarang,function(result){

            //     gaji_pokok =$('#gaji_pokok').val();
            //     gaji_pokok = gaji_pokok.split('.').join('');

            //     total_angsuran_belanja=result.angsuran_belanja_toko+result.angsuran_belanja_online+result.angsuran_belanja_konsinyasi+angsuran;
            //     total_angsuran_pinjaman=result.angsuran_jangka_panjang+result.angsuran_jangka_pendek+result.angsuran_barang;
            //     total_angsuran=total_angsuran_belanja+total_angsuran_pinjaman+result.setoran_berkala+350000;

            //     if(total_angsuran <= gaji_pokok ){
            //       if(total_angsuran_belanja > 1500000){
            //         color='danger';
            //         note='Belum bisa melakukan pengajuan belanja {{$jenis}} dengan total angsuran Rp '+accounting.formatNumber(angsuran,0,'.',',')+', karena angsuran kredit belanja melebihi limit Rp 1.500.000';
            //         $("#action").prop('disabled', false);
            //       }
            //       else{
            //         color='success';
            //         note='Silahkan melakukan pengajuan belanja {{$jenis}} dengan total angsuran Rp '+accounting.formatNumber(angsuran,0,'.',',');
            //         $("#action").prop('disabled', false);
            //       }
            //     }
            //     else{
            //       color='danger';
            //       note='Belum bisa melakukan pengajuan kredit belanja {{$jenis}} dengan total angsuran Rp '+accounting.formatNumber(angsuran,0,'.',',')+' karena total angsuran melebihi Gaji Pokok. Silahkan masukkan jumlah kredit dan tenor yang sesuai atau ubah kembali nominal setoran berkala';
            //       $("#action").prop('disabled', true);
            //     }
            //     alert='<div class="alert alert-'+color+'" role="alert">'+note+'</div>';
            //     $('#alert').html(alert);
            //   });
            // }
        }

        function add_barang(){
            $('#nama_supplier').val('');
            $('#nama_barang').val('');
            $('#jumlah').val(1);
            $('#satuan').val('');
            $('#harga_beli').val(0);
            $('#margin').val(10);
            $('#nominal_margin').val(0);
            $('#harga_jual').val(0);
            $('#total_harga').val(0);
            $('#id').val(0);
            $('#action').val('add');
            $('#title').html('Tambah Barang');
            $('#form-barang').modal('show');
        }

        function edit_barang(id){
            $.get("{{ url('api/find_items_penjualan') }}/"+id,function(result){
                $('#nama_supplier').val(result.nama_supplier);
                $('#nama_barang').val(result.nama_barang);
                $('#jumlah').val(result.jumlah);
                $('#satuan').val(result.satuan);
                $('#harga_beli').val(accounting.formatNumber(result.harga_beli,0,'.',','));
                $('#margin').val(result.margin);
                $('#nominal_margin').val(accounting.formatNumber(result.margin_nominal,0,'.',','));
                $('#harga_jual').val(accounting.formatNumber(result.harga,0,'.',','));
                $('#total_harga').val(accounting.formatNumber(result.total,0,'.',','));
                $('#id').val(id);
                $('#action').val('edit');
                $('#title').html('Edit Barang');
                $('#form-barang').modal('show');
            });
        }

        let check_limit_anggota = () => {
            let  anggota_id = $('#fid_anggota').val();
            if (anggota_id === '') {
                swal.fire('Pilih anggota dahulu !');
            } else {
                $.get("{{ url('pos/penjualan/check_limit') }}?fid_anggota=" + anggota_id, (result) => {
                    console.log(result);
                    limit = result;
                    $('#limit_anggota').html(add_commas(limit));
                    if (result < 0) {
                        swal.fire('Anggota sudah melebihi limit pinjaman');
                        $('#form_belanja').attr('disabled', 'disabled');
                    } else {
                        $('#form_belanja').removeAttr('disabled');
                    }
                });
            }
        }
    </script>
@endsection
