@php
  $subpage='Belanja '.ucfirst($jenis);
  $keterangan='Halaman riwayat belanja '.$jenis.' anggota';
@endphp
@extends('main.belanja.layout')
@section('content_belanja')
<div class="card">
  <div class="card-header">
    <div class="row">
      <div class="col-md-3">
        <form action="" method="get" id="filter_transaksi" >
          <input type="hidden" id="status_id" name="status" value="{{(!empty($filter['pinjaman']) ? $filter['pinjaman']['status'] : 'all' )}}">
          <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
            <option value="#282828" data-id="all">Semua Status</option>
            @foreach ($data['status'] as $key => $value)
            <option value="{{$value->color}}" {{($status==$value->id ? 'selected' : '' )}}  data-id="{{ $value->id}}" >{{$value->status}}</option>
            @endforeach
          </select>
        </form>
      </div>
      <div class="{{($jenis=='toko' ? 'col-md-9' : 'col-md-9')}}">
        <form action="" method="get">
          <div class="input-group">
            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Search Transaksi Belanja">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@if(count($data['penjualan'])==0)
<div style="width:100%;text-align:center" class="mb-5">
  <img src="{{asset('assets/images/not-found.png')}}" class="mt-3" style="width:180px">
  <h5 class="mt-3">Data Belanja Tidak Ditemukan</h5>
</div>
@else
<div class="table-responsive">
  <table class="table table-middle table-custom">
    <thead>
      <tr>
        <th>Waktu</th>
        <th>No. Transaksi</th>
        <th class="center">Metode<br>Pembayaran</th>
        <th style="text-align:right">Total<br>Belanja</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach ($data['penjualan'] as $key => $value)
        <tr>
          <td style="border-color:{{$value->color}}">{{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'d/m/Y')}}<br>{{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')}}</td>
          <td><h6>{{$value->no_transaksi}}</h6></td>
          <td class="center">{{$value->metode_pembayaran}}</td>
          <td style="text-align:right">Rp {{number_format(str_replace('-','',$value->total_pembayaran),0,',','.')}}</td>
          <td style="width:1px;white-space:nowrap">
            <div class="text-center">
              <a href="{{url('main/belanja/riwayat/'.$jenis.'/detail?id='.$value->id)}}" class="text-dark"><i class="bx bx-search h3 m-0"></i></a>
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
<div style="margin-top:20px">
  {{ $data['penjualan']->links('include.pagination', ['pagination' => $data['penjualan']] ) }}
</div>
@endif
@endsection
@section('add_js')
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
