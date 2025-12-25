@php
  $app='sinjam';
  $page='Setoran Simpanan';
  $subpage='Setoran Langsung';
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
      <img src="{{asset('assets/images/icon-page/wallet.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Setoran Simpanan</h4>
        <p class="text-muted m-0">Menampilkan detail setoran simpanan sukarela yang sudah diinput oleh petugas atau anggota</p>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="{{($data['simpanan']->fid_status == 5 ? 'col-md-12' : 'col-md-8')}}">
      <div class="card">
        <div class="card-body">
          @if(!empty($data['keterangan']))
          <div class="center mb-5">
            <img src="{{asset('assets/images/'.$data['simpanan']->icon)}}" style="width:80px">
            <h4 class="mt-3">{{$data['keterangan']->label}}</h4>
            <p>{{$data['keterangan']->keterangan}}</p>
          </div>
          @else
            <div class="center mb-5">
              <img src="{{asset('assets/images/canceled.png')}}" style="width:80px">
              <h4 class="mt-3">Transaksi Dibatalkan</h4>
              <p>Transaksi ini sudah dibatalkan, silahkan melakukan transaksi yang lain</p>
            </div>
          @endif
          <h5 class="mb-3">Informasi Transaksi</h5>
          <table class="table table-informasi">
            <tr>
              <th width="180px">No. Anggota</th>
              <th width="10px">:</th>
              <td>{{$data['simpanan']->no_anggota}}</td>
            </tr>
            <tr>
              <th>Nama Lengkap</th>
              <th>:</th>
              <td>{{$data['simpanan']->nama_lengkap}}</td>
            </tr>
            <tr>
              <th>Jenis Transaksi</th>
              <th>:</th>
              <td>{{$data['simpanan']->jenis_transaksi}}</td>
            </tr>
            <tr>
              <th>Metode Transaksi</th>
              <th>:</th>
              <td>{{$data['simpanan']->metode_transaksi}}</td>
            </tr>
            <tr>
              <th>Jumlah Simpanan</th>
              <th>:</th>
              <td>Rp {{number_format($data['simpanan']->nominal,0,',','.')}}</td>
            </tr>
            <tr>
              <th>Keterangan</th>
              <th>:</th>
              <td>{{(!empty($data['simpanan']->keterangan) ? $data['simpanan']->keterangan : 'Tidak ada keterangan')}}</td>
            </tr>
            <tr>
              <th>Bukti Pembayaran</th>
              <th>:</th>
              <td>
                @if(!empty($data['simpanan']->bukti_transaksi))
                <a href="{{asset('storage/'.$data['simpanan']->bukti_transaksi)}}" target="_blank" class="btn btn-primary" >Lihat Dokumen</a>
                @else
                <a href="" target="_blank" class="btn btn-danger" disabled >Belum Upload</a>
                @endif
              </td>
            </tr>
          </table>
          <h5 class="mb-3 mt-4">Riwayat Transaksi</h5>
          <ul class="verti-timeline list-unstyled">
            <li class="event-list">
              <div class="event-timeline-dot">
                <i class="bx bx-right-arrow-circle"></i>
              </div>
              <h6>{{\App\Helpers\GlobalHelper::tgl_indo($data['simpanan']->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($data['simpanan']->created_at,'H:i:s')}}</h6>
              <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500">{{$data['simpanan']->nama_petugas}}</span></p>
            </li>
            @foreach (\App\Helpers\GlobalHelper::get_verifikasi_transaksi($id,'transaksi') as $key => $value)
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
          <div class=" pull-right">
            <a class="btn btn-dark" href="{{url('simpanan/sukarela')}}" >Kembali</a>
            @if($data['simpanan']->fid_status <= 2 )
            <a class="btn btn-primary"  href="{{url('simpanan/sukarela/form?id='.$id)}}">Edit Setoran</a>
            @endif
          </div>
        </div>
      </div>
    </div>
    @if($data['simpanan']->fid_status!=5)
    <div class="col-md-4">
      <div style="position:sticky;top:100px;width:100%;z-index:100">
        <div class="alert alert-secondary mb-4" role="alert">
          <h5 class="font-size-18 mb-3">Verifikasi Transaksi</h5>
          @if($data['simpanan']->fid_status==1)
            <p>Harap segera melakukan verifikasi terhadap transaksi ini berdsarkan bukti pembayaran yang sudah diupload </p>
          @else
            <p>Terimakasih sudah melakukan verifikasi terhadap transaksi ini, silahkan batalkan verifikasi jika terjadi kesalahan</p>
          @endif
          <div class="mb-2">
            @if($data['simpanan']->fid_status==1)
            <button class="btn btn-danger" onclick="confirm_verifikasi(2)">Ditolak</button>
            <button class="btn btn-primary" onclick="confirm_verifikasi(4)">Disetujui</button>
            @else
            <button class="btn btn-dark" onclick="confirm_verifikasi(1)">Batalkan Verifikasi</button>
            @endif
          </div>
        </div>
        @if($data['simpanan']->fid_status <= 2 )
        <div class="alert alert-danger  mb-4" role="alert">
          <h5 class="font-size-18">Batalkan Transaksi</h5>
          <p class="mt-3">Silahkan melakukan pembatalan setoran simpanan sebelum diverifikasi oleh petugas</p>
          <button class="btn btn-danger mb-2" onclick="confirm_verifikasi(5)">Batalkan Setoran</button>
        </div>
        @endif
      </div>
    </div>
    @endif
  </div>
</div>
<form action="{{url('simpanan/sukarela/verifikasi')}}" id="verifikasi_transaksi" method="post">
  {{ csrf_field() }}
  <input type="hidden" name="id" value="{{$id}}">
  <input type="hidden" name="status" id="status">
</form>
@endsection
@section('js')
<script>
function confirm_verifikasi(status){
  if(status==2){
    text="Apakah anda yakin ingin menolak transaksi setoran simpanan ini?";
  }
  else if(status==3){
    text="Apakah anda yakin ingin menerima transaksi setoran simpanan ini?";
  }
  else if(status==5){
    text="Apakah anda yakin ingin membatalkan transaksi setoran simpanan ini?";
  }
  else{
    text="Apakah anda yakin ingin membatalkan verifikasi transaksi setoran simpanan?";
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
      $('#verifikasi_transaksi').submit();
    }
  });
}
</script>
@endsection
