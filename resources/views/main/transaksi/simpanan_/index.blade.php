@php
  $page='Simpanan';
  $subpage='Simpanan';
@endphp
@extends('layouts.main')
@section('title')
Simpanan |
@endsection
@section('content')
<div class="container-fluid">
  <div class="content-breadcrumb mb-2">
    <div class="page-title-box">
      <div class="media">
        <img src="{{asset('assets/images/wallet.png')}}" class="avatar-md mr-3">
        <div class="media-body align-self-center">
          <h4 class="mb-0 font-size-18">Data Simpanan</h4>
          <p class="text-muted m-0">Menampilkan data setoran simpanan sukarela yang sudah diinput oleh petugas atau anggota</p>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h5>Formulir Setoran Simpanan</h5>
          <hr>
          <form action="{{url('transaksi/proses')}}" method="post">
            {{ csrf_field() }}
            <div class="form-group">
              <label>Jumlah Setoran</label>
              <input type="text" style="text-align:right" class="form-control autonumeric" data-a-dec="." data-a-sep="," name="nominal" value="0">
            </div>
            <div class="form-group">
              <label>Keterangan</label>
              <textarea name="keterangan" class="form-control" style="height:100px"></textarea>
            </div>
            <input type="hidden" name="jenis_transaksi" value="4">
            <input type="hidden" name="modul" value="simpanan">
            <div class="pull-right">
              <button class="btn btn-primary" name="action" value="add">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card">
        <div class="card-header p-3">
          <form action="{{url('transaksi/filter')}}" method="post" id="filter_transaksi">
            @php($filter=Session::get('filter_transaksi'))
            {{ csrf_field() }}
            <div class="row">
              <div class="col-md-4">
                <input type="hidden" id="status_id" name="status" value="{{(!empty($filter['simpanan']) ? $filter['simpanan']['status'] : 'all' )}}">
                <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
                  <option value="#282828" data-id="all">Semua Status</option>
                  @foreach ($data['status-transaksi'] as $key => $value)
                  <option value="{{$value->color}}" {{(!empty($filter['simpanan']) ? ($filter['simpanan']['status']==$value->id ? 'selected' : '') : '' )}}  data-id="{{ $value->id}}" >{{$value->status}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <div>
                  <div class="input-daterange input-group" data-date-format="dd-mm-yyyy" data-provide="datepicker">
                    <input type="text" class="form-control" value="{{(!empty($filter['simpanan']) ? $filter['simpanan']['from'] : '' )}}" autocomplete="off" id="from" onchange="javascript:submit()" name="from" placeholder="Dari Tanggal" />
                    <input type="text" class="form-control" value="{{(!empty($filter['simpanan']) ? $filter['simpanan']['to'] : '' )}}" autocomplete="off" id="to" onchange="javascript:submit()" name="to" placeholder="Sampai Tanggal" />
                  </div>
                </div>
              </div>
            </div>
            <input type="hidden" name="modul" value="simpanan">
            <input type="hidden" name="jenis" value='all'>
          </form>
        </div>
      </div>

      @if(count($data['simpanan'])==0)
      <div style="width:100%;text-align:center" class="mb-5">
        <img src="{{asset('assets/images/not-found.png')}}" class="mt-3" style="width:180px">
        <h5 class="mt-3">Data Setoran Simpanan Tidak Ditemukan</h5>
      </div>
      @else
      <div class="table-responsive">
        <table class="table table-middle table-custom">
          <thead class="thead-light">
            <tr>
              <th class="center">Tanggal</th>
              <th>Jenis Simpanan</th>
              <th>Metode Pembayaran</th>
              <th style="text-align:right">Jumlah Simpanan</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($data['simpanan'] as $key => $value)
              <tr>
                <td class="center" style="width:1px;white-space:nowrap;border-color:{{$value->color}}">{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')}}</td>
                <td>{{$value->jenis_transaksi}}</td>
                <td>{{$value->metode_transaksi}}</td>
                <td style="text-align:right">Rp {{number_format($value->nominal,0,',','.')}}</td>
                <td style="width:1px;white-space:nowrap">
                  <div class="text-center">
                    <a href="{{url('simpanan/detail?id='.$value->id)}}" class="text-dark"><i class="bx bx-search h3 m-0"></i></a>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="mb-4">
        {{ $data['simpanan']->links('include.pagination', ['pagination' => $data['simpanan']] ) }}
      </div>
      @endif
    </div>
  </div>
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
    $('#filter_transaksi').submit();
  }
  </script>
@endsection
