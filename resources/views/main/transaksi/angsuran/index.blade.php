@php
  $page='Angsuran';
  $subpage='Angsuran';
@endphp
@extends('layouts.main')
@section('title')
Angsuran |
@endsection
@section('css')
  <link href="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
@endsection
@section('content')
  <div class="content-breadcrumb mb-2">
    <div class="container-fluid">
      <div class="page-title-box pb-0">
        <div class="media">
          <img src="{{asset('assets/images/icon-page/pay-day.png')}}" class="avatar-md mr-3">
          <div class="media-body align-self-center">
            <h4 class="mb-0 font-size-18">Angsuran</h4>
            <p class="text-muted m-0">Menampilkan data angsuran pinjaman yang sudah diinput oleh petugas atau anggota</p>
          </div>
        </div>
      </div>
      <form action="{{url('main/transaksi/filter')}}" method="post" id="filter_transaksi" class="mt-5">
        @php($filter=Session::get('filter_transaksi'))
        {{ csrf_field() }}
        <div class="row">
          <div class="col-md-3">
            <select name="jenis" class="form-control select2" onchange="javascript:submit()">
              <option value="all"  >Semua Jenis</option>
              @foreach ($data['jenis-transaksi'] as $key => $value)
              <option value="{{$value->id}}" {{(!empty($filter['angsuran']) ? ($filter['angsuran']['jenis']==$value->id ? 'selected' : '') : '' )}}  >{{$value->jenis_transaksi}}</option>
              @endforeach
            </select>
          </div>
          {{-- <div class="col-md-2">
            <input type="hidden" id="status_id" name="status" value="{{(!empty($filter['angsuran']) ? $filter['angsuran']['status'] : 'all' )}}">
            <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
              <option value="#282828" data-id="all">Semua Status</option>
              @foreach ($data['status-transaksi'] as $key => $value)
              <option value="{{$value->color}}" {{(!empty($filter['angsuran']) ? ($filter['angsuran']['status']==$value->id ? 'selected' : '') : '' )}}  data-id="{{ $value->id}}" >{{$value->status_angsuran}}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <div>
              <div class="input-daterange input-group" data-date-format="dd-mm-yyyy" data-provide="datepicker">
                <input type="text" class="form-control" value="{{(!empty($filter['angsuran']) ? $filter['angsuran']['from'] : '' )}}" autocomplete="off" id="from" onchange="javascript:submit()" name="from" placeholder="Dari Tanggal" />
                <input type="text" class="form-control" value="{{(!empty($filter['angsuran']) ? $filter['angsuran']['to'] : '' )}}" autocomplete="off" id="to" onchange="javascript:submit()" name="to" placeholder="Sampai Tanggal" />
              </div>
            </div>
          </div> --}}
        </div>
        <input type="hidden" name="modul" value="angsuran">
      </form>
    </div>
  </div>
  <div class="container-fluid mt-4 mb-5">
    <div class="row">
      <div class="col">
        <div style="height:100%">
          @if(count($data['angsuran'])==0)
            <div style="width:100%;text-align:center" class="mb-5">
              <img src="{{asset('assets/images/not-found.png')}}" class="mt-3" style="width:180px">
              <h5 class="mt-3">Data Angsuran Tidak Ditemukan</h5>
            </div>
          @else
          <div class="table-responsive">
            <table class="table table-middle table-custom">
              <thead >
                <tr>
                  <th class="center">Angsuran Ke</th>
                  <th class="center">Bulan</th>
                  <th>Jenis Pinjaman</th>
                  <th style="text-align:right">Angsuran<br>Pokok<br></th>
                  <th style="text-align:right">Angsuran<br>Bunga<br></th>
                  <th style="text-align:right">Total<br>Angsuran<br></th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($data['angsuran'] as $key => $value)
                  <tr>
                    <td class="center" style="width:1px;white-space:nowrap;border-color:{{$value->color}}">{{$value->angsuran_ke}}</td>
                    <td class="center">{{\App\Helpers\GlobalHelper::nama_bulan($value->bulan)}}</td>
                    <td style="font-weight:500">{{$value->jenis_transaksi}}</td>
                    <td style="text-align:right">{{number_format(str_replace('-','',$value->angsuran_pokok),0,',','.')}}</td>
                    <td style="text-align:right">{{number_format(str_replace('-','',$value->angsuran_bunga),0,',','.')}}</td>
                    <td style="text-align:right">{{number_format(str_replace('-','',$value->total_angsuran),0,',','.')}}</td>
                    {{-- <td class="center">{{$value->sisa_tenor}} dari {{$value->tenor}}</td>
                    <td style="text-align:right">{{number_format(str_replace('-','',$value->sisa_pinjaman),0,',','.')}}</td> --}}
                    <td style="width:1px;white-space:nowrap">
                      <div class="text-center">
                        <a href="{{url('main/angsuran/detail?id='.$value->id)}}" class="text-dark"><i class="bx bx-search-alt h3 m-0"></i></a>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="mb-4">
            {{ $data['angsuran']->links('include.pagination', ['pagination' => $data['angsuran']] ) }}
          </div>
          @endif
        </div>
      </div>
      <div class="col-auto">
        <div style="border-left:1px solid #dedede;padding:20px 20px;height:100%;width:250px" class="mb-4">
          @foreach ($data['jenis-transaksi'] as $key => $value)
            <div class="form-group">
              <label>Angsuran {{str_replace('Pinjaman',' ',$value->jenis_transaksi)}}</label>
              <div class="font-size-13">Rp {{number_format($value->angsuran_pinjaman,0,',','.')}}</div>
            </div>
          @endforeach
          <div class="form-group">
            <label>Total Angsuran</label>
            <div class="font-size-13">Rp {{number_format($data['total-angsuran'],0,',','.')}}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')
  <script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
  <script src="{{asset('assets/js/pages/form-advanced.init.js')}}"></script>
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
    $('#filter_transaksi').submit();
  }
  </script>
@endsection
