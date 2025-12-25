@php
  $page='Pinjaman';
  $subpage='Pinjaman';
@endphp
@extends('layouts.main')
@section('title')
Pinjaman |
@endsection
@section('css')
  <link href="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
@endsection
@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box d-flex align-items-center justify-content-between">
        <h4 class="mb-0 font-size-18">Pinjaman Anggota</h4>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <h5>Edit Formulir Pinjaman</h5>
        </div>
        <div class="card-body">
          <form action="{{url('transaksi/proses')}}" method="post">
            {{ csrf_field() }}
            <div class="form-group">
              <label>Jenis Pinjaman</label>
              <select name="jenis_transaksi" class="form-control select2">
                @foreach ($data['jenis-transaksi'] as $key => $value)
                <option value="{{$value->id}}" {{($data['pinjaman']->fid_jenis_transaksi == $value->id ? 'selected' : '') }}   >{{$value->jenis_transaksi}}</option>
                @endforeach
              </select>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Pinjaman (Rp)</label>
                  <input type="text" value="{{str_replace('-','',$data['pinjaman']->nominal)}}" style="text-align:right" class="form-control autonumeric" data-a-dec="." data-a-sep="," name="nominal" >
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label">Tenor (bulan)</label>
                  <input data-toggle="touchspin" name="tenor" value="{{$data['pinjaman']->tenor}}" type="text" value="0" data-max='100'>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Gaji Pokok (Rp)</label>
                  <input type="text" style="text-align:right" class="form-control autonumeric" data-a-dec="." data-a-sep="," name="gaji_pokok" value="{{$data['gaji-pokok'][1]}}">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Gaji Bulan</label>
                  <input type="text" class="form-control" name="bulan" value="{{$data['gaji-pokok'][0]}}" readonly >
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Upload Slip Gaji</label>
              <div class="custom-file">
                <input type="file" class="custom-file-input" id="customFile" name="attachment">
                <label class="custom-file-label" for="customFile">Choose file</label>
              </div>
            </div>
            <div class="form-group">
              <label>Keterangan</label>
              <textarea name="keterangan" class="form-control" style="height:110px"></textarea>
            </div>
            <input type="hidden" name="modul" value="pinjaman">
            <input type="hidden"name="metode_transaksi" value="3">
            <input type="hidden"name="id" value="{{$id}}">
            <div class="pull-right">
              <button class="btn btn-primary" name="action" value="edit">Hitung Angsuran</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <div class="center mb-5">
            <img src="{{asset('assets/images/question.png')}}" style="width:80px">
            <h4 class="mt-3">Konfirmasi Angsuran Pinjaman</h4>
            <p>Silahkan melakukan konfirmasi terhadap angsuran pinjaman anda</p>
          </div>
          <table class="table table-middle table-bordered table-hover mt-3">
            <thead class="thead-light">
              <tr>
                <th style="width:1px;white-space:nowrap">Ke</th>
                <th style="text-align:right">Sisa<br>Pinjaman</th>
                <th class="center">Bunga<br>(%)</th>
                <th style="text-align:right">Angsuran<br>Pokok</th>
                <th style="text-align:right">Angsuran<br>Bunga</th>
                <th style="text-align:right">Total<br>Angsuran</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data['angsuran'] as $key => $value)
                <tr>
                  <td>{{$value->angsuran_ke}}</td>
                  <td style="text-align:right">{{number_format($value->sisa_hutang,'0',',','.')}}</td>
                  <td class="center">{{$value->bunga}}</td>
                  <td style="text-align:right">{{number_format($value->angsuran_pokok,'0',',','.')}}</td>
                  <td style="text-align:right">{{number_format($value->angsuran_bunga,'0',',','.')}}</td>
                  <td style="text-align:right">{{number_format($value->angsuran_pokok+$value->angsuran_bunga,'0',',','.')}}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
          {!!$data['status']!!}
        </div>
        <div class="card-footer">
          <div class="pull-right">
            <a href="{{url('pinjaman')}}" class="btn btn-dark ">Kembali</a>
            <button @if($data['status']==null) onclick="confirm_pinjaman()" @else disabled @endif class="btn btn-primary">Konfirmasi</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<form action="{{url('pinjaman/konfirmasi_angsuran')}}" method="post" id="konfirmasi_angsuran">
  {{ csrf_field() }}
  <input type="hidden"name="modul" value="pinjaman">
  <input type="hidden"name="id" value="{{$id}}">
</form>
@endsection
@section('js')
  <script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
  <script src="{{asset('assets/js/pages/form-advanced.init.js')}}"></script>
  <script>
  function confirm_pinjaman(){
    Swal.fire({
      title: "Are you sure?",
      type:"question",
      text:'Apakah anda yakin melakukan pinjaman dengan nilai tersebut',
      showCancelButton: true,
      confirmButtonColor: '#16a085',
      cancelButtonColor: '#cbcbcb',
      confirmButtonText: 'Yes'
    }).then((result) => {
      if (result.value == true) {
        $('#konfirmasi_angsuran').submit();
      }
    });
  }
  </script>
@endsection
