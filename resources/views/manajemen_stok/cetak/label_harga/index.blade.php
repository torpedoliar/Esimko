@php
    $app='manajemen_barang';
    $page='Cetak Label';
    $subpage='Label Harga';
@endphp
@extends('layouts.admin')
@section('title')
    Label Harga |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="{{asset('assets/images/icon-page/tag.png')}}" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Cetak Label Harga</h4>
                        <p class="text-muted m-0">Halaman untuk mencetak label harga dari barang yang teserdia di toko</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <form action="" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" value="{{$search}}" placeholder="Cari Data Barang">
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary btn-block" data-toggle="modal" data-target='#filter-barang' >Tambahkan Barang</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-primary btn-block" href="{{url('manajemen_stok/cetak/label_harga?mode=cetak&search=' . $search)}}" target="_blank" >Cetak Label</a>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-warning btn-block" href="{{url('manajemen_stok/cetak/label_harga?mode=kosongi')}}" >Kosongi</a>
                </div>
            </div>
        </div>
        @if(count($data['produk'])==0)
            <div style="width:100%;text-align:center">
                <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
                <h4 class="mt-2">DATA BARANG TIDAK DITEMUKAN</h4>
            </div>
        @else
            <div class="table-responsive mt-4 mb-4">
                <table class="table table-middle table-custom" id="datatable">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Barang<hr class="line-xs">Nama Barang</th>
                        <th class="center">Kategori Produk</th>
                        <th style="text-align:right">Harga Jual</th>
                        <th class="center" width="100px">Jumlah<br>Label</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data['produk'] as $key => $value)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>
                                <div class="media">
                                    <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px">
                                        <img src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                                    </div>
                                    <div class="align-self-center media-body">
                                        <span>Kode. {{$value->kode}}</span>
                                        <h6>{{$value->nama_produk}}</h6>
                                    </div>
                                </div>
                            </td>
                            <td class="center">
                                <div style="font-weight:600">{{$value->kelompok}}</div>
                                <div>{{$value->kategori}}</div>
                                <div class="text-muted">{{$value->sub_kategori}}</div>
                            </td>
                            <td style="text-align:right;white-space:nowrap">Rp {{number_format($value->harga_jual,0,',','.')}}</td>
                            <td><input type="text" class="form-control center" value="{{$value->jumlah}}" id="jumlah_{{$value->id}}" onchange="edit_jumlah({{$value->id}})"></td>
                            <td style="width:1px;white-space:nowrap">
                                <div class="text-center">
                                    <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                                    <form action="{{url('manajemen_stok/cetak/label_harga/proses')}}" method="post" id="hapus{{$value->id}}">
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
        @endif
    </div>
    @foreach ($data['produk'] as $key => $value)

    @endforeach
    <div id="filter-barang" class="modal fade">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <form action="{{url('manajemen_stok/cetak/label_harga/filter')}}" method="post">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5>Pilih Barang</h5>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Kelompok Barang</label>
                            <select class="select2" style="width:100%" id="kelompok" name="kelompok"></select>
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            <select class="select2" style="width:100%" id="kategori" name="kategori"></select>
                        </div>
                        <div class="form-group">
                            <label>Sub Kategori</label>
                            <select class="select2" style="width:100%" id="sub_kategori" name="sub_kategori"></select>
                        </div>
                        <div class="form-group">
                            <label>Search</label>
                            <input type="text" class="form-control" id="search" name="search" >
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>

        function edit_jumlah(id){
            jumlah=$('#jumlah_'+id).val();
            $.ajax({
                url:'{{url('manajemen_stok/cetak/label_harga/proses')}}',
                method:'POST',
                data:{
                    _token: "{{ csrf_token() }}",
                    id: id,
                    action: 'edit',
                    jumlah:jumlah,
                },
                error:function(error){
                    console.log(error)
                }
            });
        }

        table = $('#datatable').DataTable({
            "ordering": false,
            "bLengthChange": false,
            "bSearchable": false,
            "filter": false

        });

        selected_kelompok = '{{(!empty(Session::get('filter_label_harga')) && Session::get('filter_label_harga')['kelompok'] !='all'  ? Session::get('filter_label_harga')['kelompok'] : 'all')}}';
        selected_kategori = '{{(!empty(Session::get('filter_label_harga')) && Session::get('filter_label_harga')['kategori'] !='all' ? Session::get('filter_label_harga')['kategori'] : 'all')}}';
        selected_subkategori = '{{(!empty(Session::get('filter_label_harga')) && Session::get('filter_label_harga')['sub_kategori'] !='all' ? Session::get('filter_label_harga')['sub_kategori'] : 'all')}}';
        function get_kategori(select_target, parent_id, selected){
            $.get("{{ url('api/get_kategori') }}/"+parent_id+'/'+selected, function (result) {
                $selectElement = $('#'+select_target);
                $selectElement.empty();
                $.each(result, function (i, value) {
                    $selectElement.append('<option data-id="'+value.id+'" value="'+value.id+'" '+value.selected+' >'+value.nama_kategori+'</option>');
                });
                $selectElement.trigger('change');
            });
        }

        get_kategori('kelompok','0', selected_kelompok);
        $('#kelompok').change(function () {
            let id = $(this).find('option:selected').attr('data-id');
            get_kategori('kategori',id, selected_kategori);
        });
        $('#kategori').change(function () {
            let id = $(this).find('option:selected').attr('data-id');
            get_kategori('sub_kategori',id, selected_subkategori);
        });
    </script>
@endsection
