@php
  $app='laporan';
  $page='Bagan Akun';
  $subpage='Bagan Akun';
@endphp
@extends('layouts.admin')
@section('title')
  Bagan Akun |
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/organization-chart.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Bagan Akun</h4>
        <p class="text-muted m-0">Menampilkan bagan akun keuangan yang digunakan dalam proses transkasi di koperasi</p>
      </div>
    </div>
  </div>
  <div class="row mt-3">
    <div class="col-md-5">
      <div class="card">
        <div class="card-header">
          <h5 id="title"></h5>
        </div>
        <div class="card-body">
          <form action="{{url('keuangan/bagan_akun/proses')}}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Kode Akun</label>
                  <input type="text" class="form-control" name="kode" id="kode">
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <label>Nama Akun</label>
                  <input type="text" class="form-control" name="nama_akun" id="nama_akun">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Deskripsi</label>
              <textarea type="text" class="form-control" name="deskripsi" id="deskripsi"></textarea>
            </div>
            <div class="form-group">
              <label>Akun Parent</label>
              <div class="input-group">
                <input type="text" class="form-control" id="nama_akun_parent" disabled>
                <div class="input-group-append">
                  <button class="btn btn-danger" id="del_parent" type="button" >
                    <i class="bx bx-x font-size-18 align-middle"></i>
                  </button>
                  <button class="input-group-text" type="button"  data-target="#modal_akun_parent" data-toggle="modal">
                    <i class="bx bx-search font-size-16 align-middle"></i>
                  </button>
                </div>
              </div>
              <input type="hidden" id="id_akun_parent" name="parent_id">
            </div>
            <div class="form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="active" name="active" value="1">
                <label class="custom-control-label" for="active">Active</label>
              </div>
            </div>
            <input type="hidden" id="id" name="id" value="0">
            <button class="btn btn-primary btn-block" id="button"></button>
            <button class="btn btn-danger btn-block" type="button" id="hapus_akun">Delete</button>
          </form>
          <form action="{{url('keuangan/bagan_akun/proses')}}" method="post" id="form_hapus">
            {{ csrf_field() }}
            <input type="hidden" name="id" id="hapus_id">
            <input type="hidden" name="action" value="delete">
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-7">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <button type="button" id="add_akun" class="btn btn-primary btn-block">Tambah Akun</button>
            </div>
            <div class="col-md-8">
              <input type="text" class="form-control" id="search_akun" placeholder="Cari Kode Akun">
            </div>
          </div>
          <hr>
          <div id="tree_bagan_akun"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="modal_akun_parent" class="modal fade right">
   <div class="modal-dialog">
     <div class="modal-content">
       <div class="modal-body">
         <input class="form-control" type="text" id="search_parent" placeholder="Cari Kode Akun">
         <hr>
         <div id="tree_parent_akun"></div>
       </div>
     </div>
   </div>
 </div>
@endsection
@section('js')
  <script>
  $(function () {
    $("#hapus_akun").hide();
    $("#add_akun").trigger('click');
    var to;
    $.get("{{url('api/get_bagan_akun')}}",function (result) {
      console.log(result);
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
        "plugins" : [ "search","state", "types","wholerow" ],
        "search" : { "show_only_matches" : true }
      }).on("select_node.jstree", function (e, data) {
        $("#nama_akun").val(data.node.original.nama_akun);
        $('#kode').val(data.node.original.kode);
        $("#deskripsi").val(data.node.original.keterangan);
        $('#id_akun_parent').val(data.node.original.parent_id);
        $('#nama_akun_parent').val(data.node.original.nama_akun_parent);
        if(data.node.original.is_active!=0){
          $("#active").prop("checked", true);
        }
        else{
          $("#active").prop("checked", false);
        }
        if(data.node.original.parent_id==0){
          $('#del_parent').hide();
        }
        else{
          $('#del_parent').show();
        }
        $('#action').val('edit');
        $('#title').html('Edit Bagan Akun');
        $('#button').html('Simpan');
        $('#cancel').show();
        $('#id').val(data.node.original.id);
        $("#hapus_akun").show();
        $('#hapus_akun').val(data.node.original.id);
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

    $.get("{{url('api/get_bagan_akun')}}",function (result) {
      $('#tree_parent_akun').jstree({
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
        $('#nama_akun_parent').val(data.node.original.text);
        $('#id_akun_parent').val(data.node.original.id);
        $('#del_parent').show();
        $('#modal_akun_parent').modal('hide')
      });
    });
    $('#search_parent').keyup(function () {
        if(to){
          clearTimeout(to);
        }
        to = setTimeout(function () {
            var v = $('#search_parent').val();
            $('#tree_parent_akun').jstree(true).search(v);
        }, 250);
    });
  });

  $("#add_akun").click(function(){
    $("#nama_akun").val('');
    $('#kode').val('');
    $("#deskripsi").val('');
    $('#id_akun_parent').val('');
    $('#nama_akun_parent').val('');
    $("#active").prop("checked", true);
    $('#not_parent').hide();
    $('#not_work_unit').hide();
    $('#action').val('add');
    $('#title').html('Tambah Bagan Akun');
    $('#button').html('Tambahkan');
    $('#cancel').show();
    $('#id').val(0);
    $("#hapus_akun").hide();
    $('#hapus_akun').val(0);
  });

  $("#del_parent").click(function(){
    $('#del_parent').hide();
    $('#id_akun_parent').val(0);
    $('#nama_akun_parent').val('');
  });

  $("#hapus_akun").click(function(){
    let id=$('#id').val();
    $('#hapus_id').val(id);
    Swal.fire({
      title: "Are you sure?",
      text: "Apakah anda yakin ingin menghapus akun ini",
      type:"question",
      showCancelButton: true,
      confirmButtonColor: '#d63030',
      cancelButtonColor: '#cbcbcb',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.value == true) {
        $('#form_hapus').submit();
      }
    });
  });

  </script>
@endsection
