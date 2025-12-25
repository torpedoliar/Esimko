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

  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/icon-page/profile.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Data Anggota</h4>
          <p class="text-muted m-0">Menampilkan data anggota yang sudah terdaftar di koperasi</p>
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
            <option value="{{$value->color}}" {{($status == $value->id ? 'selected' : '')}} data-id="{{ $value->id}}" >{{$value->status_anggota}}</option>
            @endforeach
          </select>
        </form>
      </div>
      <div class="col-md-7">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control box" value="{{$search}}" name="search" placeholder="Cari Data Anggota">
            <div class="input-group-append">
              <button class="btn btn-dark box" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-2">
        <a href="{{url('anggota/form')}}" class="btn box btn-primary btn-block">Tambah Anggota</a>
      </div>
    </div>
  </div>
  @if(count($data['anggota'])==0)
  <div style="width:100%;text-align:center">
    <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
    <h4 class="mt-2">Data Karyawan tidak Ditemukan</h4>
  </div>
  @else
  <div class="table-responsive">
    <table class="table table-middle table-custom">
      <thead>
        <tr>
          <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
          <th class="center">No. HIRS</th>
          <th>Alamat</th>
          <th>Kontak</th>
          <th class="center">Tanggal<br>Bergabung</th>
          <th class="center">Tanggal<br>Bekerja</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['anggota'] as $key => $value)
          <tr>
            <td onclick="location.href = '{{url('anggota/detail?anggota='.$value->no_anggota)}}'" style="border-color:{{$value->color}}">
              <div class="media">
                <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                  <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle">
                </div>
                <div class="media-body align-self-center">
                  <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                  <h5 class="text-truncate font-size-13"><a href="{{url('anggota/detail?id='.$value->id)}}" class="text-dark">{{$value->nama_lengkap}}</a></h5>
                </div>
              </div>
            </td>
            <td onclick="location.href = '{{url('anggota/detail?anggota='.$value->no_anggota)}}'" class="center" >{{$value->no_hirs}}23648736</td>
            <td onclick="location.href = '{{url('anggota/detail?anggota='.$value->no_anggota)}}'">{{$value->alamat}}</td>
            <td>
              @if($value->email==null && $value->no_handphone==null)
                <div style="font-style:italic;white-space:nowrap;text-align:center">Belum<br>Ada Kontak</div>
              @else
                <div>{{$value->email}}</div>
                <div>{{$value->no_handphone}}</div>
              @endif
            </td>
            <td onclick="location.href = '{{url('anggota/detail?anggota='.$value->no_anggota)}}'" class="center" >
              <div>{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal_bergabung,'d/m/Y')}}</div>
              <div style="font-weight:500">{{\App\Helpers\GlobalHelper::hitung_hari($value->tanggal_bergabung,date('Y-m-d'),'y')}} tahun</div>
            </td>
            <td onclick="location.href = '{{url('anggota/detail?anggota='.$value->no_anggota)}}'" class="center" >
              <div>{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal_bekerja,'d/m/Y')}}</div>
              <div style="font-weight:500">{{\App\Helpers\GlobalHelper::hitung_hari($value->tanggal_bekerja,date('Y-m-d'),'y')}} tahun</div>
            </td>
            <td style="width:1px;white-space:nowrap">
              <div class="text-center">
                <a href="{{url('anggota/form?id='.$value->id)}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                <form action="{{url('anggota/proses')}}" method="post" id="hapus{{$value->id}}">
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
  <div class="mb-3">
    {{ $data['anggota']->links('include.pagination', ['pagination' => $data['anggota']] ) }}
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
