@php
  $app='pos';
  $page='Angsuran Belanja';
  $subpage='Angsuran Belanja';

  if(!empty($data['payroll'])){
    if($data['payroll']->fid_status==1 || $data['payroll']->fid_status==0){
      $disabled=$data['status'];
    }
    else{
      $disabled='disabled';
    }
  }
  else{
    $disabled=$data['status'];
  }

@endphp
@extends('layouts.admin')
@section('title')
  Angsuran Belanja |
@endsection
@section('content')
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/icon-page/pay-day.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Angsuran Belanja</h4>
          <p class="text-muted m-0">Menampilkan data angsuran kredit belanja anggota di toko</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-2">
        <form action="" method="get">
          <input type="text" name="bulan" class="form-control monthpicker" value="{{$bulan}}" onchange="javascript:submit()" autocomplete="off">
        </form>
      </div>
      <div class="col-md-7">
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
        <button class="btn btn-primary btn-block" @if($disabled=='disabled') {{$disabled}} @else onclick="confirm_proses()" @endif >Proses Payroll</button>
      </div>
    </div>
  </div>
  @if($data['payroll']==null)
  <div style="width:100%;text-align:center">
    <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
    <h4 class="mt-3">Payroll Angsuran Belanja Belum Diproses</h4>
  </div>
  @else
  <div class="row mt-4 mb-4">
    <div class="col-md-8">
      <div style="height:100%">
        @if(count($data['payroll']->data)==0)
        <div style="width:100%;text-align:center">
          <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
          <h4 class="mt-3">Data Anggota tidak Ditemukan</h4>
        </div>
        @else
        <div class="table-responsive">
          <table class="table table-middle table-custom">
            <thead>
              <tr>
                <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
                <th class="center">Jenis Belanja</th>
                <th>No Transaksi</th>
                <th class="center">Angsuran Ke</th>
                <th style="text-align:right;width:100px">Total Angsuran</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data['payroll']->data as $key => $value)
              <tr>
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
                <td class="center" >Belanja {{ucfirst($value->jenis_belanja)}}</td>
                <td style="white-space:nowrap">{{$value->no_transaksi}}</td>
                <td class="center">{{$value->angsuran_ke}} dari {{$value->tenor}}</td>
                <td style="text-align:right">{{number_format($value->total_angsuran,'0',',','.')}}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div style="margin-top:20px">
          {{ $data['payroll']->data->links('include.pagination', ['pagination' => $data['payroll']->data] ) }}
        </div>
        @endif
      </div>
    </div>
    <div class="col-md-4">
      <div style="border-left:1px solid #dedede;padding:20px 20px;height:100%">
        <div class="center">
          <img src="{{asset('assets/images/'.$data['payroll']->icon)}}" style="width:65px" class="">
          <h5 class="mb-2 mt-2">{{$data['payroll']->status}}</h5>
          <p>{{$data['payroll']->keterangan}}</p>
          <a href="" class="btn btn-secondary" >Cetak Payroll</a>
          @if($data['payroll']->fid_status==1)
          <button class="btn btn-warning" onclick="confirm_payroll(2)">Sudah Dikirim</button>
          @elseif($data['payroll']->fid_status==2)
          <button class="btn btn-info" onclick="confirm_payroll(3)">Konfirmasi Pembayaran</button>
          @else
          <button class="btn btn-danger" onclick="confirm_payroll(1)">Batalkan Verifikasi</button>
          @endif
        </div>
        <h5 class="mb-3 mt-5">Riwayat Transaksi</h5>
        <ul class="verti-timeline list-unstyled">
          <li class="event-list">
            <div class="event-timeline-dot">
              <i class="bx bx-right-arrow-circle"></i>
            </div>
            <h6>{{\App\Helpers\GlobalHelper::tgl_indo($data['payroll']->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($data['payroll']->created_at,'H:i:s')}}</h6>
            <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500">{{$data['payroll']->nama_lengkap}}</span></p>
          </li>
          @foreach (\App\Helpers\GlobalHelper::get_verifikasi_transaksi($data['payroll']->id,'payroll_belanja') as $key => $value)
          <li class="event-list">
            <div class="event-timeline-dot">
              <i class="bx bx-right-arrow-circle"></i>
            </div>
            <h6>{{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')}}</h6>
            <p class="text-muted">{{$value->caption}} <span style="font-weight:500">{{$value->nama_lengkap}}</span></p>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
  {{-- <div class="alert alert-{{$data['payroll']->color}} mt-3 mb-4" style="text-align:left" role="alert">
    <h5 class="mb-2">{{$data['payroll']->status}}</h5>
    <p class="mb-2">{{$data['payroll']->keterangan}}</p>
    <a href="" class="btn btn-secondary" >Cetak Payroll</a>
    @if($data['payroll']->fid_status==1)
    <button class="btn btn-warning" onclick="confirm_payroll(2)">Sudah Dikirim</button>
    @elseif($data['payroll']->fid_status==2)
    <button class="btn btn-info" onclick="confirm_payroll(3)">Konfirmasi Pembayaran</button>
    @else
    <button class="btn btn-danger" onclick="confirm_payroll(1)">Batalkan Verifikasi</button>
    @endif
  </div> --}}
  <form action="{{url('pos/angsuran/verifikasi')}}" method="post" id="verifikasi_payroll" >
    {{ csrf_field() }}
    <input type="hidden" name="bulan" value="{{$bulan}}">
    <input type="hidden" name="status" id="status">
  </form>
  @endif
</div>
<form action="{{url('pos/angsuran/proses')}}" id="proses_angsuran" method="post">
  {{ csrf_field() }}
  <input type="hidden" name="bulan" value="{{$bulan}}">
</form>
@endsection
@section('js')
<script>
function confirm_proses(){
  Swal.fire({
    title: "Are you sure?",
    text: "Apakah anda yakin ingin memproses angsuran belanja anggota pada bulan ini",
    type:"question",
    showCancelButton: true,
    confirmButtonColor: '#16a085',
    cancelButtonColor: '#cbcbcb',
    confirmButtonText: 'Proses Angsuran'
  }).then((result) => {
    if (result.value == true) {
      $('#proses_angsuran').submit();
    }
  });
}

function confirm_payroll(status){
  if(status==2){
    text="Apakah Data Payroll Angsuran sudah dikirim ke HRD Perusahaan?";
  }
  else if(status==3){
    text="Apakah Pembayaran Payroll Angsuran dari Perusahaan sudah diterima oleh Koperasi?";
  }
  else{
    text="Apakah anda yakin ingin membatalkan Payroll Angsuran ini agar bisa diproses ulang";
  }
  $('#status').val(status);
  Swal.fire({
    title: "Are you sure?",
    type:"question",
    text:text,
    showCancelButton: true,
    confirmButtonColor: '#16a085',
    cancelButtonColor: '#cbcbcb',
    confirmButtonText: 'Yes'
  }).then((result) => {
    if (result.value == true) {
      $('#verifikasi_payroll').submit();
    }
  });
}
</script>
@endsection
