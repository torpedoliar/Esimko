@php
    $app='manajemen_barang';
    $page='Data Barang';
    $subpage='Data Barang';
@endphp
@extends('layouts.admin')
@section('title')
    Data Barang |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="{{asset('assets/images/icon-page/boxes.png')}}" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Data Barang</h4>
                        <p class="text-muted m-0">Menampilkan data barang atau produk yang dijual ditoko secara online atau offline</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <form action="" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Produk">
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary btn-block" data-toggle="modal" data-target='#filter-barang' >Filter Barang</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-primary btn-block" href="{{url('manajemen_stok/barang/form')}}">Tambah Barang</a>
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
                <table class="table table-middle table-custom">
                    <thead>
                    <tr>
                        <th>Kode / Nama Produk</th>
                        <th class="center">Kategori Produk</th>
                        <th class="center">Stok<br>Masuk</th>
                        <th class="center">Stok<br>Keluar</th>
                        <th class="center">Penyesuaian<br>Stok</th>
                        <th class="center">Sisa<br>Stok</th>
                        <th style="text-align:right">Harga Beli</th>
                        <th style="text-align:right">Margin</th>
                        <th style="text-align:right">Harga Jual</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data['produk'] as $key => $value)
                        <tr>
                            <td>
                                <div class="media">
                                    <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px">
                                        <img src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                                    </div>
                                    <div class="align-self-center media-body">
                                        <span>Kode. {{$value->kode}} | <b>{{ $value->id }}</b></span>
                                        <h6>{{$value->nama_produk}}</h6>
                                    </div>
                                </div>
                            </td>
                            <td class="center">
                                <div style="font-weight:600">{{$value->kelompok}}</div>
                                <div>{{$value->kategori}}</div>
                                <div class="text-muted">{{$value->sub_kategori}}</div>
                            </td>
                            <td class="center">{{$value->stok_masuk}}<br>{{$value->satuan}}</td>
                            <td class="center">{{$value->stok_keluar}}<br>{{$value->satuan}}</td>
                            <td class="center">{{$value->penyesuaian ?? ''}}<br>{{$value->satuan}}</td>
                            <td class="center">{{$value->sisa}}<br>{{$value->satuan}}</td>
                            <td style="text-align:right;white-space:nowrap">Rp {{number_format($value->harga_beli,0,',','.')}}</td>
                            <td style="text-align:right;white-space:nowrap">({{$value->margin}}%)<br>Rp {{number_format($value->margin_nominal,0,',','.')}}</td>
                            <td style="text-align:right;white-space:nowrap">Rp {{number_format($value->harga_jual,0,',','.')}}</td>
                            <td style="width:1px;white-space:nowrap">
                                <a href="{{url('manajemen_stok/barang/detail?id='.$value->id)}}" class="text-dark"><i class="bx bx-search-alt h3 m-0"></i></a>
                                <a href="{{url('manajemen_stok/barang/form?page='.$request->page.'&id='.$value->id)}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                                <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                                <form action="{{url('manajemen_stok/barang/proses')}}" method="post" id="hapus{{$value->id}}">
                                    {{ csrf_field()}}
                                    <input type="hidden" name="id" value="{{$value->id}}">
                                    <input type="hidden" name="action" value="delete">
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3 mb-3">
                {{ $data['produk']->links('include.pagination', ['pagination' => $data['produk']] ) }}
            </div>
        @endif
    </div>
    <div id="filter-barang" class="modal fade right">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Filter Barang</h5>
                </div>
                <div class="modal-body">
                    <form action="{{url('main/belanja/produk/filter')}}" method="post">
                        {{ csrf_field() }}
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
                            <label>Status</label>
                            <select class="select2" style="width:100%" id="is_aktif" name="is_aktif">
                                <option value="all" >Semua</option>
                                <option value="1" >Aktif</option>
                                <option value="0" >Tidak Aktif</option>
                            </select>
                        </div>
                        <button class="btn btn-primary btn-block">Filter Barang </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        selected_kelompok = '{{(!empty(Session::get('filter_produk')) && Session::get('filter_produk')['kelompok'] !='all'  ? Session::get('filter_produk')['kelompok'] : 'all')}}';
        selected_kategori = '{{(!empty(Session::get('filter_produk')) && Session::get('filter_produk')['kategori'] !='all' ? Session::get('filter_produk')['kategori'] : 'all')}}';
        selected_subkategori = '{{(!empty(Session::get('filter_produk')) && Session::get('filter_produk')['sub_kategori'] !='all' ? Session::get('filter_produk')['sub_kategori'] : 'all')}}';
        selected_is_aktif = '{{(!empty(Session::get('filter_produk')) && Session::get('filter_produk')['is_aktif'] != 'all' ? Session::get('filter_produk')['is_aktif'] : 'all')}}';
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
        $('#is_aktif').val(selected_is_aktif).trigger('change');
    </script>
@endsection
