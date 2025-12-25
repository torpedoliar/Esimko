@php
  $app='sinjam';
  $page='Penarikan Simpanan';
  $subpage='Semua Simpanan';
@endphp
@extends('layouts.admin')
@section('title')
  Penarikan Semua Simpanan |
@endsection
@section('content')
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/icon-page/penarikan.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Penarikan Semua Simpanan</h4>
          <p class="text-muted m-0">Menampilkan data penarikan semua simpanan yang sudah diinput oleh petugas atau anggota</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
        <form action="" method="get" id="status_form" >
          <input type="hidden" value="{{$status}}" id="status_id" name="status" value="">
          <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
            <option value="#282828" data-id="all">Semua Status</option>
            @foreach ($data['status'] as $key => $value)
            <option value="{{$value->color}}" {{($status == $value->id ? 'selected' : '')}} data-id="{{ $value->id}}" >{{$value->status}}</option>
            @endforeach
          </select>
        </form>
      </div>
      <div class="col-md-6">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Anggota">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <a class="btn btn-primary btn-block" href="{{url('penarikan/penutupan/form')}}">Formulir Penarikan</a>
      </div>
    </div>
  </div>
  @if(count($data['penarikan'])==0)
  <div style="width:100%;text-align:center">
    <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
    <h4 class="mt-3">Data Penarikan Semua Simpanan<br>Tidak Ditemukan</h4>
  </div>
  @else
  <div class="table-responsive">
    <table class="table table-middle table-custom">
      <thead>
        <tr>
          <th class="center" style="width:150px" >Tanggal</th>
          <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
          <th class="center">Metode<br>Transaksi</th>
          <th style="text-align:right">Jumlah<br>Penarikan</th>
          <th class="center">Keterangan</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['penarikan'] as $key => $value)
          <tr>
            <td class="center" style="border-color:{{$value->color}}">{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')}}</td>
            <td>
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
            <td class="center">{{$value->metode_transaksi}}</td>
            <td style="text-align:right">{{number_format(str_replace('-','',$value->nominal),0,',','.')}}</td>
            <td class="center">{{(!empty($value->keterangan) ? $value->keterangan : 'Tidak ada Keterangan')}}</td>
            <td style="width:1px;white-space:nowrap">
              <div class="text-center">
                <a href="{{url('penarikan/penutupan/detail?id='.$value->id)}}" class="text-dark"><i class="bx bx-search h3 m-0"></i></a>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="mb-4">
    {{ $data['penarikan']->links('include.pagination', ['pagination' => $data['penarikan']] ) }}
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
