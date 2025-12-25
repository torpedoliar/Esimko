@php
  $app='sinjam';
  $page='Setoran Simpanan';
  $subpage='Setoran Berkala';
@endphp
@extends('layouts.admin')
@section('title')
  Setoran Simpanan |
@endsection
@section('css')
  <style>
  .list-anggota{
    padding-bottom:10px;
    border-bottom: 1px solid #f2f2f2;
    margin-top:10px;
    cursor: pointer;
  }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/calendar.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Setoran Simpanan Berkala</h4>
        <p class="text-muted m-0">Formulir setoran berkala simpanan sukarela yang dilakukan oleh petugas</p>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          @if($data['setoran-berkala']->fid_status == 1 )
            <div class="center mb-5">
              <img src="{{asset('assets/images/success.png')}}" style="width:80px">
              <h4 class="mt-3">Setoran Berkala Aktif</h4>
              <p>Silahkan melakukan proses payroll untuk pemotongan gaji setiap bulan untuk setoran simpanan berkala</p>
            </div>
          @else
            <div class="center mb-5">
              <img src="{{asset('assets/images/failed.png')}}" style="width:80px">
              <h4 class="mt-3">Setoran Berkala Dihentikan</h4>
              <p>Anggota ini sudah tidak akan dilakukan pemotongan gaji setiap bulan untuk setoran simpanan berkala</p>
            </div>
          @endif
          <h5 class="mb-3">Informasi Transaksi</h5>
          <table class="table table-informasi">
            <tr>
              <th width="180px">No. Anggota</th>
              <th width="10px">:</th>
              <td>{{$data['setoran-berkala']->no_anggota}}</td>
            </tr>
            <tr>
              <th>Nama Lengkap</th>
              <th>:</th>
              <td>{{$data['setoran-berkala']->nama_lengkap}}</td>
            </tr>
            <tr>
              <th>Jenis Transaksi</th>
              <th>:</th>
              <td>Setoran Berkala Simpanan Sukarela</td>
            </tr>
            <tr>
              <th>Metode Transaksi</th>
              <th>:</th>
              <td>Payroll / Potong Gaji</td>
            </tr>
            <tr>
              <th>Jumlah Setoran</th>
              <th>:</th>
              <td>Rp {{number_format($data['setoran-berkala']->nominal,0,',','.')}}</td>
            </tr>
            <tr>
              <th>Jadwal Setoran</th>
              <th>:</th>
              <td>
                @if($data['setoran-berkala']->bulan_awal==$data['setoran-berkala']->bulan_akhir)
                {{\App\Helpers\GlobalHelper::nama_bulan($data['setoran-berkala']->bulan_awal)}}
                @elseif($data['setoran-berkala']->bulan_akhir=='Belum Ditentukan')
                {{\App\Helpers\GlobalHelper::nama_bulan($data['setoran-berkala']->bulan_awal)}} s/d Belum Ditentukan
                @else
                {{\App\Helpers\GlobalHelper::nama_bulan($data['setoran-berkala']->bulan_awal)}} s/d {{\App\Helpers\GlobalHelper::nama_bulan($data['setoran-berkala']->bulan_akhir)}}
                @endif
              </td>
            </tr>
            <tr>
              <th>Keterangan</th>
              <th>:</th>
              <td>{{(!empty($data['setoran-berkala']->keterangan) ? $data['setoran-berkala']->keterangan : 'Tidak ada keterangan')}}</td>
            </tr>
          </table>
          <h5 class="mb-3 mt-4">Riwayat Transaksi</h5>
          <ul class="verti-timeline list-unstyled">
            <li class="event-list">
              <div class="event-timeline-dot">
                <i class="bx bx-right-arrow-circle"></i>
              </div>
              <h6>{{\App\Helpers\GlobalHelper::tgl_indo($data['setoran-berkala']->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($data['setoran-berkala']->created_at,'H:i:s')}}</h6>
              <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500">{{$data['setoran-berkala']->nama_petugas}}</span></p>
            </li>
            @foreach (\App\Helpers\GlobalHelper::get_verifikasi_transaksi($id,'setoran berkala') as $key => $value)
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
        <div class="card-footer">
          <div class="pull-right">
            <a class="btn btn-dark" href="{{url('simpanan/sukarela/berkala')}}" >Kembali</a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div style="position:sticky;top:100px;width:100%;z-index:100">
        <div class="alert alert-secondary mb-4" role="alert">
          <h5 class="font-size-18 mb-3">Aktifasi Setoran</h5>
          @if($data['setoran-berkala']->fid_status==1)
            <p>Setoran berkala dalam status aktif, silahkan ubah atau nonaktifkan setoran berkala anda</p>
          @else
            <p>Setoran berkala dalam status nonaktif, silahkan aktifkan kembali setoran berkala anda</p>
          @endif
          <div class="mb-2">
            @if($data['setoran-berkala']->fid_status == 1 )
            <a class="btn btn-primary"  href="{{url('simpanan/sukarela/berkala/form?id='.$id)}}">Ubah Setoran</a>
            <button class="btn btn-danger" onclick="proses_aktivasi(2)">Nonaktifkan</button>
            @else
            <button class="btn btn-primary" onclick="proses_aktivasi(1)">Aktifkan Kembali</button>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<form action="{{url('simpanan/sukarela/berkala/proses')}}" id="proses_aktifasi" method="post">
  {{ csrf_field() }}
  <input type="hidden" name="no_anggota" value="{{$data['setoran-berkala']->no_anggota}}">
  <input type="hidden" name="action" value="aktifasi">
  <input type="hidden" id="status" name="status">
  <input type="hidden" name="id" value="{{$id}}">
</form>
@endsection
@section('js')
<script>
function proses_aktivasi(id){
  if(id==1){
    text='Apakah anda ingin mengaktifkan kembali setoran berkala ini';
  }
  else{
    text='Apakah anda ingin mengnonaktifkan setoran berkala ini';
  }
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
      $('#status').val(id);
      $('#proses_aktifasi').submit();
    }
  });
}
</script>
@endsection
