@php
  $app='master';
  $page='Data Pengurus';
  $subpage='Data Pengurus';
@endphp
@extends('layouts.admin')
@section('title')
  Data Pengurus |
@endsection
@section('content')
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/icon-page/organization-chart.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Data Pengurus</h4>
          <p class="text-muted m-0">Menampilkan data pengurus yang sudah dibentuk berdasarkan periode kepengurusan</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
        <form action="" method="get">
        <select class="select2 form-control" name="periode" onchange="javascript:submit()">
          <option value="new">Tambah Periode Baru</option>
          @foreach ($data['pilih-periode'] as $key => $value)
          <option value="{{$value->id}}" {{($periode == $value->id ? 'selected' : '')}}>Periode {{$value->periode_awal}} s/d {{$value->periode_akhir}}</option>
          @endforeach
        </select>
        </form>
      </div>
      <div class="col-md-6">
        <form action="" method="get">
          <input type="hidden" name="periode" value="{{$periode}}">
          <div class="input-group">
            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Pengurus">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <a href="{{url('master/pengurus/form')}}" class="btn btn-primary btn-block">Tambah Pengurus</a>
      </div>
    </div>
  </div>
  @if(count($data['pengurus'])==0)
  <div style="width:100%;text-align:center">
    <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
    <h4 class="mt-2">Anggota tidak Ditemukan</h4>
  </div>
  @else
  <div class="table-responsive">
    <table class="table table-middle table-custom">
      <thead class="thead-light">
        <tr>
          <th>No. Anggota / Nama Lengkap</th>
          <th>Jabatan</th>
          <th>Contact</th>
          <th class="center">Tanggal<br>Menjabat</th>
          <th class="center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['pengurus'] as $key => $value)
          <tr>
            <td>
              <div class="media" style="border-color:{{($value->status=='Aktif' ? '#16a085' : '#c0392b')}}">
                <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                  <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle">
                </div>
                <div class="media-body align-self-center">
                  <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                  <h5 class="text-truncate font-size-15"><a href="{{url('anggota/detail?id='.$value->id)}}" class="text-dark">{{$value->nama_lengkap}}</a></h5>
                </div>
              </div>
            </td>
            <td>{{$value->nama_jabatan}}</td>
            <td>
              <div>{{$value->email}}</div>
              <div>{{$value->no_handphone}}</div>
            </td>
            <td class="center">{{\App\Helpers\GlobalHelper::tgl_indo($value->tanggal)}}</td>
            <td class="center">{{$value->status}}</td>
            <td style="width:1px;white-space:nowrap">
              <div class="text-center">
                <a href="{{url('master/pengurus/form?id='.$value->id)}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                <form action="{{url('master/pengurus/proses')}}" method="post" id="hapus{{$value->id}}">
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
  <div class="mb-4">
    {{ $data['pengurus']->links('include.pagination', ['pagination' => $data['pengurus']] ) }}
  </div>
  @endif
</div>
@endsection
@section('js')
  <script>

  </script>
@endsection
