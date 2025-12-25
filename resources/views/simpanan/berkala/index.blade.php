@php
  $app='sinjam';
  $page='Setoran Simpanan';
  $subpage='Setoran Simpanan';
@endphp
@extends('layouts.admin')
@section('title')
  Setoran Simpanan |
@endsection
@section('content')
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/icon-page/calendar.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Setoran Simpanan Berkala</h4>
          <p class="text-muted m-0">Menampilkan data pengajuan setoran berkala simpanan sukarela yang sudah diinput oleh petugas atau anggota</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
        <form action="" method="get" id="status_form" >
          <input type="hidden" value="{{$status}}" id="status_id" name="status" value="">
          <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
            <option value="#282828" data-id="all" {{($status =='all' ? 'selected' : '')}} >Semua Status</option>
            <option value="#27ae60" data-id="1" {{($status == '1' ? 'selected' : '')}} >Aktif</option>
            <option value="#e74c3c" data-id="2" {{($status == '2' ? 'selected' : '')}} >Tidak Aktif</option>
          </select>
        </form>
      </div>
      <div class="col-md-6">
        <form action="" method="get">
          <input type="hidden" name="status" value="{{$status}}">
          <div class="input-group">
            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Setoran Simpanan Berkala">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <a href="{{url('simpanan/sukarela/berkala/form')}}" class="btn btn-primary btn-block">Formulir Setoran Berkala</a>
      </div>
    </div>
  </div>
  @if(count($data['setoran-berkala'])==0)
  <div style="width:100%;text-align:center">
    <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
    <h4 class="mt-3">Data Setoran Simpanan Berkala tidak Ditemukan</h4>
  </div>
  @else
  <div class="table-responsive">
    <table class="table table-middle table-custom">
      <thead>
        <tr>
          <th class="center">Tanggal</th>
          <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
          <th style="text-align:right">Nominal Setoran</th>
          <th class="center">Jadwal<br>Setoran Berkala </th>
          <th class="center">Keterangan</th>
          {{-- <th>Created by</th> --}}
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['setoran-berkala'] as $key => $value)
          <tr>
            <td class="center" style="width:1px;white-space:nowrap;border-color:{{($value->fid_status == 1 ? '#27ae60' : '#e74c3c')}}">
              {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'d/m/Y')}}
            </td>
            <td>
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
            <td style="text-align:right">Rp {{number_format($value->nominal,0,',','.')}}</td>
            <td class="center">
              @if($value->bulan_awal==$value->bulan_akhir)
              {{\App\Helpers\GlobalHelper::nama_bulan($value->bulan_awal)}}
              @elseif($value->bulan_akhir=='Belum Ditentukan')
              {{\App\Helpers\GlobalHelper::nama_bulan($value->bulan_awal)}} s/d Belum Ditentukan
              @else
              {{\App\Helpers\GlobalHelper::nama_bulan($value->bulan_awal)}} s/d {{\App\Helpers\GlobalHelper::nama_bulan($value->bulan_akhir)}}
              @endif
            </td>
            <td class="center">{{(!empty($value->keterangan) ? $value->keterangan : 'Tidak ada Keterangan')}}</td>
            {{-- <td style="width:1px;white-space:nowrap">
              <h6>({{$value->created_by}}) {{$value->nama_petugas}}</h6>
              at {{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')}}
            </td> --}}
            <td style="width:1px;white-space:nowrap">
              <div class="text-center">
                <a href="{{url('simpanan/sukarela/berkala/detail?id='.$value->id)}}" class="text-dark"><i class="bx bx-search h3 m-0"></i></a>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="mb-3">
    {{ $data['setoran-berkala']->links('include.pagination', ['pagination' => $data['setoran-berkala']] ) }}
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
