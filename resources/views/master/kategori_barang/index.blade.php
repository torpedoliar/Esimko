@php
  $app='master';
  $page='Data Master';
  $subpage='Kategori Barang';
@endphp
@extends('layouts.admin')
@section('title')
  Kategori Barang |
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/boxes.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Kategori Barang</h4>
        <p class="text-muted m-0">Menampilkan data master kategori barang yang melekat pada setiap barang di toko</p>
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
          <form action="{{url('master/kategori_barang/proses')}}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Kode</label>
                  <input type="text" class="form-control" name="kode" id="kode">
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <label>Nama Kategori</label>
                  <input type="text" class="form-control" name="nama_kategori" id="nama_kategori">
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="have_parent" name="have_parent" value="1">
                <label class="custom-control-label" for="have_parent">Mempunyai parent kategori</label>
              </div>
            </div>
            <div class="form-group" id="parent_kategori_form">
              <label>Parent Kategori</label>
              <div class="input-group">
                <input type="text" class="form-control" id="parent_kategori" disabled>
                <div class="input-group-append">
                  <button class="input-group-text" type="button"  data-target="#modal-kategori" data-toggle="modal">
                    <i class="bx bx-search font-size-16 align-middle"></i>
                  </button>
                </div>
              </div>
              <input type="hidden" id="parent_kategori_id" name="parent_id">
            </div>
            <div class="form-group">
              <label>Keterangan</label>
              <textarea class="form-control" name="keterangan" id="keterangan" style="height:100px"></textarea>
            </div>
            <input type="hidden" id="id" name="id" value="0">
            <button class="btn btn-primary btn-block" id="button">Tambahkan</button>
            <button class="btn btn-danger btn-block" type="button" id="del_button">Delete</button>
          </form>
          <form action="{{url('master/kategori_barang/proses')}}" method="post" id="form_hapus">
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
              <button type="button" id="add_kategori" class="btn btn-primary btn-block">Tambah Kategori</button>
            </div>
            <div class="col-md-8">
              <input type="text" class="form-control" id="search_kategori" placeholder="Search Kategori">
            </div>
          </div>
          <hr>
          <div id="tree_kategori"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="modal-kategori" class="modal fade right">
   <div class="modal-dialog">
     <div class="modal-content">
       <div class="modal-body">
         <input class="form-control" type="text" id="search_parent" placeholder="Search Kategori">
         <hr>
         <div id="tree_parent_kategori"></div>
       </div>
     </div>
   </div>
 </div>
@endsection
@section('js')
  <script>
    $(function () {
      var to;
      $("#del_kategori").hide();
      $("#add_kategori").trigger('click');
      $.get("{{url('api/get_tree_kategori')}}",function (result) {
        console.log(result);
        $('#tree_kategori').jstree({
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
          "plugins" : [ "contextmenu", "dnd", "search","state", "types","wholerow" ],
          "search" : { "show_only_matches" : true }
        }).on("select_node.jstree", function (e, data) {
          $("#nama_kategori").val(data.node.original.text);
          $('#kode').val(data.node.original.kode);
          if(data.node.original.parent_kode != 0){
            $("#have_parent").prop("checked", true);
            $('#parent_kategori_form').show();
            $('#parent_kategori_id').val(data.node.original.parent_kode);
            $('#parent_kategori').val(data.node.original.nama_parent);
          }
          else{
            $("#have_parent").prop("checked", false);
            $('#parent_kategori_form').hide();
            $('#parent_kategori_id').val(0);
            $('#parent_kategori').val('');
          }
          $("#keterangan").val(data.node.original.keterangan);
          $('#action').val('edit');
          $('#title').html('Edit Kategori Barang');
          $('#button').html('Simpan');
          $('#cancel').show();
          $('#id').val(data.node.original.id);
          $("#del_button").show();
          $('#del_button').val(data.node.original.id);
        });
      });
      $('#search_kategori').keyup(function () {
          if(to){
            clearTimeout(to);
          }
          to = setTimeout(function () {
              var v = $('#search_kategori').val();
              $('#tree_kategori').jstree(true).search(v);
          }, 250);
      });

      $.get("{{url('api/get_tree_kategori')}}",function (result) {
        console.log(result);
        $('#tree_parent_kategori').jstree({
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
          $('#parent_kategori').val(data.node.original.text);
          $('#parent_kategori_id').val(data.node.original.id);
          $('#modal-kategori').modal('hide')
        });
      });
      $('#search_parent').keyup(function () {
          if(to){
            clearTimeout(to);
          }
          to = setTimeout(function () {
              var v = $('#search_parent').val();
              $('#tree_parent_kategori').jstree(true).search(v);
          }, 250);
      });
    });

    $("#add_kategori").click(function(){
      $("#nama_kategori").val('');
      $('#kode').val('');
      $("#del_button").hide();
      $("#have_parent").prop("checked", true);
      $("#keterangan").val('');
      $('#parent_kategori_form').show();
      $('#parent_kategori_id').val(0);
      $('#parent_kategori').val('');
      $('#action').val('add');
      $('#title').html('Tambah Kategori Barang');
      $('#button').html('Tambahkan');
      $('#cancel').show();
      $('#id').val(0);
    });

    $("#have_parent").click(function(){
      if($(this).prop("checked") == true){
        $('#parent_kategori_form').show();
      }
      else if($(this).prop("checked") == false){
        $('#parent_kategori_form').hide();
      }
    });

    $("#delSatker").click(function(){
      let id=$('#id').val();
      $('#hapus_id').val(id);
      Swal.fire({
        title: "Are you sure?",
        text: "Apakah anda yakin ingin menghapus data ini",
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
