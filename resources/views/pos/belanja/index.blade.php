@php
  $app='pos';
  $page='Belanja '.ucfirst($jenis);
  $subpage='Belanja '.ucfirst($jenis);
@endphp
@extends('layouts.admin')
@section('title')
  {{$page}} |
@endsection
@section('content')
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/icon-page/shopping-cart.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">{{$page}}</h4>
          <p class="text-muted m-0">Menampilkan data belanja {{$jenis}} anggota</p>
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
      <div class="col-md-7">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Search Transaksi Penjualan">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-2">
        <a href="{{url('pos/belanja/'.$jenis.'/form')}}" class="btn btn-primary btn-block" >Tambah Transaksi</a>
      </div>
    </div>
  </div>
  @if(count($data['penjualan'])==0)
  <div style="width:100%;text-align:center">
    <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
    <h4 class="mt-2">Data {{$page}} tidak Ditemukan</h4>
  </div>
  @else
  <div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
      <thead>
        <tr>
          <th>No. Transaksi<hr class="line-xs">Tanggal</th>
          <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
          @if($jenis=='online')
          <th>Marketplace Platform<hr class="line-xs">Nama Toko</th>
          @endif
          <th style="text-align:right">Total<br>Belanja</th>
          <th style="text-align:right">Angsuran</th>
          <th class="center">Sisa<br>Tenor</th>
          <th style="text-align:right">Sisa<br>Angsuran<br></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['penjualan'] as $key => $value)
        <tr>
          <td style="width:1px;white-space: nowrap;border-color:{{$value->color}}">
            <h6>No. {{$value->no_transaksi}}</h6>
            {{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,"H:i:s")}}
          </td>
          @if($jenis=='online')
          <td>
            <span>{{$value->marketplace}}<span>
            <h6>{{$value->nama_toko}}</h6>
          </td>
          @endif
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
          <td style="text-align:right">Rp {{number_format($value->total_pembayaran,0,',','.')}}</td>
          <td style="text-align:right">Rp {{number_format($value->angsuran,0,',','.')}}</td>
          <td class="center">{{$value->sisa_tenor}} dari {{$value->tenor}}</td>
          <td style="text-align:right">Rp {{number_format($value->sisa_angsuran,0,',','.')}}</td>
          <td style="width:1px;white-space:nowrap">
            <div class="text-center">
              <a href="{{url('pos/belanja/'.$jenis.'/detail?id='.$value->id)}}" class="text-dark"><i class="bx bx-search-alt h3 m-0"></i></a>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="mb-4">
    {{ $data['penjualan']->links('include.pagination', ['pagination' => $data['penjualan']] ) }}
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
