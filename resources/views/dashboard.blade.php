@php
  $app='main_dashboard';
  $page='Dashboard';
  $subpage='Dashboard';
@endphp
@section('title')
  Dashboard |
@endsection
@extends('layouts.dashboard')
@section('css')
  <style>
  .nav-pills>li>a, .nav-tabs>li>a {
    color: #2f2f2f;
    font-weight: 400;
  }
  .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
    color: #fff;
    background-color: #1a4f73;
  }
  .nav-pills .nav-link {
    border-radius: 0px;
  }
  .card-title {
    font-size: 15px;
    margin: 0px;
    font-weight: 500;
    letter-spacing: 0.5px
  }
  .verti-timeline .event-list {
    position: relative;
    padding: 0 0 0px 20px;
  }
  .table-hover tr td{
    cursor: pointer;
  }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-3">
          <div class="media">
            <img src="{{asset('assets/images/icon-page/profile.png')}}" style="height:70px;margin-right:10px">
            <div class="media-body align-self-center">
              <p class="text-muted mb-1">Total Anggota</p>
              <h5 class="font-size-17">{{number_format($data['total-anggota'],0,',','.')}}</h5>
            </div>
          </div>
        </div>
        <div class="col-3">
          <div class="media">
            <img src="{{asset('assets/images/icon-page/wallet.png')}}" style="height:70px;margin-right:10px">
            <div class="media-body align-self-center">
              <p class="text-muted mb-1">Total Simpanan</p>
              <h5 class="font-size-17">Rp {{number_format($data['total-simpanan'],0,',','.')}}</h5>
            </div>
          </div>
        </div>
        <div class="col-3">
          <div class="media">
            <img src="{{asset('assets/images/icon-page/save-money.png')}}" style="height:70px;margin-right:10px">
            <div class="media-body align-self-center">
              <p class="text-muted mb-1">Total Pinjaman</p>
              <h5 class="font-size-17">Rp {{number_format(str_replace('-','',$data['total-pinjaman']),0,',','.')}}</h5>
            </div>
          </div>
        </div>
        <div class="col-3">
          <div class="media">
            <img src="{{asset('assets/images/icon-page/shopping-basket.png')}}" style="height:70px;margin-right:10px">
            <div class="media-body align-self-center">
              <p class="text-muted mb-1">Total Penjualan</p>
              <h5 class="font-size-17">Rp {{number_format($data['total-penjualan'],0,',','.')}}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-body p-0">
          <div class="p-3">
            <h4 class="card-title">Verifikasi Transaksi</h4>
          </div>
          <ul class="nav nav-pills" style="background:#f2f2f5" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" data-toggle="tab" href="#setoran" role="tab">Setoran</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#penarikan" role="tab">Penarikan</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#pinjaman" role="tab">Pinjaman</a>
            </li>
          </ul>
          <div class="table-responsive" data-simplebar style="height:220px;">
            <div class="tab-content">
              <div class="tab-pane active" id="setoran" role="tabpanel">
                @if(count($data['ver_setoran'])==0)
                <div style="width:100%;text-align:center">
                  <img src="{{asset('assets/images/icon-page/pay-per-click.png')}}" class="mt-5" style="width:80px">
                  <p class="font-size-14 mt-3">Data Setoran Simpanan<br>tidak Ditemukan</p>
                </div>
                @else
                <table class="table table-middle table-hover">
                  <tbody>
                    @foreach ($data['ver_setoran'] as $key => $value)
                      <tr onclick="location.href = '{{url('simpanan/sukarela/detail?id='.$value->id)}}'">
                        <td>
                          <div class="media">
                            <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                              <img src="{{asset('assets/images/user-avatar-placeholder.png')}}" alt="" class="rounded-circle">
                            </div>
                            <div class="media-body align-self-center">
                              <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                              <h5 class="text-truncate font-size-13">{{$value->nama_lengkap}}</h5>
                            </div>
                          </div>
                        </td>
                        <td style="text-align:right;">
                          <p class="text-muted mb-0">{{\App\Helpers\GlobalHelper::tgl_indo($value->tanggal)}}</p>
                          <h5 class="text-truncate font-size-13">Rp {{number_format($value->nominal,0,',','.')}}</h5>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
                @endif
              </div>
              <div class="tab-pane" id="penarikan" role="tabpanel">
                @if(count($data['ver_penarikan'])==0)
                <div style="width:100%;text-align:center">
                  <img src="{{asset('assets/images/icon-page/penarikan.png')}}" class="mt-5" style="width:80px">
                  <p class="font-size-14 mt-3">Data Penarikan Simpanan<br>tidak Ditemukan</p>
                </div>
                @else
                <table class="table table-middle table-hover">
                  <tbody>
                    @foreach ($data['ver_penarikan'] as $key => $value)
                      <tr onclick="location.href = '{{url('penarikan/sukarela/detail?id='.$value->id)}}'">
                        <td>
                          <div class="media">
                            <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                              <img src="{{asset('assets/images/user-avatar-placeholder.png')}}" alt="" class="rounded-circle">
                            </div>
                            <div class="media-body align-self-center">
                              <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                              <h5 class="text-truncate font-size-13">{{$value->nama_lengkap}}</h5>
                            </div>
                          </div>
                        </td>
                        <td style="text-align:right;">
                          <p class="text-muted mb-0">{{\App\Helpers\GlobalHelper::tgl_indo($value->tanggal)}}</p>
                          <h5 class="text-truncate font-size-13">Rp {{number_format(str_replace('-','',$value->nominal),0,',','.')}}</h5>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
                @endif
              </div>
              <div class="tab-pane" id="pinjaman" role="tabpanel">
                @if(count($data['ver_pinjaman'])==0)
                <div style="width:100%;text-align:center">
                  <img src="{{asset('assets/images/icon-page/save-money.png')}}" class="mt-5" style="width:80px">
                  <p class="font-size-14 mt-3">Data Pengajuan Pinjaman<br>tidak Ditemukan</p>
                </div>
                @else
                <table class="table table-middle table-hover">
                  <tbody>
                    @foreach ($data['ver_pinjaman'] as $key => $value)
                      <tr onclick="location.href = '{{url('pinjaman/pengajuan/detail?id'.$value->id)}}'">
                        <td>
                          <div class="media">
                            <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                              <img src="{{asset('assets/images/user-avatar-placeholder.png')}}" alt="" class="rounded-circle">
                            </div>
                            <div class="media-body align-self-center">
                              <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                              <h5 class="text-truncate font-size-13">{{$value->nama_lengkap}}</h5>
                            </div>
                          </div>
                        </td>
                        <td style="text-align:right;">
                          <p class="text-muted mb-0">{{\App\Helpers\GlobalHelper::tgl_indo($value->tanggal)}}</p>
                          <h5 class="text-truncate font-size-13">Rp {{number_format(str_replace('-','',$value->nominal),0,',','.')}}</h5>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-body p-0">
          <div class="p-3">
            <h4 class="card-title">Proses Payroll Angsuran</h4>
          </div>
          <ul class="nav nav-pills" style="background:#f2f2f5" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" data-toggle="tab" href="#payroll_simpanan" role="tab">Simpanan</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#payroll_pinjaman" role="tab">Pinjaman</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#payroll_belanja" role="tab">Belanja</a>
            </li>
          </ul>
          <div class="table-responsive" data-simplebar style="height:220px;">
            <div class="tab-content p-4">
              <div class="tab-pane active" id="payroll_simpanan" role="tabpanel">
                <div class="media mt-4">
                  <img src="{{asset('assets/images/icon-page/pay-day.png')}}" class="mr-3" style="width:80px" >
                  <div class="media-body align-self-center">
                    @if($data['bulan-payrol-simpanan']['status']=='belum')
                    <p style="font-size:14px" class="mt-2">
                      Silahkan Proses<br>Payroll Angsuran Simpanan<br>pada Bulan
                      <span class="font-weight-semibold">{{\App\Helpers\GlobalHelper::nama_bulan($data['bulan-payrol-simpanan']['bulan'])}}</span>
                    </p>
                    <button class="btn btn-primary" onclick="confirm_proses('simpanan')" >Proses Payroll</button>
                    @else
                    <p style="font-size:14px" class="mt-2">
                      Payroll Angsuran Simpanan<br>pada Bulan
                      <span class="font-weight-semibold">{{\App\Helpers\GlobalHelper::nama_bulan($data['bulan-payrol-simpanan']['bulan'])}}</span><br>
                      sudah selesai diproses
                    </p>
                    <button class="btn btn-primary" @if($data['bulan-payrol-simpanan']['status']=='disabled') disabled @else onclick="confirm_proses('simpanan')" @endif >Proses Ulang</button>
                    @endif
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="payroll_pinjaman" role="tabpanel">
                <div class="media mt-4">
                  <img src="{{asset('assets/images/icon-page/pay-day.png')}}" class="mr-3" style="width:80px" >
                  <div class="media-body align-self-center">
                    @if($data['bulan-payrol-pinjaman']['status']=='belum')
                    <p style="font-size:14px" class="mt-2">
                      Silahkan Proses<br>Payroll Angsuran Pinjaman<br>pada Bulan
                      <span class="font-weight-semibold">{{\App\Helpers\GlobalHelper::nama_bulan($data['bulan-payrol-pinjaman']['bulan'])}}</span>
                    </p>
                    <button class="btn btn-primary" onclick="confirm_proses('pinjaman')" >Proses Payroll</button>
                    @else
                    <p style="font-size:14px" class="mt-2">
                      Payroll Angsuran Pinjaman<br>pada Bulan
                      <span class="font-weight-semibold">{{\App\Helpers\GlobalHelper::nama_bulan($data['bulan-payrol-pinjaman']['bulan'])}}</span><br>
                      sudah selesai diproses
                    </p>
                    <button class="btn btn-primary" @if($data['bulan-payrol-pinjaman']['status']=='disabled') disabled @else onclick="confirm_proses('pinjaman')" @endif >Proses Ulang</button>
                    @endif
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="payroll_belanja" role="tabpanel">
                <div class="media mt-4">
                  <img src="{{asset('assets/images/icon-page/pay-day.png')}}" class="mr-3" style="width:80px" >
                  <div class="media-body align-self-center">
                    @if($data['bulan-payrol-belanja']['status']=='belum')
                    <p style="font-size:14px" class="mt-2">
                      Silahkan Proses<br>Payroll Angsuran Belanja<br>pada Bulan
                      <span class="font-weight-semibold">{{\App\Helpers\GlobalHelper::nama_bulan($data['bulan-payrol-belanja']['bulan'])}}</span>
                    </p>
                    <button class="btn btn-primary" onclick="confirm_proses('belanja')">Proses Payroll</button>
                    @else
                    <p style="font-size:14px" class="mt-2">
                      Payroll Angsuran Belanja<br>pada Bulan
                      <span class="font-weight-semibold">{{\App\Helpers\GlobalHelper::nama_bulan($data['bulan-payrol-belanja']['bulan'])}}</span><br>
                      sudah selesai diproses
                    </p>
                    <button class="btn btn-primary" @if($data['bulan-payrol-belanja']['status']=='disabled') disabled @else onclick="confirm_proses('belanja')" @endif >Proses Ulang</button>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      {{-- Posting Bunga --}}
      <div class="card">
        <div class="card-body">
          <div class="center" style="height:265px">
            <img src="{{asset('assets/images/icon-page/pay-day.png')}}" class="mt-4" style="width:80px" >
            @if($data['posting-bunga']['status']=='belum')
            <p style="font-size:14px" class="mt-2">Silahkan Posting<br>Bunga Simpanan Sukarela<br>tanggal <span class="font-weight-semibold">{{\App\Helpers\GlobalHelper::tgl_indo($data['posting-bunga']['tanggal'])}}</span></p>
            <button class="btn btn-primary" onclick="confirm_proses('posting_bunga')">Posting Bunga</button>
            @else
            <p style="font-size:14px" class="mt-2">Bunga Simpanan Sukarela<br>tanggal <span class="font-weight-semibold">{{\App\Helpers\GlobalHelper::tgl_indo($data['posting-bunga']['tanggal'])}}</span><br>sudah diposting</p>
            <button class="btn btn-primary" onclick="confirm_proses('posting_bunga')">Posting Ulang</button>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<form id="form_proses" method="post">
  {{ csrf_field() }}
  <input type="hidden" name="bulan" id="bulan">
  <input type="hidden" name="tanggal" id="tanggal">
