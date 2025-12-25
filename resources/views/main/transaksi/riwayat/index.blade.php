@php
  $page='Transaksi';
  $subpage='Riwayat';
@endphp
@extends('layouts.main')
@section('title')
Riwayat Transaksi |
@endsection
@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-flex align-items-center justify-content-between">
        <h4 class="mb-0 font-size-18">Riwayat Transaksi</h4>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-pills" role="tablist">
        <li class="nav-item waves-effect waves-light">
          <a class="nav-link active" data-toggle="tab" href="#simpanan" role="tab">
            <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
            <span class="d-none d-sm-block">Simpanan</span>
          </a>
        </li>
        <li class="nav-item waves-effect waves-light">
          <a class="nav-link" data-toggle="tab" href="#pinjaman" role="tab">
            <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
            <span class="d-none d-sm-block">Pinjaman</span>
          </a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      <div class="tab-content">
        <div class="tab-pane active" id="simpanan" role="tabpanel">
          <form action="" method="get" class="mb-3">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Jenis Simpanan</label>
                  <select name="metode_transaksi" class="form-control select2">
                    <option value="all"  >Semua Jenis</option>
                    @foreach ($data['jenis-transaksi-simpanan'] as $key => $value)
                    <option value="{{$value->id}}"  >{{$value->jenis_transaksi}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Tanggal Transaksi</label>
                  <div>
                    <div class="input-daterange input-group" data-date-format="dd-mm-yyyy" data-provide="datepicker">
                      <input type="text" class="form-control" autocomplete="off" id="date_from" name="date_from" placeholder="Date From" />
                      <input type="text" class="form-control" autocomplete="off" id="date_to" name="date_to" placeholder="Date To" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Sisa Saldo Simpanan</label>
                  <h5 style="font-size:25px"><i class="bx bx-wallet text-success align-middle mr-1" style="font-size:35px"></i> Rp 2.000.000</h5>
                </div>
              </div>
            </div>
          </form>
          <table class="table table-middle table-bordered table-hover mt-3">
            <thead class="thead-light">
              <tr>
                <th>No</th>
                <th class="center">Tanggal</th>
                <th>Jenis Transaksi</th>
                <th style="text-align:right">Debit</th>
                <th style="text-align:right">Kredit</th>
                <th style="text-align:right">Saldo</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data['simpanan'] as $key => $value)
              <tr>
                <td style="width:1px;white-space:nowrap">{{$key+1}}</td>
                <td class="center" style="width:1px;white-space:nowrap">{{\App\Helpers\GlobalHelper::tgl_indo($value->tanggal)}}</td>
                <td>{{$value->jenis_transaksi}}</td>
                <td style="text-align:right;width:1px;white-space:nowrap">{{number_format(str_replace('-','',$value->debit),'0',',','.')}}</td>
                <td style="text-align:right;width:1px;white-space:nowrap">{{number_format(str_replace('-','',$value->kredit),'0',',','.')}}</td>
                <th style="text-align:right;width:1px;white-space:nowrap">{{number_format($value->saldo,'0',',','.')}}</th>
                <td>{{$value->keterangan}}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <div style="margin-top:20px">
            {{ $data['simpanan']->links('include.pagination', ['pagination' => $data['simpanan']] ) }}
          </div>
        </div>
        <div class="tab-pane" id="pinjaman" role="tabpanel">
          <form action="" method="get" class="mb-3">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Jenis Pinjaman</label>
                  <select name="metode_transaksi" class="form-control select2" style="width:100%">
                    <option value="all"  >Semua Jenis</option>
                    @foreach ($data['jenis-transaksi-pinjaman'] as $key => $value)
                    <option value="{{$value->id}}"  >{{$value->jenis_transaksi}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Tanggal Transaksi</label>
                  <div>
                    <div class="input-daterange input-group" data-date-format="dd-mm-yyyy" data-provide="datepicker">
                      <input type="text" class="form-control" autocomplete="off" id="date_from" name="date_from" placeholder="Date From" />
                      <input type="text" class="form-control" autocomplete="off" id="date_to" name="date_to" placeholder="Date To" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
          <table class="table table-middle table-bordered table-hover mt-3">
            <thead class="thead-light">
              <tr>
                <th>No</th>
                <th class="center">Tanggal</th>
                <th>Jenis<br>Transaksi</th>
                <th style="text-align:right">Debit</th>
                <th style="text-align:right">Kredit</th>
                <th style="text-align:right">Sisa Hutang</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data['pinjaman'] as $key => $value)
              <tr>
                <td>{{$key+1}}</td>
                <td class="center">{{\App\Helpers\GlobalHelper::tgl_indo($value->tanggal)}}</td>
                <td>{{$value->jenis_transaksi}}</td>
                <td style="text-align:right">{{number_format(str_replace('-','',$value->debit),'0',',','.')}}</td>
                <td style="text-align:right">{{number_format(str_replace('-','',$value->kredit),'0',',','.')}}</td>
                <td style="text-align:right">{{number_format(str_replace('-','',$value->saldo),'0',',','.')}}</td>
                <td>{{$value->keterangan}}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <div style="margin-top:20px">
            {{ $data['pinjaman']->links('include.pagination', ['pagination' => $data['pinjaman']] ) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
