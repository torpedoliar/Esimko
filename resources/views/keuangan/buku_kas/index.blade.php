@php
  $app='laporan';
  $page='Buku Kas';
  $subpage='Buku Kas';
@endphp
@extends('layouts.admin')
@section('title')
  Buku Kas |
@endsection
@section('content')
<div class="content-breadcrumb mb-2">
  <div class="container-fluid">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/icon-page/wallet.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Buku Kas</h4>
          <p class="text-muted m-0">Menampilkan Buku Kas dari semua transaksi yang ada di Koperasi</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
        <form action="" method="get" id="jenis_form" >
          <input type="hidden" value="{{$jenis}}" id="jenis_id" name="jenis" value="">
          <select class="select2-jenis" id="jenis_color" style="width:100%" onchange="pilih_jenis()">
            <option value="#282828" {{($jenis == 'all' ? 'selected' : '')}} data-id='all' >Semua Kas</option>
            <option value="#27ae60" {{($jenis == 'masuk' ? 'selected' : '')}} data-id='masuk' >Kas Masuk</option>
            <option value="#c0392b" {{($jenis == 'keluar' ? 'selected' : '')}} data-id='keluar' >Kas Keluar</option>
          </select>
        </form>
      </div>
      <div class="col-md-6">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="" name="search" placeholder="Cari Data Kas">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <button type="button" class="btn btn-primary btn-block" data-target="#form-kas" data-toggle="modal" >Tambah</button>
      </div>
    </div>
  </div>
</div>
<div class="container-fluid">
  @if(count($data['buku_kas'])==0)
    <div style="width:100%;text-align:center">
      <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
      <h4 class="mt-2">Data Kas tidak Ditemukan</h4>
    </div>
  @else
    <table class="table table-middle table-custom">
      <thead class="thead-light">
        <tr>
          <th width="20px">No</th>
          <th>Tanggal</th>
          <th style="white-space:nowrap">No. Transaksi</th>
          <th>Akun Kas</th>
          <th>Catatan</th>
          @if($jenis == 'all')
          <th style="text-align:right">Debit</th>
          <th style="text-align:right">Kredit</th>
          @else
          <th style="text-align:right">Nominal</th>
          @endif
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['buku_kas'] as $key => $value)
        <tr>
          <td style="border-color:{{($value->jenis=='masuk' ? '#27ae60' : '#c0392b')}}">{{ $data['buku_kas']->firstItem() + $key }}</td>
          <td style="width:1px;white-space:nowrap">{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')}}</td>
          <td style="width:1px;white-space:nowrap">{{$value->no_transaksi}}</td>
          <td>{{$value->nama_akun}}</td>
          <td>{{(!empty($value->catatan) ? $value->catatan : '-' )}}</td>
          @if($jenis == 'all')
          <td style="text-align:right">{{($value->jenis == 'masuk' ? number_format($value->nominal,0,'.',',') : 0 ) }}</td>
          <td style="text-align:right">{{($value->jenis == 'keluar' ? number_format($value->nominal,0,'.',',') : 0 ) }}</td>
          @else
          <td style="text-align:right">{{number_format($value->nominal,0,'.',',')}}</td>
          @endif
          <td style="width:1px;white-space:nowrap">
            <h6>{{$value->nama_lengkap}}</h6>
            at {{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')}}
          </td>
          <td style="width:1px;white-space:nowrap">
            <div class="text-center">
              <a href="{{url('keuangan/buku_kas/form?id='.$value->id)}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
              <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
              <form action="{{url('keuangan/buku_kas/proses')}}" method="post" id="hapus{{$value->id}}">
                {{ csrf_field()}}
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="{{$value->id}}">
              </form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>
<div class="modal fade" id="form-kas">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="title">Tambah Kas</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{url('keuangan/buku_kas/proses')}}" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Tanggal</label>
                <input type="text" class="form-control datepicker" autocomplete="off" name="tanggal">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>No. Transaksi</label>
                <input type="text" class="form-control" autocomplete="off" name="no_transaksi">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Catatan</label>
            <textarea class="form-control" name="catatan"  style="height:100px"></textarea>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Jenis Kas</label>
                <select class="form-control select2" name="jenis" style="width:100%">
                  <option value="masuk">Kas Masuk</option>
                  <option value="keluar">Kas Keluar</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Akun Kas</label>
                <select class="form-control select2" name="akun_kas" style="width:100%">
                  @foreach ($data['akun_kas'] as $key => $value)
                    <option value="{{$value->kode}}">{{$value->nama_akun}}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Bagan Akun</label>
                <div class="input-group">
                  <input type="hidden" class="form-control" id="kode_akun" name="kode_akun" >
                  <input type="text" class="form-control" id="nama_akun" placeholder="Search Kode Akun" disabled >
                  <div class="input-group-append">
                    <button class="input-group-text" type="button" data-target="#modal-akun" data-toggle="modal">
                      <i class="bx bx-search font-size-16 align-middle"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Nominal</label>
                <input type="text" class="form-control autonumeric" data-a-dec="." data-a-sep="," autocomplete="off" name="nominal">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="id" value="0">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" name="action" value="add">Tambah</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div id="modal-akun" class="modal fade right">
   <div class="modal-dialog">
     <div class="modal-content">
       <div class="modal-body">
         <input class="form-control" type="text" id="search_akun" placeholder="Search Akun">
         <hr>
         <div id="tree_akun"></div>
       </div>
     </div>
   </div>
 </div>
@endsection
@section('js')
  <script>
    $(function () {
      var to;
      $.get("{{url('api/get_bagan_akun')}}",function (result) {
        console.log(result);
        $('#tree_akun').jstree({
          "core" : {
              "themes" : {
                  "responsive": true
              },
              "check_callback" : true,
              'data': result
          },
          "types" : {
              "default" : {
                  "icon" : "fa fa-folder m--font-brand"
              },
              "file" : {
                  "icon" : "fa fa-file  m--font-brand"
              }
          },
          "plugins" : [ "contextmenu", "dnd", "search", "types" ],
          "search" : { "show_only_matches" : true }
        }).on("select_node.jstree", function (e, data) {
          $('#nama_akun').val(data.node.original.text);
          $('#kode_akun').val(data.node.original.kode);
          $('#modal-akun').modal('hide')
        });
      });
      $('#search_akun').keyup(function () {
          if(to){
            clearTimeout(to);
          }
          to = setTimeout(function () {
              var v = $('#search_akun').val();
              $('#tree_akun').jstree(true).search(v);
          }, 250);
      });
    });

    function formatJenis(status) {
      var $status = $(
        '<span style="display:flex;align-items:center;"><div class="indikator-status mr-2" style="background:'+status.id+'"></div>'+status.text+'</span>'
      );
      return $status;
    };

    $(".select2-jenis").select2({
      templateResult: formatJenis
    });

    function pilih_jenis(){
      let id = $('#jenis_color').find('option:selected').attr('data-id');
      $('#jenis_id').val(id);
      $('#jenis_form').submit();
    }
    </script>
@endsection