</form>
@endsection
@section('js')
  <script>
  function confirm_proses(jenis){
    if(jenis=='simpanan'){
      text="Apakah anda yakin ingin memproses payroll simpanan anggota pada bulan {{\App\Helpers\GlobalHelper::nama_bulan($data['bulan-payrol-simpanan']['bulan'])}}";
      $('#bulan').val('{{$data['bulan-payrol-simpanan']['bulan']}}');
      $('#form_proses').attr('action', '{{url('simpanan/payroll/proses')}}');
    }
    else if(jenis=='pinjaman'){
      text="Apakah anda yakin ingin memproses payroll angsuran pinjaman pada bulan {{\App\Helpers\GlobalHelper::nama_bulan($data['bulan-payrol-pinjaman']['bulan'])}}";
      $('#bulan').val('{{$data['bulan-payrol-pinjaman']['bulan']}}');
      $('#form_proses').attr('action', '{{url('pinjaman/payroll/proses')}}');
    }
    else if(jenis=='belanja'){
      text="Apakah anda yakin ingin memproses payroll angsuran kredit belanja pada bulan {{\App\Helpers\GlobalHelper::nama_bulan($data['bulan-payrol-belanja']['bulan'])}}";
      $('#bulan').val('{{$data['bulan-payrol-belanja']['bulan']}}');
      $('#form_proses').attr('action', '{{url('pos/angsuran/proses')}}');
    }
    else{
      text="Apakah anda yakin ingin memposting bunga simpanan sukarela untuk tanggal {{\App\Helpers\GlobalHelper::tgl_indo($data['posting-bunga']['tanggal'])}} ini";
      $('#tanggal').val('{{\App\Helpers\GlobalHelper::dateFormat($data['posting-bunga']['tanggal'],'d-m-Y')}}');
      $('#form_proses').attr('action', '{{url('simpanan/bunga/proses')}}');
    }
    Swal.fire({
      title: "Are you sure?",
      text:text,
      type:"question",
      showCancelButton: true,
      confirmButtonColor: '#16a085',
      cancelButtonColor: '#cbcbcb',
      confirmButtonText: 'Proses Simpanan'
    }).then((result) => {
      if (result.value == true) {
        $('#form_proses').submit();
      }
    });
  }
  </script>
@endsection
