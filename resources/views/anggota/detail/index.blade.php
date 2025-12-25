@php
  $app='master';
  $page='Data Anggota';
  $subpage='Data Anggota';
@endphp
@extends('layouts.admin')
@section('title')
  Data Anggota |
@endsection
@section('css')
  <style>
  .table-informasi td,
  .table-informasi th {
    padding: .4rem .75rem;
    vertical-align: top;
    border-top: 1px solid rgb(0 0 0 / 7%);
  }
  .table-informasi tr:first-child td,
  .table-informasi tr:first-child th {
    border-top: none;
  }
  .verti-timeline {
    border-left: 2px dashed #e0e0e0;
    margin: 0 10px;
  }
  .nav-pills .nav-link.active,
  .nav-pills .nav-link.active:hover,
  .nav-pills .show>.nav-link {
    color: #fff;
    background-color: #45a086;
  }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="content-breadcrumb mb-2" style="padding-bottom:10px">
    <div class="row">
      <div class="col-auto">
        <div class="media" style="margin-right:40px">
          <div class="avatar-thumbnail rounded-circle mr-2" id="avatar" style="height:80px;width:80px">
            <img src="{{(!empty($data['anggota']->avatar) ? asset('storage/'.$data['anggota']->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle" />
          </div>
          <div class="media-body align-self-center">
            <p class="mb-0 font-size-15">No. {{$data['anggota']->no_anggota}}</p>
            <h5 class="text-truncate font-size-20"><a href="{{url('anggota/detail?id='.$data['anggota']->no_anggota)}}" class="text-dark">{{$data['anggota']->nama_lengkap}}</a></h5>
          </div>
        </div>
      </div>
      <div class="col align-self-center">
        <div class="mt-4 mt-lg-0">
          <div class="row">
            <div class="col-3">
              <div>
                <p class="text-muted text-truncate mb-1">Saldo Simpanan</p>
                <h5 class="mb-0 font-size-15">Rp {{number_format($data['anggota']->total_simpanan,'0',',','.')}}</h5>
              </div>
            </div>
            <div class="col-3">
              <div>
                <p class="text-muted text-truncate mb-1">Sisa Pinjaman</p>
                <h5 class="mb-0 font-size-15">Rp {{number_format($data['anggota']->sisa_pinjaman,'0',',','.')}}</h5>
              </div>
            </div>
            <div class="col-3">
              <div>
                <p class="text-muted text-truncate mb-1">Total Angsuran</p>
                <h5 class="mb-0 font-size-15">Rp {{number_format($data['anggota']->total_angsuran,'0',',','.')}}</h5>
              </div>
            </div>
            <div class="col-3">
              <div>
                <p class="text-muted text-truncate mb-1">Kredit Belanja</p>
                <h5 class="mb-0 font-size-15">Rp {{number_format($data['anggota']->total_angsuran,'0',',','.')}}</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <hr>
    <ul class="nav nav-pills mb-2 mt-4">
      <li class="nav-item waves-effect waves-light pr-2">
        <a class="nav-link {{($tab=='profil' ? 'active' : '')}}" href="{{url('anggota/detail?anggota='.$data['anggota']->no_anggota.'&tab=profil')}}">Profil</a>
      </li>
      <li class="nav-item waves-effect waves-light pr-2">
        <a class="nav-link {{($tab=='simpanan' ? 'active' : '')}}" href="{{url('anggota/detail?anggota='.$data['anggota']->no_anggota.'&tab=simpanan')}}">Simpanan</a>
      </li>
      <li class="nav-item waves-effect waves-light pr-2">
        <a class="nav-link {{($tab=='pinjaman' ? 'active' : '')}}" href="{{url('anggota/detail?anggota='.$data['anggota']->no_anggota.'&tab=pinjaman')}}">Pinjaman</a>
      </li>
      <li class="nav-item waves-effect waves-light pr-2">
        <a class="nav-link {{($tab=='belanja_toko' ? 'active' : '')}}" href="{{url('anggota/detail?anggota='.$data['anggota']->no_anggota.'&tab=belanja_toko')}}">Belanja Toko</a>
      </li>
      <li class="nav-item waves-effect waves-light pr-2">
        <a class="nav-link {{($tab=='belanja_konsinyasi' ? 'active' : '')}}" href="{{url('anggota/detail?anggota='.$data['anggota']->no_anggota.'&tab=belanja_konsinyasi')}}">Belanja Konsinyasi</a>
      </li>
      <li class="nav-item waves-effect waves-light pr-2">
        <a class="nav-link {{($tab=='belanja_online' ? 'active' : '')}}" href="{{url('anggota/detail?anggota='.$data['anggota']->no_anggota.'&tab=belanja_online')}}">Belanja Online</a>
      </li>
    </ul>
  </div>
  @include('anggota.detail.'.$tab)
</div>
@endsection
@section('js')
<script>
function changeImage(target) {
  var readURL = function(input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('.'+target+'-wrapper img').attr('src', e.target.result);
      };
      reader.readAsDataURL(input.files[0]);
    }
  };
  $(".file-upload").on('change', function(){
    readURL(this);
  });
  $(".file-upload").click();
}

function formatStatus(status) {
  var $status = $(
    '<span style="display:flex;align-items:center;"><div class="indikator-status mr-2" style="background:'+status.id+'"></div>'+status.text+'</span>'
  );
  return $status;
};

$(".select2-status").select2({
  templateResult: formatStatus
});

function pilih_status(){
  let id = $('#status_color').find('option:selected').attr('data-id');
  $('#status_id').val(id);
  $('#filter_transaksi').submit();
}

</script>
@endsection
