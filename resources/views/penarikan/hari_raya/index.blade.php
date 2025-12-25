@php
  $app='sinjam';
  $page='Penarikan Simpanan';
  $subpage='Simpanan Hari Raya';
@endphp
@extends('layouts.admin')
@section('title')
  Penarikan Simpanan |
@endsection
@section('content')
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/icon-page/penarikan.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Penarikan Simpanan Hari Raya</h4>
          <p class="text-muted m-0">Menampilkan data penarikan simpanan hari raya yang diproses oleh petugas setiap menjelang hari raya</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-2">
        <form action="" method="get">
          <select class="select2 form-control" name="id" onchange="javascript:submit()">
            <option value="add">Tambah Periode</option>
            @foreach ($data['periode'] as $key => $value)
            <option value="{{$value->id}}" {{($id==$value->id ? 'selected' : '')}} >{{$value->periode}} H</option>
            @endforeach
          </select>
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
        @if(!empty($data['detail-periode']))
        <button class="btn btn-primary btn-block" @if($data['detail-periode']->fid_status!=4) onclick="confirm_proses()" @else disabled @endif >Proses Penarikan</button>
        @else
        <button class="btn btn-primary btn-block" onclick="confirm_proses()">Proses Penarikan</button>
        @endif
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-8">
      <div style="height:100%">
        @if(count($data['penarikan'])==0)
        <div style="width:100%;text-align:center">
          <img src="{{asset('assets/images/not-found.png')}}" class="mt-5" style="width:200px">
          <h4 class="mt-3">Data Penarikan Simpanan Hari Raya<BR>Tidak Ditemukan</h4>
        </div>
        @else
        <div class="table-responsive">
          <table class="table table-middle table-custom">
            <thead>
              <tr>
                <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
                <th>Divisi<hr class="line-xs">Bagian</th>
                <th style="text-align:right">Jumlah<br>Penarikan</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data['penarikan'] as $key => $value)
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
                  <td>
                    <div>{{$value->divisi}}</div>
                    <div>{{$value->bagian}}</div>
                  </td>
                  <td style="text-align:right">{{number_format(str_replace('-','',$value->nominal),0,',','.')}}</td>
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
    </div>
    <div class="col-md-4">
      <div style="border-left:1px solid #dedede;padding:20px 20px;height:100%" class="mt-3 mb-3">
        <form action="{{url('penarikan/hari_raya/proses')}}" id="proses_penarikan" method="post">
          {{ csrf_field() }}
          <input type="hidden" name="id" value="{{$id}}">
          <input type="hidden" name="action" value="{{$action}}">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Tahun Hijriyah</label>
                <input type="text" class="form-control" name="periode" value="{{(!empty($data['detail-periode']) ? $data['detail-periode']->periode : '')}}" required >
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Tanggal</label>
                <input type="text" class="form-control datepicker" name="tanggal" value="{{(!empty($data['detail-periode']) ? \App\Helpers\GlobalHelper::dateFormat($data['detail-periode']->tanggal,'d-m-Y') : date('d-m-Y'))}}">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Total Anggota</label>
            <div class="font-size-13">{!!(!empty($data['detail-periode']) ? $data['detail-periode']->jumlah.' orang' : '<hr>')!!}</div>
          </div>
          <div class="form-group">
            <label>Total Penarikan</label>
            <div class="font-size-13">{!!(!empty($data['detail-periode']) ? 'Rp '.number_format(str_replace('-','',$data['detail-periode']->total),0,',','.') : '<hr>')!!}</div>
          </div>
          <div class="form-group">
            <label>Keterangan</label>
            <textarea type="text" class="form-control" name="keterangan" style="height:80px">{{(!empty($data['detail-periode']) ? $data['detail-periode']->keterangan : '')}}</textarea>
          </div>
          {{-- <button class="btn btn-danger btn-block">Hapus Penarikan</button> --}}
        </form>
      </div>
    </div>
  </div>
  @if(count($data['penarikan'])!=0)
  <div class="alert {{($data['detail-periode']->fid_status==1 ? 'alert-warning' : ($data['detail-periode']->fid_status==2 ? 'alert-danger' : 'alert-success'))}}  mt-3 mb-4" style="text-align:left" role="alert">
    <div class="media">
      <img src="{{asset('assets/images/'.$data['detail-periode']->icon)}}" class="align-self-center" style="width:90px;margin-right:20px">
      <div class="media-body align-self-center">
        <h5 class="mb-2">{{$data['keterangan']->label}}</h5>
        @if($data['detail-periode']->fid_status==1 )
        <p class="mb-2">Harap segera melakukan verifikasi pada proses penarikan simpanan hari raya ini.</p>
        <button class="btn btn-danger" onclick="confirm_verifikasi(2)">Ditolak</button>
        <button class="btn btn-primary" onclick="confirm_verifikasi(4)">Disetujui</button>
        @elseif($data['detail-periode']->fid_status==2 )
        <p class="mb-2">Terimakasih sudah melakukan verifikasi pada proses penarikan simpanan hari raya.<br>Silahkan proses ulang data penarikan simpanan hari raya</p>
        @else
        <p class="mb-2">Terimakasih sudah melakukan verifikasi pada proses penarikan simpanan hari raya.<br>Klik tombol dibawah ini untuk membatalkan verifikasi anda</p>
        <button class="btn btn-secondary" onclick="confirm_verifikasi(1)">Batalkan Verifikasi</button>
        @endif
      </div>
    </div>
  </div>
  <form action="{{url('penarikan/hari_raya/verifikasi')}}" method="post" id="verifikasi_transaksi" >
    {{ csrf_field() }}
    <input type="hidden" name="id" value="{{$id}}">
    <input type="hidden" name="status" id="status">
  </form>
  @endif
</div>
@endsection
@section('js')
<script>
function confirm_proses(){
  Swal.fire({
    title: "Are you sure?",
    text: "Apakah anda yakin ingin memproses penarikan simpanan hari raya untuk hari ini",
    type:"question",
    showCancelButton: true,
    confirmButtonColor: '#16a085',
    cancelButtonColor: '#cbcbcb',
    confirmButtonText: 'Proses Simpanan'
  }).then((result) => {
    if (result.value == true) {
      $('#proses_penarikan').submit();
    }
  });
}

function confirm_verifikasi(status){
  if(status==2){
    text="Apakah anda yakin ingin menolak transaksi penarikan simpanan hari raya ini?";
  }
  else if(status==3){
    text="Apakah anda yakin ingin menerima transaksi penarikan simpanan hari raya ini?";
  }
  else{
    text="Apakah anda yakin ingin membatalkan verifikasi transaksi penarikan simpanan hari raya?";
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
