@php
  $app='sinjam';
  $page='Monitoring Anggota';
  $subpage='Sisa Pinjaman';
@endphp
@extends('layouts.admin')
@section('title')
  Monitoring Sisa Pinjaman |
@endsection
@section('content')
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/icon-page/save-money.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Monitoring Sisa Pinjaman</h4>
          <p class="text-muted m-0">Menampilkan data sisa pinjaman anggota</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-4">
        <form action="" method="get" id="status_form" >
          <input type="hidden" value="{{$status}}" id="status_id" name="status" value="">
          <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
            <option value="#282828" data-id="all">Semua Status</option>
            @foreach ($data['status'] as $key => $value)
            <option value="{{$value->color}}" {{($status == $value->id ? 'selected' : '')}} data-id="{{ $value->id}}" >{{$value->status_anggota}}</option>
            @endforeach
          </select>
        </form>
      </div>
      <div class="col-md-8">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Anggota">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  @if($data['sisa_pinjaman']==null || count($data['sisa_pinjaman']) == 0)
  <div style="width:100%;text-align:center">
    <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
    <h4 class="mt-3">Anggota tidak Ditemukan</h4>
  </div>
  @else
  <div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
      <thead>
        <tr>
          <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
          <th style="text-align:right;width:150px">Sisa Pinjaman<br>Jangka Panjang</th>
          <th style="text-align:right;width:150px">Sisa Pinjaman<br>Jangka Pendek</th>
          <th style="text-align:right;width:150px">Sisa Pinjaman<br>Barang</th>
          <th style="text-align:right;width:150px">Total<br>Sisa Pinjaman</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['sisa_pinjaman'] as $key => $value)
          <tr>
            <td style="border-color:{{$value->color}}">
              <div class="media">
                <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                  <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle">
                </div>
                <div class="media-body align-self-center">
                  <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                  <h5 class="text-truncate font-size-15"><a href="{{url('anggota/detail?id='.$value->id)}}" class="text-dark">{{$value->nama_lengkap}}</a></h5>
                </div>
              </div>
            </td>
            <td style="text-align:right">
              <div style="font-weight:500">Rp {{number_format($value->sisa_jangka_panjang,'0',',','.')}}</div>
              @if($value->sisa_jangka_panjang!=0)<div>{{$value->tenor_jangka_panjang['sisa']}} dari {{$value->tenor_jangka_panjang['tenor']}}</div>@endif
            </td>
            <td style="text-align:right">
              <div style="font-weight:500">Rp {{number_format($value->sisa_jangka_pendek,'0',',','.')}}</div>
              @if($value->sisa_jangka_pendek!=0)<div>{{$value->tenor_jangka_pendek['sisa']}} dari {{$value->tenor_jangka_pendek['tenor']}}</div>@endif
            </td>
            <td style="text-align:right">
              <div style="font-weight:500">Rp {{number_format($value->sisa_barang,'0',',','.')}}</div>
              @if($value->sisa_barang!=0)<div>{{$value->tenor_barang['sisa']}} dari {{$value->tenor_barang['tenor']}}</div>@endif
            </td>
            <td style="text-align:right">
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="mb-4 mt-3">
    {{ $data['sisa_pinjaman']->links('include.pagination', ['pagination' => $data['sisa_pinjaman']] ) }}
  </div>
  @endif
</div>
@endsection
@section('js')
<script>
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
  $('#status_form').submit();
}
</script>
@endsection
