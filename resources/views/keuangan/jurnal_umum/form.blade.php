@php
  $app='laporan';
  $page='Jurnal Umum';
  $subpage='Jurnal Umum';
@endphp
@extends('layouts.admin')
@section('title')
  Jurnal Umum |
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/book.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Jurnal Umum</h4>
        <p class="text-muted m-0">Menampilkan Jurnal Umum dari semua transaksi yang ada di Koperasi</p>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <h5>Edit Jurnal Umum</h5>
    </div>
    <div class="card-body">
      <form action="{{url('keuangan/jurnal_umum/proses')}}" method="post" id="form_jurnal">
        {{ csrf_field() }}
        <div class="row">
          <div class="col-md-2">
            <div class="form-group">
              <label>Tanggal</label>
              <input type="text" class="form-control datepicker" autocomplete="off" name="tanggal" value="{{(!empty($data['jurnal']) ? \App\Helpers\GlobalHelper::dateFormat($data['jurnal']->tanggal,'d-m-Y') : '')}}">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>No. Jurnal </label>
              <input type="text" class="form-control" autocomplete="off" name="nomor_jurnal" value="{{(!empty($data['jurnal']) ? $data['jurnal']->nomor_jurnal : '')}}" readonly>
            </div>
          </div>
          <div class="col-md-7">
            <div class="form-group">
              <label>Deskripsi</label>
              <input type="text" class="form-control" name="deskripsi" value="{{(!empty($data['jurnal']) ?$data['jurnal']->deskripsi : '')}}">
            </div>
          </div>
        </div>
        <input type="hidden" name="id" value="{{$id}}">
        <input type="hidden" name="action" value="edit">
      </form>
      <table class="table table-middle table-bordered table-hover mt-3">
        <thead class="thead-light">
          <tr>
            <th width="20px">No</th>
            <th style="white-space:nowrap" class="center">Kode Akun</th>
            <th>Nama Akun</th>
            <th style="text-align:right">Debit</th>
            <th style="text-align:right">Kredit</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <form action="{{url('keuangan/jurnal_umum/detail/proses')}}" id="form_jurnal_detail" method="post">
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
              <td width="150px"><input type="text" class="form-control autonumeric" data-a-dec="." data-a-sep="," id="debit" style="text-align:right" name="debit"></td>
              <td width="150px"><input type="text" class="form-control autonumeric" data-a-dec="." data-a-sep="," id="kredit" style="text-align:right" name="kredit" ></td>
              <td style="width:1px;white-space:nowrap">
                <input type="hidden" name="jurnal_id" value="{{$id}}">
                <input type="hidden" id="id" name="id">
                <button class="btn btn-primary btn-block" id="action" name="action">Tambah</button>
                <button class="btn btn-secondary btn-block" id="cancel" type="button" onclick="add_akun()">Cancel</button>
              </td>
            </tr>
          </form>
          @foreach ($data['jurnal-detail'] as $key => $value)
          <tr>
            <td>{{$key+1}}</td>
            <td style="width:1px;white-space:nowrap">{{$value->kode}}</td>
            <td>{{$value->nama_akun}}</td>
            <td style="text-align:right">{{number_format($value->debit,'0','.',',')}}</td>
            <td style="text-align:right">{{number_format($value->kredit,'0','.',',')}}</td>
            <td style="width:1px;white-space:nowrap">
              <div class="text-center">
                <a href="javascript:;" onclick="edit_akun({{ $value->id }})" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                <form action="{{url('finance/ledger/general-journal/detail/proses')}}" method="post" id="hapus{{$value->id}}">
                  {{ csrf_field()}}
                  <input type="hidden" name="journal_id" value="{{$id}}">
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
            <th style="text-align:right" colspan="3">Total</th>
            <th style="text-align:right">{{number_format($data['jurnal']->total_debit,'0','.',',')}}</th>
            <th style="text-align:right">{{number_format($data['jurnal']->total_kredit,'0','.',',')}}</th>
            <td></td>
          </tr>
        </tfoot>
      </table>
      @if($data['jurnal']->total_debit!=$data['jurnal']->total_kredit)
      <div class="alert alert-danger" role="alert">
        <b>Warning</b>. Jurnal yang anda masukkan tidak seimbang antara debit dengan kredit
      </div>
      @endif
    </div>
    <div class="card-footer">
      <div class="pull-right">
        <a href="{{url('keuangan/jurnal_umum')}}" class="btn btn-dark">Kembali</a>
        <button type="button" class="btn btn-primary" onclick="$('#form_jurnal').submit();">Simpan</button>
      </div>
    </div>
  </div>
</div>
<div id="modal-akun" class="modal fade right">
   <div class="modal-dialog">
     <div class="modal-content">
       <div class="modal-body">
         <input class="form-control" type="text" id="search_akun" placeholder="Search Bagan Akun">
         <hr>
         <div id="tree_bagan_akun"></div>
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
      $('#tree_bagan_akun').jstree({
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
        $('#tree_bagan_akun').jstree(true).search(v);
      }, 250);
    });
  });

  function add_akun(){
    $('#kode_akun').val('');
    $('#nama_akun').val('');
    $('#kredit').val('');
    $('#debit').val('');
    $('#id').val(0);
    $('#action').val('add');
    $('#action').html('Tambah');
    $('#cancel').hide();
  }

  function edit_akun(id){
    $.get("{{ url('api/find_jurnal_detail') }}/"+id,function(result){
      $('#kode_akun').val(result.kode_akun);
      $('#nama_akun').val(result.kode_akun+' - '+result.nama_akun);
      $('#kredit').val(result.kredit);
      $('#debit').val(result.debit);
      $('#id').val(id);
      $('#action').val('edit');
      $('#action').html('Simpan');
      $('#cancel').show();
    });
  }
  </script>
@endsection
