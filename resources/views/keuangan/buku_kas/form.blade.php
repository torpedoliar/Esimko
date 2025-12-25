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
  <div class="card">
    <div class="card-body">
      <form action="{{url('keuangan/buku_kas/proses')}}" id="buku_kas_form" method="post">
        {{ csrf_field() }}
        <div class="row">
          <div class="col-md-2">
            <div class="form-group">
              <label>Tanggal</label>
              <input type="text" class="form-control datepicker" autocomplete="off" name="tanggal" value="{{(!empty($data['buku_kas']) ? \App\Helpers\GlobalHelper::dateFormat($data['buku_kas']->tanggal,'d-m-Y') : '')}}" required>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>No. Transaksi </label>
              <input type="text" class="form-control" autocomplete="off" name="no_transaksi" value="{{(!empty($data['buku_kas']) ? $data['buku_kas']->no_transaksi : '')}}" required>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Akun Kas</label>
              <select class="form-control select2" name="cash_bank_code" style="width:100%" required>
                @foreach ($data['akun_kas'] as $key => $value)
                  <option value="{{$value->kode}}">{{$value->nama_akun}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-5">
            <div class="form-group">
              <label>Catatan</label>
              <input type="text" class="form-control" autocomplete="off" name="no_transaksi" value="{{$data['buku_kas']->catatan}}" required>
            </div>
          </div>
        </div>
        <input type="hidden" name="id" value="{{$id}}">
      </form>
      <table class="table table-middle table-bordered table-hover mt-3">
        <thead class="thead-light">
          <tr>
            <th width="20px">No</th>
            <th style="width:1px;white-space:nowrap">Kode</th>
            <th>Nama Akun</th>
            <th>Deskripsi</th>
            <th style="text-align:right">Nominal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <form action="{{url('keuangan/buku_kas/detail/proses')}}" id="form-journal" method="post">
            {{ csrf_field() }}
            <tr>
              <td>#</td>
              <td colspan="2">
                <div class="input-group">
                  <input type="hidden" class="form-control" id="kode_akun" name="kode_akun">
                  <input type="text" class="form-control" id="nama_akun" disabled>
                  <div class="input-group-append">
                    <button class="input-group-text" type="button" data-target="#modal-akun" data-toggle="modal">
                      <i class="bx bx-search font-size-16 align-middle"></i>
                    </button>
                  </div>
                </div>
              </td>
              <td width="300px">
                <input type="text" class="form-control" name="deskripsi" id="deskripsi" >
              </td>
              <td width="150px"><input type="text" class="form-control autonumeric" data-a-dec="." data-a-sep="," id="nominal" style="text-align:right" name="nominal" ></td>
              <td style="width:1px;white-space:nowrap">
                <input type="hidden" name="buku_kas_id" value="{{$id}}">
                <input type="hidden" id="id" name="id">
                <button class="btn btn-primary btn-block" id="action" name="action">Tambah</button>
                <button class="btn btn-secondary btn-block" id="cancel" type="button" onclick="add_items()">Cancel</button>
              </td>
            </tr>
          </form>
          @foreach ($data['items'] as $key => $value)
            <tr>
              <td>{{$key+1}}</td>
              <td>{{$value->kode_akun}}</td>
              <td>{{$value->nama_akun}}</td>
              <td>{{$value->deskripsi}}</td>
              <td style="text-align:right">{{number_format($value->nominal,'0','.',',')}}</td>
              <td style="width:1px;white-space:nowrap">
                <div class="text-center">
                  <a href="javascript:;" onclick="edit_items({{ $value->id }})" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                  <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                  <form action="{{url('keuangan/buku_kas/detail/proses')}}" method="post" id="hapus{{$value->id}}">
                    {{ csrf_field()}}
                    <input type="hidden" name="buku_kas_id" value="{{$id}}">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="{{$value->id}}">
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <th style="text-align:right" colspan="4">TOTAL</th>
            <th style="text-align:right">{{number_format($data['buku_kas']->nominal,'0','.',',')}}</th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
    <div class="card-footer">
      <div class="pull-right">
        <a href="{{url('keuangan/buku_kas')}}" class="btn btn-dark">Kembali</a>
        <button class="btn btn-primary" onclick="$('#buku_kas_form').submit();">Save</button>
      </div>
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
    $('#cancel').trigger('click');
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
  function add_items(){
    $('#kode_akun').val('');
    $('#nama_akun').val('');
    $('#deskripsi').val('');
    $('#nominal').val('');
    $('#id').val(0);
    $('#action').val('add');
    $('#action').html('Tambah');
    $('#cancel').hide();
  }

  function edit_items(id){
    $.get("{{ url('api/find_buku_kas_detail') }}/"+id,function(result){
      $('#kode_akun').val(result.kode);
      $('#nama_akun').val(result.kode+' - '+result.nama_akun);
      $('#deskripsi').val(result.deskripsi);
      $('#nominal').val(result.nominal);
      $('#id').val(id);
      $('#action').val('edit');
      $('#action').html('Edit');
      $('#cancel').show();
    });
  }
  </script>
@endsection
