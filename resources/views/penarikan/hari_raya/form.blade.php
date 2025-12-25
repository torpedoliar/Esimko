@php
  $page='Penarikan Simpanan';
  $subpage='Simpanan Hari Raya';
@endphp
@extends('layouts.admin')
@section('title')
  Penarikan Simpanan |
@endsection
@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-flex align-items-center justify-content-between">
        <h4 class="mb-0 font-size-18">Penarikan Simpanan Hari Raya</h4>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <div class="row">
        <div class="col-md-9">
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
          <button class="btn btn-primary btn-block" onclick="confirm_proses()" >Proses Penarikan</button>
        </div>
      </div>
    </div>
    <div class="card-body">
      <form action="{{url('penarikan/hari_raya/proses')}}" id="proses_penarikan" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="id" value="{{$data['periode']->id}}">
        <input type="hidden" name="action" value="payroll">
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>Tahun Hijriyah</label>
              <input type="text" class="form-control" name="periode" value="{{$data['periode']->periode}}" >
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Tanggal</label>
              <input type="text" class="form-control datepicker" name="tanggal" value="{{\App\Helpers\GlobalHelper::dateFormat($data['periode']->tanggal,'d-m-Y')}}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Keterangan</label>
              <input type="text" class="form-control" name="keterangan" value="{{$data['periode']->keterangan}}">
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-middle table-bordered table-hover">
            <thead class="thead-light">
              <tr>
                <th scope="col" class="center" style="width:50px">No</th>
                <th scope="col" class="center" style="width:130px">Tanggal</th>
                <th scope="col">No. Anggota<hr style="margin-top: 0.5rem;margin-bottom: 0.5rem;">Nama Lengkap</th>
                <th>Divisi / Bagian</th>
                <th scope="col" style="text-align:right">Nominal</th>
                <th scope="col" class="center"  style="width:150px">Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data['penarikan'] as $key => $value)
                <tr>
                  <td class="center">{{ $data['penarikan']->firstItem() + $key }}</td>
                  <td class="center">{{\App\Helpers\GlobalHelper::tgl_indo($value->tanggal)}}</td>
                  <td>
                    <div class="media">
                      <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle img-thumbnail avatar-sm mr-2">
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
                  <td class="center">
                    <a href="javascript:;" onclick="confirm({{ $value->id }})" style="background:{{$value->color}};padding:3px 6px;color:#fff;font-size:11px">{{$value->status}}</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div style="margin-top:20px">
          {{ $data['penarikan']->links('include.pagination', ['pagination' => $data['penarikan']] ) }}
        </div>
      </form>
      <div class="alert alert-warning mt-3 mb-0" style="text-align:left" role="alert">
        <h5 class="mb-2">Verifikasi Penarikan Simpanan Hari Raya</h5>
        @if($data['periode']->fid_status==1 )
        <p class="mb-2">Harap segera melakukan verifikasi pada proses penarikan simpanan hari raya ini.</p>
        <button class="btn btn-danger" onclick="confirm_verifikasi(2)">Ditolak</button>
        <button class="btn btn-success" onclick="confirm_verifikasi(4)">Disetujui</button>
        @else
        <p class="mb-2">Terimakasih sudah melakukan verifikasi pada proses penarikan simpanan hari raya.<br>Klik tombol dibawah ini untuk membatalkan verifikasi anda</p>
        <button class="btn btn-warning" onclick="confirm_verifikasi(1)">Batalkan Verifikasi</button>
        @endif
      </div>

      <form action="{{url('penarikan/hari_raya/verifikasi')}}" method="post" id="verifikasi_transaksi" >
        {{ csrf_field() }}
        <input type="hidden" name="id" value="{{$data['periode']->id}}">
        <input type="hidden" name="status" id="status">
      </form>

    </div>
    <div class="card-footer">
      <a href="{{url('penarikan/hari_raya')}}" class="btn btn-dark pull-right" >Kembali</a>
    </div>
  </div>
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
