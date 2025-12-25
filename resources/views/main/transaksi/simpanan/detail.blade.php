@php
  $page='Simpanan';
  $subpage='Simpanan';
@endphp
@extends('layouts.main')
@section('title')
Simpanan |
@endsection
@section('css')
  <style>

  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="page-title-box pb-0">
    <div class="media">
      <img src="{{asset('assets/images/icon-page/wallet.png')}}" class="avatar-md mr-3">
      <div class="media-body align-self-center">
        <h4 class="mb-0 font-size-18">Data Simpanan</h4>
        <p class="text-muted m-0">Menampilkan data setoran simpanan sukarela yang sudah diinput oleh petugas atau anggota</p>
      </div>
    </div>
  </div>
  <div class="row mt-5">
    <div class="col-md-8">
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

          @if($data['simpanan']->fid_status==1)
          <div class="alert alert-{{(!empty($data['simpanan']->bukti_transaksi) ? 'success' : 'warning')}} mt-5" role="alert">
            <h5 class="mb-2">Konfirmasi Pembayaran</h5>
            @if(!empty($data['simpanan']->bukti_transaksi))
              <p>Terimakasih sudah melakukan pembayaran setoran simpanan. Harap menunggu verifikasi dari petugas</p>
            @else
              <p>Anda belum melakukan pembayaran setoran simpanan. Harap segera melakukan pembayaran dan upload bukti pembayaran</p>
            @endif
            <button class="btn btn-{{(!empty($data['simpanan']->bukti_transaksi) ? 'primary' : 'warning')}}" data-target="#modal-upload" data-toggle="modal">Upload Bukti Pembayaran</button>
            @if(!empty($data['simpanan']->bukti_transaksi))
            <a href="{{url('')}}" class="btn btn-secondary">Lihat Bukti Transaksi</a>
            @endif
          </div>
          @endif
        </div>
        <div class="card-footer">
          <a href="{{url('main/simpanan')}}" class="btn btn-dark pull-right">Kembali</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h5>Edit {{$data['simpanan']->group_transaksi}}</h5>
          <hr>
          <form action="{{url('main/transaksi/proses')}}" method="post">
            {{ csrf_field() }}
            <div class="form-group">
              <label>Jumlah {{$data['simpanan']->group_transaksi}} (Rp)</label>
              <input type="text" name="nominal" {{($data['simpanan']->fid_status >= 3 ? 'disabled' : '')}} value="{{$data['simpanan']->nominal}}" style="text-align:right" class="form-control autonumeric" data-a-dec="," data-a-sep="." required >
            </div>
            <div class="form-group">
              <label>Keterangan</label>
              <textarea name="keterangan" {{($data['simpanan']->fid_status >= 3 ? 'disabled' : '')}} class="form-control" style="height:100px">{{$data['simpanan']->keterangan}}</textarea>
            </div>
            <input type="hidden" name="jenis_transaksi" value="{{$data['simpanan']->fid_jenis_transaksi}}">
            <input type="hidden" name="modul" value="{{$data['simpanan']->modul}}">
            <input type="hidden" name="id" value="{{$id}}">
            @if(in_array($data['simpanan']->fid_status,array('3','4')))
              <div class="alert alert-warning mt-2 mb-0">
                <p class="text-muted m-0">Maaf anda sudah tidak bisa mengubah atau membatalkan transaksi ini, karena sudah disetujui oleh petugas koperasi</p>
              </div>
            @elseif($data['simpanan']->fid_status == 5 )
              <div class="alert alert-warning mt-2 mb-0">
                <p class="text-muted m-0">Maaf anda sudah tidak bisa mengubah atau melanjutkan proses transaksi ini, karena transaksi ini sudah dibatalkan</p>
              </div>
            @else
            <div class="alert alert-secondary mt-2">
              <p class="text-muted m-0">Silahkan anda {{($data['simpanan']->fid_status == 1 ? 'mengubah' : 'memproses ulang')}} atau membatalkan transaksi ini sebelum disetujui oleh petugas koperasi</p>
            </div>
            <div class="pull-right">
              <button class="btn btn-danger" type="button" name="button" onclick="batalkan()">Batalkan Transaksi</button>
              <button class="btn btn-primary" name="action" value="{{($data['simpanan']->fid_status == 1 ? 'edit' : 'proses_ulang')}}">{{($data['simpanan']->fid_status == 1 ? 'Edit Transaksi' : 'Proses Ulang')}}</button>
            </div>
            @endif
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<form action="{{url('main/transaksi/proses_pembatalan')}}" id="proses_pembatalan" method="post">
  {{ csrf_field() }}
  <input type="hidden" name="modul" value="simpanan">
  <input type="hidden" name="id" value="{{$id}}">
</form>
<div id="modal-upload" class="modal fade bs-example-modal-center" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title mt-0">Upload Bukti Pembayaran</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{url('main/transaksi/upload')}}" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="modal-body">
          <input type="file" class="dropify" name="bukti_transaksi">
          <input type="hidden" name="id" value="{{$id}}">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
@section('js')
<script>
function batalkan(){
  Swal.fire({
    title: "Are you sure?",
    type:"question",
    text:"Apakah anda ingin membatalkan transaksi ini",
    showCancelButton: true,
    confirmButtonColor: '#16a085',
    cancelButtonColor: '#cbcbcb',
    confirmButtonText: 'Yes'
  }).then((result) => {
    if (result.value == true) {
      $('#proses_pembatalan').submit();
    }
  });
}
</script>
@endsection
